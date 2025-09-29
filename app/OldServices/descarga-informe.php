<?php

/**
 * Arma un archivo PDF a partir de un array multidimensional y lo envía por el
 * navegador.
 *
 * El array puede tener una o más de estas estructuras:
 *
 * {
 *     'titulo': (string) Titulo de la sección (texto azul en negrita)
 *     'contenido': [{SECCION}, {SECCION}, {SECCION}, ...],
 * }
 *
 * Cada SECCION debe ser un array asociativo como el siguiente:
 * 
 * {
 *     'tipo': (string) Tipo de contenido, puede ser 'tabla' o 'parrafo'.
 *     
 *     'cabeceras' (array) Array de arrays que representan filas <tr> dentro de
 *                         <thead>. Sólo se usa cuando 'tipo' es 'tabla'.
 *
 *     'contenido': (string/array) Si 'tipo' es 'parrafo' se debe usar un string.
 *                                 Si 'tipo' es tabla se debe usar un array de
 *                                 arrays, que representa filas <tr> dentro de
 *                                 <tbody>.
 * },
 *
 * Por ejemplo:
 * {
 *     'tipo': 'parrafo',
 *     'contenido': '<p>Contenido del parrafo con
 *                     <strong style="color:red">reducido soporte HTML y
 *                     CSS</strong>. Debe estar bien formateado</p>'
 * },
 * {
 *     'tipo': 'tabla',
 *     'cabeceras': [
 *         [ CELDA, CELDA, CELDA, ],
 *         ...
 *     ],
 *     'contenido': [
 *         [ CELDA, CELDA, CELDA, ],
 *         [ CELDA, CELDA, CELDA, ],
 *         [ CELDA, CELDA, CELDA, ],
 *         ...
 *     ]
 * }
 * 
 * Cada CELDA puede ser un string representando el valor de la misma (el contenido
 * de <td>), ejemplo:
 * 
 * 'contenido':
 *     [
 *         [
 *             '10/10',
 *             'Oferente 1',
 *         ],
 *         [
 *             '11/10',
 *             'Oferente 2',
 *         ],
 *     ],
 *     ...
 *
 * O si se requiere personalizar la CELDA se puede usar un array, ejemplo:
 *
 * 'contenido':
 *     [
 *         [
 *             '10/10',
 *             'Oferente 1',
 *         ],
 *         [
 *             '11/10',
 *             {
 *                 'valor': 'Oferente 2',
 *                 'css': {
 *                     'background-color: green',
 *                     'color': 'red',
 *                     'font-weight': 'bold',
 *                 },
 *                 'attr': {
 *                     'align': 'center'
 *                 },
 *             },
 *         ],
 *     ],
 *
 *
 * ===============================
 * EL SOPORTE HTML/CSS ES REDUCIDO
 * ===============================
 *     - no acepta:
 *         - margin
 *         - padding
 *         - float
 *         - background (pero sí background-color)
 *
 *     - Tags soportados: a, b, blockquote, br, dd, del, div, dl, dt, em, font,
 *                        h1, h2, h3, h4, h5, h6, hr, i, img, li, ol, p, pre,
 *                        small, span, strong, sub, sup, table, tcpdf, td, th,
 *                        thead, tr, tt, u, ul
 *
 */

require 'rest.php';
require 'tcpdf/tcpdf.php';
require 'calculos-etapas.php';

header("Access-Control-Allow-Origin: *");
// header("Content-type: application/pdf");

function formatearFecha($string) 
{
    $array = explode('-', $string);
    if (count($array) === 3) {
        $string = $array[2] . '/' . $array[1] . '/' . $array[0];
    }
    return $string;
}

class DataInforme extends Rest 
{
    private $concurso = null;
    private $rondaActualConcurso = null;
    private $rondaConcursoArrayResult = null;


    public function __construct($idConcurso) 
    {

        $this->concurso = \App\Models\Concurso::find(abs(intval($idConcurso)));
        $this->rondaActualConcurso = $this->concurso->ronda_actual;
        $this->rondaConcursoArrayResult = $this->rondaActualConcurso - 1;

        

        parent::__construct();

        // Debug
        $this->_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Requerido para usar calcularEtapaAnalisisOfertas()
     */
    public function reverse($date) 
    {
        $d = explode('-', $date);
        return $d[2] . "-" . $d[1] . "-" . $d[0];
    }

    // 1) Sección de Preparación
    private function seccionPreparacion()
    {
        $primero = $this->concurso->oferentes->first();
        $fechaConvocatoria = '';
        if ($primero && isset($primero->invitation->created_at)) {
            $fechaConvocatoria = $primero->invitation->created_at->format('d/m/Y');
        }

        $archivos = [];
        foreach ($this->concurso->sheets as $sheet) {
            $archivos[] = [
                $sheet->type->description ?? '',
                sprintf('<a target="_blank" href="%1$s">Enlace</a>', $sheet->filename)
            ];
        }

        return [
            'titulo'   => 'Preparación',
            'contenido'=> [
                [
                    'tipo'     => 'parrafo',
                    'contenido'=> '<p>Fecha de envío de invitaciones: ' . $fechaConvocatoria . '</p>',
                ],
                [
                    'tipo'     => 'tabla',
                    'cabeceras'=> [['Documento / Archivo', 'URL']],
                    'contenido'=> $archivos,
                ],
            ]
        ];
    }

    // 2) Sección de Parámetros
    private function seccionParametros()
    {
        $c = $this->concurso;
        // Helper para formatear fechas
        $fmt = function($dt) {
            if ($dt instanceof \Carbon\Carbon) {
                return $dt->format('d/m/Y H:i:s');
            }
            if (is_string($dt) && trim($dt) !== '') {
                return \Carbon\Carbon::parse($dt)->format('d/m/Y H:i:s');
            }
            return 'No aplica.';
        };

        $contenido = [];
        $nuevasRondas = \App\Models\Concurso::NUEVAS_RONDAS;
        $fechasNuevasRondas = \App\Models\Concurso::CAMPOS_FECHA_NUEVA_RONDA;

        $contenido[] = ['Tipo de convocatoria', $c->alcance->nombre ?? ''];
        $contenido[] = ['Fecha cierre muro consultas', $fmt($c->finalizacion_consultas)];
        $contenido[] = ['Fecha aceptación pliegos, términos y condiciones', $fmt($c->fecha_limite)];
        $contenido[] = ['Fecha límite oferta técnica',
            $c->technical_includes ? $fmt($c->ficha_tecnica_fecha_limite) : 'No aplica.'];
        $contenido[] = ['Fecha límite oferta económica',
            $c->is_online ? $fmt($c->inicio_subasta) : $fmt($c->fecha_limite_economicas)];
        $contenido[] = ['Cantidad de Rondas ejecutadas', (string)$c->ronda_actual];

        if ($this->rondaActualConcurso > 1) {
            for ($i = 2; $i <= $this->rondaActualConcurso; $i++) {
                $campo = $fechasNuevasRondas[$i] ?? null;
                $contenido[] = [
                    'Fecha límite ' . ($nuevasRondas[$i] ?? ''),
                    $campo ? $fmt($c->{$campo}) : 'No aplica.'
                ];
            }
        }

        $contenido[] = ['Adjudicación anticipada', ucfirst($c->finalizar_si_oferentes_completaron_economicas)];
        $contenido[] = ['Fecha Cancelación', $c->trashed() ? $fmt($c->deleted_at) : ''];
        $contenido[] = ['Usuario Cancelación',
            $c->trashed()
                ? ($c->usuario_cancelacion->full_name ?? 'Proceso automático')
                : ''];

        return [
            'titulo'   => '1. Parámetros del concurso',
            'contenido'=> [[
                'tipo'      => 'tabla',
                'cabeceras' => [['Parámetro','Valor / Configuración']],
                'contenido' => $contenido,
            ]]
        ];
    }

    // 3) Sección de Convocatoria de Proveedores
    private function seccionConvocatoriaOferentes()
    {
        $filas = [];
        foreach ($this->concurso->oferentes as $oferente) {
            // Fecha invitación
            $fechaInvitacion = '';
            if (isset($oferente->invitation->created_at)) {
                $fechaInvitacion = $oferente->invitation->created_at->format('d/m/Y');
            }
            // Términos y condiciones
            $terminos = $oferente->invitation->status->description ?? '';
            // Fecha aceptación términos
            $fechaAceptacion = '';
            if (isset($oferente->invitation->updated_at)) {
                $fechaAceptacion = $oferente->invitation->updated_at->format('d/m/Y');
            }
            $filas[] = [
                $oferente->company->business_name ?? '',
                $fechaInvitacion,
                $terminos,
                $fechaAceptacion,
            ];
        }

        return [
            'titulo'   => '2. Convocatoria de Proveedores',
            'contenido'=> [[
                'tipo'      => 'tabla',
                'cabeceras' => [[
                    'Proveedor',
                    'Fecha invitación',
                    'Términos y condiciones',
                    'Fecha aceptación términos y condiciones',
                ]],
                'contenido' => $filas,
            ]]
        ];
    }

    private function seccionPresentacionTecnica()
    {
        if (! $this->concurso->technical_includes) {
            return [
                'titulo'   => '3. Presentación etapa técnica',
                'contenido'=> [[
                    'tipo'     => 'parrafo',
                    'contenido'=> '<em>No se realizó etapa técnica para este concurso.</em>',
                ]]
            ];
        }

        $oferentes = $this->concurso->oferentes;

        // Determinar ronda técnica máxima
        $rondaMaximaTecnica = 1;
        foreach ($oferentes as $oferente) {
            if ($oferente->ronda_tecnica > $rondaMaximaTecnica) {
                $rondaMaximaTecnica = $oferente->ronda_tecnica;
            }
        }

        $contenido              = [];
        $comentariosDeclinacion = [];
        $comentariosReprobacion = [];

        $rondasTitulos = [
            1 => '1ª Ronda Técnica',
            2 => '2ª Ronda Técnica',
            3 => '3ª Ronda Técnica',
            4 => '4ª Ronda Técnica',
            5 => '5ª Ronda Técnica',
        ];

        for ($ronda = 1; $ronda <= $rondaMaximaTecnica; $ronda++) {
            $tabla          = [];
            $estadosConEnvio = [
                'Se le solicitó otra ronda técnica',
                'No fue calificada',
                'Aprobado',
                'Reprobado',
            ];

            foreach ($oferentes as $oferente) {
                $res = $this->determinarEstadoTecnico($oferente, $ronda);
                if ($res === null) {
                    continue;
                }

                $estado = $res['estado'];

                // Acumular motivos
                if ($estado === 'Declinó' && $oferente->reasonDeclination) {
                    $comentariosDeclinacion[$oferente->company->business_name] = $oferente->reasonDeclination;
                }
                if ($estado === 'Reprobado' && ! empty($res['comentario'])) {
                    $comentariosReprobacion[$oferente->company->business_name] = $res['comentario'];
                }

                // Cálculo de fecha según estado y ronda
                $fecha = 'No se registro fecha';
                if (in_array($estado, $estadosConEnvio, true)) {
                    $camposFecha = [
                        1 => 'fecha_primera_ronda_tecnica',
                        2 => 'fecha_segunda_ronda_tecnica',
                        3 => 'fecha_tercera_ronda_tecnica',
                        4 => 'fecha_cuarta_ronda_tecnica',
                        5 => 'fecha_quinta_ronda_tecnica',
                    ];
                    $campo = $camposFecha[$ronda] ?? null;
                    if ($campo && isset($oferente->$campo)) {
                        $valorCampo = $oferente->$campo;
                        if ($valorCampo instanceof \Carbon\Carbon) {
                            $fecha = $valorCampo->format('d/m/Y H:i');
                        } elseif (is_string($valorCampo) && trim($valorCampo) !== '') {
                            $fecha = \Carbon\Carbon::parse($valorCampo)->format('d/m/Y H:i');
                        }
                    }
                } elseif ($estado === 'Declinó') {
                    $decl = $oferente->fecha_declination ?? null;
                    if ($decl instanceof \Carbon\Carbon) {
                        $fecha = $decl->format('d/m/Y H:i');
                    } elseif (is_string($decl) && trim($decl) !== '') {
                        $fecha = \Carbon\Carbon::parse($decl)->format('d/m/Y H:i');
                    }
                }

                $tabla[] = [
                    $oferente->company->business_name ?? '',
                    $estado,
                    $fecha,
                ];
            }

            if (! empty($tabla)) {
                $contenido[] = [
                    'tipo'     => 'parrafo',
                    'contenido'=> '<div style="font-weight:bold">' . $rondasTitulos[$ronda] . '</div>',
                ];
                $contenido[] = [
                    'tipo'     => 'tabla',
                    'cabeceras'=> [['Proveedor', 'Estado', 'Fecha']],
                    'contenido'=> $tabla,
                ];
            }
        }

        // Párrafo de declinación
        if (! empty($comentariosDeclinacion)) {
            $lineas = array_map(
                function ($motivo, $prov) {
                    return 'Motivo de declinación "' . $prov . '": ' . $motivo;
                },
                $comentariosDeclinacion,
                array_keys($comentariosDeclinacion)
            );

            $contenido[] = [
                'tipo'     => 'parrafo',
                'contenido'=> '<div style="font-style:italic; margin-top:10px;"><strong>Motivos de declinación:</strong><br>'
                    . implode('<br>', $lineas)
                    . '</div>',
            ];
        }

        // Párrafo de reprobación
        if (! empty($comentariosReprobacion)) {
            $lineas = array_map(
                function ($motivo, $prov) {
                    return 'Motivo de reprobación "' . $prov . '": ' . $motivo;
                },
                $comentariosReprobacion,
                array_keys($comentariosReprobacion)
            );

            $contenido[] = [
                'tipo'     => 'parrafo',
                'contenido'=> '<div style="font-style:italic; margin-top:10px;"><strong>Motivos de reprobación:</strong><br>'
                    . implode('<br>', $lineas)
                    . '</div>',
            ];
        }

        return [
            'titulo'   => '3. Presentación etapa técnica',
            'contenido'=> $contenido,
        ];
    }

    private function determinarEstadoTecnico($oferente, $ronda)
    {
        $estado         = $oferente->etapa_actual;
        $rondaProveedor = (int) $oferente->ronda_tecnica;

        // 1) Excluir quienes no llegaron a técnica
        if (in_array($estado, [
            'seleccionado',
            'invitacion-pendiente',
            'invitacion-rechazada',
        ])) {
            return null;
        }

        // 2) Grupos de estados
        $pendientes  = [
            'tecnica-pendiente','tecnica-pendiente-2','tecnica-pendiente-3',
            'tecnica-pendiente-4','tecnica-pendiente-5'
        ];
        $presentados = [
            'tecnica-presentada','tecnica-presentada-2','tecnica-presentada-3',
            'tecnica-presentada-4','tecnica-presentada-5'
        ];
        $declinados  = [
            'tecnica-declinada','tecnica-declinada-2','tecnica-declinada-3',
            'tecnica-declinada-4','tecnica-declinada-5'
        ];
        $posteriores = [
            'economica-pendiente','economica-pendiente-2','economica-pendiente-3',
            'economica-pendiente-4','economica-pendiente-5','economica-2da-pendiente',
            'economica-presentada','economica-revisada','economica-declinada',
            'adjudicacion-pendiente','adjudicacion-aceptada','adjudicacion-rechazada',
            'estrategia-aceptada','estrategia-rechazada',
        ];

        // 3) Caso posterior a técnica
        if (in_array($estado, $posteriores)) {
            if ($ronda < $rondaProveedor) {
                return ['estado' => 'Se le solicitó otra ronda técnica'];
            }
            if ($ronda == $rondaProveedor) {
                return ['estado' => 'Aprobado'];
            }
            return null;
        }

        // 4) Durante la etapa técnica
        $todosTecn = array_merge($pendientes, $presentados, $declinados);
        if (in_array($estado, $todosTecn)) {
            if ($ronda < $rondaProveedor) {
                return ['estado' => 'Se le solicitó otra ronda técnica'];
            }
            if ($ronda == $rondaProveedor) {
                if (in_array($estado, $pendientes)) {
                    return ['estado' => 'No presentó técnica'];
                }
                if (in_array($estado, $presentados)) {
                    if ($oferente->rechazado && is_array($oferente->analisis_tecnica_valores)) {
                        $vals = $oferente->analisis_tecnica_valores;
                        if (isset($vals[0]['comentario']) && trim($vals[0]['comentario']) !== '') {
                            return [
                                'estado'     => 'Reprobado',
                                'comentario' => trim($vals[0]['comentario']),
                            ];
                        }
                    }
                    return ['estado' => 'No fue calificada'];
                }
                if (in_array($estado, $declinados)) {
                    return ['estado' => 'Declinó'];
                }
            }
        }

        // Cualquier otro caso
        return null;
    }

    

    private function seccionPresentacionOfertas() 
    {
        if ($this->concurso->technical_includes) {
            $oferentes = $this->concurso->oferentes->where('has_tecnica_aprobada');
        } else {
            $oferentes = $this->concurso->oferentes->where('has_invitacion_aceptada');
        }
        
        $rondasOfertasTitle = [
            1 => '1ª Ronda de Ofertas',
            2 => '2ª Ronda de Ofertas',
            3 => '3ª Ronda de Ofertas',
            4 => '4ª Ronda de Ofertas',
            5 => '5ª Ronda de Ofertas'
        ];
        $contenido = [];
        
        for ($i = 1; $i <= $this->rondaActualConcurso; $i++) {
        $contenidoTabla = [];
        foreach ($oferentes as $oferente) {
            if ($oferente->is_economica_pendiente) {
                $contenidoTabla[] = [
                    $oferente->company->business_name ?? '',
                    'El proveedor no presentó su propuesta en la fecha establecida',
                    $oferente->reasonDeclination ?? '',
                ];
            } else {
                $ep = $oferente->economic_proposal
                    ->where('participante_id', $oferente->id)
                    ->where('numero_ronda', $i)
                    ->first();
                $fecha = '';
                if ($ep && isset($ep->created_at)) {
                    $fecha = $ep->created_at->format('d/m/Y H:i:s');
                }
                $total = $ep && isset($ep->values)
                    ? $this->calcEconomics($ep->values)
                    : 0;

                if ($oferente->is_concurso_rechazado && ! $ep) {
                    $contenidoTabla[] = [
                        $oferente->company->business_name ?? '',
                        'El proveedor declinó su participacion',
                        $oferente->reasonDeclination ?? '',
                    ];
                } else {
                    $contenidoTabla[] = [
                        $oferente->company->business_name ?? '',
                        $oferente->has_economica_presentada ? $fecha : '',
                        (string)number_format($total, 2, ',', '.'),
                    ];
                }
            }
        }
            $parrafo = [
                'tipo' => 'parrafo',
                'contenido' => '<div style="font-weight:bold">'.$rondasOfertasTitle[$i].'</div>',
            ];
            $tabla = [
                'tipo' => 'tabla',
                'cabeceras' => [
                    [
                        'Proveedor',
                        'Fecha',
                        'Cotización'
                    ]
                ],
                'contenido' => $contenidoTabla,
            ];
            $contenido[] = $parrafo;
            $contenido[] = $tabla;
        }        
        return [
            'titulo' => '4. Presentacion de ofertas',
            'contenido' => $contenido
        ];
    }

    

    private function seccionAdjudicacion() 
    {
        $list = [
            'RondasOfertas' => [],
        ];

        $contenidoSinOfertas = [
            'titulo' => '5. Adjudicación',
            'contenido' => [
                [
                    'tipo' => 'parrafo',
                    'contenido' => '<div style="text-align:center;font-weight:bold">Mejor oferta </div>',
                ],
                [
                    'tipo' => 'parrafo',
                    'contenido' => '<div style="text-align:center;font-weight:bold">Sin Ofertas de proveedores</div>',
                ]
            ]
        ];

        $contenidoSinAdjudicar = [
            'titulo' => '5. Adjudicación',
            'contenido' => [
                [
                    'tipo' => 'parrafo',
                    'contenido' => '<div style="text-align:center;font-weight:bold">Mejor oferta </div>',
                ],
                [
                    'tipo' => 'parrafo',
                    'contenido' => '<div style="text-align:center;font-weight:bold">Aun no se adjudica la '.$this->concurso->tipo_concurso_nombre.'</div>',
                ]
            ]
        ];
        
        

        $tipoAdjudicacion = $this->concurso::TIPO_ADJUDICACION;

        calcularEtapaAnalisisOfertas($list, $this->concurso->id);
        $ofertaActual = $list['RondasOfertas'][$this->rondaConcursoArrayResult];
        
        if (empty($ofertaActual['ConcursoEconomicas']['proveedores'])) {
            return $contenidoSinOfertas;
        }

        if(!$this->concurso->adjudicado){
            return $contenidoSinAdjudicar;
        }

        if($this->concurso->adjudicado){
            $id_adjudicacion = $this->concurso->oferentes
            ->whereIn('adjudicacion', $this->concurso::TIPO_ADJUDICACION)
            ->first()->adjudicacion;

            $tipoAdjudicacion = array_search($id_adjudicacion, $this->concurso::TIPO_ADJUDICACION);
            $comentario = $this->concurso->adjudicacion_comentario;

            if($tipoAdjudicacion == 'integral'){
                $mejorIntegral = $ofertaActual['ConcursoEconomicas']['mejoresOfertas']['mejorIntegral'];
                $proveedores = $ofertaActual['ConcursoEconomicas']['proveedores'];
                return $this->setIntegral($mejorIntegral, $proveedores, $comentario);
            }

            if($tipoAdjudicacion == 'individual'){
                $mejorIndividual = $ofertaActual['ConcursoEconomicas']['mejoresOfertas']['mejorIndividual'];
                $proveedores = $ofertaActual['ConcursoEconomicas']['proveedores'];
                return $this->setIndividual($mejorIndividual, $proveedores, $comentario);
            }

            if($tipoAdjudicacion == 'manual'){
                $adjudicacionManual = $this->concurso->adjudicacion_items;
                return $this->setManual($adjudicacionManual, $comentario);
            }
        }
    }

    private function seccionAceptacionAdjudicaciones() 
    {
        return [
            'titulo' => 'Aceptacion de adjudicaciones',
            'contenido' => [
                [
                    'tipo' => 'parrafo',
                    'contenido' => '[POR HACER] Fecha y hora de aceptación de ofertas de cada proveedor',
                ]
            ]
        ];
    }

    private function seccionReputacion() 
    {
        // Una tabla por cada fila.
        $tablas = [];
        foreach ($this->concurso->oferentes as $oferente) {
            $evaluacion = $oferente->evaluacion;
            if (!$evaluacion) {
                continue;
            }
            $valores = json_decode($evaluacion->valores, true);
            // Item             = index en db = index en array
            // No cumple        = 1           = 1
            // Cumple levemente = 2           = 2
            // Cumple           = 3           = 3
            // Supera           = 4           = 4
            // No aplica        = 0           = 5
            foreach ($valores as $k => $v) {
                if ($k) {
                    if ($v === '0') {
                        $v = '5';
                    }
                    $valores[$k] = intval($v);
                } else {
                    unset($valores[$k]);
                }
            }

            $marca = [
                'valor' => 'X',
                'attr' => ['align' => 'center'],
            ];

            $filasTabla = [];

            $filasTabla[0] = array_fill(0, 6, '');
            $filasTabla[0][0] = 'Puntualidad';
            if (isset($valores['Puntualidad'])) {
                $filasTabla[0][($valores['Puntualidad'])] = $marca;
            }

            $filasTabla[1] = array_fill(0, 6, '');
            $filasTabla[1][0] = 'Calidad';
            if (isset($valores['Calidad'])) {
                $filasTabla[1][($valores['Calidad'])] = $marca;
            }

            $filasTabla[2] = array_fill(0, 6, '');
            $filasTabla[2][0] = 'Orden y limpieza';
            if (isset($valores['OrdenYlimpieza'])) {
                $filasTabla[2][($valores['OrdenYlimpieza'])] = $marca;
            }

            $filasTabla[3] = array_fill(0, 6, '');
            $filasTabla[3][0] = 'Medio ambiente';
            if (isset($valores['MedioAmbiente'])) {
                $filasTabla[3][($valores['MedioAmbiente'])] = $marca;
            }

            $filasTabla[4] = array_fill(0, 6, '');
            $filasTabla[4][0] = 'Higiene y seguridad';
            if (isset($valores['HigieneYseguridad'])) {
                $filasTabla[4][($valores['HigieneYseguridad'])] = $marca;
            }

            $filasTabla[5] = array_fill(0, 6, '');
            $filasTabla[5][0] = 'Experiencia';
            if (isset($valores['Experiencia'])) {
                $filasTabla[5][($valores['Experiencia'])] = $marca;
            }

            $filasTabla[6] = [
                'Comentarios',
                [
                    'valor' => $evaluacion->comentario,
                    'attr' => [
                        'colspan' => 5,
                    ]
                ]
            ];

            $tablas[] = [
                'tipo' => 'tabla',
                'cabeceras' => [
                    [
                        [
                            'valor' => $oferente->user->offerer_company->business_name,
                            'attr' => ['colspan' => 6],
                        ]
                    ],
                    [
                        'Item',
                        'No cumple expectativas',
                        'Cumple levemente las expectativas',
                        'Cumple las expectativas',
                        'Supera las expectativas',
                        'No aplica',
                    ]
                ],
                'contenido' => $filasTabla,
            ];
        }

        if (!$tablas) {
            $tablas = [
                [
                    'tipo' => 'parrafo',
                    'contenido' => '<em style="color:#888">Sin información disponible.</em>',
                ]
            ];
        }

        return [
            'titulo' => 'Reputación',
            'contenido' => $tablas
        ];
    }

    public function obtenerInformacion() 
    {
        $title ='';
        
        $head = '';
        if($this->concurso->is_online){
            $title = 'Informe concurso subasta';
            $head = 'REPORTE CONCURSO SUBASTA';
        }
        if($this->concurso->is_sobrecerrado){
            $title = 'Informe concurso licitación';
            $head = 'REPORTE CONCURSO LICITACIÓN';
        }
        if($this->concurso->is_go){
            $title = 'Informe concurso go';
            $head = 'REPORTE CONCURSO GO';
        }
        
        $retorno = [];
        
        $retorno['nombreConcurso'] = $this->concurso->nombre;
        $retorno['logo'] = publicPath(asset('/global/img/logo.png'));
        $retorno['title'] = $title;
        $retorno['header'] = $head;
        $retorno[] = $this->seccionPreparacion();
        $retorno[] = $this->seccionParametros();
        $retorno[] = $this->seccionConvocatoriaOferentes();
        $retorno[] = $this->seccionPresentacionTecnica();
        $retorno[] = $this->seccionPresentacionOfertas();        
        $retorno[] = $this->seccionAdjudicacion();
        $retorno[] = $this->seccionReputacion();
        return $retorno;
        
    }

    /**
     * Arma la tabla de comparativas.
     *
     * @param array $list Retorno de calcularEtapaAnalisisOfertas()
     * @return array
     */
    private function armarTablaComparativaOfertas($proveedores) 
    { 
        $retorno = [
            'tipo' => 'tabla',
            'opciones' => ['resumen-ofertas'],
            'cabeceras' => [
                [
                    [
                        'valor' => 'Resumen de ofertas',
                        'css' => [
                            'font-size' => '14'
                        ],
                        'attr' => [
                            'colspan' => '3'
                        ]
                    ]
                ],
                [
                    'PROVEEDOR',
                    'ITEM',
                    'Total',
                ],
                [
                    '',
                    'Moneda',
                    '',
                ],
                [
                    '',
                    'Unidad de medida',
                    '',
                ],
                [
                    '',
                    'Cantidad solicitada',
                    '',
                ]
            ],
            'contenido' => [],
        ];

        
        // Cabeceras
        foreach ($proveedores[0]['items'] as $item) {
            $retorno['cabeceras'][0][0]['attr']['colspan'] ++;
            $retorno['cabeceras'][1][] = $item['nombre'];
            $retorno['cabeceras'][2][] = $item['moneda'];
            $retorno['cabeceras'][3][] = $item['unidad'];
            $retorno['cabeceras'][4][] = [
                'valor' => $item['cantidad'],
                'css' => ['color' => '#FF0000', 'font-weight' => 'bold'],
            ];
        }
        
        
        // Filas "Cotización"
        foreach ($proveedores as $oferente) {
            if(!$oferente['isRechazado']){
                $fila = [
                    $oferente['nombreOferente'],
                    [
                        'valor' => 'Cotización',
                        'css' => ['font-size' => '10pt'],
                    ],
                    strval($oferente['total'])
                ];
                foreach ($oferente['items'] as $valor) {
                    if ($valor['isMenorCotizacion']) {
                        $fila[] = [
                            'valor' => $valor['cotizacion'],
                            'css' => [
                                'background-color' => '#c6e0b4',
                                'font-weight' => 'bold',
                            ]
                        ];
                    } else {
                        $fila[] = $valor['cotizacion'];
                    }
                }
                $retorno['contenido'][] = $fila;
            }
            
        }

        // Filas "Cantidad cotizada"
        foreach ($proveedores as $oferente) {
            if(!$oferente['isRechazado']){
                $fila = [
                    $oferente['nombreOferente'],
                    [
                        'valor' => 'Cantidad cotizada',
                        'css' => [
                            'font-size' => '10pt',
                        ]
                    ],
                    '',
                ];
                foreach ($oferente['items'] as $valor) {
                    if ($valor['isMenorCantidad']) {
                        $fila[] = [
                            'valor' => $valor['cantidad'],
                            'css' => [
                                'background-color' => '#c6e0b4',
                                'font-weight' => 'bold',
                            ]
                        ];
                    } else {
                        $fila[] = $valor['cantidad'];
                    }
                }
                $retorno['contenido'][] = $fila;
            }
        }

        // Filas "Plazo de entrega"
        foreach ($proveedores as $oferente) {
            if(!$oferente['isRechazado']){
                $fila = [
                    $oferente['nombreOferente'],
                    [
                        'valor' => 'Plazo de entrega (días)',
                        'css' => [
                            'font-size' => '10pt',
                        ]
                    ],
                    '',
                ];
                foreach ($oferente['items'] as $valor) {
                    if ($valor['isMenorPlazo']) {
                        $fila[] = [
                            'valor' => strval($valor['fecha']),
                            'css' => [
                                'background-color' => '#c6e0b4',
                                'font-weight' => 'bold',
                            ]
                        ];
                    } else {
                        $fila[] = strval($valor['fecha']);
                    }
                }
                $retorno['contenido'][] = $fila;
            }
        }

        return $retorno;
    }

    private function calcEconomics($values){
        $total = 0;
        foreach ($values as $value) {
            $total = $total + $value['total'];
        }
        return $total;
    }

    private function setIntegral($mejorIntegral, $proveedores, $comentario)
    {
        $contenidoMejorOfertaIntegral = [];
        foreach ($mejorIntegral['items'] as $item) {
        
            $contenidoMejorOfertaIntegral[] = [
                $item['nombre'],
                strval(number_format($item['subtotal'], 2, ',', '.')),
                $item['razonSocial'],
            ];
        }

        $contenidoMejorOfertaIntegral[] = [
            [
                'valor' => 'Oferta total Integral',
                'css' => ['font-weight' => 'bold']
            ],
            [
                'valor' => $mejorIntegral['total'],
                'css' => ['font-weight' => 'bold']
            ],
            $mejorIntegral['razonSocial'],
        ];

        $tablaComparativaOfertas = $this->armarTablaComparativaOfertas($proveedores);

        return [
            'titulo' => '5. Adjudicación',
            'contenido' => [
                [
                    'tipo' => 'parrafo',
                    'contenido' => '<div style="text-align:center;font-weight:bold">Mejor oferta Integral</div>',
                ],
                [
                    'tipo' => 'tabla',
                    'cabeceras' => [
                        [
                            'Item',
                            'Cotización',
                            'Proveedor'
                        ]
                    ],
                    'contenido' => $contenidoMejorOfertaIntegral,
                ],
                [
                    'tipo' => 'tabla',
                    'cabeceras' => [
                        [
                            'Comentarios'
                        ]
                    ],
                    'contenido' => [[
                        'valor' => $comentario ? $comentario:'<em>No hay comentarios</em>',
                        'css' => ['font-weight' => 'bold']
                    ]]
                ],
                $tablaComparativaOfertas,
            ],
        ];
    }
    private function setIndividual($mejorIndividual, $proveedores, $comentario)
    {
        $contenidoMejorOfertaIndividual = [];
                    
        foreach ($mejorIndividual['individual'] as $item) {
            
            $contenidoMejorOfertaIndividual[] = [
                $item['itemNombre'],
                strval(number_format($item['subTotal'], 2, ',', '.')),
                $item['razonSocial'],
            ];
        }
        $contenidoMejorOfertaIndividual[] = [
            [
                'valor' => 'Oferta total',
                'css' => ['font-weight' => 'bold']
            ],
            [
                'valor' => $mejorIndividual['total1'],
                'css' => ['font-weight' => 'bold']
            ],
            $mejorIndividual['razonSocial'],
        ];
        $tablaComparativaOfertas = $this->armarTablaComparativaOfertas($proveedores);
        
        return [
            'titulo' => '5. Adjudicación',
            'contenido' => [
                [
                    'tipo' => 'parrafo',
                    'contenido' => '<div style="text-align:center;font-weight:bold">Mejor oferta Individual </div>',
                ],
                [
                    'tipo' => 'tabla',
                    'cabeceras' => [
                        [
                            'Item',
                            'Cotización',
                            'Proveedor'
                        ]
                    ],
                    'contenido' => $contenidoMejorOfertaIndividual,
                ],
                [
                    'tipo' => 'tabla',
                    'cabeceras' => [
                        [
                            'Comentarios'
                        ]
                    ],
                    'contenido' => [[
                        'valor' => $comentario ? $comentario:'<em>No hay comentarios</em>',
                        'css' => ['font-weight' => 'bold']
                    ]]
                ],
                $tablaComparativaOfertas,
            ]
        ];
    }
    private function setManual($adjudicacionItems, $comentario)
    {
        $contenidoMejorOfertaManual = [];
        $totalAdjudicacion = 0;
        foreach ($adjudicacionItems as $item) {
            $totalAdj = 0.00;
            $totalAdj = (float)$item['cantidadAdj'] * (float)$item['cotUnitaria'];
            $totalAdjudicacion +=$totalAdj;
            $contenidoMejorOfertaManual[] = [                            
                $item['itemNombre'],
                strval(number_format($item['itemSolicitado'], 2, ',', '.')),
                strval(number_format($item['cantidad'], 2, ',', '.')),
                strval(number_format($item['cotizacion'], 2, ',', '.')),
                strval(number_format($item['cantidadAdj'], 2, ',', '.')),
                strval(number_format($totalAdj, 2, ',', '.')),
                $item['razonSocial'],
            ];
        }
        $contenidoMejorOfertaManual[] = [
            [
                'valor' => '',
                'css' => ['font-weight' => 'bold'],
            ],
            [
                'valor' => '',
                'css' => ['font-weight' => 'bold'],
            ],
            [
                'valor' => '',
                'css' => ['font-weight' => 'bold'],
            ],
            [
                'valor' => '',
                'css' => ['font-weight' => 'bold'],
            ],
            [
                'valor' => 'Oferta total',
                'css' => ['font-weight' => 'bold'],
            ],
            [
                'valor' => strval(number_format($totalAdjudicacion, 2, ',', '.')),
                'css' => ['font-weight' => 'bold'],
            ],
            [
                'valor' => '',
                'css' => ['font-weight' => 'bold'],
            ],
            $adjudicacionItems['razonSocial'],
        ]; 
        return [
            'titulo' => '5. Adjudicación',
            'contenido' => [
                [
                    'tipo' => 'parrafo',
                    'contenido' => '<div style="text-align:center;font-weight:bold">Mejor oferta Manual </div>',
                ],
                [
                    'tipo' => 'tabla',
                    'cabeceras' => [
                        [
                            'Item',
                            'Cantidad Solicitada',
                            'Cantidad Cotizada',
                            'Cotización',
                            'Cantidad Adjudicada',
                            'Total Adjudicación',
                            'Proveedor',
                        ]
                    ],
                    'contenido' => $contenidoMejorOfertaManual,
                ],
                [
                    'tipo' => 'tabla',
                    'cabeceras' => [
                        [
                            'Comentarios'
                        ]
                    ],
                    'contenido' => [[
                        'valor' => $comentario ? $comentario:'<em>No hay comentarios</em>',
                        'css' => ['font-weight' => 'bold']
                    ]]
                ],
            ],
        ];   
    }

}

class InformePDF extends TCPDF 
{
    protected $logoConcurso = '';
    protected $title = '';
    protected $head = '';

    public function __construct($config) 
    {
        if (!empty($config['logo']) && file_exists($config['logo'])) {
            $this->logoConcurso = $config['logo'];
        }

        parent::__construct('Portrait', 'pt', 'A4');

        $this->SetCreator('Optus');
        $this->SetAuthor('Optus');
        $this->SetTitle($config['title']);

        $this->SetMargins(30, 105, 30);
        $this->SetHeaderMargin(30);

        $this->setPrintFooter(false);

        // set auto page breaks
        $this->SetAutoPageBreak(TRUE, 30);

        // set image scale factor
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);

        $this->AddPage();

        $this->SetFont('helvetica', '', 10, __DIR__ . '/tcpdf/fonts/helvetica.php');
        $this->setColor('text', 119);
        $this->Ln(5);
        $this->Cell(0, 10 * 1.4, $config['header'], 0, 1, 'C', false, '', 0, true, 'T', 'M');

        $this->setColor('text', 32);
        $this->SetFont('helvetica', 'b', 20, __DIR__ . '/tcpdf/fonts/helvetica.php');
        $this->MultiCell(0, 0, $config['nombre-concurso'], 0, 'C');

        $this->Ln(3);
    }

    public function Header() 
    {
        if ($this->header_xobjid === false || $this->CurOrientation !== 'P') {
            $this->header_xobjid = $this->startTemplate($this->w, $this->tMargin);

            // Logo del concurso, de alto fijo.
            if ($this->logoConcurso) {
                $x = $this->original_lMargin;
                $y = $this->header_margin;

                $maxH = 60;
                $dimensiones = getimagesize($this->logoConcurso);
                $w = ceil(($dimensiones[0] * $maxH) / $dimensiones[1]);
                $h = $maxH;

                $this->Image($this->logoConcurso, $x, $y, $w, $h);
            }

            // Logo Optus
            $archivo = asset('/global/img/logo-pdf-optus-gris.gif');
            $x = $this->w - $this->original_rMargin - 120;
            $y = $this->header_margin;
            $this->Image($archivo, $x, $y, 120, 0, 'gif', '', 'N', false, 300, '', false, false, 0, false, false, false);

            // Linea separadora
            $x = $this->original_lMargin;
            $y = (2.835 / $this->k) + max($this->getImageRBY(), $this->y);
            $w = $this->w - $this->original_lMargin - $this->original_rMargin;

            $this->Rect($x, $y, $w, 0.5, 'F', [], array(200));

            $this->endTemplate();
        }

        parent::Header();

        if ($this->CurOrientation !== 'P') {
            $this->resetHeaderTemplate();
        }
    }

    public function crearTitulo($string) 
    {
        $this->SetFont('helvetica', 'b', 16, __DIR__ . '/tcpdf/fonts/helvetica.php');
        $this->setColor('text', 46, 116, 181);
        $this->MultiCell(0, 0, $string, 0, 'L');
        $this->Ln(3);
    }

    public function crearSubTitulo($string) 
    {
        $this->SetFont('helvetica', 'b', 14, __DIR__ . '/tcpdf/fonts/helvetica.php');
        $this->setColor('text', 46, 116, 181);
        $this->MultiCell(0, 0, $string, 0, 'L');
        $this->Ln(3);
    }

    public function agregarContenido($contenido) 
    {
        $this->SetFont('helvetica', '', 12, __DIR__ . '/tcpdf/fonts/helvetica.php');
        if ($contenido['tipo'] === 'tabla') {
            $this->crearTabla($contenido);
        } else if ($contenido['tipo'] === 'parrafo') {
            $this->crearParrafo($contenido);
        }
    }

    public function crearCeldaHTML($celda, $tipo = 'normal') 
    {
        $porDefecto = [
            'valor' => '',
            'css' => [
                'border' => '0.5pt solid #aaa',
                'color' => '#202020',
                'font-size' => '11pt',
            ],
            'attr' => [
                'cellpadding' => '1',
            ]
        ];

        if (is_string($celda)) {
            $celda = array_merge($porDefecto, ['valor' => $celda]);
        } else if (is_array($celda)) {
            if (!isset($celda['css'])) {
                $celda['css'] = [];
            }
            if (!isset($celda['attr'])) {
                $celda['attr'] = [];
            }
            $celda['css'] = array_merge($porDefecto['css'], $celda['css']);
            $celda['attr'] = array_merge($porDefecto['attr'], $celda['attr']);
        } else {
            $celda = $porDefecto;
        }

        if ($tipo === 'cabecera') {
            $celda['attr']['align'] = 'center';
        }

        $estilos = [];
        foreach ($celda['css'] as $prop => $valor) {
            $estilos[] = $prop . ':' . $valor;
        }

        $atributos = [];
        foreach ($celda['attr'] as $prop => $valor) {
            $atributos[] = $prop . '="' . $valor . '"';
        }

        return sprintf(
                '<td %1$s style="%2$s">%3$s</td>', implode(' ', $atributos), implode(';', $estilos), $celda['valor']
        );
    }

    public function crearTabla($contenido) 
    {
        $html = '<table cellpadding="2">';

        if ($contenido['cabeceras']) {
            $html .= '<thead>';
            foreach ($contenido['cabeceras'] as $fila) {
                if (is_array($fila)) {
                    $html .= '<tr style="background-color:#e8e8e8;font-weight:bold">';
                    foreach ($fila as $celda) {
                        $html .= $this->crearCeldaHTML($celda, 'cabecera');
                    }
                    $html .= '</tr>';
                }
            }
            $html .= '</thead>';
        }

        if ($contenido['contenido']) {
            $html .= '<tbody>';
            foreach ($contenido['contenido'] as $fila) {
                $html .= '<tr>';
                foreach ($fila as $celda) {
                    $html .= $this->crearCeldaHTML($celda);
                }
                $html .= '</tr>';
            }
            $html .= '</tbody>';
        }

        $html .= '</table>';

        if (isset($contenido['opciones'])) {
            if (in_array('resumen-ofertas', $contenido['opciones'])) {
                $this->AddPage('L');
                $this->writeHTML($html);
                $this->AddPage('P');
            }
        } else {
            $this->writeHTML($html);
        }
    }

    public function crearParrafo($contenido) 
    {
        $this->SetFont('helvetica', '', 12, __DIR__ . '/tcpdf/fonts/helvetica.php');
        $this->setColor('text', 40);
        $this->writeHTML($contenido['contenido']);
        $this->Ln(5);
    }

   

}

$dataInforme = new DataInforme($_GET['Id']);
// dd($dataInforme->obtenerInformacion());
$data = $dataInforme->obtenerInformacion();
// === NUEVO: armar nombre de archivo {Id}_Informe_{Nombre}_{fecha}.pdf ===
$concursoId = abs(intval($_GET['Id']));
$sanitize = function ($text) {
    $text = (string)$text;
    $t = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    if ($t === false) { $t = $text; }
    $t = preg_replace('/[^A-Za-z0-9]+/', '_', $t);
    $t = trim(preg_replace('/_+/', '_', $t), '_');
    return $t !== '' ? strtolower($t) : 'concurso';
};
$safeConcursoName = $sanitize($data['nombreConcurso'] ?? 'concurso');
$timestamp = date('Ymd-His');
$archivo = "{$concursoId}_Informe_{$safeConcursoName}_{$timestamp}.pdf";


$informe = new InformePDF([
    'nombre-concurso' => $data['nombreConcurso'],
    'logo' => $data['logo'],
    'title' => $data['title'],
    'header' => $data['header'],
]);

foreach ($data as $index => $seccion) {
    if (!is_int($index)) {
        continue;
    }
    $informe->crearTitulo($seccion['titulo']);
    if(isset(($seccion['subTitulo']))){
        $informe->crearSubTitulo($seccion['subTitulo']);
    }
    if (is_array($seccion['contenido'])) {
        foreach ($seccion['contenido'] as $contenido) {
            $informe->agregarContenido($contenido);
        }
    }
}

//Para suprimir el mensaje:
//TCPDF ERROR: Some data has already been output, can't send PDF file
ob_end_clean();

// ==== Paths y guardado ====
$basepath    = rootPath() . filePath(config('app.files_tmp')); // debe terminar con "/"
if (!is_dir($basepath)) {
    @mkdir($basepath, 0777, true);
}
$real_path   = $basepath . $archivo;
$public_path = filePath(config('app.files_tmp') . $archivo, true);

// Guardar el PDF en disco
$informe->Output($real_path, 'F');

// Devolver JSON con filename sugerido
echo json_encode([
    'data' => [
        'real_path'          => filePath(config('app.files_tmp') . $archivo),
        'public_path'        => $public_path,
        'suggested_filename' => $archivo,
    ]
]);
return;
