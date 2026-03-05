{capture 'post_scripts_child'}
    <script src="{asset('/global/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js')}" type="text/javascript"></script>
    <script src="{asset('/pages/scripts/components-bootstrap-maxlength.js')}" type="text/javascript"></script>
    <script src="{asset('/global/plugins/bootbox/bootbox.min.js')}" type="text/javascript"></script>
{/capture}
{$post_scripts_child[] = $smarty.capture.post_scripts_child scope="global"}

<div class="row">
    <div class="col-sm-12">
        <div class="m-heading-1 border-default m-bordered text-left">
            <h4 class="block bold" style="margin-top: 0; padding-top: 0;">Resultados rondas de ofertas</h4>
            <ul data-bind="foreach: RondasOfertas()" class="nav nav-pills nav-justified">
                <li data-bind="css: { active: active ? 'active' : '' }">
                    <a data-toggle="pill" data-bind="text:title, attr: { href: '#'+ref }"></a>
                </li>
            </ul>
            <div class="tab-content" data-bind="foreach: RondasOfertas()">
                <div class="tab-pane fade"
                    data-bind="attr: { id: ref }, css: { in: active ? 'in' : '', active: active ? 'active' : '' }">

                    {* Comparativas de ofertas *}
                    <div class="m-heading-1 border-default m-bordered text-left"
                        style="display: flex; justify-content: space-between; flex-direction: column;">
                        {include file='concurso/detail/customer/partials/economica/resumen-adjudicacion/comparativa-ofertas.tpl'}
                    </div>
                
                    
                    <div class="m-heading-1 border-default m-bordered text-left"
                        style="display: flex; justify-content: space-between; flex-direction: column;">
                        {include file='concurso/detail/customer/partials/economica/resumen-adjudicacion/mejor-integral.tpl'}
                    </div>
                    
                    <div class="m-heading-1 border-default m-bordered text-left"
                        style="display: flex; justify-content: space-between; flex-direction: column;">
                        {include file='concurso/detail/customer/partials/economica/resumen-adjudicacion/mejor-individual.tpl'}
                    </div>
                    
                    <!-- ko if: active -->
                    <div class="m-heading-1 border-default m-bordered text-left table-responsive"
                        style="display: flex; justify-content: space-between; flex-direction: column;">
                        {include file='concurso/detail/customer/partials/economica/resumen-adjudicacion/mejor-manual.tpl'}
                    </div>
                    <div class="m-heading-1 border-default m-bordered text-left"
                        style="display: flex; justify-content: space-between; flex-direction: column;">
                        {include file='concurso/detail/customer/partials/economica/resumen-adjudicacion/resumen.tpl'}
                    </div>
                    <!-- /ko -->
                    <!-- Bloque único de Informes y Archivos -->
                    <div class="m-heading-1 border-default m-bordered text-left"
                        style="display:flex; flex-direction:column; gap:16px;">

                        <!-- Informe de trazabilidad -->
                        <div style="display:flex; flex-direction:column; gap:8px;">
                            <h4 class="block bold" style="margin:0;">Informe de trazabilidad</h4>
                            <div>
                                <a class="btn btn-success"
                                data-bind="click: $root.downloadReport"
                                download>
                                    Descargar <i class="fa fa-download"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Archivos concurso -->
                        <div style="display:flex; flex-direction:column; gap:8px;">
                            <h4 class="block bold" style="margin:0;">Archivos concurso</h4>
                            <div>
                                <a class="btn btn-success"
                                data-bind="click: $root.downloadZip"
                                download>
                                    Descargar <i class="fa fa-download"></i>
                                </a>
                            </div>
                        </div>
                        <p class="text-muted" style="margin:0;">
                            <b>ACLARACIÓN:</b> Tanto el informe como los archivos se generarán con la información obtenida y disponible hasta el momento de su descarga.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nueva Ronda (fuera de los contenedores) -->
<div class="modal fade bs-modal-md" id="newRound" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <!-- HEADER -->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title text-center" data-bind="text:'¿Confirma la ' + $root.NuevaRonda() + '?'"></h4>
            </div>

            <div class="modal-body text-center">
                 <!-- FECHA LIMITE NUEVA RONDA -->
                <span> Indica la fecha limite de la nueva ronda <b style="color: red;">*</b></span>
                    <br>
                <span>(Minimo 72 hs a partir de hoy)</span>

                <div class="form-group required" data-bind="validationElement: $root.FechaNewRound()">
                    <div class="input-group date form_datetime bs-datetime" style="margin: auto;">
                        <input class="form-control" size="36" type="text" data-bind="dateTimePicker: $root.FechaNewRound, dateTimePickerOptions: {
                            format: 'dd-mm-yyyy hh:ii',
                            momentFormat: 'DD-MM-YYYY HH:mm',
                            startDate: $root.ThreeDaysFromTodayDate(),
                            value: $root.FechaNewRound(),
                            todayBtn: false
                        }">
                    </div>
                </div>

                <!-- FECHA LIMITE CIERRE MURO DE CONSULTA -->
                <span class="text-center" data-bind="text:'Indica la fecha limite para el cierre del muro de consultas'"></span>
                    <br>
                <span>(24 hs antes de la fecha limite de la nueva ronda)</span>

                <div class="form-group required" data-bind="validationElement: $root.NuevaFechaCierreMuroConsulta()">
                    <div class="input-group date form_datetime bs-datetime" style="margin: auto;">
                        <input id="NuevaFechaCierreMuroConsulta" class="form-control" size="36" type="text" 
                            data-bind="dateTimePicker: $root.NuevaFechaCierreMuroConsulta, 
                                        dateTimePickerOptions: {
                                            format: 'dd-mm-yyyy hh:ii',
                                            momentFormat: 'DD-MM-YYYY HH:mm',
                                            startDate: $root.TodayDate(),
                                            endDate: $root.FechaMaximaCierreDeConsulta(),
                                            value: $root.NuevaFechaCierreMuroConsulta(),
                                            todayBtn: false
                                        },
                                        enable: $root.FechaNewRound()">
                    </div>
                </div>
                
                <!-- BOX DE COMENTARIO -->
                <p>Añada un comentario para la nueva ronda <b style="color: red;">*</b></p>
                    <textarea rows="3" cols="50" class="form-control" style="resize: none;"data-bind="textInput: $root.ComentarioNuevaRonda">
                </textarea>

                <span>
                        Todos los campos marcados con <b style="color: red;">*</b> son oblgatorios
                    <br>
                </span>
            </div>

            <!-- FOOTER CON BOTONES -->
            <div class="modal-footer">
                <button type="button" class="btn red BTNC" data-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn green"  data-dismiss="modal"
                    data-bind="click: $root.SendNewRound">
                    Aceptar
                </button>
            </div>
        </div>
    </div>
</div>