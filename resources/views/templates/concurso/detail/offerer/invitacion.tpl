<div class="modal fade bs-modal-lg" id="large" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title">Terminos y Condiciones</h4>
            </div>
            <div class="modal-body text-center" data-bind="html: TerminosCondiciones"></div>
            <div class="modal-footer">
                <button type="button" class="btn red BTNC" data-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn green" data-dismiss="modal"
                    data-bind="click: AcceptRejectInvitation.bind($data, 'accept')">
                    Aceptar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="m-heading-1 border-default m-bordered text-left">
    <h4 class="block bold" style="margin-top: 0; padding-top: 0;">Documentación</h4>
    <!-- ko if: Media().length > 0 -->
    <table class="table table-striped table-bordered" id="ListaConcursosEconomicas">
        <tbody data-bind="foreach: Media().filter(function(m, index, self) { 
            return m.indice != 0 && self.findIndex(function(t) { 
                return t.path === m.path; 
            }) === index; 
        })">

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

<div class="m-heading-1 border-default m-bordered text-left">
    <h4 class="block bold" style="margin-top: 0; padding-top: 0;">Items a licitar</h4>
    <table class="table table-striped table-bordered" id="ListaConcursosEconomicas">
        <thead>
            <tr>
                <th class="text-center vertical-align-middle" style="white-space: nowrap;">
                    Item
                </th>
                <th class="text-center vertical-align-middle" style="white-space: nowrap;">
                    Descripción
                </th>
                <th class="text-center vertical-align-middle" style="white-space: nowrap;">
                    Unidad
                </th>
                <th class="text-center vertical-align-middle" style="white-space: nowrap;">
                    Cant Sol
                </th>
                <th class="text-center vertical-align-middle" style="white-space: nowrap;">
                    Cant Min
                </th>
            </tr>
        </thead>
        <tbody data-bind="foreach: Productos()">
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


<!-- ko if: IsSobrecerrado() || IsOnline() -->
<div class="m-heading-1 border-default m-bordered text-left">
    <h4 class="block bold" style="margin-top: 0; padding-top: 0;">Localización</h4>
    <div class="row">
        <div class="col-md-6">
            <div class="tabbable-custom nav-justified">
                <ul class="nav nav-tabs nav-justified">
                    <li class="active">
                        <a href="#tab_1" data-toggle="tab">Lugar de prestación del servicio</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="tab_1">
                        <div class="form-group">
                            <label class="control-label visible-ie8 visible-ie9" style="display: block;">País</label>
                            <input type="text" class="form-control" id="pais" name="pais" disabled
                                data-bind="value: Pais">
                        </div>
                        <div class="form-group">
                            <label class="control-label visible-ie8 visible-ie9"
                                style="display: block;">Provincia</label>
                            <input type="text" class="form-control" id="provincia" name="provincia" disabled
                                data-bind="value: Provincia">
                        </div>
                        <div class="form-group">
                            <label class="control-label visible-ie8 visible-ie9"
                                style="display: block;">Localidad</label>
                            <input type="text" class="form-control" id="localidad" name="localidad" disabled
                                data-bind="value: Localidad">
                        </div>
                        <div class="form-group">
                            <label class="control-label visible-ie8 visible-ie9"
                                style="display: block;">Dirección</label>
                            <input type="text" class="form-control" id="direccion" name="direccion" disabled
                                data-bind="value: Direccion">
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label visible-ie8 visible-ie9"
                                        style="display: block;">CP</label>
                                    <input type="text" class="form-control placeholder-no-fix" name="cp" id="cp"
                                        disabled data-bind="value: Cp" />
                                </div>
                            </div>
                            <input type="hidden" name="latitud" id="latitud" data-bind="value: Latitud">
                            <input type="hidden" name="longitud" id="longitud" data-bind="value: Longitud">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label visible-ie8 visible-ie9" style="display: block;">Google Map</label>
                <div id="map-canvas-1" style="width: 100%; height: 406px; background: #ccc;"></div>
            </div>
        </div>
    </div>
</div>
<!-- /ko -->

<!-- ko if: IsInvitacionPendiente() && !PlazoVencidoAceptacion() && !Adjudicado() && !Eliminado() -->
<div class="m-heading-1 border-default m-bordered text-left">
    <h4 class="block bold" style="margin-top: 0; padding-top: 0;">¿Acepta invitación, terminos y condiciones?</h4>
    <table class="table table-striped table-bordered" id="ListaConcursosEconomicas">
        <thead>
            <tr>
                <th> Fechas límites </th>
                <th> Fecha </th>
                <th> Hora </th>
                <th> Zona Horaria </th>
                <th style="text-align: center;"> Acción / Estados </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td data-bind="text: 'Aceptación invitación'" class="col-md-3" style="vertical-align: middle;"></td>
                <td data-bind="text: AceptacionInvitacion" class="col-md-2" style="vertical-align: middle;"></td>
                <!--<td data-bind="text: '23:59:59'" class="col-md-1" style="vertical-align: middle;"></td>-->
                <td data-bind="text: AceptacionInvitacionHora" class="col-md-1" style="vertical-align: middle;"></td>

                <td data-bind="text: ZonaHoraria" class="col-md-2" style="vertical-align: middle;"></td>
                <td class="col-md-4" style="text-align: center; vertical-align: middle;">
                    <!-- ko if: AceptacionTerminos() == 'si' -->
                    <a class="btn btn-xl green" title="Aceptar" data-toggle="modal" href="#large">
                        Aceptar
                        <i class="fa fa-thumbs-up"></i>
                    </a>
                    <!-- /ko -->

                    <!-- ko if: AceptacionTerminos() == 'no' -->
                    <button type="button" class="btn green"
                        data-bind="click: AcceptRejectInvitation.bind($data, 'accept')">
                        Aceptar
                        <i class="fa fa-thumbs-up"></i>
                    </button>
                    <!-- /ko -->

                    <button type="button" class="btn red"
                        data-bind="click: AcceptRejectInvitation.bind($data, 'reject')">
                        Rechazar
                        <i class="fa fa-thumbs-down"></i>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<!-- /ko -->