{extends 'solped/solicitante/main.tpl'}

<!-- ESTILOS -->
{block 'styles'}

    <link href="{asset('/global/plugins/DataTables2/DataTables-2.0.2/css/dataTables.bootstrap.min.css')}" rel="stylesheet"
        type="text/css" />
    <link href="{asset('/global/plugins/DataTables2/Buttons-3.0.1/css/buttons.bootstrap.min.css')}" rel="stylesheet"
        type="text/css" />
    <link href="{asset('/global/plugins/DataTables2/ColReorder-2.0.0/css/colReorder.bootstrap.min.css')}" rel="stylesheet"
        type="text/css" />
    <link href="{asset('/global/plugins/DataTables2/FixedColumns-5.0.0/css/fixedColumns.bootstrap.min.css')}"
        rel="stylesheet" type="text/css" />
    <link href="{asset('/global/plugins/DataTables2/FixedHeader-4.0.1/css/fixedHeader.bootstrap.min.css')}" rel="stylesheet"
        type="text/css" />
    <link href="{asset('/global/plugins/DataTables2/Responsive-3.0.0/css/responsive.bootstrap.min.css')}" rel="stylesheet"
        type="text/css" />
    <link href="{asset('/global/plugins/DataTables2/RowReorder-1.5.0/css/rowReorder.bootstrap.min.css')}" rel="stylesheet"
        type="text/css" />
    <link href="{asset('/global/plugins/DataTables2/Scroller-2.4.1/css/scroller.bootstrap.min.css')}" rel="stylesheet"
        type="text/css" />
    <link href="{asset('/global/plugins/bootstrap-summernote/summernote.css')}" rel="stylesheet" type="text/css" />
    <link href="{asset('/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css')}" rel="stylesheet"
        type="text/css" />
    <link href="{asset('/global/plugins/bootstrap-tagsinput/bootstrap-tagsinput.css')}" rel="stylesheet" type="text/css" />
    <link href="{asset('/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css')}" rel="stylesheet" type="text/css" />
    <link href="{asset('/global/plugins/bootstrap-fileinput-kartik/css/fileinput.min.css')}" rel="stylesheet"
        type="text/css" />
    <link href="{asset('/global/plugins/bootstrap-toastr/toastr.min.css')}" rel="stylesheet" type="text/css" />
    <link href="{asset('/global/plugins/icheck/skins/all.css')}" rel="stylesheet" type="text/css" />
    <link href="{asset('/global/css/common.css')}" rel="stylesheet" type="text/css" />
    <link href="{asset('/apps/css/inbox.min.css')}" rel="stylesheet" type="text/css" />


{/block}

<!-- SCRIPTS PREVIOS A KNOCKOUT -->
{block 'pre-scripts'}

    <script src="{asset('/global/plugins/DataTables2/JSZip-3.10.1/jszip.min.js')}" type="text/javascript">
    </script>
    <script src="{asset('/global/plugins/DataTables2/pdfmake-0.2.7/pdfmake.min.js')}" type="text/javascript">
    </script>
    <script src="{asset('/global/plugins/DataTables2/pdfmake-0.2.7/vfs_fonts.js')}" type="text/javascript">
    </script>
    <script src="{asset('/global/plugins/DataTables2/DataTables-2.0.2/js/dataTables.min.js')}" type="text/javascript">
    </script>
    <script src="{asset('/global/plugins/DataTables2/DataTables-2.0.2/js/dataTables.bootstrap.min.js')}"
        type="text/javascript">
    </script>
    <script src="{asset('/global/plugins/DataTables2/Buttons-3.0.1/js/dataTables.buttons.min.js')}" type="text/javascript">
    </script>
    <script src="{asset('/global/plugins/DataTables2/Buttons-3.0.1/js/buttons.bootstrap.min.js')}" type="text/javascript">
    </script>
    <script src="{asset('/global/plugins/DataTables2/Buttons-3.0.1/js/buttons.colVis.min.js')}" type="text/javascript">
    </script>
    <script src="{asset('/global/plugins/DataTables2/Buttons-3.0.1/js/buttons.html5.min.js')}" type="text/javascript">
    </script>
    <script src="{asset('/global/plugins/DataTables2/Buttons-3.0.1/js/buttons.print.min.js')}" type="text/javascript">
    </script>
    <script src="{asset('/global/plugins/DataTables2/ColReorder-2.0.0/js/dataTables.colReorder.min.js')}"
        type="text/javascript">
    </script>
    <script src="{asset('/global/plugins/DataTables2/FixedColumns-5.0.0/js/dataTables.fixedColumns.min.js')}"
        type="text/javascript">
    </script>
    <script src="{asset('/global/plugins/DataTables2/FixedHeader-4.0.1/js/dataTables.fixedHeader.min.js')}"
        type="text/javascript">
    </script>
    <script src="{asset('/global/plugins/DataTables2/Responsive-3.0.0/js/dataTables.responsive.min.js')}"
        type="text/javascript">
    </script>
    <script src="{asset('/global/plugins/DataTables2/Responsive-3.0.0/js/responsive.bootstrap.js')}" type="text/javascript">
    </script>
    <script src="{asset('/global/plugins/DataTables2/RowReorder-1.5.0/js/dataTables.rowReorder.min.js')}"
        type="text/javascript">
    </script>
    <script src="{asset('/global/plugins/DataTables2/Scroller-2.4.1/js/dataTables.scroller.min.js')}"
        type="text/javascript">
    </script>
    <script src="{asset('/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js')}" type="text/javascript">
    </script>
    <script src="{asset('/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js')}"
        type="text/javascript"></script>
    <script src="{asset('/global/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js')}" type="text/javascript">
    </script>
    <script src="{asset('/global/plugins/jquery-inputmask/inputmask/inputmask.date.extensions.min.js')}"
        type="text/javascript"></script>
    </script>
    <script src="{asset('/global/plugins/dropzone/dropzone.min.js')}" type="text/javascript"></script>


{/block}

<!-- SCRIPTS POSTERIORES A KNOCKOUT -->
{block 'post-scripts'}
    <script>
        Inputmask.extendAliases({
            cant: {
                alias: 'integer',
                inputmode: 'numeric',
                autoUnmask: true,
                onUnMask: function(maskedValue, unmaskedValue, opts) {
                    return parseInt(maskedValue);
                }
            }
        });
    </script>

    <script src="{asset('/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js')}" type="text/javascript"></script>
    <script src="{asset('/global/plugins/bootstrap-fileinput-kartik/js/fileinput.min.js')}"></script>
    <script src="{asset('/global/plugins/bootstrap-fileinput-kartik/js/locales/es.js')}"></script>

{/block}

<!-- VISTA -->
{block 'solped-detail-solicitante'}
<div class="row margin-top-20">
    <div class="col-md-12 text-center">
        <h2 style="font-weight: bold; color: #555;">
            <!-- número en azul -->
            <span data-bind="text: '#' + IdSolicitud()" style="color: #32C5D2;"></span>
            <!-- separador y nombre en gris oscuro -->
            <span> – </span>
            <span data-bind="text: Nombre()"></span>
        </h2>
    </div>
</div>

<div class="row margin-top-40">
    <div class="col-sm-12">
        <div class="m-heading-1 border-default m-bordered text-left">
            <h4 class="block bold" style="margin-top: 0; padding-top: 0;">Información de la Solicitud</h4>
            <table class="table table-striped table-bordered">
                <tbody>
                    <tr>
                        <td class="col-md-3 vertical-align-middle">Solicitante</td>
                        <td class="col-md-9 vertical-align-middle" data-bind="text: Solicitante"></td>
                    </tr>
                    <tr>
                        <td class="col-md-3 vertical-align-middle">Fecha y Hora de Creacion de la Solicitud</td>
                        <td class="col-md-9 vertical-align-middle">
                            <span data-bind="text: FechaCreacion"></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="col-md-3 vertical-align-middle">Fecha y hora de envío de la solicitud</td>
                        <td class="col-md-9 vertical-align-middle" data-bind="text: FechaEnvioComprador"></td>
                    </tr>
                    <tr>
                        <td class="col-md-3 vertical-align-middle">Área Solicitante</td>
                        <td class="col-md-9 vertical-align-middle" data-bind="text: AreaSolicitante"></td>
                    </tr>
                    <!-- ko if: CompradorSugerido() -->
                    <tr>
                        <td class="col-md-3 vertical-align-middle">Comprador Sugerido</td>
                        <td class="col-md-9 vertical-align-middle" data-bind="text: CompradorSugerido"></td>
                    </tr>
                    <!-- /ko -->
                    <!-- ko if: EstadoActual() !== 'esperando-revision'  -->
                    <tr>
                        <td class="col-md-3 vertical-align-middle">Comprador que abrió la solicitud y fecha de apertura</td>
                        <td class="col-md-9 vertical-align-middle" data-bind="text: CompradorFecha"></td>
                    </tr>
                    <!-- /ko -->
                    <!-- ko if: EstadoActual() === 'rechazada' || EstadoActual() === 'devuelta' || EstadoActual() === 'aceptada' || EstadoActual() === 'adjudicada' -->
                    <tr>
                        <td class="col-md-3 vertical-align-middle">Comprador que tomó la decisión y fecha de decisión</td>
                        <td class="col-md-9 vertical-align-middle" data-bind="text: CompradorDecisionFecha"></td>
                    </tr>
                    <!-- /ko -->
                    <!-- ko if: EstadoActual() === 'adjudicada' && AdjudicacionProveedor && AdjudicacionProveedor() -->
                    <tr>
                        <td class="col-md-3 vertical-align-middle">Proveedor adjudicado</td>
                        <td class="col-md-9 vertical-align-middle">
                            <span data-bind="text: AdjudicacionProveedor"></span>
                            <!-- ko if: AdjudicacionFechaHora && AdjudicacionFechaHora() -->
                            <span class="text-muted"> (el <span data-bind="text: AdjudicacionFechaHora"></span>)</span>
                            <!-- /ko -->
                            <!-- ko if: CompradorConcurso && CompradorConcurso() -->
                            <div class="small text-muted">Decisión tomada por <span data-bind="text: CompradorConcurso"></span></div>
                            <!-- /ko -->
                        </td>
                    </tr>
                    <!-- /ko -->

                    
                </tbody>
            </table>
        </div>
    </div>

    <!-- Estado de la Solicitud -->
<div class="col-sm-12">
    <div class="m-heading-1 border-default m-bordered text-left">
        <h4 class="block bold" style="margin-top: 0; padding-top: 0;">Estado de la Solicitud</h4>

        <div class="text-center p-3" style="font-size: 18px; font-weight: bold; border-radius: 8px; color: #fff;"
            data-bind="css: {
                'bg-green': EstadoActual() === 'esperando-revision' || EstadoActual() === 'esperando-revision-2',
                'bg-primary': EstadoActual() === 'revisada' || EstadoActual() === 'revisada-2',
                'bg-red': EstadoActual() === 'rechazada',
                'bg-yellow-gold': EstadoActual() === 'devuelta',
                'bg-info': EstadoActual() === 'licitando',
                'bg-secondary': EstadoActual() !== 'esperando-revision' && EstadoActual() !== 'esperando-revision-2' && EstadoActual() !== 'revisada' && EstadoActual() !== 'revisada-2' && EstadoActual() !== 'rechazada' && EstadoActual() !== 'devuelta' && EstadoActual() !== 'licitando',
                'bg-green-jungle': EstadoActual() === 'aceptada',
                'bg-blue-steel': EstadoActual() === 'licitando',
                'bg-green-haze': EstadoActual() === 'adjudicada'  
            }">
            
            <!-- Texto dinámico según estado -->
            <span data-bind="text: 
                EstadoActual() === 'esperando-revision' ? 'Tu solicitud ha sido enviada, en breve un comprador la revisará.' :
                EstadoActual() === 'esperando-revision-2' ? 'Tu solicitud ha sido enviada para segunda revisión, en breve un comprador la revisará.' :
                EstadoActual() === 'revisada' ? 'Tu solicitud ha sido revisada, pero aún no ha sido aprobada.' :
                EstadoActual() === 'revisada-2' ? 'Tu solicitud ha sido revisada en segunda instancia, pero aún no ha sido aprobada.' :
                EstadoActual() === 'rechazada' ? 'La solicitud ha sido evaluada y fue rechazada.' :
                EstadoActual() === 'devuelta' ? 'Tu solicitud ha sido devuelta para correcciones. Revisa los comentarios y modifica lo necesario.' :
                EstadoActual() === 'aceptada' ? 'Felicitaciones tu solicitud ha sido aceptada, espera mientras se procesa.' :
                EstadoActual() === 'licitando' ? 'Tu solicitud entró en el proceso de licitación, para más detalles contacta con el equipo de compras' :
                EstadoActual() === 'adjudicada' ? 'Tu solicitud ha sido adjudicada, contacta con el proveedor y el equipo de compras para coordinar la entrega.' :
                'Estado desconocido'">
            </span>

            <!-- ko if: EstadoActual() === 'aceptada' -->
            <div class="p-2" style="background-color: #f7f7f7; border-radius: 6px; font-size: 16px; color: #666; margin-top: 10px;">
            <strong>Aceptada por:</strong> <span data-bind="text: CompradorAceptacionComplete"></span>
        </div>
            <!-- /ko -->
        </div>
    </div>
</div>

    <!-- Motivo de devolución -->
    <!-- ko if: EstadoActual() === 'devuelta' -->
<div class="col-sm-12">
    <div class="m-heading-1 border-default m-bordered text-left">
        <h5 class="block bold" style="margin-top: 0; padding-top: 0; color: #eb9800; font-size: 22px;">
            Motivo de la devolución:
        </h5>
        <div class="p-3" style="background-color: #ffffff; border: 1px solid #ffffff; border-radius: 6px; font-size: 18px; color: #000000;">
            <span data-bind="text: ReturnComment"></span>
        </div>
        <!-- ko if: CompradorDevolucionFecha -->
        <div class="p-2" style="background-color: #f7f7f7; border-radius: 6px; font-size: 16px; color: #666; margin-top: 10px;">
            <strong>Devuelta por:</strong> <span data-bind="text: CompradorDevolucionFecha"></span>
        </div>
        <!-- /ko -->
    </div>
</div>
<!-- /ko -->

    


<!-- Motivo de rechazo -->
    <!-- ko if: EstadoActual() === 'rechazada' -->
<div class="col-sm-12" >
    <div class="m-heading-1 border-default m-bordered text-left">
        <h5 class="block bold" style="margin-top: 0; padding-top: 0; color: #ff0000; font-size: 22px;">
            Motivo de rechazo:
        </h5>
        <div class="p-3" style="border-radius: 6px; font-size: 18px; color: #000000;">
            <span data-bind="text: RejectComment"></span>
        </div>
        <!-- ko if: CompradorDecisionFecha -->
        <div class="p-2" style="background-color: #f7f7f7; border-radius: 6px; font-size: 16px; color: #666; margin-top: 10px;">
            <strong>Rechazada por:</strong> <span data-bind="text: CompradorDecisionFecha"></span>
        </div>
        <!-- /ko -->
    </div>
</div>
<!-- /ko -->


    
    <!-- Descripción -->
    <div class="col-sm-12">
        <div class="m-heading-1 border-default m-bordered text-left">
            <h4 class="block bold" style="margin-top: 0; padding-top: 0;">Descripción</h4>
            <div class="well" data-bind="html: Descripcion"></div>
        </div>
    </div>

    <!-- Documento -->
    <div class="m-heading-1 border-default m-bordered text-left">
    <h4 class="block bold">Documentación</h4>

    <!-- Si hay documento -->
    <!-- ko if: FilePathComplete() -->
    <table class="table table-striped table-bordered">
        <tr>
            <td class="col-md-6 text-center" style="vertical-align: middle;">Documento</td>
            <td class="col-md-6 text-center" style="vertical-align: middle;">
                <a data-bind="click: $root.downloadFile.bind($data, FilePathComplete(), 'solped', null)"
                   download class="btn btn-xl green" title="Descargar">
                    Descargar <i class="fa fa-download"></i>
                </a>
            </td>
        </tr>
    </table>
    <!-- /ko -->

    <!-- Si no hay documento -->
    <!-- ko ifnot: FilePathComplete() -->
    <div class="alert alert-success text-center">
        No hay documentos
    </div>
    <!-- /ko -->
</div>


    <!-- Items/Productos -->
    <div class="col-sm-12">
        <div class="m-heading-1 border-default m-bordered text-left">
            <h4 class="block bold" style="margin-top: 0; padding-top: 0;">Items Solicitados</h4>
            <table class="table table-striped table-bordered" id="ListaItems">
                <thead>
                    <tr>
                        <th class="text-center">Nombre</th>
                        <th class="text-center">Descripción</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-center">Cantidad Mínima</th>
                        <th class="text-center">Unidad de Medida</th>
                        <th class="text-center">Costo Objetivo</th>

                    </tr>
                </thead>
                <tbody data-bind="foreach: Productos">
                    <tr>
                        <td class="text-center" data-bind="text: Nombre"></td>
                        <td data-bind="text: Descripcion"></td>
                        <td class="text-center" data-bind="text: Cantidad"></td>
                        <td class="text-center" data-bind="text: OfertaMinima"></td>
                        <td class="text-center" data-bind="text: UnidadMedidaNombre"></td>
                        <td class="text-center" data-bind="text: TargetCost"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Botones de acción -->
<div class="form-group text-right" style="display:inline-block margin-top: 20px;">
    <a href="javascript:history.back()" class="btn btn-primary">
        Volver al listado
    </a>
    <!-- ko if: EstadoActual() !== 'aceptada' && EstadoActual() !== 'rechazada' && EstadoActual() !== 'cancelada' && EstadoActual() !== 'licitando' && EstadoActual() !== 'adjudicada' -->
   <a data-bind="click: cancelarSolicitud, css: { disabled: !SolpedActive() }" class="btn btn-danger">
        <i class="fa fa-times"></i> Cancelar Solicitud
    </a>
    <!-- /ko -->
    <!-- ko if: EstadoActual() === 'esperando-revision' || EstadoActual() === 'esperando-revision-2' -->
    <a data-bind="attr: { href: SolpedActive() ? '/solped/edicion/' + IdSolicitud() : 'javascript:void(0);' }, css: { disabled: !SolpedActive() }, click: function(){ if (!SolpedActive()) { guardSolpedActive(); } }" class="btn btn-success">
        <i class="fa fa-edit"></i> Editar
    </a>
    <!-- /ko -->
    <!-- ko if: EstadoActual() === 'devuelta' -->
    <a data-bind="attr: { href: SolpedActive() ? '/solped/edicion/' + IdSolicitud() : 'javascript:void(0);' }, css: { disabled: !SolpedActive() }, click: function(){ if (!SolpedActive()) { guardSolpedActive(); } }" class="btn btn-warning">
        <i class="fa fa-edit"></i> Editar
    </a>
    <a data-bind="click: enviarSolicitudCorregida, css: { disabled: !SolpedActive() }" class="btn btn-success">
        <i class="fa fa-send"></i> Enviar Solicitud Corregida
    </a>
    <!-- /ko -->
</div>
</div>
{/block}

<!-- KNOCKOUT -->
{block 'knockout'}
    <script type="text/javascript">
        var solpedActiveFlag = {if isSolpedActive()}true{else}false{/if};
        var SolpedPorEtapaSolicitante = function(data) {
            var self = this;
            console.log("data", data);
            console.log("CompradorFirstRevision:", data.list.CompradorFirstRevision);
            console.log("FechaFirstRevision:", data.list.FechaFirstRevision);
            console.log("ReturnComment:", data.list.ReturnComment);
            console.log("EstadoActual:", data.list.EstadoActual);

            this.Breadcrumbs = ko.observableArray(data.breadcrumbs);

            this.SolpedActive = ko.observable(!!solpedActiveFlag);
            this.guardSolpedActive = function() {
                if (self.SolpedActive()) {
                    return true;
                }
                swal('Atención', 'El módulo de Solped está desactivado para tu empresa.', 'warning');
                return false;
            };


            this.IdSolicitud = ko.observable(data.list.IdSolicitud);
            this.Nombre = ko.observable(data.list.Nombre);
            this.IdSolicitante = ko.observable(data.list.IdSolicitante);
            this.Solicitante = ko.observable(data.list.Solicitante);
            this.TipoCompra = ko.observable(data.list.TipoCompra);
            this.CodigoInterno = ko.observable(data.list.CodigoInterno);
            this.Descripcion = ko.observable(data.list.Descripcion);
            this.AreaSolicitante = ko.observable(data.list.AreaSolicitante);
            this.CompradorSugerido = ko.observable(data.list.CompradorSugerido);
            this.FechaCreacion = ko.observable(data.list.FechaCreacion);
            this.FechaEnvioComprador = ko.observable(data.list.FechaEnvioComprador);
            this.UsuarioReject = ko.observable(data.list.UsuarioReject);
            this.UsuarioAccept = ko.observable(data.list.UsuarioAccept);
            this.FechaRechazo = ko.observable(data.list.FechaRechazo);
            this.FechaAceptacion = ko.observable(data.list.FechaAceptacion);
            this.FechaFirstRevision = ko.observable(data.list.FechaFirstRevision);
            this.RejectComment = ko.observable(data.list.RejectComment);
            this.ReturnComment = ko.observable(data.list.ReturnComment);
            this.Productos = ko.observableArray(data.list.Productos);

            this.CompradorDecision = ko.observable(data.list.CompradorDecision);
            this.CompradorFirstRevision = ko.observable(data.list.CompradorFirstRevision);
            this.CompradorDecisionFecha = ko.observable(data.list.CompradorDecisionFecha);
            this.CompradorDevolucionFecha = ko.observable(data.list.CompradorDevolucionFecha);

            this.Eliminado = ko.observable(data.list.eliminado);
            this.FilePath = ko.observable(data.list.file_path);
            this.FilePathComplete = ko.observable(data.list.FilePathComplete);


            this.Etapa = ko.observable(data.list.Etapa);
            this.EstadoActual = ko.observable(data.list.EstadoActual);

            // Datos mínimos de adjudicación
            this.AdjudicacionProveedor = ko.observable(data.list.AdjudicacionProveedor || null);
            this.AdjudicacionFechaHora = ko.observable(data.list.AdjudicacionFechaHora || null);
            this.CompradorConcurso = ko.observable(data.list.CompradorConcurso || null);

            this.CompradorFecha = ko.computed(function() {
                    if (this.CompradorFirstRevision() && this.FechaFirstRevision()) {
                        return this.CompradorFirstRevision() + ' - ' + this.FechaFirstRevision();
                    }
                    return '';
                }, this);
            this.CompradorAceptacionComplete = ko.computed(function() {
                    if (this.CompradorDecision() && this.FechaAceptacion()) {
                        return this.CompradorDecision() + ' - ' + this.FechaAceptacion();
                    }
                    return '';
                }, this);

            this.downloadFile = function(path, type = null, id = null) {
                $.blockUI();
                var data = {
                    Id: id,
                    Type: type,
                    Path: path,
                };
                var url = '/media/file/download';

                Services.Post(url, {
                        UserToken: User.Token,
                        Entity: JSON.stringify(ko.toJS(data))
                    },
                    (response) => {
                        $.unblockUI();
                        if (response.success) {
                            window.open(response.data.public_path); // abre la URL para descargar
                        } else {
                            swal('Error', response.message, 'error');
                        }
                    },
                    (error) => {
                        $.unblockUI();
                        swal('Error', typeof error.message != 'undefined' ? error.message : error.responseJSON.message, 'error');
                    },
                    null,
                    null
                );
            }


            this.enviarSolicitudCorregida = function() {
                if (!self.guardSolpedActive()) {
                    return;
                }
                swal({
                    title: '¿Desea enviar la solicitud corregida?',
                    text: 'Esta a punto de reenviar las notificaciones para esta solicitud corregida.',
                    type: 'info',
                    closeOnClickOutside: false,
                    showCancelButton: true,
                    closeOnConfirm: true,
                    confirmButtonText: 'Aceptar',
                    confirmButtonClass: 'btn btn-success',
                    cancelButtonText: 'Cancelar',
                    cancelButtonClass: 'btn btn-default'
                }, function(result) {
                    swal.close();
                    if (result) {
                        $.blockUI();
                        var url = '/solped/solicitante/send';
                        Services.Post(url, {
                                UserToken: User.Token,
                                IdSolped: this.IdSolicitud()
                            },
                            (response) => {
                                swal.close();
                                $.unblockUI();
                                setTimeout(function() {
                                    if (response.success) {
                                        swal({
                                            title: 'Hecho',
                                            text: response.message,
                                            type: 'success',
                                            closeOnClickOutside: false,
                                            closeOnConfirm: true,
                                            confirmButtonText: 'Aceptar',
                                            confirmButtonClass: 'btn btn-success'
                                        }, function(result) {
                                            location.reload();
                                        });
                                    } else {
                                        swal('Error', response.message, 'error');
                                    }
                                }, 500);
                            },
                            (error) => {
                                swal.close();
                                $.unblockUI();
                                setTimeout(function() {
                                    console.error('Error al enviar solicitud corregida:', error);
                                    var errorMessage = error.message || 'Error desconocido al enviar la solicitud';
                                    if (error.status === 500) {
                                        errorMessage = 'Error interno del servidor. Por favor, verifique los datos e intente nuevamente.';
                                    }
                                    swal('Error', errorMessage, 'error');
                                }, 500);
                            },
                            null,
                            null
                        );
                    }
                }.bind(this));
            };

            this.cancelarSolicitud = function() {
                if (!self.guardSolpedActive()) {
                    return;
                }
                swal({
                    title: 'Cancelar Solicitud',
                    text: 'Por favor, proporcione una justificación para cancelar esta solicitud:',
                    type: 'input',
                    inputType: 'text',
                    inputPlaceholder: 'Escriba aquí la justificación...',
                    closeOnClickOutside: false,
                    showCancelButton: true,
                    closeOnConfirm: false,
                    confirmButtonText: 'Cancelar Solicitud',
                    confirmButtonClass: 'btn btn-danger',
                    cancelButtonText: 'Volver',
                    cancelButtonClass: 'btn btn-default',
                    inputValidator: function(value) {
                        return new Promise(function(resolve, reject) {
                            if (value && value.trim().length >= 10) {
                                resolve();
                            } else {
                                reject('La justificación debe tener al menos 10 caracteres');
                            }
                        });
                    }
                }, function(justificacion) {
                    if (justificacion) {
                        swal({
                            title: '¿Está seguro?',
                            text: 'Esta acción cancelará permanentemente la solicitud y no se podrá deshacer.',
                            type: 'warning',
                            closeOnClickOutside: false,
                            showCancelButton: true,
                            closeOnConfirm: true,
                            confirmButtonText: 'Sí, cancelar',
                            confirmButtonClass: 'btn btn-danger',
                            cancelButtonText: 'No, volver',
                            cancelButtonClass: 'btn btn-default'
                        }, function(confirmar) {
                            if (confirmar) {
                                $.blockUI();
                                var url = '/solped/solicitante/cancelSolped';
                                Services.Post(url, {
                                        UserToken: User.Token,
                                        IdSolped: this.IdSolicitud(),
                                        Justificacion: justificacion
                                    },
                                    (response) => {
                                        swal.close();
                                        $.unblockUI();
                                        setTimeout(function() {
                                            if (response.success) {
                                                swal({
                                                    title: 'Solicitud Cancelada',
                                                    text: response.message,
                                                    type: 'success',
                                                    closeOnClickOutside: false,
                                                    closeOnConfirm: true,
                                                    confirmButtonText: 'Aceptar',
                                                    confirmButtonClass: 'btn btn-success'
                                                }, function(result) {
                                                    window.location.href = '/solped/solicitante';
                                                });
                                            } else {
                                                swal('Error', response.message, 'error');
                                            }
                                        }, 500);
                                    },
                                    (error) => {
                                        swal.close();
                                        $.unblockUI();
                                        setTimeout(function() {
                                            console.error('Error al cancelar solicitud:', error);
                                            var errorMessage = error.message || 'Error desconocido al cancelar la solicitud';
                                            if (error.status === 500) {
                                                errorMessage = 'Error interno del servidor. Por favor, intente nuevamente.';
                                            }
                                            swal('Error', errorMessage, 'error');
                                        }, 500);
                                    },
                                    null,
                                    null
                                );
                            }
                        }.bind(this));
                    }
                }.bind(this));
            };

            
            
        }





        jQuery(document).ready(function () {
            let pathParts = window.location.pathname.split("/");
            let etapa = pathParts[3];  // en-analisis
            let id = pathParts[4];     // 7
            
            console.log("Etapa:", etapa);
            console.log("ID:", id);
            console.log("URL:", '/solped/solicitante/' + etapa + '/' + id + '/detail');

            $.blockUI();
            Services.Get('/solped/solicitante/' + etapa + '/' + id + '/detail', {
                Entity: JSON.stringify(ko.toJS({}))
            },
            (response) => {
                console.log("Response:", response); // Agregar para debug
                if (response.success) {
                    window.E = new SolpedPorEtapaSolicitante(response.data);
                    AppOptus.Bind(E);
                }
                $.unblockUI();
            },
            (error) => {
                console.error("Error:", error); // Agregar para debug
                $.unblockUI();
                swal('Error', error.message, 'error');
            });
        });
        </script>

{/block}