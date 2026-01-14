<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use App\Models\Go;
use App\Models\User;
use App\Models\Model;
use App\Models\Alcance;
use App\Models\Measurement;
use App\Models\Producto;
use App\Models\Mensaje;
use App\Models\PlantillaTecnica;
use App\Models\Participante;
use App\Models\TipoOperacion;
use App\Models\TipoConvocatoria;
use App\Models\Invitation;
use App\Models\Sheet;
use App\Services\DocumentService;
use App\Models\OffererCompany;

class Concurso extends Model
{
    use SoftDeletes;

    protected $table = 'concursos';

    protected $casts = [
        'adjudicado' => 'boolean'
    ];

    protected $dates = [
        'fecha_alta',
        'finalizacion_consultas',
        'fecha_limite',
        'fecha_limite_economicas',
        'email_economica_enviado_at',
        'segunda_ronda_fecha_limite',
        'ficha_tecnica_fecha_limite',
        'inicio_subasta',
        'deleted_at',
        'tercera_ronda_fecha_limite',
        'cuarta_ronda_fecha_limite',
        'quita_ronda_fecha_limite',
        'fecha_apertura_sobres'
    ];

    protected $fillable = [
        'id',
        'id_cliente',
        'tipo_concurso',
        'tipo_operacion',
        'nombre',
        'imagen',
        'solicitud_compra',
        'orden_compra',
        'resena',
        'descripcion',
        'pais',
        'provincia',
        'localidad',
        'direccion',
        'cp',
        'latitud',
        'longitud',
        'tipo_convocatoria',
        'finalizacion_consultas',
        'aceptacion_terminos',
        'fecha_limite',
        'seguro_caucion',
        'diagrama_gant',
        'usuario_califica_reputacion',
        'inicio_subasta',
        'duracion',
        'tiempo_adicional',
        'tiempo_adicional_aplicado',
        'plantilla_economicas',
        'fecha_limite_economicas',
        'finalizar_si_oferentes_completaron_economicas',
        'ofertas_parciales_permitidas',
        'ofertas_parciales_cantidad_min',
        'segunda_ronda_habilita',
        'segunda_ronda_fecha_limite',
        'moneda',
        'tipo_valor_ofertar',
        'chat',
        'ver_num_oferentes_participan',
        'ver_oferta_ganadora',
        'ver_ranking',
        'ver_tiempo_restante',
        'permitir_anular_oferta',
        'precio_maximo',
        'precio_minimo',
        'solo_ofertas_mejores',
        'aperturasobre',
        'fecha_apertura_sobres',
        'subastavistaciega',
        'unidad_minima',
        'ficha_tecnica_incluye',
        'ficha_tecnica_fecha_limite',
        'ficha_tecnica_usuario_evalua',
        'ficha_tecnica_plantilla',
        'fecha_alta',
        'usuario_cancelacion',
        'deleted_at',
        'adjudicado',
        'adjudicacion_items',
        'adjudicacion_comentario',
        'id_go',
        'descriptionTitle',
        'descriptionDescription',
        'descriptionUrl',
        'descriptionImagen',
        'base_condiciones_firmado',
        'condiciones_generales',
        'pliego_tecnico',
        'acuerdo_confidencialidad',
        'legajo_impositivo',
        'antecendentes_referencia',
        'reporte_accidentes',
        'estructura_costos',
        'tecnico_ofertas',
        'comentario_cancelacion',
        'ronda_actual',
        'tercera_ronda_fecha_limite',
        'cuarta_ronda_fecha_limite',
        'quita_ronda_fecha_limite',
        'condicion_pago',
        'envio_muestra',
        'segunda_ronda_comentario',
        'tercera_ronda_comentario',
        'cuarta_ronda_comentario',
        'quita_ronda_comentario',
        'nom251', 
        'distintivo',
        'filtros_sanitarios',
        'repse',
        'poliza',
        'primariesgo',
        'obras_referencias',
        'obras_organigrama',
        'obras_equipos',
        'obras_cronograma',
        'obras_memoria',
        'obras_antecedentes',
        'tarima_ficha_tecnica',
        'tarima_licencia',
        'tarima_nom_144',
        'tarima_acreditacion',
        'concurso_fiscalizado',
        'token',
        'edificio_balance',
        'edificio_iva',
        'edificio_cuit',
        'edificio_brochure',
        'edificio_organigrama',
        'edificio_organigrama_obra',
        'edificio_subcontratistas',
        'edificio_gestion',
        'edificio_maquinas',
        'lista_prov',
        'cert_visita',
        'area_sol',
        'usuario_fiscalizador',
        'total_cotizacion',
        'apu',
        'tipo_licitacion',
        'entrega_doc_evaluacion', 
        'requisitos_legales', 
        'experiencia_y_referencias', 
        'repse_two', 
        'alcance_two', 
        'forma_pago', 
        'tiempo_fabricacion', 
        'ficha_tecnica',
        'garantias',
        // ===== NUEVOS CAMPOS PLANTILLA 7 =====
        'propuesta_tecnica',
        'plan_mantenimiento_preventivo',
        'nda_firmado',
        'inventario_equipos',
        'acreditaciones_permisos',
        'requerimientos_tecnologicos',
        'requisitos_personal',
        'organigrama_equipo',
        'valor_agregado',
        'acuerdos_nivel_servicio',
        'hseq_anexo2',
        'referencias_comerciales',
        'riesgo_financiero',
        // ===== NUEVOS CAMPOS PLANTILLA 8 =====
        'ficha_especificaciones',
        'msds_hojas_seguridad',
        'garantia_tecnica',
        'cronograma_entrega',
        'carta_representante_marca',
        'soporte_post_venta',        
        'lugar_forma_entrega',
        // ===== NUEVOS CAMPOS PLANTILLA 1 =====
        'listado_equipos_herramientas',
        'equipo_humano_competencias',
        'balances_estados_resultados',
        'estatuto_contrato_social',
        'actas_designacion_autoridades',

    ];

    protected $appends = [
        'portrait',
        'technical_includes',
        'economic_includes_second_round',
        'file_path',
        'adjudicacion_resultados_output',
        'fin_subasta',
        'subasta_status',
        'countdown',
        'timeleft',
        'parsed_duracion',
        'products_output',
        'pliegos',
        'attachments',
        'tipo_concurso_nombre',
        'adjudicacion_anticipada',
        'mejores_ofertas_subasta',
        'todos_presentaron_economica',
        'alguno_presento_economica',
        'existen_ofertas',
        'is_online',
        'is_sobrecerrado',
        'is_go',
        'parsed_technical_proposals',
        'is_finalizado',
        'is_chat_enabled',
        'oferentes_etapa_preparacion',
        'oferentes_etapa_convocatoria',
        'oferentes_etapa_tecnica',
        'oferentes_etapa_economica',
        'oferentes_etapa_evaluacion',
        'oferentes_etapa_informes',
        'is_subastaciega',
        'plazosPagos',
        'portraitDescription',
        'canceled',
        'losing',
        'adjudication_type',
        'offerers_adjudicated',
        'users_eval_tec',
        'todos_revisados',
        'alguno_revisado'
    ];

    const TYPE_DESCRIPTION = [
        'sobrecerrado' => 'Licitación',
        'online' => 'Subasta',
        'go' => 'Go'
    ];
    const TYPES = [
        'sobrecerrado' => 'sobrecerrado',
        'online' => 'online',
        'go' => 'go'
    ];

    const MAX_RONDAS = 5;

    const CAMPOS_FECHA_NUEVA_RONDA = [
        2 => 'segunda_ronda_fecha_limite',
        3 => 'tercera_ronda_fecha_limite',
        4 => 'cuarta_ronda_fecha_limite',
        5 => 'quita_ronda_fecha_limite'
    ];

    const CAMPOS_COMENTARIOS_NUEVA_RONDA = [
        2 => 'segunda_ronda_comentario',
        3 => 'tercera_ronda_comentario',
        4 => 'cuarta_ronda_comentario',
        5 => 'quinta_ronda_comentario'
    ];

    const NUEVAS_RONDAS = [
        2 => '2ª Ronda de Ofertas',
        3 => '3ª Ronda de Ofertas',
        4 => '4ª Ronda de Ofertas',
        5 => '5ª Ronda de Ofertas'
    ];

    const TIPO_ADJUDICACION = [
        'integral' => 1,
        'individual' => 2,
        'manual' => 3
    ];



    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_concurso', 'id')->with('unidad_medida')
            ->where('eliminado', '!=', '1');
    }

    public function mensajes()
    {
        return $this->hasMany(Mensaje::class, 'cso_id', 'id');
    }

    public function alcance()
    {
        return $this->belongsTo(TipoOperacion::class, 'tipo_operacion', 'id');
    }

    public function convocatoria()
    {
        return $this->belongsTo(TipoConvocatoria::class, 'tipo_convocatoria', 'id');
    }

    public function tipo_moneda()
    {
        return $this->belongsTo(Moneda::class, 'moneda', 'id');
    }

    public function plantilla_tecnica()
    {
        return $this->belongsTo(PlantillaTecnica::class, 'id', 'id_concurso');
    }

    public function plantilla_items()
    {
        return $this->hasOne(ConcursoPlantillaItem::class, 'concurso_id', 'id');
    }

    public function cliente()
    {
        return $this->belongsTo(User::class, 'id_cliente', 'id');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'usuario_fiscalizador', 'id');
    }
    
    public function evaluaTecnica()
    {
        return $this->belongsTo(User::class, 'ficha_tecnica_usuario_evalua', 'id');
    }

    public function oferentes()
    {
        return $this->hasMany(Participante::class, 'id_concurso', 'id');
    }

    public function cancelacion_usuario()
    {
        return $this->belongsTo(User::class, 'usuario_cancelacion', 'id');
    }

    public function go()
    {
        return $this->hasOne(Go::class, 'id', 'id_go');
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class, 'concurso_id', 'id');
    }

    public function sheets()
    {
        return $this->hasMany(Sheet::class, 'concurso_id', 'id');
    }

    public function getIsFinalizadoAttribute()
    {
        return
            $this->oferentes
                ->where('etapa_actual', 'adjudicacion-aceptada')
                ->where('evaluacion', '!=', null)
                ->count() > 0
            &&
            $this->oferentes
                ->where('etapa_actual', 'adjudicacion-pendiente')
                ->count() == 0;
    }

    public function getPortraitAttribute()
    {
        return
            $this->attributes['imagen'] && file_exists(rootPath() . filePath(config('app.images_path') . $this->attributes['imagen'])) ?
            $this->attributes['imagen'] :
            'default.gif';
    }

    public function getPortraitDescriptionAttribute()
    {
        return
            $this->attributes['descriptionImagen'] && file_exists(rootPath() . filePath(config('app.images_path') . $this->attributes['descriptionImagen'])) ?
            $this->attributes['descriptionImagen'] :
            'default.gif';
    }

    public function getSoloOfertasMejoresAttribute()
    {
        return
            $this->is_online ?
            (
                $this->attributes['solo_ofertas_mejores'] == 'si' ?
                true :
                false
            ) :
            false;
    }

    public function getChatAttribute()
    {
        return
            $this->is_online ?
            (
                $this->attributes['chat'] == 'si' ?
                true :
                false
            ) :
            false;
    }

    public function getIsSubastaciegaAttribute()
    {
        return
            $this->is_online ?
            (
                $this->attributes['subastavistaciega'] == 'si' ?
                true :
                false
            ) :
            false;
    }

    public function getVerNumOferentesParticipanAttribute()
    {
        return
            $this->is_online ?
            (
                $this->attributes['ver_num_oferentes_participan'] == 'si' ?
                true :
                false
            ) :
            false;
    }

    public function getVerOfertaGanadoraAttribute()
    {
        return
            $this->is_online ?
            (
                $this->attributes['ver_oferta_ganadora'] == 'si' ?
                true :
                false
            ) :
            false;
    }

    public function getVerRankingAttribute()
    {
        return
            $this->is_online ?
            (
                $this->attributes['ver_ranking'] == 'si' ?
                true :
                false
            ) :
            false;
    }

    public function getVerTiempoRestanteAttribute()
    {
        return
            $this->is_online ?
            (
                $this->attributes['ver_tiempo_restante'] == 'si' ?
                true :
                false
            ) :
            false;
    }

    public function getEconomicIncludesSecondRoundAttribute()
    {
        return $this->segunda_ronda_habilita === 'si' && $this->is_sobrecerrado;
    }

    public function getDisponibleHabilitarSegundaRondaEconomicaAttribute()
    {
        $habilita = false;


        if ($this->is_sobrecerrado) {
            if ($this->attributes['segunda_ronda_habilita'] == 'no' && $this->ronda_actual == 1) {
                $habilita = true;
            }

            if ($this->attributes['segunda_ronda_habilita'] == 'si' && $this->ronda_actual <= $this::MAX_RONDAS) {
                $habilita = true;
            }
        }

        return $habilita;
    }

    public function getTechnicalIncludesAttribute()
    {
        return $this->ficha_tecnica_incluye === 'si' || $this->is_go;
    }

    public function getIsSobrecerradoAttribute()
    {
        return $this->tipo_concurso == $this::TYPES['sobrecerrado'];
    }

    public function getIsOnlineAttribute()
    {
        return $this->tipo_concurso == $this::TYPES['online'];
    }

    public function getIsGoAttribute()
    {
        return $this->tipo_concurso == $this::TYPES['go'];
    }

    public function getParsedTechnicalProposalsAttribute()
    {
        $results = collect();

        foreach ($this->oferentes->where('has_tecnica_presentada', true) as $oferente) {
            $technical_proposal = $oferente->parsed_technical_proposal;
            $result = new \StdClass();
            $result->id = $oferente->id;
            $result->name = $oferente->company->business_name;
            $result->razon_social = $oferente->company->business_name;
            $result->comment = $technical_proposal->comment;
            $result->documents = $technical_proposal->documents;
            $results = $results->push($result);
        }

        return $results;
    }

    public function getTodosPresentaronEconomicaAttribute()
    {
        $concurso = $this;
        $oferentes = $this->oferentes;
        $rondaActual = $concurso->ronda_actual;
        $pending_offerers = collect();
        $enabled_offerers = $oferentes->where('rechazado', false)
            ->filter(
                function ($oferente) use ($concurso) {
                    return
                        $concurso->technical_includes ?
                        $oferente->has_tecnica_aprobada :
                        $oferente->has_invitacion_aceptada;
                }
            );
        

        if ($this->segunda_ronda_habilita == 'no') {
            if ($this->fecha_limite >= Carbon::now()) {
                $pending_offerers = $pending_offerers->merge(
                    $oferentes->where('is_economica_pendiente', true)
                );
            }
            
            if ($this->is_sobrecerrado || $this->is_online) {
                if ($this->technical_includes && $this->ficha_tecnica_fecha_limite >= Carbon::now()) {
                    $pending_offerers = $pending_offerers->merge(
                        $oferentes
                            ->where('has_invitacion_aceptada', true)
                            ->where('rechazado', false)
                            ->where('has_tecnica_aprobada', false)
                            ->where('is_tecnica_declinada', false)
                    );
                }
            }
        }

        if ($this->segunda_ronda_habilita == 'si') {
            $campoFecha = $this::CAMPOS_FECHA_NUEVA_RONDA[$rondaActual];

            if ($this->$campoFecha >= Carbon::now()) {
                $pending_offerers = $pending_offerers->merge(
                    $oferentes->where('is_economica_pendiente', true)
                );
            }
        }

        return
            $enabled_offerers->where('has_economica_presentada', true)->count() > 0 &&
            $pending_offerers->count() === 0;
    }

    public function getAlgunoPresentoEconomicaAttribute()
    {
        return $this->oferentes
            ->where('rechazado', false)
            ->where('has_economica_presentada', true)
            ->count() > 0;
    }

    public function getExistenOfertasAttribute()
    {
        return $this->oferentes
            ->where('rechazado', false)
            ->where('has_economica_presentada', true)
            ->count() > 0;
    }

    public function getTipoConcursoNombreAttribute()
    {
        return $this::TYPE_DESCRIPTION[$this->attributes['tipo_concurso']];
    }

    public function getPliegosAttribute()
    {
        if (!$this->attributes['fecha_alta']) {
            return null;
        }

        $pliegos = collect();

        foreach ($this->sheets as $sheet) {
            $pliego = new \StdClass();
            $pliego->filename = $sheet->filename;
            $pliego->path = $this->file_path . $sheet->filename;
            $pliego->type_id = $sheet->type->id;
            $pliegos = $pliegos->push($pliego);
        }

        return $pliegos->count() > 0 ? $pliegos : null;
    }

    public function getFilePathAttribute()
    {
        return $this->cliente->file_path_customer;
    }

    public function getAttachmentsAttribute()
    {
        $attachments = collect();

        // Imagen de Concurso
        if ($this->portrait) {
            $attachment = new \StdClass();
            $attachment->name = 'imagen';
            $attachment->filename = $this->portrait;
            $attachment->path = filePath(config('app.images_path') . $this->portrait);
            $attachment->type_id = null;
            $attachments = $attachments->push($attachment);
        }

        // Pliegos
        $pliegos = $this->pliegos;
        if ($pliegos) {
            foreach ($pliegos as $pliego) {
                $attachment = new \StdClass();
                $attachment->name = 'pliego';
                $attachment->filename = $pliego->filename;
                $attachment->path = filePath($pliego->path);
                $attachment->type_id = $pliego->type_id;
                $attachments = $attachments->push($attachment);
            }
        }

        return $attachments->count() > 0 ? $attachments : null;
    }

    public function getMejoresOfertasSubastaAttribute()
    {
        $result = collect();
        if ($this->is_online) {
            // Recargar relaciones frescas desde la base de datos
            $this->unsetRelation('oferentes');
            $this->unsetRelation('productos');
            $this->load(['oferentes.proposals', 'productos.unidad_medida']);

            $ascendente = $this->tipo_valor_ofertar == 'ascendente' ? true : false;
            foreach ($this->productos as $producto) {

                $cotizaciones = collect();

                $total_cotizaciones = [];
                
                // Filtrar oferentes que tienen propuesta económica con valores
                $oferentes = $this->oferentes->filter(function($oferente) {
                    $proposal = $oferente->proposals->where('is_economic', true)->first();
                    return $proposal && $proposal->values && count($proposal->values) > 0;
                });

                foreach ($oferentes as $oferente) {
                    $proposal = $oferente->proposals->where('is_economic', true)->first();
                    if (!$proposal || !$proposal->values) {
                        continue;
                    }
                    $ofertaArray = array_values(
                        array_filter(
                            $proposal->values,
                            function ($item) use ($producto) {
                                return $item['producto'] == $producto->id;
                            }
                        )
                    );
                    if (empty($ofertaArray)) {
                        continue;
                    }
                    $oferta = $ofertaArray[0];
                    if (!$oferta['cotizacion']) {
                        continue;
                    }
                    // Obtenemos todas las cotizaciones para el producto
                    $total_cotizaciones[(int) $oferente->id] = $oferta['cotizacion'];
                }

                // Filtramos las mejores
                $mejores_cotizaciones = array_filter(
                    $total_cotizaciones,
                    function ($value, $key) use ($total_cotizaciones, $ascendente) {
                        if ($ascendente) {
                            return (float) $value >= max(array_filter($total_cotizaciones));
                        }
                        return (float) $value <= (float) min(array_filter($total_cotizaciones));
                    },
                    ARRAY_FILTER_USE_BOTH
                );

                // Les mapeamos la información completa
                array_walk(
                    $mejores_cotizaciones,
                    function (&$value, $key) use ($producto) {
                        $oferente = Participante::find($key);
                        $proposal = $oferente->proposals->where('is_economic', true)->first();
                        $oferta = array_values(
                            array_filter(
                                $proposal->values,
                                function ($item) use ($producto) {
                                    return $item['producto'] == $producto->id;
                                }
                            )
                        )[0];
                        // Asegurar que cotizacion y cantidad sean floats
                        $oferta['cotizacion'] = (float) $oferta['cotizacion'];
                        $oferta['cantidad'] = (float) $oferta['cantidad'];
                        $value = array_merge(
                            [
                                'oferente' => (int) $oferente->id_offerer
                            ],
                            $oferta
                        );
                    }
                );
                $mejores_cotizaciones = collect(json_decode(json_encode(array_values($mejores_cotizaciones)), false, 512, JSON_PRESERVE_ZERO_FRACTION));
                foreach ($mejores_cotizaciones as $cotizacion) {
                    $cotizacion->parsed_date = Carbon::createFromFormat('Y-m-d H:i:s', $cotizacion->creado);
                }
                $mejores_cotizaciones = $mejores_cotizaciones->sortBy('parsed_date');

                foreach ($oferentes as $oferente) {
                    // Si este oferente no cotizó, lo salteamos.
                    if (!isset($total_cotizaciones[(string) $oferente->id])) {
                        continue;
                    }

                    // Traemos la mejor cotización para este producto
                    $mejor_cotizacion = $mejores_cotizaciones->first();

                    $proposal = $oferente->proposals->where('is_economic', true)->first();
                    if (!$proposal || !$proposal->values) {
                        continue;
                    }
                    $ofertaArray = array_values(
                        array_filter(
                            $proposal->values,
                            function ($item) use ($producto) {
                                return $item['producto'] === $producto->id;
                            }
                        )
                    );
                    if (empty($ofertaArray)) {
                        continue;
                    }
                    $oferta = $ofertaArray[0];

                    // Obtener puesto de los oferentes
                    $cotizaciones_item = new \StdClass;
                    $cotizaciones_item->producto = $producto->id;
                    $cotizaciones_item->oferente_id = $oferente->id_offerer;
                    $cotizaciones_item->cotizacion = (float) $oferta['cotizacion'];
                    $cotizaciones_item->cantidad = (float) $oferta['cantidad'];
                    $cotizaciones_item->creado = Carbon::createFromFormat('Y-m-d H:i:s', $oferta['creado']);
                    $cotizaciones_item->empatado = $mejores_cotizaciones->where('cotizacion', (float) $oferta['cotizacion'])->count() > 1;
                    $cotizaciones_item->valores_mejor = (array) $mejor_cotizacion;
                    $cotizaciones = $cotizaciones->push($cotizaciones_item);
                }

                // Ordenar tabla
                if ($ascendente) {
                    $cotizaciones = $cotizaciones->sortBy('parsed_date')->sortByDesc('cotizacion');
                } else {
                    $cotizaciones = $cotizaciones->sortBy('parsed_date')->sortBy('cotizacion');
                }

                $cotizacion_previa = null;
                foreach ($cotizaciones as $cotizacion) {
                    $cotizacion->oferta_puesto =
                        $cotizacion->empatado ?
                        (
                            $cotizacion_previa ?
                            $cotizacion_previa->oferta_puesto :
                            1
                        ) : (
                            isset($cotizaciones->where('oferta_puesto', '!=', null)->last()->oferta_puesto) ?
                            $cotizaciones->where('oferta_puesto', '!=', null)->last()->oferta_puesto + 1 :
                            1
                        );
                    $cotizacion_previa = $cotizacion;
                }
                $result = $result->merge($cotizaciones);
            }
        }

        return $result;
    }

    public function getCotizacionesOutput()
    {
        $result = [];

        try {
            foreach ($this->productos as $producto) {
                $cotizaciones = null;
                if ($this->is_online) {
                    $ranking = [];
                    $cotizaciones = $this->mejores_ofertas_subasta->where('producto', $producto->id);
                    if ($cotizaciones) {
                        $cotizacionesFirst = $cotizaciones->first();
                        $valores_mejor = isset($cotizacionesFirst->valores_mejor) ? $cotizacionesFirst->valores_mejor : 0;

                        if ($cotizaciones->count() > 0) {
                            $ranking = $cotizaciones->map(function ($item) {
                                return [
                                    'oferente_id' => $item->oferente_id,
                                    'oferta_puesto' => $item->oferta_puesto,
                                    'empatado' => $item->empatado
                                ];
                            });
                        }
                    }
                } else {
                    $valores_mejor = null;
                    $ranking = null;
                }



                $valores = null;


                foreach ($this->oferentes as $oferente) {
                    $proposal = $oferente->proposals->where('is_economic', true)->last();
                    if ($proposal) {
                        $oferta = array_values(
                            array_filter(
                                $proposal->values,
                                function ($item) use ($producto) {
                                    return $item['producto'] == $producto->id;
                                }
                            )
                        )[0];
                    } else {
                        $oferta = null;
                    }

                    $mejor_oferta_cotizacion = $valores_mejor ? (float) $valores_mejor['cotizacion'] : null;
                    $mejor_oferta_cantidad = $valores_mejor ? (float) $valores_mejor['cantidad'] : null;
                    $mejor_oferta_oferente = $valores_mejor ? $valores_mejor['oferente'] : null;
                    $timezone_cliente = $this->cliente->customer_company->timeZone ?? 'UTC';
                    $timezone_servidor = config('app.timezone') ?? 'America/Argentina/Cordoba';
                    $mejor_oferta_hora = $valores_mejor && !empty($valores_mejor['creado']) 
                        ? Carbon::createFromFormat('Y-m-d H:i:s', $valores_mejor['creado'], $timezone_servidor)
                            ->setTimezone($timezone_cliente)
                            ->format('H:i:s') 
                        : null;

                    $oferta_puesto = null;
                    $empatado = false;
                    if ($this->is_online) {
                        foreach ($ranking as $item) {
                            if ($item['oferente_id'] == $oferente->id_offerer) {
                                $oferta_puesto = $item['oferta_puesto'];
                                $empatado = $item['empatado'];
                            }
                        }
                    }

                    $result[] = [
                        'id' => $producto->id,
                        'id_oferente' => $oferente->id_offerer,
                        'nombre' => $producto->nombre,
                        'descripcion' => $producto->descripcion,
                        'cantidad' => $producto->cantidad,
                        'oferta_puesto' => $oferta_puesto,
                        'empatado' => $empatado,
                        'oferta_minima' => $producto->oferta_minima,
                        'unidad' => $producto->unidad_medida->name,
                        'unidadID' => $producto->unidad_medida->id,
                        'unidades' => Measurement::getList(),
                        'valores' => [
                            'producto' => $oferta ? $oferta['producto'] : null,
                            'cotizacion' => $oferta ? (float) $oferta['cotizacion'] : null,
                            'cantidad' => $oferta ? (float) $oferta['cantidad'] : null,
                            'fecha' => $oferta ? $oferta['fecha'] : null,
                            'creado' => $oferta ? (isset($oferta['creado']) ? $oferta['creado'] : null) : null
                        ],
                        'valores_mejor' => [
                            'cotizacion' => $mejor_oferta_cotizacion,
                            'cantidad' => $mejor_oferta_cantidad,
                            'oferente' => $mejor_oferta_oferente,
                            'hora' => $mejor_oferta_hora
                        ],
                        'ranking' => $ranking
                    ];
                }
            }
        } catch (\Exception $e) {
            $result = null;
        }

        return $result;
    }

    public function getCotizacionesOutputByUser()
    {
        $items = $this->getCotizacionesOutput();
        $user = user();

        // Limpiar items y obtener ranking
        foreach ($items as $items_index => &$hijo) {
            if ($hijo['id_oferente'] != $user->offerer_company_id) {
                unset($items[$items_index]);
                continue;
            }

            $items_index++;
        }

        return array_values($items);
    }

    public function getSubastaOutput()
    {
        if (!$this->is_online) {
            return null;
        }

        $result = ['Items' => [], 'ItemsMejores' => [], 'Log' => []];
        try {
            $result['Items'] = $this->getCotizacionesOutput();
            // dd($result);
            foreach ($result['Items'] as $item) {
                if ($item['id_oferente'] == $item['valores_mejor']['oferente']) {

                    $producto = Producto::find($item['id']);
                    $oferente = Participante::where([
                        ['id_concurso', $this->attributes['id']],
                        ['id_offerer', $item['id_oferente']]
                    ])->first();
                    // Creamos la tabla de mejores ofertas
                    $result['ItemsMejores'][] = [
                        'sol_oferta_minima' => $producto->oferta_minima,
                        'sol_cantidad' => $producto->cantidad,
                        'sol_unidad' => $producto->unidad_medida->name,
                        'razon_social' => $oferente->company->business_name,
                        'producto' => $producto->nombre,
                        'cotizacion' => $item['valores_mejor']['cotizacion'],
                        'cantidad' => $item['valores_mejor']['cantidad'],
                        'hora' => $item['valores_mejor']['hora']
                    ];
                }
            }
            $result['Log'] = $this->getSubastaLogOutput();
        } catch (\Exception $e) {
            $result = null;
        }

        return $result;
    }

    public function getSubastaLogOutput()
    {
        $result = [];
        try {
            $cant_productos = $this->productos->count();

            foreach ($this->oferentes as $oferente) {
                $proposal = $oferente->proposals->where('is_economic', true)->first();

                if ($proposal) {
                    $i = -1;
                    foreach ($proposal->values as $oferta) {
                        $i++;
                        // Verificamos que sea una oferta o una anulación.
                        if (!$oferta['cotizacion'] && !$oferta['anulada']) {
                            continue;
                        }

                        $producto = Producto::find($oferta['producto']);
                        $index = $i + $cant_productos;

                        // Verificamos que la oferta sea distinta del registro anterior.
                        if (
                            isset($proposal->values[$index]) &&
                            ($proposal->values[$index]['cotizacion'] == $oferta['cotizacion'] &&
                                $proposal->values[$index]['cantidad'] == $oferta['cantidad'])
                        ) {
                            continue;
                        }

                        $timezone_cliente = $this->cliente->customer_company->timeZone ?? 'UTC';
                        $timezone_servidor = config('app.timezone') ?? 'America/Argentina/Cordoba';
                        $oferta_hora = $oferta && !empty($oferta['creado']) 
                            ? Carbon::createFromFormat('Y-m-d H:i:s', $oferta['creado'], $timezone_servidor)
                                ->setTimezone($timezone_cliente)
                                ->format('H:i:s') 
                            : null;

                        $result[] = [
                            'razon_social' => $oferente->company->business_name,
                            'producto' => $producto->nombre,
                            'sol_oferta_minima' => $producto->oferta_minima,
                            'sol_cantidad' => $producto->cantidad,
                            'cotizacion' => !$oferta['anulada'] ? $oferta['cotizacion'] : 'Anulada',
                            'creado' => $oferta_hora,
                            'unidad' => $oferta['unidad'],
                            'cantidad' => !$oferta['anulada'] ? $oferta['cantidad'] : '-'
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
        }
        return $result;
    }

    public function getSubastaOutputByUser()
    {
        $subasta_output = $this->getSubastaOutput();
        // dd($subasta_output);
        $user = user();

        // Limpiar Items y obtener ranking
        foreach ($subasta_output['Items'] as $items_index => &$hijo) {
            if ($hijo['id_oferente'] != $user->offerer_company_id) {
                unset($subasta_output['Items'][$items_index]);
                continue;
            }

            $items_index++;
        }

        $subasta_output['Items'] = array_values($subasta_output['Items']);

        return $subasta_output;
    }

    public function getSubastaStatusAttribute()
    {
        return $this->inicio_subasta ?
            (
                $this->inicio_subasta <= Carbon::now() && $this->countdown ?
                'En curso' : (
                    $this->inicio_subasta > Carbon::now() ?
                    'No comenzada' :
                    'Finalizada'
                )
            ) : null;
    }

    public function getAdjudicacionItemsAttribute()
    {
        $result = [];

        // Decodifica seguro (si no existe o es invalido, pasa a [])
        $parsed = json_decode($this->attributes['adjudicacion_items'] ?? '[]', true);
        if (! is_array($parsed)) {
            $parsed = [];
        }

        foreach ($parsed as $row) {
            // Definir valores con fallback
            $cantidad    = isset($row['cantidad'])    ? $row['cantidad']    : 0;
            $cantidadAdj = isset($row['cantidadAdj']) ? $row['cantidadAdj'] : 0;

            if ($cantidad > 0) {
                $result[] = [
                    'itemId'          => $row['itemId']          ?? null,
                    'itemSolicitado'  => $row['itemSolicitado']  ?? null,
                    'itemNombre'      => $row['itemNombre']      ?? null,
                    'oferenteId'      => $row['oferenteId']      ?? null,
                    'razonSocial'     => $row['razonSocial']     ?? null,
                    'cotUnitaria'     => $row['cotUnitaria']     ?? null,
                    'cantidad'        => $cantidad,
                    'cotizacion'      => $row['cotizacion']      ?? null,
                    'cantidadCot'     => $row['cantidadCot']     ?? null,
                    'cantidadAdj'     => $cantidadAdj,
                    'total'           => $row['total']           ?? null,
                    'moneda'          => $row['moneda']          ?? null,
                    'unidad'          => $row['unidad']          ?? null,
                    'fecha'           => $row['fecha']           ?? null,
                ];
            }
        }

        // **Importante**: devuelve SIEMPRE un array (vacío si no hay nada)
        return $result;
    }


    public function getFinSubastaAttribute()
    {
        return $this->inicio_subasta ? $this->inicio_subasta->copy()->addSeconds($this->attributes['duracion']) : null;
    }

    public function getCountdownAttribute()
    {

        return
            $this->inicio_subasta ?
            ($this->inicio_subasta <= Carbon::now() &&
                $this->fin_subasta >= Carbon::now() ?
                Carbon::now()->diffInSeconds($this->fin_subasta) :
                null
            ) :
            null;
    }

    public function getTimeleftAttribute()
    {
        return
            !$this->countdown && $this->fin_subasta >= Carbon::now() ?
            Carbon::now()->diffInSeconds($this->inicio_subasta) :
            null;
    }

    public function setAdditionalTime($seconds)
    {
        // Solo aplicar el tiempo adicional si no se ha aplicado antes
        if (!$this->attributes['tiempo_adicional_aplicado']) {
            $this->attributes['duracion'] = $this->attributes['duracion'] + $this->attributes['tiempo_adicional'];
            $this->attributes['tiempo_adicional_aplicado'] = true;
        }
    }

    public function getParsedDuracionAttribute()
    {
        return
            $this->attributes['duracion'] ?
            ([
                str_pad((string) Carbon::now()->diffInMinutes(Carbon::now()->addSeconds($this->attributes['duracion'])), 3, 0, STR_PAD_LEFT),
                Carbon::now()->diff(Carbon::now()->addSeconds($this->attributes['duracion']))->format('%S')
            ]) :
            null;
    }

    public function getAdjudicacionAnticipadaAttribute()
    {
        return $this->attributes['finalizar_si_oferentes_completaron_economicas'] == 'si' ? true : false;
    }

    public function getProductsOutputAttribute()
    {
        $result = [];
        foreach ($this->productos as $producto) {
            $result[] = [
                'id' => $producto->id,
                'name' => $producto->nombre,
                'quantity' => $producto->cantidad,
                'minimum_quantity' => $producto->oferta_minima,
                'measurement_id' => $producto->unidad_medida->id,
                'targetcost' => (float) $producto->targetcost,
                'description' => $producto->descripcion
            ];
        }

        return $result;
    }

    public function getAdjudicacionResultadosOutputAttribute()
    {
        $results = [];

        if (!$this->adjudicacion_items) {
            return $results;
        }

        try {
            // Obtener el oferente
            $oferente = $this->oferentes->where('id_offerer', user()->offerer_company_id)->first();
            foreach ($this->adjudicacion_items as $adjudicacion) {
                if ($adjudicacion['oferenteId'] == $oferente->id_offerer) {
                    array_push($results, [
                        'nombre' => $adjudicacion['itemNombre'],
                        'valores' => [
                            'cotizacion' => $adjudicacion['cotUnitaria'] * $adjudicacion['cantidadAdj'],
                            'cantidad_solicitada' => $adjudicacion['cantidad'],
                            'precio_unitario' => $adjudicacion['cotUnitaria'],
                            'cantidad_adjudicada' => $adjudicacion['cantidadAdj'],
                            'plazo_dias' => $adjudicacion['fecha'],
                            'unidad' => $adjudicacion['unidad'],
                        ],
                        'moneda' => $adjudicacion['moneda'],
                    ]);
                }
            }
        } catch (\Exception $e) {
        }

        return $results;
    }

    public function getIsChatEnabledAttribute()
    {
        $chat_enabled = false;
        if ($this->is_online) {
            $chat_enabled = (!$this->countdown && $this->finalizacion_consultas > Carbon::now()) || ($this->countdown && $this->chat);
        } else {
            $chat_enabled = $this->finalizacion_consultas > Carbon::now();
        }

        return $chat_enabled;
    }

    public function getOferentesAInvitarList($only_associated = true)
    {
        $result = [];

        if ($only_associated) {
            $users = user()->getRelatedByRoleSlug(UserType::TYPES['offerer']);
        } else {
            $users = User::where('type.code', UserType::TYPES['offerer'])->get();
        }

        $users = $users->filter(function ($item) {
            return $item->concursos_invitado->where('id', $this->id)->count() === 0;
        });

        foreach ($users as $user) {
            // Razón social
            $text = $user->offerer_company->business_name;
            // Cuit
            $text =
                (
                    $user->offerer_company->cuit || !empty($user->offerer_company->cuit)
                ) ?
                $text . ', ' . $user->offerer_company->cuit :
                $text;
            // Incluir rubros
            $rubros = [];
            if ($user->offerer_company->rubros) {
                foreach ($user->offerer_company->rubros as $rubro) {
                    $rubros[] = [
                        'id' => $rubro->id,
                        'text' => $rubro->nombre
                    ];
                }
            }

            $alcances = $user->offerer_company->alcances;

            $result[] = [
                'id' => $user->id,
                'text' => $text,
                'rubro' => $rubros,
                'alcance_pais' => $alcances ? $alcances->where('id_provincia', null)->where('id_ciudad', null) : [],
                'alcance_provincia' => $alcances ? $alcances->where('id_pais', null)->where('id_ciudad', null) : [],
                'alcance_ciudad' => $alcances ? $alcances->where('id_pais', null)->where('id_provincia', null) : []
            ];
        }

        return $result;
    }

    public function getOferentesEtapaPreparacionAttribute()
    {
        return $this->oferentes->where('is_seleccionado', true);
    }

    public function getOferentesEtapaConvocatoriaAttribute()
    {
        return $this->oferentes
            ->where('has_invitacion_vencida', false)
            ->where('is_invitacion_pendiente', true)
            ->where('is_seleccionado', false);
    }

    public function getOferentesEtapaTecnicaAttribute()
    {
        return $this->oferentes
            ->where('rechazado', false)
            ->whereIn('etapa_actual', [
                Participante::ETAPAS['tecnica-pendiente'],
                Participante::ETAPAS['tecnica-presentada']
            ]);
    }

    public function getOferentesEtapaEconomicaAttribute()
    {
        return $this->oferentes
            ->where('rechazado', false)
            ->whereIn('etapa_actual', Participante::ETAPAS_ECONOMICAS);
    }

    public function getOferentesEtapaEvaluacionAttribute()
    {
        return $this->oferentes
            ->where('is_adjudicacion_aceptada', true)
            ->where('id_evaluacion', null);
    }

    public function getOferentesEtapaInformesAttribute()
    {
        return $this->oferentes()->whereHas('evaluacion');
    }

    public function getOferentesData()
    {
        $results = collect();
        // dd($this->oferentes);
        foreach ($this->oferentes as $oferente) {

            $parsed_go_documents = [];
            $gcg_rows = new \StdClass();
            $gcg_rows->success = false;
            $gcg_rows->message = 'Sin Verificar';

            // Documentación GO
            if ($this->is_go) {
                $documentService = new DocumentService();

                $validation = json_decode(
                    $documentService->getDocumentation(
                        $this,
                        $oferente->id_conductor,
                        $oferente->id_vehiculo,
                        $oferente->id_trailer
                    )
                );

                // Agregamos los resultados de habilitación a nivel Documento.
                $parsed_go_documents = $oferente->parsed_go_documents;
                foreach ($parsed_go_documents->go_driver_documents as &$go_document) {
                    $go_document->success = $validation->data->driver && $oferente->id_conductor ? $validation->data->driver->habilitado : false;
                    $go_document->message = $validation->data->driver && $oferente->id_conductor ? $validation->data->driver->habilitacion : 'Sin Verificar';
                }

                foreach ($parsed_go_documents->go_vehicle_documents as &$go_document) {
                    $go_document->success = $validation->data->vehicle && $oferente->id_vehiculo ? $validation->data->vehicle->habilitado : false;
                    $go_document->message = $validation->data->vehicle && $oferente->id_vehiculo ? $validation->data->vehicle->habilitacion : 'Sin Verificar';
                }

                foreach ($parsed_go_documents->go_trailer_documents as &$go_document) {
                    $go_document->success = $validation->data->trailer && $oferente->id_trailer ? $validation->data->trailer->habilitado : false;
                    $go_document->message = $validation->data->trailer && $oferente->id_trailer ? $validation->data->trailer->habilitacion : 'Sin Verificar';
                }

                // Agregamos los resultados de habilitación a nivel Oferente.
                if (!$oferente->id_conductor || !$oferente->id_vehiculo || !$oferente->id_trailer) {
                    $gcg_rows->message = 'El Oferente no ha seleccionado Conductor y/o Vehículo.';
                } else {
                    // if (!$validation->data->driver->habilitado ||
                    //     !$validation->data->vehicle->habilitado ||
                    //     !$validation->data->trailer->habilitado) {

                    //     $gcg_rows->message = 'El Oferente no cumple con los requisitos para ser adjudicado.';
                    // } else {
                    $gcg_rows->success = true;
                    $gcg_rows->message = 'El Oferente cumple con los requisitos para ser adjudicado.';
                    // }
                }
            }

            $result = new \StdClass();

            $result->id = $oferente->id;
            $result->name = $oferente->company->business_name;
            $result->razon_social = $oferente->company->business_name;
            $result->cuit = $oferente->company->cuit;
            $result->driver_id = $oferente->id_conductor;
            $result->driver_description = isset($validation->data->driver->nombre) ? $validation->data->driver->nombre : '';
            $result->vehicle_id = $oferente->id_vehiculo;
            $result->vehicle_description = isset($validation->data->vehicle->dominio) ? $validation->data->vehicle->dominio : '';
            $result->trailer_id = $oferente->id_trailer;
            $result->trailer_description = isset($validation->data->trailer->dominio) ? $validation->data->trailer->dominio : '';
            $result->documents = (array) $parsed_go_documents;
            $result->success = $gcg_rows->success;
            $result->message = $gcg_rows->message;

            $results = $results->push($result);
        }

        return $results;
    }

    public function getCanceledAttribute()
    {
        return $this->where('adjudicado', false)->where('deleted_at', '<>', null);
    }

    public function getLosingAttribute()
    {
        # code...
    }

    public function getAdjudicationTypeAttribute()
    {
        $type = $this->oferentes->whereIn('adjudicacion', [1, 2, 3])->first()->adjudicacion;
        $adjType = $type === 1 ? 'Integral' : ($type === 2 ? 'Individual' : 'Manual');
        return $adjType;
    }

    public function getOfferersAdjudicatedAttribute()
    {
        $results = collect();
        $oferentesPending = $this->oferentes->whereIn('is_adjudicacion_pendiente', true);
        $oferentesAccepted = $this->oferentes->whereIn('is_adjudicacion_aceptada', true);
        $oferentesRejected = $this->oferentes->whereIn('is_adjudicacion_rechazada', true);

        if (count($oferentesPending) > 0) {
            foreach ($oferentesPending as $offerer) {
                $results->push($offerer->company->business_name);
            }
        }

        if (count($oferentesAccepted) > 0) {
            foreach ($oferentesAccepted as $offerer) {
                $results->push($offerer->company->business_name);
            }
        }

        if (count($oferentesRejected) > 0) {
            foreach ($oferentesRejected as $offerer) {
                $results->push($offerer->company->business_name);
            }
        }

        return $results;
    }

    public function getUsersEvalTecAttribute()
    {
        return User::whereIn('id', explode(',', $this->usuario_califica_reputacion))->get();
    }

    public function getEvalOfferersAdjudicatedAttribute()
    {
        $results = collect();
        $oferentesAccepted = $this->oferentes->whereIn('is_adjudicacion_aceptada', true);

        if (count($oferentesAccepted) > 0) {
            foreach ($oferentesAccepted as $offerer) {
                if (isset($offerer->evaluacion)) {
                    $results->push($offerer->company->business_name);
                }
            }
        }


        return $results;
    }

    public function getTodosRevisadosAttribute()
    {
        $oferentes = $this->oferentes;
        $nuevasRondas = $this->segunda_ronda_habilita;
        $pending_offerers = collect();
        $enabled_offerers = $oferentes->where('has_economica_presentada', true);
        if ($nuevasRondas == 'no') {
            if ($this->fecha_limite_economicas >= Carbon::now()) {
                $pending_offerers = $pending_offerers->merge(
                    $oferentes->where('is_economica_pendiente', true)
                );
            }
        }

        if ($nuevasRondas == 'si') {
            $ronda = $this->ronda_actual;
            switch ($ronda) {
                case 2:
                    if ($this->segunda_ronda_fecha_limite >= Carbon::now()) {
                        $pending_offerers = $pending_offerers->merge(
                            $oferentes->where('is_economica_pendiente', true)
                        );
                    }
                    break;
                case 3:
                    if ($this->tercera_ronda_fecha_limite >= Carbon::now()) {
                        $pending_offerers = $pending_offerers->merge(
                            $oferentes->where('is_economica_pendiente', true)
                        );
                    }
                    break;
                case 4:
                    if ($this->cuarta_ronda_fecha_limite >= Carbon::now()) {
                        $pending_offerers = $pending_offerers->merge(
                            $oferentes->where('is_economica_pendiente', true)
                        );
                    }
                    break;
                case 5:
                    if ($this->quinta_ronda_fecha_limite >= Carbon::now()) {
                        $pending_offerers = $pending_offerers->merge(
                            $oferentes->where('is_economica_pendiente', true)
                        );
                    }
                    break;
            }
        }


        return
            $enabled_offerers->where('has_economica_revisada', true)->count() > 0 && $pending_offerers->count() === 0;
    }

    public function getAlgunoRevisadoAttribute()
    {
        return $this->oferentes
            ->where('has_economica_revisada', true)
            ->count() > 0;
    }

    public function participantesFiltradosPorEtapa()
    {
        return $this->oferentes()->filtrarPorEtapa();
    }
}
