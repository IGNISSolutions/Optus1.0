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
            $listtotales = [];
            foreach ($oferenteItems as $g => $f) {
                $listtotales[$g] = $f['total'];
            }
            $maxtotal = min($listtotales);
            foreach ($oferenteItems as $g => $f) {
                if ($f['total'] == 0) {
                    $oferenteItems[$g]['difvsmejorofert'] = 0;
                } else {
                    $oferenteItems[$g]['difvsmejorofert'] = round((($maxtotal - $f['total']) / $f['total']) * 100, 2);
                }
            }
            
            $mejorIntegral = setMejorIntegral($concurso, $oferenteItems);
            $posicion = array_search($mejorIntegral['idOferente'], array_column($oferenteItems, 'oferente_id'));
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

    if (
        $concurso->is_online &&
        $concurso->tipo_valor_ofertar === 'ascendente'
    ) {
        return $total > $mejorOferta;
    }

    return $total < $mejorOferta;
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

                foreach ($economicByRound->values as $propuesta) {
                    // evitar logs en la lista
                    $i++;
                    if ($i > $concurso->productos->count()) {
                        break;
                    }

                    $producto   = Producto::find($propuesta['producto']);
                    $cotizacion = isset($propuesta['cotizacion']) ? (float)$propuesta['cotizacion'] : 0.00;
                    $cantidad   = isset($propuesta['cantidad'])   ? (int)$propuesta['cantidad']   : 0;

                    $targetcost = isset($producto->targetcost) ? (float)$producto->targetcost : 0.00;

                    $subtotal           = $cotizacion * $cantidad;
                    $subtotaltargetcost = $targetcost * $cantidad; // COTIZADO

                    // Denominador "pedido" (para % totales)
                    $prodConcurso  = $concurso->productos->find($producto->id);
                    $qtySolicitada = $prodConcurso ? (int)$prodConcurso->cantidad : $cantidad; // fallback
                    $subTC_solicitado = $targetcost * $qtySolicitada;

                    // Ahorro por ítem (como lo tenías)
                    $ahorro_abs  = 0.00;
                    $ahorro_porc = 0.00;
                    if ($subtotaltargetcost > 0.00) {
                        $ahorro_abs  = $targetcost > 0.00 ? ($subtotaltargetcost - $subtotal) : 0.00;
                        $ahorro_porc = $targetcost > 0.00 ? (($ahorro_abs / $subtotaltargetcost) * 100) : 0.00;
                    }

                    // Ganancia por ÍTEM (solo ascendente, inversa del ahorro)
                    $ganancia_item_abs  = null;
                    $ganancia_item_porc = null;
                    if ($isAscendente && $subtotaltargetcost > 0.00) {
                        $ganancia_item_abs  = $subtotal - $subtotaltargetcost;
                        $ganancia_item_porc = ($ganancia_item_abs / $subtotaltargetcost) * 100;
                    }

                    $values[] = [
                        'id'               => $producto->id,
                        'nombre'           => $producto->nombre,
                        'cotizacion'       => $cotizacion,
                        'cantidad'         => (int)$cantidad,
                        'fecha'            => $propuesta['fecha'] ? $propuesta['fecha'] : 0,
                        'subtotal'         => $subtotal,
                        'oferente_id'      => $oferente->company->id,
                        'razonSocial'      => $oferente->company->business_name,
                        'tipoAdjudicacion' => $oferente->adjudicacion,
                        'moneda'           => $concurso->tipo_moneda->nombre,
                        'unidad'           => $producto->unidad_medida->name ?? null,
                        'targetcost'       => $targetcost,
                        'ahorro_porc'      => $ahorro_porc,
                        'ahorro_abs'       => $ahorro_abs,
                        'ganancia_abs'     => $ganancia_item_abs,
                        'ganancia_porc'    => $ganancia_item_porc,
                        'tipoValorOferta'  => $oferente->tipo_valor_ofertar,
                        'isMenorCantidad'  => false,
                        'isMenorPlazo'     => false,
                        'isMejorCotizacion'=> false,
                    ];

                    $total                        += $subtotal;
                    $totaltargetcost_ofertado     += $subtotaltargetcost;
                    $totaltargetcost_solicitado   += $subTC_solicitado;
                }

                // Totales (abs contra OFERTADO, % contra SOLICITADO)
                $totalahorro_abs  = $totaltargetcost_ofertado   > 0.00 ? ($totaltargetcost_ofertado - $total) : 0.00;
                $totalahorro_porc = $totaltargetcost_solicitado > 0.00 ? ($totalahorro_abs / $totaltargetcost_solicitado) * 100 : 0.00;

                $ganancia_abs  = null;
                $ganancia_porc = null;
                if ($isAscendente) {
                    $ganancia_abs  = $total - $totaltargetcost_ofertado;
                    $ganancia_porc = $totaltargetcost_solicitado > 0.00 ? ($ganancia_abs / $totaltargetcost_solicitado) * 100 : null;
                }

                $posicion    = array_search($economicByRound->payment_deadline, array_column(Participante::PLAZOS_PAGO, 'id'));
                $condicionId = array_search($economicByRound->payment_condition, array_column(Participante::CONDICIONES_PAGO, 'id'));

                $ConcursoEconomicas = [
                    'OferenteId'          => $oferente->id,
                    'oferente_id'         => $oferente->company->id,
                    'nombreOferente'      => $oferente->company->business_name,
                    'razonSocial'         => $oferente->company->business_name,
                    'tipoAdjudicacion'    => $oferente->adjudicacion,
                    'tipoValorOferta'     => $oferente->tipo_valor_ofertar,
                    'items'               => count($values) > 0 ? $values : [],
                    'total'               => $total,
                    'ahorro_abs'          => $totalahorro_abs,
                    'ahorro_porc'         => $totalahorro_porc,
                    'ganancia_abs'        => $ganancia_abs,
                    'ganancia_porc'       => $ganancia_porc,
                    'mejorOfertaIntegral' => false,
                    'difvsmejorofert'     => 0.00,
                    'evaluationalcanzada' => $evaluationalcanzada,
                    'file_path'           => filePath($oferente->file_path),
                    'porpuesta_economica' => $economic_proposal ? getEconomicDoc($economicByRound) : null,
                    'planilla_costos'     => $economic_proposal ? getCostDocument($concurso, $economicByRound) : null,
                    'analisis_apu'        => $economic_proposal ? getApuDocument($concurso, $economicByRound) : null,
                    'comentarios'         => $economic_proposal ? $economicByRound->comment : null,
                    'fechaPresentacion'   => $economic_proposal ? $economicByRound->updated_at->format('d-m-Y H:i') : null,
                    'cuit'                => $oferente->company->cuit,
                    'plazoPago'           => $posicion !== false ? Participante::PLAZOS_PAGO[$posicion]['text'] : null,
                    'condicionPago'       => $condicionId !== false ? Participante::CONDICIONES_PAGO[$condicionId]['text'] : null,
                    'isRechazado'         => $oferente->is_concurso_rechazado,
                    'isVencido'           => false,
                ];
                $oferenteItems = $ConcursoEconomicas;

            } else {
                $oferenteItems = [
                    'OferenteId'          => $oferente->id,
                    'oferente_id'         => $oferente->company->id,
                    'nombreOferente'      => $oferente->company->business_name,
                    'razonSocial'         => $oferente->company->business_name,
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
                    'file_path'           => filePath($oferente->file_path),
                    'porpuesta_economica' => null,
                    'planilla_costos'     => null,
                    'analisis_apu'        => null,
                    'comentarios'         => null,
                    'fechaPresentacion'   => null,
                    'cuit'                => $oferente->company->cuit,
                    'plazoPago'           => null,
                    'condicionPago'       => null,
                    'isRechazado'         => $oferente->is_concurso_rechazado,
                    'isVencido'           => true,
                ];
            }
        } else {
            $oferenteItems = [
                'OferenteId'          => $oferente->id,
                'oferente_id'         => $oferente->company->id,
                'nombreOferente'      => $oferente->company->business_name,
                'razonSocial'         => $oferente->company->business_name,
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
                'file_path'           => filePath($oferente->file_path),
                'porpuesta_economica' => null,
                'planilla_costos'     => null,
                'analisis_apu'        => null,
                'comentarios'         => null,
                'fechaPresentacion'   => null,
                'cuit'                => $oferente->company->cuit,
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
            'oferente_id'         => $oferente->company->id,
            'nombreOferente'      => $oferente->company->business_name,
            'razonSocial'         => $oferente->company->business_name,
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
            'file_path'           => filePath($oferente->file_path),
            'porpuesta_economica' => null,
            'planilla_costos'     => null,
            'analisis_apu'        => null,
            'comentarios'         => null,
            'fechaPresentacion'   => null,
            'cuit'                => $oferente->company->cuit,
            'plazoPago'           => null,
            'isRechazado'         => $oferente->is_concurso_rechazado,
        ];
    }

    return $oferenteItems;
}


function setMejorIntegral($concurso, $oferentes)
{
    $idOferente = 0;
    $mejorOfertaIntegral = 0.00;
    $nombreOferente = '';
    $razonSocial = '';
    $items = [];
    $TipoAdjudicacion = '';
    $productos = $concurso->productos;

    $mejor_ahorro_porc = 0.00;
    $mejor_ahorro_abs  = 0.00;

    // Ganancia integral (se recalcula desde ítems del ganador)
    $mejor_ganancia_abs  = null;
    $mejor_ganancia_porc = null;

    // Usa el mismo flag que en otras partes (con fallback)
    $tipoAsc = $concurso->tipo_valor_ofertar ?? $concurso->tipo_subasta ?? null;
    $isAscendente = ($tipoAsc === 'ascendente');

    foreach ($oferentes as $oferente) {
        if (!empty($oferente['isRechazado'])) {
            continue; // no abortar el bucle
        }

        // Debe cotizar todos los ítems con la cantidad exacta
        $oferIsEnable = true;
        foreach ($oferente['items'] as $item) {
            $prod = $productos->find($item['id']);
            if (!($item['cotizacion'] > 0.00 && $prod && $item['cantidad'] == $prod->cantidad)) {
                $oferIsEnable = false;
                break;
            }
        }
        if (!$oferIsEnable) continue;

        $total       = isset($oferente['total']) ? (float)$oferente['total'] : 0.00;
        $ahorro_porc = isset($oferente['ahorro_porc']) ? (float)$oferente['ahorro_porc'] : 0.00;
        $ahorro_abs  = isset($oferente['ahorro_abs'])  ? (float)$oferente['ahorro_abs']  : 0.00;

        if (isMejorOferta($concurso, $total, $mejorOfertaIntegral) || $mejorOfertaIntegral === 0.00) {
            $idOferente          = $oferente['oferente_id'];
            $mejorOfertaIntegral = $total;

            $mejor_ahorro_porc   = $ahorro_porc;
            $mejor_ahorro_abs    = $ahorro_abs;

            $TipoAdjudicacion    = $oferente['tipoAdjudicacion'];
            $items               = $oferente['items'];
            $nombreOferente      = $oferente['nombreOferente'];
            $razonSocial         = $oferente['razonSocial'];
        }
    }

    // Recalcular ganancia del ganador desde sus ítems (robusto)
    if ($isAscendente && !empty($items)) {
        $sumSubtotal = 0.0;
        $sumTarget   = 0.0;
        foreach ($items as $it) {
            $sub = isset($it['subtotal'])
                ? (float)$it['subtotal']
                : ((float)($it['cotizacion'] ?? 0) * (float)($it['cantidad'] ?? 0));
            $sumSubtotal += $sub;
            $sumTarget   += (float)($it['targetcost'] ?? 0) * (float)($it['cantidad'] ?? 0);
        }
        if ($sumTarget > 0.0) {
            $mejor_ganancia_abs  = $sumSubtotal - $sumTarget;
            $mejor_ganancia_porc = ($mejor_ganancia_abs / $sumTarget) * 100;
        } else {
            // sin targetcost no aplica ganancia
            $mejor_ganancia_abs = $mejor_ganancia_porc = null;
        }
    }

    return [
        'idOferente'       => $idOferente,
        'nombreOferente'   => $nombreOferente,
        'razonSocial'      => $razonSocial,
        'tipoAdjudicacion' => $TipoAdjudicacion,
        'total'            => $mejorOfertaIntegral,
        'ahorro_porc'      => $mejor_ahorro_porc,
        'ahorro_abs'       => $mejor_ahorro_abs,
        'ganancia_abs'     => $mejor_ganancia_abs,
        'ganancia_porc'    => $mejor_ganancia_porc,
        'items'            => $items,
    ];
}



function setMejorIndividual($concurso, $oferentes)
{
    $mejorIndividual = [];
    $TipoAdjudicacion = null;

    // Subasta ascendente?
    $isAscendente = isset($concurso->tipo_valor_ofertar) && $concurso->tipo_valor_ofertar === 'ascendente';

    /**
     * MEJOR OFERTA INDIVIDUAL
     */
    $mejorIndividual = [];
    $i = 0;
    foreach ($concurso->productos as $producto) {
        $mejores_plazos = [];
        $mejores_cantidades = [];
        $mejores_cotizaciones = [];

        if (count($oferentes) > 0) {
            foreach ($oferentes as $row) {
                if (count($row['items']) > 0) {
                    $mejores_plazos[$row['OferenteId']]       = $row['items'][$i]['fecha'];
                    $mejores_cantidades[$row['OferenteId']]   = $row['items'][$i]['cantidad'];
                    $mejores_cotizaciones[$row['OferenteId']] = $row['items'][$i]['cotizacion'];
                }
            }

            $mejores_cantidades = array_filter(
                $mejores_cantidades,
                function ($value, $key) use ($mejores_cantidades, $producto) {
                    return $value > 0 && $value == $producto->cantidad;
                },
                ARRAY_FILTER_USE_BOTH
            );

            $mejores_plazos = array_intersect_key($mejores_plazos, $mejores_cantidades);
            $mejores_plazos = array_filter(
                $mejores_plazos,
                function ($value, $key) use ($mejores_plazos) {
                    return $value > 0 && $value <= min(array_filter($mejores_plazos));
                },
                ARRAY_FILTER_USE_BOTH
            );

            $mejores_cotizaciones = array_intersect_key($mejores_cotizaciones, $mejores_cantidades);
            $mejores_cotizaciones = array_filter(
                $mejores_cotizaciones,
                function ($value, $key) use ($mejores_cotizaciones, $concurso) {
                    if ($concurso->is_online && $concurso->tipo_valor_ofertar === 'ascendente') {
                        return $value >= max(array_filter($mejores_cotizaciones));
                    }
                    return $value <= min(array_filter($mejores_cotizaciones));
                },
                ARRAY_FILTER_USE_BOTH
            );

            foreach ($oferentes as $index => $row) {
                if (count($row['items']) > 0) {
                    if (!$row['items'][$i]['cotizacion']) {
                        continue;
                    }

                    // Flags de mejor cotización/cantidad/plazo
                    $oferentes[$index]['items'][$i]['isMejorCotizacion'] = isset($mejores_cotizaciones[$row['OferenteId']]);
                    $oferentes[$index]['items'][$i]['isMenorCantidad']   = isset($mejores_cantidades[$row['OferenteId']]);
                    $oferentes[$index]['items'][$i]['isMenorPlazo']      = isset($mejores_plazos[$row['OferenteId']]);

                    if ($oferentes[$index]['items'][$i]['isMejorCotizacion']) {
                        $cotizacion          = isset($row['items'][$i]['cotizacion']) ? (float)$row['items'][$i]['cotizacion'] : 0.00;
                        $cantidad            = isset($row['items'][$i]['cantidad'])   ? (float)$row['items'][$i]['cantidad']   : 0;
                        $targetcost          = isset($producto->targetcost)            ? (float)$producto->targetcost           : 0.00;

                        $subtotal            = $cotizacion * $cantidad;
                        $subtotaltargetcost  = $targetcost * $cantidad;

                        // Ahorro (como ya lo tenías)
                        $ahorro_abs  = $subtotaltargetcost > 0.00 ? ($subtotaltargetcost - $subtotal) : 0.00;
                        $ahorro_porc = $subtotaltargetcost > 0.00 ? ($ahorro_abs / $subtotaltargetcost) * 100 : 0.00;

                        // NUEVO: Ganancia por ítem (solo ascendente)
                        $ganancia_item_abs  = null;
                        $ganancia_item_porc = null;
                        if ($isAscendente && $subtotaltargetcost > 0.00) {
                            $ganancia_item_abs  = $subtotal - $subtotaltargetcost;
                            $ganancia_item_porc = ($ganancia_item_abs / $subtotaltargetcost) * 100;
                        }

                        $mejorIndividual[$i] = [
                            'idOferente'         => $row['oferente_id'],
                            'nombreOferente'     => $row['nombreOferente'],
                            'razonSocial'        => $row['razonSocial'],
                            'itemId'             => $row['items'][$i]['id'],
                            'itemNombre'         => $row['items'][$i]['nombre'],
                            'itemCotizacion'     => $cotizacion,
                            'itemCantidad'       => $cantidad,
                            'subTotal'           => $subtotal,
                            'subtotaltargetcost' => $subtotaltargetcost,
                            'targetcost'         => $targetcost,
                            'ahorro_porc'        => $ahorro_porc,
                            'ahorro_abs'         => $ahorro_abs,
                            // NUEVO: ganancia por ítem
                            'ganancia_porc'      => $ganancia_item_porc,
                            'ganancia_abs'       => $ganancia_item_abs,
                            //
                            'itemFecha'          => $row['items'][$i]['fecha'],
                            'tipoAdj'            => $row['tipoAdjudicacion'],
                            'tipoValorOferta'    => $row['tipoValorOferta'],
                        ];
                    }
                }
            }

            $i++;
        }
    }

    // Totales
    $total1 = 0.00;
    $total1targetcost = 0.00;
    $idOferentes = [];
    foreach ($mejorIndividual as $k => $v) {
        $TipoAdjudicacion = $v['tipoAdj'];
        $total1 += $v['subTotal'];
        $total1targetcost += $v['subtotaltargetcost'];
        $idOferentes[] = $v['idOferente'] . ':' . $v['itemId'];
    }

    $mejor_ahorro_abs  = $total1targetcost > 0.00 ? ($total1targetcost - $total1) : 0.00;
    $mejor_ahorro_porc = $total1targetcost > 0.00 ? ($mejor_ahorro_abs / $total1targetcost) * 100 : 0.00;

    // NUEVO: ganancia total (solo ascendente)
    $mejor_ganancia_abs  = null;
    $mejor_ganancia_porc = null;
    if ($isAscendente && $total1targetcost > 0.00) {
        $mejor_ganancia_abs  = $total1 - $total1targetcost;
        $mejor_ganancia_porc = ($mejor_ganancia_abs / $total1targetcost) * 100;
    }

    $mejorIndividual = [
        'mejorIndividual' => [
            'individual'   => array_values($mejorIndividual),
            'total1'       => $total1,
            'ahorro_porc'  => $mejor_ahorro_porc,
            'ahorro_abs'   => $mejor_ahorro_abs,
            // NUEVO: ganancia total
            'ganancia_porc'=> $mejor_ganancia_porc,
            'ganancia_abs' => $mejor_ganancia_abs,
            //
            'tipoAdj'      => $TipoAdjudicacion,
            'idOferentes'  => implode(',', $idOferentes),
        ],
        'oferentes' => $oferentes
    ];

    return $mejorIndividual;
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