<h4 class="block bold" style="margin-top: 0; padding-top: 0;">Resumen</h4>
<table class="table table-striped table-bordered" id="ListaConcursosEconomicas">
    <thead class="text-center">
        <tr>
            <th class="text-center"></th>
            <th class="text-center"> Mejor oferta integral </th>
            <th class="text-center"> Mejor oferta individual </th>
            <th class="text-center"> Adjudicación manual </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th> Monto</th>
            <td data-bind="number: ConcursoEconomicas.mejoresOfertas.mejorIntegral.total, precision: 2"
                class="vertical-align-middle text-center text-bold"></td>
            <td data-bind="number: ConcursoEconomicas.mejoresOfertas.mejorIndividual.total1, precision: 2"
                class="vertical-align-middle text-center text-bold"></td>
            <!-- ko if: $root.ManualAdjudication().total() == 0  -->
            <td data-bind="text:'-'" class="vertical-align-middle text-center text-bold"></td>
            <!-- /ko -->
            <!-- ko if: $root.ManualAdjudication().total() > 0  -->
            <td data-bind="number:$root.ManualAdjudication().total(), precision: 2"
                class="vertical-align-middle text-center text-bold"></td>
            <!-- /ko -->
        </tr>
        <tr>
            <th class= "text-center" data-bind="text: ($root.EsAscendente() || $root.EsVenta()) ? 'Ganancia %' : 'Ahorro %'"></th>
            <td class="text-center vertical-align-middle text-bold"
                data-bind="number: ($root.EsAscendente() || $root.EsVenta())
                                    ? ConcursoEconomicas.mejoresOfertas.mejorIntegral.ganancia_porc
                                    : ConcursoEconomicas.mejoresOfertas.mejorIntegral.ahorro_porc,
                            precision: 2, symbol: '%', after: true,
                            style: { color: (
                                    ($root.EsAscendente() || $root.EsVenta())
                                    ? ConcursoEconomicas.mejoresOfertas.mejorIntegral.ganancia_porc
                                    : ConcursoEconomicas.mejoresOfertas.mejorIntegral.ahorro_porc
                                    ) == 0 ? 'black' : (
                                    (
                                        ($root.EsAscendente() || $root.EsVenta())
                                        ? ConcursoEconomicas.mejoresOfertas.mejorIntegral.ganancia_porc
                                        : ConcursoEconomicas.mejoresOfertas.mejorIntegral.ahorro_porc
                                    ) > 0 ? 'green' : 'red'
                                    ) }"></td>
            <td class="text-center vertical-align-middle text-bold"
                data-bind="number: ($root.EsAscendente() || $root.EsVenta())
                                    ? ConcursoEconomicas.mejoresOfertas.mejorIndividual.ganancia_porc
                                    : ConcursoEconomicas.mejoresOfertas.mejorIndividual.ahorro_porc,
                            precision: 2, symbol: '%', after: true,
                            style: { color: (
                                    ($root.EsAscendente() || $root.EsVenta())
                                    ? ConcursoEconomicas.mejoresOfertas.mejorIndividual.ganancia_porc
                                    : ConcursoEconomicas.mejoresOfertas.mejorIndividual.ahorro_porc
                                    ) == 0 ? 'black' : (
                                    (
                                        ($root.EsAscendente() || $root.EsVenta())
                                        ? ConcursoEconomicas.mejoresOfertas.mejorIndividual.ganancia_porc
                                        : ConcursoEconomicas.mejoresOfertas.mejorIndividual.ahorro_porc
                                    ) > 0 ? 'green' : 'red'
                                    ) }"></td>
            <!-- ko if: $root.ManualAdjudication().total() == 0 -->
            <td data-bind="text:'-'" class="vertical-align-middle text-center text-bold"></td>
            <!-- /ko -->
            <!-- ko if: $root.ManualAdjudication().total() > 0  -->
            <td class="text-center vertical-align-middle text-bold"
                data-bind="number: ($root.EsAscendente() || $root.EsVenta())
                                    ? $root.ManualAdjudication().GananciaRelativa()
                                    : $root.ManualAdjudication().AhorroRelativo(),
                        precision: 2, symbol: '%', after: true,
                        style: { color: (
                                    ($root.EsAscendente() || $root.EsVenta())
                                    ? $root.ManualAdjudication().GananciaRelativa()
                                    : $root.ManualAdjudication().AhorroRelativo()
                                ) == 0 ? 'black' : (
                                    (
                                    ($root.EsAscendente() || $root.EsVenta())
                                    ? $root.ManualAdjudication().GananciaRelativa()
                                    : $root.ManualAdjudication().AhorroRelativo()
                                    ) > 0 ? 'green' : 'red'
                                ) }">
            </td>
            <!-- /ko -->
        </tr>
        <tr>
            <th class= "text-center" data-bind="text: ($root.EsAscendente() || $root.EsVenta()) ? 'Ganancia abs' : 'Ahorro abs'"></th>
            <td class="text-center vertical-align-middle text-bold"
                data-bind="number:($root.EsAscendente() || $root.EsVenta())
                                    ? ConcursoEconomicas.mejoresOfertas.mejorIntegral.ganancia_abs
                                    : ConcursoEconomicas.mejoresOfertas.mejorIntegral.ahorro_abs,
                            precision: 2,
                            style: { color: (
                                    ($root.EsAscendente() || $root.EsVenta())
                                    ? ConcursoEconomicas.mejoresOfertas.mejorIntegral.ganancia_abs
                                    : ConcursoEconomicas.mejoresOfertas.mejorIntegral.ahorro_abs
                                    ) == 0 ? 'black' : (
                                    (
                                        ($root.EsAscendente() || $root.EsVenta())
                                        ? ConcursoEconomicas.mejoresOfertas.mejorIntegral.ganancia_abs
                                        : ConcursoEconomicas.mejoresOfertas.mejorIntegral.ahorro_abs
                                    ) > 0 ? 'green' : 'red'
                                    ) }"></td>
            <td class="text-center vertical-align-middle text-bold"
                data-bind="number: ($root.EsAscendente() || $root.EsVenta())
                                    ? ConcursoEconomicas.mejoresOfertas.mejorIndividual.ganancia_abs
                                    : ConcursoEconomicas.mejoresOfertas.mejorIndividual.ahorro_abs,
                            precision: 2,
                            style: { color: (
                                    ($root.EsAscendente() || $root.EsVenta())
                                    ? ConcursoEconomicas.mejoresOfertas.mejorIndividual.ganancia_abs
                                    : ConcursoEconomicas.mejoresOfertas.mejorIndividual.ahorro_abs
                                    ) == 0 ? 'black' : (
                                    (
                                        ($root.EsAscendente() || $root.EsVenta())
                                        ? ConcursoEconomicas.mejoresOfertas.mejorIndividual.ganancia_abs
                                        : ConcursoEconomicas.mejoresOfertas.mejorIndividual.ahorro_abs
                                    ) > 0 ? 'green' : 'red'
                                    ) }"></td>
            <!-- ko if: $root.ManualAdjudication().total() == 0 -->
            <td data-bind="text:'-'" class="vertical-align-middle text-center text-bold"></td>
            <!-- /ko -->
            <!-- ko if: $root.ManualAdjudication().total() > 0  -->
            <td class="text-center vertical-align-middle text-bold"
                data-bind="number: ($root.EsAscendente() || $root.EsVenta())
                                    ? $root.ManualAdjudication().GananciaAbsoluta()
                                    : $root.ManualAdjudication().AhorroAbsoluto(),
                        precision: 2,
                        style: { color: (
                                    ($root.EsAscendente() || $root.EsVenta())
                                    ? $root.ManualAdjudication().GananciaAbsoluta()
                                    : $root.ManualAdjudication().AhorroAbsoluto()
                                ) == 0 ? 'black' : (
                                    (
                                    ($root.EsAscendente() || $root.EsVenta())
                                    ? $root.ManualAdjudication().GananciaAbsoluta()
                                    : $root.ManualAdjudication().AhorroAbsoluto()
                                    ) > 0 ? 'green' : 'red'
                                ) }">
            </td>
            <!-- /ko -->
        </tr>
        <!-- ko if: active -->
        <tr>
            <td colspan="4" class="col-md-10 vertical-align-middle">
                <label class="control-label visible-ie8 visible-ie9" style="display: block;">
                    Comentarios
                </label>
                <textarea class="form-control placeholder-no-fix" maxlength="1000" rows="3" id="maxlength_textarea"
                    data-bind="value: $root.AdjudicacionComentario, attr: { 'placeholder': $root.Adjudicado() ? '' : 'Máximo 1000 caracteres' }, disable: $root.Adjudicado() || !active || $root.AdjudicationPendingApproval()">
                </textarea>
            </td>
        </tr>
        <!-- /ko -->
        
        <!-- ko if: !$root.Adjudicado() && !$root.Eliminado() && active && !$root.AdjudicationPendingApproval() && !$root.AdjudicationRejected() -->
        <tr>
            <td class="text-center" colspan="4">
                <!-- ko if: ConcursoEconomicas.mejoresOfertas.mejorIntegral.items.length > 0  -->
                <!-- ko if: $root.UserType() !== 'customer-read' -->
                <button type="button" class="btn btn-primary"
                    data-bind="click: $root.AdjudicationSend.bind($data, 'integral', ConcursoEconomicas.mejoresOfertas.mejorIntegral.idOferente), disable: $root.BotonesAdjudicacionDeshabilitados()">
                    Adjudicar Integral
                </button>
                <!-- /ko -->
                <!-- /ko -->
                <!-- ko if: ConcursoEconomicas.mejoresOfertas.mejorIndividual.individual.length > 0 -->
                <!-- ko if: $root.UserType() !== 'customer-read' -->
                <button type="button" class="btn btn-primary"
                    data-bind="click: $root.AdjudicationSend.bind($data, 'individual', ConcursoEconomicas.mejoresOfertas.idOferentes), disable: $root.BotonesAdjudicacionDeshabilitados()">
                    Adjudicar Individual
                </button>
                <!-- /ko -->
                <!-- /ko -->
                <!-- ko if: ConcursoEconomicas.proveedores.length > 0 -->
                <!-- ko if: $root.UserType() !== 'customer-read' -->
                <button type="button" class="btn btn-primary"
                    data-bind="click: $root.AdjudicationSend.bind($data, 'manual'), disable: $root.ManualAdjudication().total() == 0 || $root.BotonesAdjudicacionDeshabilitados()">
                    Adjudicar Manual
                </button>
                <!-- /ko -->
                <!-- /ko -->
            </td>
        </tr>
        <!-- /ko -->

        <!-- ko if: !$root.Adjudicado() && !$root.Eliminado() && active && $root.ApprovalChainComplete() -->
        <tr>
            <td class="text-center" colspan="4">
                <div class="alert alert-success" style="margin-bottom: 10px;">
                    <i class="fa fa-check-circle"></i> La cadena de aprobación está completa. Puede proceder con la adjudicación.
                </div>
                <!-- ko if: $root.UserType() !== 'customer-read' -->
                <button type="button" class="btn btn-success btn-lg"
                    data-bind="click: $root.ProcessApprovedAdjudication">
                    <i class="fa fa-gavel"></i> Procesar Adjudicación Aprobada
                </button>
                <!-- /ko -->
            </td>
        </tr>
        <!-- /ko -->
    </tbody>
</table>

<!-- ko if: active && $root.EjecutarNuevaRonda() -->
<!-- ko if: $root.UserType() !== 'customer-read' -->
<!-- ko if: !$root.AdjudicationPendingApproval() || $root.AdjudicationRejected() -->
<button type="button" class="btn btn-primary" style="width: 100%;"
    data-bind="text:$root.TitleNewRound(), click: $root.ShowModalNewRound, visible: !($root.Adjudicado() || $root.Eliminado())">
</button>
<!-- /ko -->
<!-- /ko -->
<!-- /ko -->

<!-- ko if: $root.EstrategiaHabilitada() && ($root.NivelesAprobacion().length > 0 || $root.AdjudicationPendingApproval()) -->
<div style="margin-top: 20px; padding: 15px; border: 1px solid #e0e0e0; border-radius: 4px; background-color: #fafafa;">
    <h4 class="block bold" style="margin-top: 0; padding-top: 0;">
        <i class="fa fa-sitemap"></i> Cadena de Aprobación
    </h4>
    
    <!-- ko if: $root.MontoEnDolares() !== null -->
    <div style="margin-bottom: 15px; padding: 10px; background-color: #f5f5f5; border-radius: 4px;">
        <p style="margin-bottom: 5px;">
            <strong>Valor (En dólares):</strong> 
            <span style="font-size: 16px; color: #337ab7;" data-bind="text: 'USD ' + $root.MontoEnDolares().toFixed(2)"></span>
        </p>
        <!-- ko if: $root.TipoAdjudicacionSeleccionada() -->
        <p style="margin-bottom: 0;">
            <strong>Tipo de Adjudicación:</strong> 
            <span class="label label-info" data-bind="text: $root.TipoAdjudicacionSeleccionada()"></span>
        </p>
        <!-- /ko -->
    </div>
    <!-- /ko -->
    
    <!-- ko if: $root.AdjudicationPendingApproval() && !$root.AdjudicationRejected() && !$root.ApprovalChainComplete() -->
    <div class="alert alert-warning" style="margin-bottom: 15px;">
        <i class="fa fa-clock-o"></i> 
        <strong>En proceso de aprobación.</strong> 
        La adjudicación está pendiente de aprobación por la cadena de autorización.
    </div>
    <!-- /ko -->
    
    <!-- ko if: $root.AdjudicationRejected() -->
    <div class="alert alert-danger" style="margin-bottom: 15px;">
        <i class="fa fa-times-circle"></i> 
        <strong>Adjudicación rechazada.</strong> 
        La adjudicación fue rechazada por uno de los aprobadores. Puede lanzar una nueva ronda o cancelar el concurso.
    </div>
    <!-- /ko -->
    
    <!-- ko if: $root.ApprovalChainComplete() -->
    <div class="alert alert-success" style="margin-bottom: 15px;">
        <i class="fa fa-check-circle"></i> 
        <strong>Cadena de aprobación completa.</strong> 
        Todos los niveles han aprobado la adjudicación.
    </div>
    <!-- /ko -->
    
    <table class="table table-striped table-bordered" id="TablaCadenaAprobacion">
        <thead class="text-center">
            <tr>
                <th class="text-center" style="width: 50px;">#</th>
                <th class="text-center" style="white-space: nowrap;">Nivel</th>
                <th class="text-center" style="white-space: nowrap;">Usuario</th>
                <th class="text-center" style="width: 120px;">Estado</th>
                <th class="text-center">Fecha</th>
                <th class="text-center">Motivo/Comentario</th>
                <!-- ko if: $root.CanApproveInChain() -->
                <th class="text-center" style="width: 150px;">Acciones</th>
                <!-- /ko -->
            </tr>
        </thead>
        <tbody data-bind="foreach: $root.NivelesAprobacion()">
            <tr data-bind="css: { 'success': estado === 'Aprobado', 'danger': estado === 'Rechazado', 'warning': estado === 'Pendiente' && $root.IsCurrentLevel($index()) }">
                <td class="text-center" data-bind="text: orden"></td>
                <td class="text-center" style="white-space: nowrap;" data-bind="text: rol"></td>
                <td class="text-center" style="white-space: nowrap;" data-bind="text: usuario || '-'"></td>
                <td class="text-center vertical-align-middle">
                    <span class="label label-sm"
                          data-bind="
                            text: estado,
                            css: {
                                'label-warning': estado === 'Pendiente',
                                'label-success': estado === 'Aprobado',
                                'label-danger':  estado === 'Rechazado'
                            }">
                    </span>
                </td>
                <td class="text-center" data-bind="text: fecha || '-'"></td>
                <td class="text-center" data-bind="text: motivo || '-'"></td>
                <!-- ko if: $root.CanApproveInChain() -->
                <td class="text-center">
                    <!-- ko if: estado === 'Pendiente' && $root.IsCurrentLevel($index()) && $root.CanApproveInChain() -->
                    <button type="button" class="btn btn-success btn-sm" 
                            data-bind="click: $root.ApproveLevel"
                            title="Aprobar">
                        <i class="fa fa-check"></i> Aprobar
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" 
                            data-bind="click: $root.RejectLevel"
                            title="Rechazar">
                        <i class="fa fa-times"></i> Rechazar
                    </button>
                    <!-- /ko -->
                </td>
                <!-- /ko -->
            </tr>
        </tbody>
    </table>
    
    <!-- ko if: $root.AdjudicationPendingApproval() && !$root.AdjudicationRejected() && $root.IsOriginalRequester() -->
    <div style="margin-top: 15px; text-align: right;">
        <button type="button" class="btn btn-default" 
                data-bind="click: $root.CancelApprovalRequest">
            <i class="fa fa-times"></i> Cancelar solicitud de aprobación
        </button>
    </div>
    <!-- /ko -->
</div>
<!-- /ko -->

<!-- ko if: $root.Adjudicado()  -->
<div class="alert alert-success"
    data-bind="text: 'Este concurso ya ha sido adjudicado de manera ' + $root.TipoAdjudicacion() + '.'">
</div>
<!-- /ko -->

<!-- ko if: $root.Eliminado()  -->
<div class="alert alert-success" data-bind="text: 'Este concurso ha sido eliminado'">
</div>
<!-- /ko -->

<div class="modal fade" id="modalRejectionReason" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title text-center">
                    <i class="fa fa-times-circle text-danger"></i> Rechazar Adjudicación
                </h4>
            </div>
            <div class="modal-body">
                <p class="text-center">Por favor, indique el motivo del rechazo:</p>
                <div class="form-group">
                    <textarea class="form-control" rows="4" 
                              data-bind="value: $root.RejectionReason" 
                              placeholder="Escriba el motivo del rechazo (obligatorio)..."
                              style="resize: vertical;"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn btn-danger" 
                        data-bind="click: $root.ConfirmRejection, enable: $root.RejectionReason() && $root.RejectionReason().trim().length > 0">
                    <i class="fa fa-times"></i> Confirmar Rechazo
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalApprovalComment" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title text-center">
                    <i class="fa fa-check-circle text-success"></i> Aprobar Adjudicación
                </h4>
            </div>
            <div class="modal-body">
                <p class="text-center">¿Desea agregar un comentario a la aprobación? (opcional)</p>
                <div class="form-group">
                    <textarea class="form-control" rows="3" 
                              data-bind="value: $root.ApprovalComment" 
                              placeholder="Comentario opcional..."
                              style="resize: vertical;"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn btn-success" 
                        data-bind="click: $root.ConfirmApproval">
                    <i class="fa fa-check"></i> Confirmar Aprobación
                </button>
            </div>
        </div>
    </div>
</div>
