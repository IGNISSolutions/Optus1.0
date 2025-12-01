<!-- ko if: !IncluyeTecnica() -->
<div class="row">
    <div class="col-sm-12">
        <div class="alert alert-success">
            Este concurso no incluye etapa de precalificación técnica.
        </div>
    </div>
</div>
<!-- /ko -->

<!-- ko if: IncluyeTecnica() -->
<!-- ko if: TechnicalEvaluations().length == 0 -->
<div class="row">
    <div class="col-sm-12">
        <div class="alert alert-success">
            Los proveedores aún no aceptan la invitacion al concurso
        </div>
    </div>
</div>
<!-- /ko -->


<!-- ko if: TechnicalEvaluations().length > 0 -->
<div class="m-heading-1 border-default m-bordered text-left" >
    <h4 class="block bold" style="margin-top: 0; padding-top: 0;">Plantilla calificación técnica</h4>
    <ul data-bind="foreach: TechnicalEvaluations()" class="nav nav-pills nav-justified">
        <li data-bind="css: { active: activeOfferer ? 'active' : '' }">
            <a data-toggle="pill" data-bind="text:razon_social, attr: { href: '#'+refOfferer }"></a>
        </li>
    </ul>
    <div class="tab-content" data-bind="foreach: TechnicalEvaluations()">
        <div class="tab-pane fade"
            data-bind="attr: { id: refOfferer }, css: { in: activeOfferer ? 'in' : '', active: activeOfferer ? 'active' : '' }">
            <ul data-bind="foreach: rondasTecnicas" class="nav nav-pills nav-justified">
                <li data-bind="css: { active: activeRound ? 'active' : '' }">
                    <a data-toggle="pill" data-bind="text:title, attr: { href: '#'+refRound }"></a>
                </li>
            </ul>

            <div class="tab-content" data-bind="foreach: rondasTecnicas">
                <div class="tab-pane fade"
                    data-bind="attr: { id: refRound }, css: { in: activeRound ? 'in' : '', active: activeRound ? 'active' : '' }">
                    <!-- ko if: tecnica_vencida && documents.length == 0 && !tecnica_declinada  -->
                    <div class="m-heading-1 border-default m-bordered text-center"
                        style="display: flex; justify-content: center; flex-direction: column;">
                        <span>
                            <i class="fa fa-clock-o fa-2x" aria-hidden="true"
                                title="El proveedor aun no envia su propuesta economica"
                                style="color:rgb(236, 11, 11); line-height: 28px">
                                El concurso a llegado a su fecha limite para presentar la propuesta técnica y no se
                                obtuvo respuesta del proveedor
                            </i>
                        </span>
                    </div>
                    <!-- /ko -->
                    <!-- ko if: (tecnica_pendiente && !tecnica_vencida) || tecnica_declinada -->
                    <div class="m-heading-1 border-default m-bordered text-center"
                        style="display: flex; justify-content: center; flex-direction: column;">
                        <span>
                            <!-- ko if: tecnica_pendiente && !tecnica_declinada -->
                            <i class="fa fa-clock-o fa-2x" aria-hidden="true"
                                title="El proveedor aun no envia su propuesta economica"
                                style="color:rgb(236, 213, 11)">
                                El
                                proveedor aun no envia su propuesta técnica</i>
                            <!-- /ko -->

                            <!-- ko if: (tecnica_presentada && tecnica_declinada) ||  tecnica_declinada -->
                            <i class="fa fa-clock-o fa-2x" aria-hidden="true"
                                title=" El proveedor aun no envia su propuesta economica" style="color:rgb(236, 11, 22)"
                                data-bind="text: 'El proveedor declinó su participación el dia ' + fechaDeclinacion">
                            </i>
                            <br>
                            <br>
                            <div class="note note-info text-left">
                                <h4 class="block">
                                    <b>Motivo declinación: </b>
                                </h4>
                                <p data-bind="text: motivoDeclination"></p>
                            </div>
                            <!-- /ko -->
                        </span>
                    </div>
                    <!-- /ko -->
                    <!-- ko if: tecnica_presentada && !tecnica_declinada-->
                    <div class="m-heading-1 border-default m-bordered text-left">
                        <h4 class="block bold" style="margin-top: 0; padding-top: 0;">Archivos</h4>
                        <div class="note note-info">
                            <h4 class="block">
                                Comentarios Proveedor
                            </h4>
                            <p data-bind="html: comment"></p>
                        </div>
                        <table class="table table-striped table-bordered">
                            <tbody data-bind="foreach: documents">
                            <!-- ko if: filename -->
                            <tr>
                                <td data-bind="text: name"></td>
                                <!-- alineo todo a la derecha -->
                                <td class="text-center">
                                <div style="display: flex; justify-content: flex-end; align-items: center; gap: 10px;">
                                    <!-- Botón -->
                                    <a data-bind="click: $root.downloadFile.bind($data, filename, 'oferente', $parent.oferente_id)"
                                    download
                                    class="btn btn-xl green"
                                    title="Descargar">
                                    Descargar <i class="fa fa-download"></i>
                                    </a>
                                    <!-- Fecha -->
                                    <!-- ko if: $parent.fecha_envio_propuesta -->
                                    <div style="text-align: center; font-size: 13px;">
                                        <b>Fecha de presentación:</b><br>
                                        <span data-bind="text: $parent.fecha_envio_propuesta"></span>
                                    </div>
                                    <!-- /ko -->
                                    <!-- ko ifnot: $parent.fecha_envio_propuesta -->
                                    <span style="font-size: 13px; color: #999;">
                                        No hay fecha registrada de presentación
                                    </span>
                                    <!-- /ko -->
                                </div>
                                </td>
                            </tr>
                            <!-- /ko -->
                            </tbody>
                        </table>
                    </div>
                    <!-- ko if: !revisada -->
                    <div class="m-heading-1 border-default m-bordered text-left">
                        <h4 class="block bold" style="margin-top: 0; padding-top: 0;" data-bind="visible: User.Tipo !== 5">Plantilla calificación técnica
                        </h4>

                        <table class="table table-striped table-bordered" id="ListaConcursosEconomicas" data-bind="visible: User.Tipo !== 5">
                            <thead>
                                <tr>
                                    <th colspan="3" data-bind="html: evaluation.atributo"></th>
                                    <th class="text-center" data-bind="html: evaluation.puntaje"></th>
                                    <input class="form-control minimo" type="hidden"
                                        data-bind="value: evaluation.puntaje_minimo">
                                </tr>
                            </thead>
                            <thead>
                                <tr>
                                    <th> Atributo </th>
                                    <th> Ponderación </th>
                                    <th> Puntaje </th>
                                    <th> Puntuación </th>
                                </tr>
                            </thead>
                            <tbody data-bind="foreach: evaluation.plantilla">
                                <tr>
                                    <td data-bind="text: atributo" class="col-md-6 vertical-align-middle"></td>
                                    <td data-bind="text: ponderacion + ' %'"
                                        class="col-md-2 text-center vertical-align-middle"></td>
                                    <td data-bind="text: puntaje" class="col-md-2 text-center vertical-align-middle">
                                    </td>
                                    <!-- ko if: $parent.evaluation.Evaluado -->
                                    <td class="col-md-2 text-center">
                                        <span data-bind="text: $parent.evaluation.valores[$index()]"></span>
                                    </td>
                                    <!-- /ko -->
                                    <!-- ko ifnot: $parent.evaluation.Evaluado -->
                                    <td class="col-md-2 text-center vertical-align-middle">
                                        <input class="form-control" type="hidden"
                                            data-bind="value: ponderacion, css: 'ponderacion_' + $parent.evaluation.oferente_id">
                                        <input class="form-control" type="number" min="0" max="100"
                                            onchange="$(this).val(Math.min(100, Math.max(0, $(this).val()))); "
                                            data-bind="value: $parent.evaluation.valores[$index()], css: 'puntuacion_' + $parent.evaluation.oferente_id, event: { change: function(data, event) { $root.CalcularTecnica($parent.evaluation.oferente_id) } }, attr: { 'data': ponderacion }, clickBubble: false">
                                    </td>
                                    <!-- /ko -->
                                </tr>
                                <!-- ko if: $index() === ($parent.evaluation.plantilla.length - 1) -->
                                <tr>
                                    <td data-bind="attr: { id:'puntosT_' + $parent.evaluation.oferente_id }, text: $parent.evaluation.cssText, style: { color: $parent.evaluation.cssColor }"
                                        class="col-md-3 text-center bold vertical-align-middle"></td>
                                    <td data-bind="text: '100%'" class="col-md-2 text-center vertical-align-middle">
                                    </td>
                                    <td data-bind="text: ''" class="col-md-2 text-center vertical-align-middle"></td>
                                    <td data-bind="attr: { id:'puntos_' + $parent.evaluation.oferente_id }, number: $parent.evaluation.alcanzado, precision: 2, style: { color: $parent.evaluation.cssColor }"
                                        class="col-md-2 text-center bold vertical-align-middle"></td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-right vertical-align-middle">
                                        <div class="form-group" style="margin-bottom: 0;">
                                            <!-- ko ifnot: $parent.evaluation.Evaluado -->
                                            <button type="button" class="btn btn-primary"
                                                data-bind="text: 'Guardar y enviar evaluación del proveedor', click: $root.sendTechnicalEvaluation"></button>
                                            <!-- ko if: !$parent.evaluation.lastRound -->
                                            <button type="button" class="btn btn-warning"
                                                data-bind="text: 'Solicitar ' + $parent.evaluation.newRound, click: $root.NewTechRound.bind($data, $parent.evaluation.oferente_id, $parent.proposal)"></button>
                                            <!-- /ko -->
                                            <!-- /ko -->
                                            <!-- ko if: $parent.evaluation.Evaluado -->
                                            <span
                                                data-bind="html: 'El proveedor ' + $parent.evaluation.razon_social + ' ya ha sido evaluado.', style: { color: $parent.evaluation.cssColor }"></span>
                                            <!-- ko if: $parent.evaluation.comentario -->
                                            <br>
                                            <span
                                                data-bind="html: '<b>Comentario Evaluación: </b>' + $parent.evaluation.comentario"></span>
                                            <!-- /ko -->
                                            <!-- /ko -->
                                        </div>
                                    </td>
                                </tr>
                                <!-- /ko -->

                            </tbody>
                        </table>
                    </div>
                    <!-- /ko -->
                    <!-- ko if: revisada -->
                    <div class="note note-info">
                        <h4 class="block">
                            Comentarios Nueva Ronda
                        </h4>
                        <p data-bind="html: comentario_nueva_roda"></p>
                    </div>
                    <!-- /ko -->
                    <!-- /ko -->
                </div>
            </div>

        </div>
    </div>
</div>

<!-- /ko -->
<!-- /ko -->