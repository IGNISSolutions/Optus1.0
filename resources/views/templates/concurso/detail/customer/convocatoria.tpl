<div 
    class="modal fade" 
    id="InvitarNuevosOferentes" 
    role="basic" 
    data-backdrop="static"
    data-keyboard="false"
    aria-hidden="true">

    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title">
                    Invitar Nuevo Proveedor
                </h4>
            </div>
            <div class="modal-body text-left">
                <!-- ko if: OferentesAInvitar().length > 0 -->
                <select 
                    data-bind="value: OferenteAInvitar, 
                    valueAllowUnset: true, 
                    options: OferentesAInvitar, 
                    optionsText: 'text', 
                    optionsValue: 'id', 
                    select2: { placeholder: 'Seleccionar...', allowClear: true }">
                </select>
                <!-- /ko -->
                <!-- ko if: OferentesAInvitar().length == 0 -->
                <div class="alert alert-danger">
                    Su empresa no tiene otros proveedores vinculados para invitar.
                </div>
                <!-- /ko -->
            </div>
            <div class="modal-footer">
                <button 
                    type="button" 
                    class="btn dark btn-outline" 
                    data-dismiss="modal">
                    Cerrar
                </button>

                <!-- ko if: OferenteAInvitar() -->
                <button 
                    type="button" 
                    class="btn green" 
                    data-dismiss="modal"
                    data-bind="click: $root.sendInvitation.bind($data, OferenteAInvitar(), false, true)">
                    Enviar Invitación
                </button>
                <!-- /ko -->
            </div>
        </div>
    </div>
</div>
<div class="m-heading-1 border-default m-bordered text-left">
    <h4 class="block bold" style="margin-top: 0; padding-top: 0;">Documentación</h4>
    <!-- ko if: Media().length > 0 -->
    <table class="table table-striped table-bordered" id="ListaConcursosEconomicas">
        <tbody data-bind="foreach: Media()">

            <tr>
                <td class="col-md-6 text-center" style="vertical-align: middle;" data-bind="text: nombre">

                </td>
                <td class="col-md-6 text-center" style="vertical-align: middle;">
                    <a data-bind="click: $root.downloadFile.bind($data, imagen, 'concurso', $root.IdConcurso())"
                        download class="btn btn-xl green" title="Descargar">
                        Descargar
                        <i class="fa fa-download"></i>
                    </a>
                </td>
            </tr>

        </tbody>
    </table>
    <!-- /ko -->

    <!-- ko if: !Media().some(m => m.indice != 0) -->
    <div class="alert alert-success text-center">
        No hay documentos
    </div>
    <!-- /ko -->
</div>
<!-- Items a licitar -->
<div class="m-heading-1 border-default m-bordered text-left">
  <h4 class="block bold" style="margin-top: 0; padding-top: 0;">
    Items a licitar
  </h4>
  <table class="table table-striped table-bordered" id="ListaConcursosEconomicas">
    <thead>
      <tr>
        <th class="text-center vertical-align-middle" style="white-space: nowrap;">Item</th>
        <th class="text-center vertical-align-middle" style="white-space: nowrap;">Descripción</th>
        <th class="text-center vertical-align-middle" style="white-space: nowrap;">Unidad</th>
        <th class="text-center vertical-align-middle" style="white-space: nowrap;">Cant Sol</th>
        <th class="text-center vertical-align-middle" style="white-space: nowrap;">Cant Min</th>
      </tr>
    </thead>
    <tbody data-bind="foreach: Productos">
      <tr>
        <td class="text-center vertical-align-middle col-md-4" data-bind="text: product_name"></td>
        <td class="text-center vertical-align-middle col-md-4" data-bind="text: product_description"></td>
        <td class="text-center vertical-align-middle col-md-1" data-bind="text: measurement_name"></td>
        <td class="text-center vertical-align-middle col-md-1" data-bind="text: total_quantity"></td>
        <td class="text-center vertical-align-middle col-md-1" data-bind="text: minimum_quantity"></td>
      </tr>
    </tbody>
  </table>
</div>
<!-- Evaluador Técnico -->
<div class="m-heading-1 border-default m-bordered text-left">
  <h4 class="block bold" style="margin-top: 0; padding-top: 0;">
    Evaluador Técnico
  </h4>
  <p data-bind="text: Evaluador" style="font-weight: bold;"></p>
</div>
<div class="m-heading-1 border-default m-bordered text-left">
    <h4 class="block bold" style="margin-top: 0; padding-top: 0;"></h4>
    <table class="table table-striped table-bordered" id="ListaConcursosEconomicas">
        <thead>
            <tr>
                <th class="text-center vertical-align-middle">
                    Proveedores Invitados
                </th>
                <th class="text-center vertical-align-middle">
                    Fecha Invitación
                </th>
                <th class="text-center vertical-align-middle">
                    Fecha Recordatorio
                </th>
                <th class="text-center vertical-align-middle">
                    Fecha Aceptación / Rechazo
                </th>
                <th class="text-center vertical-align-middle">
                    Invitación
                </th>
                <th class="text-center vertical-align-middle">
                    Accion
                </th>
            </tr>
        </thead>
        <tbody data-bind="dataTablesForEach : { data: OferentesInvitados, options: { paging: false, ordering: false, info: false, searching: false } }"> 
            <tr>
                <td data-bind="text: Nombre" class="col-md-3 vertical-align-middle text-center"></td>
                <td data-bind="text: FechaConvocatoria" class="col-md-2 vertical-align-middle text-center"></td>
                <td data-bind="text: FechaRecordatorio" class="col-md-2 vertical-align-middle text-center"></td>
                <td data-bind="text: FechaAceptacionRechazo" class="col-md-3 vertical-align-middle text-center"></td>
                <td class="col-md-2 vertical-align-middle text-center">
                    <span 
                        class="label label-sm labelAlign" 
                        data-bind="text: Description, css: {
                    'label-success': Description === 'Aceptada',
                    'label-warning': Description === 'Pendiente',
                    'label-danger':  Description === 'Rechazada'
                    }">
                    </span>
                </td>
                <td style="text-align: center;">
                    <!-- ko if: IsInvitacionPendiente -->
                    <a 
                        data-bind="click: $root.sendInvitation.bind($data, IdOferente, true,false)" 
                        class="btn btn-xs green" 
                        title="Recordatorio">
                        Recordatorio 
                        <i class="fa fa-send"></i>
                    </a>
                    <!-- /ko -->
                </td>
            </tr>
        </tbody>
    </table>
</div>
