<?php

use Carbon\Carbon;
use App\Models\Concurso;
use App\Models\Producto;
use App\Models\Participante;

function calcularEtapaAnalisisOfertas(&$list, $concurso_id)
{
    $concurso = Concurso::find($concurso_id);
    $cant_productos = $concurso->productos->count();

    $rondas = $concurso->ronda_actual - 1;
    $titleRound = [
        0 => [
            'title' => '1ª Ronda de oferta',
            'active' => false,
            'ref' => 'PrimeraRonda',
            'ConcursoEconomicas' => [
                'mejoresOfertas' => [
                    'mejorIntegral' => [],
                    'mejorIndividual' => [],
                    'mejorManual' => [],
                ],
                'adjudicacion' => [],
                'proveedores' => []
            ]
        ],
        1 => [
            'title' => '2ª Ronda de oferta',
            'active' => false,
            'ref' => 'SegundaRonda',
            'ConcursoEconomicas' => [
                'mejoresOfertas' => [
                    'mejorIntegral' => [],
                    'mejorIndividual' => [],
                    'mejorManual' => [],
                ],
                'adjudicacion' => [],
                'proveedores' => []
            ]
        ],
        2 => [
            'title' => '3ª Ronda de oferta',
            'active' => false,
            'ref' => 'TerceraRonda',
            'ConcursoEconomicas' => [
                'mejoresOfertas' => [
                    'mejorIntegral' => [],
                    'mejorIndividual' => [],
                    'mejorManual' => [],
                ],
                'adjudicacion' => [],
                'proveedores' => []
            ]
        ],
        3 => [
            'title' => '4ª Ronda de oferta',
            'active' => false,
            'ref' => 'CuartaRonda',
            'ConcursoEconomicas' => [
                'mejoresOfertas' => [
                    'mejorIntegral' => [],
                    'mejorIndividual' => [],
                    'mejorManual' => [],
                ],
                'adjudicacion' => [],
                'proveedores' => []
            ]
        ],
        4 => [
            'title' => '5ª Ronda de oferta',
            'active' => false,
            'ref' => 'QuintaRonda',
            'ConcursoEconomicas' => [
                'mejoresOfertas' => [
                    'mejorIntegral' => [],
                    'mejorIndividual' => [],
                    'mejorManual' => [],
                ],
                'adjudicacion' => [],
                'proveedores' => []
            ]
        ]
    ];

    $ids = $concurso->productos->pluck('id');
    $nombres = $concurso->productos->pluck('nombre');
    $cantidades = $concurso->productos->pluck('cantidad');
    $productos = $concurso->productos;
    
    

    if ($concurso->technical_includes) {
        $oferentes = $concurso->oferentes->where('has_tecnica_aprobada');
    } else {
        $oferentes = $concurso->oferentes->where('has_invitacion_aceptada');
    }

    for ($ronda = 0; $ronda <= $rondas; $ronda++) {
        $roundTab = $titleRound[$ronda];
        $roundTab['active'] = $ronda === $rondas ? true : false;
        $oferenteItems = [];

        foreach ($oferentes as $oferente) {
            // GO: Ignoramos aquellos oferentes que no tienen documentación habilitada.
            if ($concurso->is_go && $oferentes->where('id', $oferente->id)->where('success', false)->count() > 0) {
                continue;
            }

            $oferenteItems[] = setOferente($concurso, $oferente, $ronda);
        }
        
        if (count($oferenteItems) > 0) {
            // Totales válidos (> 0) para determinar el "mejor"
            $totalesValidos = [];
            foreach ($oferenteItems as $g => $f) {
                $t = isset($f['total']) ? (float)$f['total'] : 0.0;
                if ($t > 0) {
                    $totalesValidos[] = $t;
                }
            }

            // Venta o subasta ascendente => se maximiza; Compra/descendente => se minimiza
            $maximize = (
                ($concurso->is_online && $concurso->tipo_valor_ofertar === 'ascendente')
                || ($concurso->tipo_licitacion === 'venta')
            );

            if (!empty($totalesValidos)) {
                $best = $maximize ? max($totalesValidos) : min($totalesValidos);

                foreach ($oferenteItems as $g => $f) {
                    $total = isset($f['total']) ? (float)$f['total'] : 0.0;

                    if ($total <= 0 || $best <= 0) {
                        $oferenteItems[$g]['difvsmejorofert'] = 0.0;
                        continue;
                    }

                    // Gap en % respecto del mejor (siempre ≥ 0)
                    $gap = $maximize
                        ? (($best - $total) / $best) * 100.0   // mayor es mejor (venta/asc)
                        : (($total - $best) / $best) * 100.0;  // menor es mejor (compra/desc)

                    $oferenteItems[$g]['difvsmejorofert'] = round(max($gap, 0.0), 2);
                }
            } else {
                // Sin totales válidos
                foreach ($oferenteItems as $g => $f) {
                    $oferenteItems[$g]['difvsmejorofert'] = 0.0;
                }
            }
            
            $mejorIntegral = setMejorIntegral($concurso, $oferenteItems);
            $posicion = array_search(
                $mejorIntegral['idOferente'],
                array_column($oferenteItems, 'oferente_id'),
                true // <--- búsqueda estricta
            );

            // Fallback por si en algún flujo el id que guardaste es el de Participante
            if ($posicion === false) {
                $posicion = array_search(
                    $mejorIntegral['idOferente'],
                    array_column($oferenteItems, 'OferenteId'),
                    true
                );
            }

            if ($posicion !== false) {
                $oferenteItems[$posicion]['mejorOfertaIntegral'] = true;
            }
            $oferenteItems[$posicion]['mejorOfertaIntegral'] = true;
            $mejorIndividual = setMejorIndividual($concurso, $oferenteItems);
            $oferenteItems = $mejorIndividual['oferentes'];
            $mejorManual = setMejorManual($concurso, $mejorIntegral);
            $roundTab['ConcursoEconomicas']['cantidadesSolicitadas'] = $cantidades;
            $roundTab['ConcursoEconomicas']['productos'] = $productos;
            $roundTab['ConcursoEconomicas']['mejoresOfertas']['mejorIntegral'] = $mejorIntegral;
            $roundTab['ConcursoEconomicas']['mejoresOfertas']['mejorIndividual'] = $mejorIndividual['mejorIndividual'];
            $roundTab['ConcursoEconomicas']['mejoresOfertas']['mejorManual'] = $mejorManual;
            $roundTab['ConcursoEconomicas']['proveedores'] = $oferenteItems;
        }
        $list['RondasOfertas'][$ronda] = $roundTab;
    }
}

function isMejorOferta($concurso, $total, $mejorOferta)
{
    if ($mejorOferta === 0) {
        return true;
    }

     $maximize =
        ($concurso->is_online && $concurso->tipo_valor_ofertar === 'ascendente')
        || ($concurso->tipo_licitacion === 'venta');

    return $maximize ? ($total > $mejorOferta) : ($total < $mejorOferta);
}

function getEconomicDoc($economic_proposal)
{
    $fileName = isset($economic_proposal->documents->first()->filename) ? $economic_proposal->documents->first()->filename : null;
    return $fileName;
}

function getCostDocument($concurso, $economic_proposal)
{
    $fileName = null;
    if ($concurso->estructura_costos === 'si') {
        $fileName = isset($economic_proposal->documents->first()->filename) ? $economic_proposal->documents->last()->filename : null;
    }
    return $fileName;
}

function getApuDocument($concurso, $economic_proposal)
{
    $fileName = null;
    if ($concurso->apu === 'si') {
        $fileName = isset($economic_proposal->documents->first()->filename) ? $economic_proposal->documents->last()->filename : null;
    }
    return $fileName;
}

function setOferente($concurso, $oferente, $ronda)
{
    // type_id 2 = a propuesta economica
    $oferenteItems = [];
    $ronda += 1;

    $evaluation = $oferente->analisis_tecnica_valores ? $oferente->analisis_tecnica_valores[0] : null;
    $evaluationalcanzada = $evaluation ? number_format($evaluation['alcanzado'], 1) : 'No Aplica';

    $values = [];
    $total = 0.0;
    $totaltargetcost_ofertado   = 0.0; // Σ TC * cantidad COTIZADA
    $totaltargetcost_solicitado = 0.0; // Σ TC * cantidad SOLICITADA (para %)

    $isAscendente = ($concurso->tipo_valor_ofertar === 'ascendente');
    $isVenta = ($concurso->tipo_licitacion === 'venta');

    // Moneda segura (evita Notice si no hay relación)
    $monedaNombre = null;
    if (isset($concurso->tipo_moneda) && isset($concurso->tipo_moneda->nombre)) {
        $monedaNombre = $concurso->tipo_moneda->nombre;
    }

    if (!$oferente->is_concurso_rechazado) {
        $economic_proposal = $oferente->economic_proposal;
        if ($economic_proposal) {
            $economicByRound = $economic_proposal
                ->where('participante_id', $oferente->id)
                ->where('numero_ronda', $ronda)
                ->where('type_id', 2)
                ->first();

            if ($economicByRound) {
                $i = 0;

                // Aseguramos que values sea iterable
                $valores = is_iterable($economicByRound->values) ? $economicByRound->values : [];
                foreach ($valores as $propuesta) {
                    $i++;
                    if ($i > $concurso->productos->count()) {
                        break;
                    }

                    // Validar que exista el id de producto
                    $prodId = $propuesta['producto'] ?? null;
                    if (!$prodId) {
                        // si no hay producto, saltamos este ítem
                        continue;
                    }

                    $producto = Producto::find($prodId);
                    if (!$producto) {
                        // Producto inexistente (borrado/cambiado). Lo omitimos para no romper.
                        continue;
                    }

                    $cotizacion = isset($propuesta['cotizacion']) ? (float)$propuesta['cotizacion'] : 0.00;
                    $cantidad   = isset($propuesta['cantidad'])   ? (int)$propuesta['cantidad']   : 0;

                    // Targetcost y unidad protegidos
                    $targetcost = isset($producto->targetcost) ? (float)$producto->targetcost : 0.00;
                    $unidad     = isset($producto->unidad_medida) && isset($producto->unidad_medida->name)
                                    ? $producto->unidad_medida->name
                                    : null;

                    $subtotal           = $cotizacion * $cantidad;
                    $subtotaltargetcost = $targetcost * $cantidad; // COTIZADO

                    // Denominador "pedido" (para %)
                    $prodConcurso  = $concurso->productos->firstWhere('id', $producto->id);
                    $qtySolicitada = $prodConcurso ? (int)$prodConcurso->cantidad : $cantidad; // fallback
                    $subTC_solicitado = $targetcost * $qtySolicitada;

                    // Ahorro por ítem
                    $ahorro_abs  = 0.00;
                    $ahorro_porc = 0.00;
                    if ($subtotaltargetcost > 0.00) {
                        $ahorro_abs  = $targetcost > 0.00 ? ($subtotaltargetcost - $subtotal) : 0.00;
                        $ahorro_porc = $targetcost > 0.00 ? (($ahorro_abs / $subtotaltargetcost) * 100) : 0.00;
                    }

                    // Ganancia por ÍTEM (solo ascendente/venta)
                    $ganancia_item_abs  = null;
                    $ganancia_item_porc = null;
                    if (($isAscendente || $isVenta) && $subtotaltargetcost > 0.00) {
                        $ganancia_item_abs  = $subtotal - $subtotaltargetcost;
                        $ganancia_item_porc = ($ganancia_item_abs / $subtotaltargetcost) * 100;
                    }

                    $values[] = [
                        'id'               => $producto->id,
                        'nombre'           => $producto->nombre,
                        'descripcion'      => $producto->descripcion ?? null,
                        'cotizacion'       => $cotizacion,
                        'cantidad'         => (int)$cantidad,
                        'fecha'            => $propuesta['fecha'] ?? 0,
                        'subtotal'         => $subtotal,
                        'oferente_id'      => isset($oferente->company) ? $oferente->company->id : null,
                        'razonSocial'      => isset($oferente->company) ? $oferente->company->business_name : null,
                        'tipoAdjudicacion' => $oferente->adjudicacion ?? null,
                        'moneda'           => $monedaNombre,
                        'unidad'           => $unidad,
                        'targetcost'       => $targetcost,
                        'ahorro_porc'      => $ahorro_porc,
                        'ahorro_abs'       => $ahorro_abs,
                        'ganancia_abs'     => $ganancia_item_abs,
                        'ganancia_porc'    => $ganancia_item_porc,
                        'tipoValorOferta'  => $oferente->tipo_valor_ofertar ?? null,
                        'isMenorCantidad'  => false,
                        'isMenorPlazo'     => false,
                        'isMejorCotizacion'=> false,
                    ];

                    $total                      += $subtotal;
                    $totaltargetcost_ofertado   += $subtotaltargetcost;
                    $totaltargetcost_solicitado += $subTC_solicitado;
                }

                // Totales (abs contra OFERTADO, % contra SOLICITADO)
                $totalahorro_abs  = $totaltargetcost_ofertado   > 0.00 ? ($totaltargetcost_ofertado - $total) : 0.00;
                $totalahorro_porc = $totaltargetcost_solicitado > 0.00 ? ($totalahorro_abs / $totaltargetcost_solicitado) * 100 : 0.00;

                $ganancia_abs  = null;
                $ganancia_porc = null;
                if ($isAscendente || $isVenta) {
                    $ganancia_abs  = $total - $totaltargetcost_ofertado;
                    $ganancia_porc = $totaltargetcost_solicitado > 0.00 ? ($ganancia_abs / $totaltargetcost_solicitado) * 100 : null;
                }

                $posicion    = array_search($economicByRound->payment_deadline, array_column(Participante::PLAZOS_PAGO, 'id'));
                $condicionId = array_search($economicByRound->payment_condition, array_column(Participante::CONDICIONES_PAGO, 'id'));

                $ConcursoEconomicas = [
                    'OferenteId'          => $oferente->id,
                    'oferente_id'         => isset($oferente->company) ? $oferente->company->id : null,
                    'nombreOferente'      => isset($oferente->company) ? $oferente->company->business_name : null,
                    'razonSocial'         => isset($oferente->company) ? $oferente->company->business_name : null,
                    'tipoAdjudicacion'    => $oferente->adjudicacion ?? null,
                    'tipoValorOferta'     => $oferente->tipo_valor_ofertar ?? null,
                    'items'               => count($values) > 0 ? $values : [],
                    'total'               => $total,
                    'ahorro_abs'          => $totalahorro_abs,
                    'ahorro_porc'         => $totalahorro_porc,
                    'ganancia_abs'        => $ganancia_abs,
                    'ganancia_porc'       => $ganancia_porc,
                    'mejorOfertaIntegral' => false,
                    'difvsmejorofert'     => 0.00,
                    'evaluationalcanzada' => $evaluationalcanzada,
                    'file_path'           => filePath($oferente->file_path ?? null),
                    'porpuesta_economica' => $economicByRound ? getEconomicDoc($economicByRound) : null,
                    'planilla_costos'     => $economicByRound ? getCostDocument($concurso, $economicByRound) : null,
                    'analisis_apu'        => $economicByRound ? getApuDocument($concurso, $economicByRound) : null,
                    'comentarios'         => $economicByRound ? $economicByRound->comment : null,
                    'fechaPresentacion'   => $economicByRound && isset($economicByRound->updated_at)
                                                ? $economicByRound->updated_at->format('d-m-Y H:i')
                                                : null,
                    'cuit'                => isset($oferente->company) ? $oferente->company->cuit : null,
                    'plazoPago'           => $posicion !== false ? Participante::PLAZOS_PAGO[$posicion]['text'] : null,
                    'condicionPago'       => $condicionId !== false ? Participante::CONDICIONES_PAGO[$condicionId]['text'] : null,
                    'isRechazado'         => $oferente->is_concurso_rechazado,
                    'isVencido'           => count($values) === 0, // si no pudo cargar items válidos, tratamos como vencido
                ];
                $oferenteItems = $ConcursoEconomicas;

            } else {
                // No hubo propuesta para esa ronda
                $oferenteItems = [
                    'OferenteId'          => $oferente->id,
                    'oferente_id'         => isset($oferente->company) ? $oferente->company->id : null,
                    'nombreOferente'      => isset($oferente->company) ? $oferente->company->business_name : null,
                    'razonSocial'         => isset($oferente->company) ? $oferente->company->business_name : null,
                    'tipoAdjudicacion'    => null,
                    'tipoValorOferta'     => null,
                    'items'               => [],
                    'total'               => null,
                    'ahorro_abs'          => null,
                    'ahorro_porc'         => null,
                    'ganancia_abs'        => null,
                    'ganancia_porc'       => null,
                    'difvsmejorofert'     => null,
                    'mejorOfertaIntegral' => false,
                    'evaluationalcanzada' => null,
                    'file_path'           => filePath($oferente->file_path ?? null),
                    'porpuesta_economica' => null,
                    'planilla_costos'     => null,
                    'analisis_apu'        => null,
                    'comentarios'         => null,
                    'fechaPresentacion'   => null,
                    'cuit'                => isset($oferente->company) ? $oferente->company->cuit : null,
                    'plazoPago'           => null,
                    'condicionPago'       => null,
                    'isRechazado'         => $oferente->is_concurso_rechazado,
                    'isVencido'           => true,
                ];
            }
        } else {
            // Sin economic_proposal
            $oferenteItems = [
                'OferenteId'          => $oferente->id,
                'oferente_id'         => isset($oferente->company) ? $oferente->company->id : null,
                'nombreOferente'      => isset($oferente->company) ? $oferente->company->business_name : null,
                'razonSocial'         => isset($oferente->company) ? $oferente->company->business_name : null,
                'tipoAdjudicacion'    => null,
                'tipoValorOferta'     => null,
                'items'               => [],
                'total'               => null,
                'ahorro_abs'          => null,
                'ahorro_porc'         => null,
                'ganancia_abs'        => null,
                'ganancia_porc'       => null,
                'difvsmejorofert'     => null,
                'mejorOfertaIntegral' => false,
                'evaluationalcanzada' => null,
                'file_path'           => filePath($oferente->file_path ?? null),
                'porpuesta_economica' => null,
                'planilla_costos'     => null,
                'analisis_apu'        => null,
                'comentarios'         => null,
                'fechaPresentacion'   => null,
                'cuit'                => isset($oferente->company) ? $oferente->company->cuit : null,
                'plazoPago'           => null,
                'condicionPago'       => null,
                'isRechazado'         => $oferente->is_concurso_rechazado,
                'isVencido'           => true,
            ];
        }
    }

    if ($oferente->is_concurso_rechazado) {
        $oferenteItems = [
            'OferenteId'          => $oferente->id,
            'oferente_id'         => isset($oferente->company) ? $oferente->company->id : null,
            'nombreOferente'      => isset($oferente->company) ? $oferente->company->business_name : null,
            'razonSocial'         => isset($oferente->company) ? $oferente->company->business_name : null,
            'tipoAdjudicacion'    => null,
            'tipoValorOferta'     => null,
            'items'               => [],
            'total'               => null,
            'ahorro_abs'          => null,
            'ahorro_porc'         => null,
            'ganancia_abs'        => null,
            'ganancia_porc'       => null,
            'mejorOfertaIntegral' => false,
            'evaluationalcanzada' => null,
            'file_path'           => filePath($oferente->file_path ?? null),
            'porpuesta_economica' => null,
            'planilla_costos'     => null,
            'analisis_apu'        => null,
            'comentarios'         => null,
            'fechaPresentacion'   => null,
            'cuit'                => isset($oferente->company) ? $oferente->company->cuit : null,
            'plazoPago'           => null,
            'isRechazado'         => $oferente->is_concurso_rechazado,
            'isVencido'           => false,
        ];
    }

    return $oferenteItems;
}



function setMejorIntegral($concurso, $oferentes)
{
    $productos = $concurso->productos;

    // Criterio unificado (igual que en calcularEtapaAnalisisOfertas)
    $maximize = (
        ($concurso->is_online && $concurso->tipo_valor_ofertar === 'ascendente')
        || ($concurso->tipo_licitacion === 'venta')
    );

    // ---------- 1) Construir candidatos ESTRICTOS ----------
    $candidatos = [];
    foreach ($oferentes as $oferente) {
        if (!empty($oferente['isRechazado'])) continue;
        if (empty($oferente['items']) || !is_array($oferente['items'])) continue;

        // Debe cotizar todos los ítems con cantidad EXACTA solicitada
        $ok = true;
        foreach ($oferente['items'] as $item) {
            $prod = $productos->find($item['id'] ?? null);
            $cot  = (float)($item['cotizacion'] ?? 0);
            $qty  = (int)($item['cantidad'] ?? 0);
            if (!($cot > 0.0 && $prod && $qty === (int)$prod->cantidad)) {
                $ok = false; break;
            }
        }
        if (!$ok) continue;

        $total = (float)($oferente['total'] ?? 0.0);
        if ($total <= 0.0) continue;

        $candidatos[] = $oferente;
    }

    // ---------- 2) Fallback si no hubo estrictos: cualquiera con total>0 ----------
    if (empty($candidatos)) {
        foreach ($oferentes as $oferente) {
            if (!empty($oferente['isRechazado'])) continue;
            $total = (float)($oferente['total'] ?? 0.0);
            if ($total <= 0.0) continue;
            $candidatos[] = $oferente;
        }
    }

    // Si sigue sin haber candidatos, devolvemos valores neutros
    if (empty($candidatos)) {
        return [
            'idOferente'       => 0,
            'nombreOferente'   => '',
            'razonSocial'      => '',
            'tipoAdjudicacion' => '',
            'total'            => 0.00,
            'ahorro_porc'      => 0.00,
            'ahorro_abs'       => 0.00,
            'ganancia_abs'     => null,
            'ganancia_porc'    => null,
            'items'            => [],
        ];
    }

    // ---------- 3) Elegir el mejor según min/max ----------
    $bestIndex = 0;
    $bestTotal = (float)$candidatos[0]['total'];
    $bestFecha = $candidatos[0]['fechaPresentacion'] ?? null;

    foreach ($candidatos as $i => $of) {
        $t = (float)$of['total'];
        $f = $of['fechaPresentacion'] ?? null;
        
        $isBetter = $maximize ? ($t > $bestTotal) : ($t < $bestTotal);
        
        // Si hay empate en el total, gana el que presentó primero (fecha más antigua)
        if ($t == $bestTotal && $f !== null && $bestFecha !== null) {
            // Convertir fechas a timestamp para comparar
            $fechaActual = strtotime($f);
            $fechaMejor = strtotime($bestFecha);
            
            if ($fechaActual !== false && $fechaMejor !== false && $fechaActual < $fechaMejor) {
                $isBetter = true;
            }
        }
        
        if ($isBetter) {
            $bestIndex = $i;
            $bestTotal = $t;
            $bestFecha = $f;
        }
    }

    $win = $candidatos[$bestIndex];

    // Recalcular ganancia integral solo para venta / ascendente
    $ganancia_abs  = null;
    $ganancia_porc = null;
    if ($maximize && !empty($win['items'])) {
        $sumSubtotal = 0.0;
        $sumTarget   = 0.0;
        foreach ($win['items'] as $it) {
            $sub = isset($it['subtotal'])
                ? (float)$it['subtotal']
                : ((float)($it['cotizacion'] ?? 0) * (float)($it['cantidad'] ?? 0));
            $sumSubtotal += $sub;
            $sumTarget   += (float)($it['targetcost'] ?? 0) * (float)($it['cantidad'] ?? 0);
        }
        if ($sumTarget > 0.0) {
            $ganancia_abs  = $sumSubtotal - $sumTarget;
            $ganancia_porc = ($ganancia_abs / $sumTarget) * 100;
        }
    }

    return [
        'idOferente'       => $win['oferente_id'] ?? ($win['OferenteId'] ?? 0), // company_id preferente
        'nombreOferente'   => $win['nombreOferente'] ?? '',
        'razonSocial'      => $win['razonSocial'] ?? '',
        'tipoAdjudicacion' => $win['tipoAdjudicacion'] ?? '',
        'total'            => (float)($win['total'] ?? 0.0),
        'ahorro_porc'      => (float)($win['ahorro_porc'] ?? 0.0),
        'ahorro_abs'       => (float)($win['ahorro_abs']  ?? 0.0),
        'ganancia_abs'     => $ganancia_abs,
        'ganancia_porc'    => $ganancia_porc,
        'items'            => $win['items'] ?? [],
    ];
}


function setMejorIndividual($concurso, $oferentes)
{
    $TipoAdjudicacion = null;

    $isAscendente = isset($concurso->tipo_valor_ofertar) && $concurso->tipo_valor_ofertar === 'ascendente';
    $isVenta      = isset($concurso->tipo_licitacion)   && $concurso->tipo_licitacion === 'venta';

    $maximize = (
        ($concurso->is_online && $concurso->tipo_valor_ofertar === 'ascendente')
        || ($concurso->tipo_licitacion === 'venta')
    );

    $mejorIndividualItems = [];  // clave: producto_id → mejor entrada
    // Recorremos productos del concurso
    foreach ($concurso->productos as $producto) {
        $prodId         = $producto->id;
        $prodCantidad   = (int) $producto->cantidad;

        // Pool por oferente (solo si tiene ese producto)
        $plazos        = []; // oferenteId => fecha
        $cantidades    = []; // oferenteId => cantidad
        $cotizaciones  = []; // oferenteId => cotizacion
        $itemsRefs     = []; // oferenteId => ['rowIndex' => idx en $oferentes, 'itemIndex' => idx en items]

        // Construir los pools seguro por producto_id
        foreach ($oferentes as $rowIndex => $row) {
            if (empty($row['items']) || !is_array($row['items'])) continue;

            // Buscar el índice del ítem del producto actual dentro del array del oferente
            $idsDeItems = array_column($row['items'], 'id');
            $itemIndex  = array_search($prodId, $idsDeItems, true);
            if ($itemIndex === false) continue; // este oferente no cotizó este producto

            $item = $row['items'][$itemIndex];

            $cotizacion = isset($item['cotizacion']) ? (float)$item['cotizacion'] : 0.00;
            $cantidad   = isset($item['cantidad'])   ? (int)$item['cantidad']   : 0;
            $fecha      = isset($item['fecha'])      ? $item['fecha']           : 0;

            // Guardar referencias y valores
            $itemsRefs[$row['OferenteId']] = ['rowIndex' => $rowIndex, 'itemIndex' => $itemIndex];

            $cantidades[$row['OferenteId']]   = $cantidad;
            $plazos[$row['OferenteId']]       = $fecha;
            $cotizaciones[$row['OferenteId']] = $cotizacion;
        }

        if (empty($itemsRefs)) {
            // Nadie cotizó este producto → pasar al siguiente producto
            continue;
        }

        // Filtros: cantidad exacta solicitada (>0 y == cantidad del concurso)
        $cantidadesValidas = array_filter(
            $cantidades,
            function ($value) use ($prodCantidad) {
                return $value > 0 && $value == $prodCantidad;
            }
        );

        // Plazos candidatos: de los que pasaron cantidad exacta, quedarse con el/los de menor plazo
        $plazosValidos = array_intersect_key($plazos, $cantidadesValidas);
        if (!empty($plazosValidos)) {
            // Dejar solo plazos numéricos > 0
            $poolPlazos = array_filter($plazosValidos, function ($v) {
                return is_numeric($v) && $v > 0;
            });

            if (!empty($poolPlazos)) {
                $minPlazo = min($poolPlazos);
                $plazosValidos = array_filter($plazosValidos, function ($v) use ($minPlazo) {
                    return is_numeric($v) && $v > 0 && $v <= $minPlazo;
                });
            } else {
                // si no hay plazos válidos, que no gane nadie por "plazo"
                $plazosValidos = [];
            }
        }

        // Cotizaciones candidatas: de los que pasaron cantidad exacta, elegir min o max según $maximize
        $cotsValidas = array_intersect_key($cotizaciones, $cantidadesValidas);
        if (!empty($cotsValidas)) {
            $pool = array_filter($cotsValidas, function($v) {
                return $v !== null && $v !== '' && $v >= 0;
            });

            if (!empty($pool)) {
                $bestCot = $maximize ? max($pool) : min($pool);
                $cotsValidas = array_filter($cotsValidas, function($v) use ($bestCot, $maximize) {
                    return $maximize ? ($v >= $bestCot) : ($v <= $bestCot);
                });
            } else {
                $cotsValidas = [];
            }
        }

        // Marcar flags en los oferentes (solo si existe el item)
        foreach ($itemsRefs as $oferenteKey => $ref) {
            $rI = $ref['rowIndex'];
            $iI = $ref['itemIndex'];

            // Inicializar a false si no existen
            if (!isset($oferentes[$rI]['items'][$iI]['isMejorCotizacion'])) $oferentes[$rI]['items'][$iI]['isMejorCotizacion'] = false;
            if (!isset($oferentes[$rI]['items'][$iI]['isMenorCantidad']))   $oferentes[$rI]['items'][$iI]['isMenorCantidad']   = false;
            if (!isset($oferentes[$rI]['items'][$iI]['isMenorPlazo']))      $oferentes[$rI]['items'][$iI]['isMenorPlazo']      = false;

            if (isset($cotsValidas[$oferenteKey]))  $oferentes[$rI]['items'][$iI]['isMejorCotizacion'] = true;
            if (isset($cantidadesValidas[$oferenteKey])) $oferentes[$rI]['items'][$iI]['isMenorCantidad']   = true;
            if (isset($plazosValidos[$oferenteKey]))     $oferentes[$rI]['items'][$iI]['isMenorPlazo']      = true;
        }

        // Elegir el ganador del ítem (si hay uno con mejor cotización)
        // Si hay empate, tomamos el que presentó primero (fecha más antigua)
        $ganadorKey = null;
        if (!empty($cotsValidas)) {
            $fechaMejor = null;
            foreach ($cotsValidas as $key => $_) {
                if ($ganadorKey === null) {
                    $ganadorKey = $key;
                    // Obtener la fecha de presentación del oferente ganador inicial
                    $ref = $itemsRefs[$key];
                    $fechaMejor = $oferentes[$ref['rowIndex']]['fechaPresentacion'] ?? null;
                } else {
                    // Si hay otro con la misma cotización, comparar por fecha de presentación
                    $ref = $itemsRefs[$key];
                    $fechaActual = $oferentes[$ref['rowIndex']]['fechaPresentacion'] ?? null;
                    
                    if ($fechaActual !== null && $fechaMejor !== null) {
                        $timestampActual = strtotime($fechaActual);
                        $timestampMejor = strtotime($fechaMejor);
                        
                        // Si la fecha actual es anterior (menor), es mejor
                        if ($timestampActual !== false && $timestampMejor !== false && $timestampActual < $timestampMejor) {
                            $ganadorKey = $key;
                            $fechaMejor = $fechaActual;
                        }
                    }
                }
            }
        }

        if ($ganadorKey !== null) {
            $ref   = $itemsRefs[$ganadorKey];
            $rI    = $ref['rowIndex'];
            $iI    = $ref['itemIndex'];
            $row   = $oferentes[$rI];
            $item  = $row['items'][$iI];

            $TipoAdjudicacion = $row['tipoAdjudicacion'] ?? $TipoAdjudicacion;

            $cotizacion = (float)($item['cotizacion'] ?? 0.00);
            $cantidad   = (int)($item['cantidad'] ?? 0);
            $subtotal   = $cotizacion * $cantidad;

            $targetcost = (float)($item['targetcost'] ?? 0.00);
            $subtotaltargetcost = $targetcost * $cantidad;

            $ahorro_abs  = $subtotaltargetcost > 0.00 ? ($subtotaltargetcost - $subtotal) : 0.00;
            $ahorro_porc = $subtotaltargetcost > 0.00 ? ($ahorro_abs / $subtotaltargetcost) * 100 : 0.00;

            $ganancia_item_abs  = null;
            $ganancia_item_porc = null;
            if (($isAscendente || $isVenta) && $subtotaltargetcost > 0.00) {
                $ganancia_item_abs  = $subtotal - $subtotaltargetcost;
                $ganancia_item_porc = ($ganancia_item_abs / $subtotaltargetcost) * 100;
            }

            $mejorIndividualItems[$prodId] = [
                'idOferente'         => $row['oferente_id'] ?? null,
                'nombreOferente'     => $row['nombreOferente'] ?? null,
                'razonSocial'        => $row['razonSocial'] ?? null,
                'itemId'             => $item['id'] ?? $prodId,
                'itemNombre'         => $item['nombre'] ?? null,
                'itemCotizacion'     => $cotizacion,
                'itemCantidad'       => $cantidad,
                'subTotal'           => $subtotal,
                'subtotaltargetcost' => $subtotaltargetcost,
                'targetcost'         => $targetcost,
                'ahorro_porc'        => $ahorro_porc,
                'ahorro_abs'         => $ahorro_abs,
                'ganancia_porc'      => $ganancia_item_porc,
                'ganancia_abs'       => $ganancia_item_abs,
                'itemFecha'          => $item['fecha'] ?? 0,
                'tipoAdj'            => $row['tipoAdjudicacion'] ?? null,
                'tipoValorOferta'    => $row['tipoValorOferta'] ?? null,
            ];
        }
    }

    // Totales del mejor individual
    $total1            = 0.00;
    $total1targetcost  = 0.00;
    $idOferentesTokens = [];

    foreach ($mejorIndividualItems as $v) {
        $TipoAdjudicacion = $v['tipoAdj'] ?? $TipoAdjudicacion;
        $total1           += (float)$v['subTotal'];
        $total1targetcost += (float)$v['subtotaltargetcost'];
        $idOferentesTokens[] = ($v['idOferente'] ?? '0') . ':' . ($v['itemId'] ?? '0');
    }

    $mejor_ahorro_abs  = $total1targetcost > 0.00 ? ($total1targetcost - $total1) : 0.00;
    $mejor_ahorro_porc = $total1targetcost > 0.00 ? ($mejor_ahorro_abs / $total1targetcost) * 100 : 0.00;

    $mejor_ganancia_abs  = null;
    $mejor_ganancia_porc = null;
    if (($isAscendente || $isVenta) && $total1targetcost > 0.00) {
        $mejor_ganancia_abs  = $total1 - $total1targetcost;
        $mejor_ganancia_porc = ($mejor_ganancia_abs / $total1targetcost) * 100;
    }

    return [
        'mejorIndividual' => [
            'individual'    => array_values($mejorIndividualItems),
            'total1'        => $total1,
            'ahorro_porc'   => $mejor_ahorro_porc,
            'ahorro_abs'    => $mejor_ahorro_abs,
            'ganancia_porc' => $mejor_ganancia_porc,
            'ganancia_abs'  => $mejor_ganancia_abs,
            'tipoAdj'       => $TipoAdjudicacion,
            'idOferentes'   => implode(',', $idOferentesTokens),
        ],
        'oferentes' => $oferentes
    ];
}



function setMejorManual($concurso, $ConcursoEconomicas)
{
    /**
     * MEJOR OFERTA MANUAL
     */
    $ids = $concurso->productos->pluck('id');
    $nombres = $concurso->productos->pluck('nombre');
    $cantidades = $concurso->productos->pluck('cantidad');
    $mejorManual = [];
    $ConcursoEconomicasItems = isset($ConcursoEconomicas['items']) ? $ConcursoEconomicas['items'] : [];
    for ($i = 0; $i < count($ids); $i++) {
        $ConcursoEconomicasItem = isset($ConcursoEconomicasItems[$i]) ? $ConcursoEconomicasItems[$i] : [];
        $mejorManual[] =
            $ids[$i] . ',' .
            $cantidades[$i] . ',' .
            $nombres[$i] . ',' .
            (isset($ConcursoEconomicasItem['moneda']) ? $ConcursoEconomicasItem['moneda'] : '') . ',' .
            (isset($ConcursoEconomicasItem['unidad']) ? $ConcursoEconomicasItem['unidad'] : '') . ',' .
            (isset($ConcursoEconomicasItem['fecha']) ? $ConcursoEconomicasItem['fecha'] : '');
    }

    return $mejorManual;
}