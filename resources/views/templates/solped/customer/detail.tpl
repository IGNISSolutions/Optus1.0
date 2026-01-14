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
{block 'solped-detail-customer'}
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
                    <tr >
                        <td class="col-md-3 vertical-align-middle">Fecha de resolución</td>
                        <td class="col-md-9 vertical-align-middle" data-bind="text: FechaResolucion"></td>

                    </tr>
                    <tr >
                        <td class="col-md-3 vertical-align-middle">Fecha de entrega</td>
                        <td class="col-md-9 vertical-align-middle" data-bind="text: FechaEntrega"></td>

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
                    <tr data-bind="if: CompradorDecision() !== null && CompradorDecision() !== ''">
                        <td class="col-md-3 vertical-align-middle">Comprador que abrió la solicitud y fecha de apertura</td>
                        <td class="col-md-9 vertical-align-middle" data-bind="text: CompradorFecha"></td>

                    </tr>

                </tbody>
            </table>
        </div>
    </div>

    <!-- Motivo de devolución -->
    <!-- ko if: EstadoActual() === 'devuelta' -->
    <div class="col-sm-12" data-bind="if: EstadoActual() === 'devuelta'">
        <div class="m-heading-1 border-default m-bordered text-left">
            <h5 class="block bold" style="margin-top: 0; padding-top: 0; color: #eb9800; font-size: 22px;">
                Motivo de la devolución:
            </h5>
            <div class="p-3" style="background-color: #ffffff; border-radius: 6px; font-size: 18px; color: #000000;">
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

<!-- Estado de la Solicitud -->
    <!-- ko if: User.Tipo === 4 -->
    <div class="col-sm-12">
        <div class="m-heading-1 border-default m-bordered text-left">
            <h4 class="block bold" style="margin-top: 0; padding-top: 0;">Estado de la Solicitud</h4>

            <div class="text-center p-3" style="font-size: 18px; font-weight: bold; border-radius: 8px; color: #fff;"
                data-bind="css: {
                    'bg-green': EstadoActual() === 'esperando-revision',
                    'bg-primary': EstadoActual() === 'revisada',
                    'bg-red': EstadoActual() === 'rechazada',
                    'bg-info': EstadoActual() === 'licitando',
                    'bg-secondary': EstadoActual() !== 'esperando-revision' && EstadoActual() !== 'revisada' && EstadoActual() !== 'rechazada' && EstadoActual() !== 'devuelta' && EstadoActual() !== 'licitando'
                }">
                
                <!-- Texto dinámico según estado -->
                <span data-bind="text: 
                    EstadoActual() === 'esperando-revision' ? 'Tu solicitud ha sido enviada, en breve un comprador la revisará.' :
                    EstadoActual() === 'revisada' ? 'Tu solicitud ha sido revisada, pero aún no ha sido aprobada.' :
                    EstadoActual() === 'rechazada' ? 'La solicitud ha sido evaluada y fue rechazada.' :
                    EstadoActual() === 'cancelada' ? 'La solicitud ha sido cancelada por el creador.' :
                    EstadoActual() === 'licitando' ? 'Tu solicitud está siendo procesada en un proceso de licitación/subasta.' :
                    'Estado desconocido'">
                </span>
            </div>
        </div>
    </div>
    <!-- /ko -->

    <!-- ko if: EstadoActual() === 'rechazada' -->
    <div class="col-sm-12" >
        <div class="m-heading-1 border-default m-bordered text-left">
            <h5 class="block bold" style="margin-top: 0; padding-top: 0; color: #ff0000; font-size: 22px;">
                Motivo de rechazo:
            </h5>
            <div class="p-3" style="background-color: #f7f7f7; border-radius: 6px; font-size: 18px; color: #000000;">
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

    <!-- ko if: EstadoActual() === 'cancelada' -->
    <div class="col-sm-12" >
        <div class="m-heading-1 border-default m-bordered text-left">
            <h5 class="block bold" style="margin-top: 0; padding-top: 0; color: #ff0000; font-size: 22px;">
                Motivo de cancelación:
            </h5>
            <div class="p-3" style="background-color: #f7f7f7; border-radius: 6px; font-size: 18px; color: #000000;">
                <span data-bind="html: CancelMotive"></span>
            </div>
            <!-- ko if: SolicitanteCancelComplete -->
            <div class="p-2" style="background-color: #f7f7f7; border-radius: 6px; font-size: 16px; color: #666; margin-top: 10px;">
                <strong>Cancelada por:</strong> <span data-bind="text: SolicitanteCancelComplete"></span>
            </div>
            <!-- /ko -->
        </div>
    </div>
    <!-- /ko -->


        
    <!--ko if: EstadoActual() !== 'cancelada'-->
    <!-- Descripción -->
    <div class="col-sm-12">
        <div class="m-heading-1 border-default m-bordered text-left">
            <h4 class="block bold" style="margin-top: 0; padding-top: 0;">Descripción</h4>
            <div class="well" data-bind="html: Descripcion"></div>
        </div>
    </div>
    <!--/ko-->
    <!--ko if: EstadoActual() !== 'cancelada'-->
    <!-- Documento -->
    <div class="m-heading-1 border-default m-bordered text-left">
    <h4 class="block bold" style="margin-top: 0; padding-top: 0;">Documentación</h4>
    <table class="table table-striped table-bordered" id="ListaConcursosEconomicas">
        <tbody data-bind="foreach: FilePath()">

            <tr>
                <td class="col-md-6 text-center" style="vertical-align: middle;" data-bind="text: nombre">

                </td>
                <td class="col-md-6 text-center" style="vertical-align: middle;">
                    <a data-bind="click: $root.downloadFile.bind($data, imagen, 'solped', $root.IdSolped())"
                        download class="btn btn-xl green" title="Descargar">
                        Descargar
                        <i class="fa fa-download"></i>
                    </a>
                </td>
            </tr>

        </tbody>
    </table>

    <div class="alert alert-success text-center">
        No hay documentos
    </div>
</div>
<!--/ko-->
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

    <!--ko if : User.Tipo === 3 -->
        <!-- ko if: EstadoActual() === 'esperando-revision' || EstadoActual() === 'revisada' ||  EstadoActual() === 'esperando-revision-2' || EstadoActual() === 'revisada-2' -->
    <a data-bind="click: aceptarSolicitud" class="btn green btn-sm">
        <i class="fa fa-check"></i> Aceptar Solicitud</a>
    <a data-bind="click: rechazarSolicitud" class="btn red btn-sm">
        <i class="fa fa-times"></i> Rechazar Solicitud</a>
        <!-- ko if: EstadoActual() === 'esperando-revision' || EstadoActual() === 'revisada' -->
    <a data-bind="click: devolverSolicitud" class="btn yellow-gold btn-sm">
        <i class="fa fa-undo"></i> Devolver Solicitud</a>
        <!-- /ko -->
        <!-- /ko -->
    <!-- /ko -->
</div>
</div>
{/block}

<!-- KNOCKOUT -->
{block 'knockout'}
    <script type="text/javascript">

        console.log("Usuario", User)

        var SolpedPorEtapaSolicitante = function(data) {
            console.log("data", data);


            this.Breadcrumbs = ko.observableArray(data.breadcrumbs);

            this.IdSolicitud = ko.observable(data.list.IdSolicitud);
            this.Nombre = ko.observable(data.list.Nombre);
            this.IdSolicitante = ko.observable(data.list.IdSolicitante);
            this.Solicitante = ko.observable(data.list.Solicitante);
            this.TipoCompra = ko.observable(data.list.TipoCompra);
            this.CodigoInterno = ko.observable(data.list.CodigoInterno);
            this.Descripcion = ko.observable(data.list.Descripcion);
            this.AreaSolicitante = ko.observable(data.list.AreaSolicitante);
            this.CompradorSugerido = ko.observable(data.list.CompradorSugerido);
            this.FechaEnvioComprador = ko.observable(data.list.FechaEnvioComprador);
            this.FechaCreacion = ko.observable(data.list.FechaCreacion);
            this.FechaResolucion = ko.observable(data.list.FechaResolucion);
            this.FechaEntrega = ko.observable(data.list.FechaEntrega);
            this.UsuarioReject = ko.observable(data.list.UsuarioReject);
            this.UsuarioAccept = ko.observable(data.list.UsuarioAccept);
            this.FechaRechazo = ko.observable(data.list.FechaRechazo);
            this.FechaAceptacion = ko.observable(data.list.FechaAceptacion);
            this.FechaFirstRevision = ko.observable(data.list.FechaFirstRevision);
            this.RejectComment = ko.observable(data.list.RejectComment);
            this.ReturnComment = ko.observable(data.list.ReturnComment);
            this.Productos = ko.observableArray(data.list.Productos);

            this.CancelMotive = ko.observable(data.list.CancelMotive);
            this.FechaCancelacion = ko.observable(data.list.FechaCancelacion);

            this.CompradorDecision = ko.observable(data.list.CompradorDecision);
            this.CompradorFirstRevision = ko.observable(data.list.CompradorFirstRevision);
            this.CompradorDecisionFecha = ko.observable(data.list.CompradorDecisionFecha);
            this.CompradorDevolucionFecha = ko.observable(data.list.CompradorDevolucionFecha);

            this.Eliminado = ko.observable(data.list.eliminado);
            this.FilePath = ko.observable(data.list.file_path);

            this.Etapa = ko.observable(data.list.Etapa);
            this.EstadoActual = ko.observable(data.list.EstadoActual);

            this.CompradorFecha = ko.computed(function() {
            return this.CompradorFirstRevision() + ' - ' + this.FechaFirstRevision();
            }, this);

            this.SolicitanteCancelComplete = ko.computed(function() {
                return this.Solicitante() + ' - ' + this.FechaCancelacion();
            }, this);



            this.aceptarSolicitud = function() {
                var self = this;
                swal({  title: 'Aceptar Solicitud', 
                        text: '¿Esta seguro que desea aceptar la solicitud?', 
                        icon: 'info' }, function (result) {
                            swal.close();
                            if (result) {
                                $.blockUI();
                                Services.Post('/solped/cliente/approve', {
                                    UserToken: User.Token,
                                    Entity: JSON.stringify(ko.toJS({
                                        IdSolicitud: self.IdSolicitud(),
                                        IdUsuario: User.Id
                                    }))
                                },
                                (response) => {
                                    $.unblockUI();
                                    if (response.success) {
                                        swal({
                                            title: 'Éxito',
                                            text: 'Solicitud aceptada correctamente',
                                            type: 'success',
                                            closeOnClickOutside: false,
                                            confirmButtonText: 'Aceptar',
                                            confirmButtonClass: 'btn btn-success'
                                        }, function() {
                                            if (response.data && response.data.redirect) {
                                                window.location.href = response.data.redirect;
                                            } else {
                                                location.reload();
                                            }
                                        });
                                    } else {
                                        swal('Error', response.message, 'error');
                                    }
                                },
                                (error) => {
                                    $.unblockUI();
                                    swal('Error', error.message || 'Error al procesar la solicitud', 'error');
                                });
                            }
                        });
            };

            this.rechazarSolicitud = function() {
                var self = this;
                swal({
                    title: 'Rechazar Solicitud',
                    text: '¿Por qué deseas rechazar la solicitud?',
                    type: 'input',
                    inputPlaceholder: 'Escribe un motivo (obligatorio)',
                    closeOnClickOutside: false,
                    showCancelButton: true,
                    closeOnConfirm: false,
                    closeOnCancel: true,
                    confirmButtonText: 'Aceptar',
                    confirmButtonClass: 'btn btn-danger',
                    cancelButtonText: 'Cancelar',
                    cancelButtonClass: 'btn btn-default'
                }, function(result) {
                    swal.close();

                    if (result === false || result === null) return; // canceló
                    if (!result.trim()) {
                        swal('Error', 'Debes ingresar un motivo para rechazar la solicitud.', 'error');
                        return;
                    }

                    $.blockUI();

                    Services.Post('/solped/cliente/reject', {
                            UserToken: User.Token,
                            Entity: JSON.stringify(ko.toJS({
                                IdSolicitud: self.IdSolicitud(),
                                IdUsuario: User.Id
                            })),
                            Reason: result
                        },
                        function(response) {
                            $.unblockUI();
                            if (response.success) {
                                setTimeout(function() {
                                    swal({
                                        title: 'Éxito',
                                        text: 'Solicitud rechazada correctamente',
                                        type: 'success',
                                        closeOnClickOutside: false,
                                        confirmButtonText: 'Aceptar',
                                        confirmButtonClass: 'btn btn-success'
                                    }, function() {
                                        if (response.data && response.data.redirect) {
                                            window.location.href = response.data.redirect;
                                        } else {
                                            location.reload();
                                        }
                                    });
                                }, 500);
                            } else {
                                setTimeout(function() {
                                    swal('Error', response.message, 'error');
                                }, 500);
                            }
                        },
                        function(error) {
                            $.unblockUI();
                            setTimeout(function() {
                                swal('Error', error.message || 'Error al procesar la solicitud', 'error');
                            }, 500);
                        }
                    );
                });
            };



            this.devolverSolicitud = function() {
                var self = this;
                swal({
                    title: 'Devolver Solicitud',
                    text: '¿Por qué deseas devolver la solicitud para su modificación?',
                    type: 'input',
                    inputPlaceholder: 'Escribe un motivo (obligatorio)',
                    closeOnClickOutside: false,
                    showCancelButton: true,
                    closeOnConfirm: false,
                    closeOnCancel: true,
                    confirmButtonText: 'Aceptar',
                    confirmButtonClass: 'btn btn-warning',
                    cancelButtonText: 'Cancelar',
                    cancelButtonClass: 'btn btn-default'
                }, function(result) {
                    swal.close();

                    // Validar que se haya ingresado un motivo
                    if (result === false || result === null) return; // canceló
                    if (!result.trim()) {
                        swal('Error', 'Debes ingresar un motivo para devolver la solicitud.', 'error');
                        return;
                    }

                    $.blockUI();

                    Services.Post('/solped/cliente/send-back', {
                            UserToken: User.Token,
                            Entity: JSON.stringify(ko.toJS({
                                IdSolicitud: self.IdSolicitud(),
                                IdUsuario: User.Id
                            })),
                            Reason: result
                        },
                        function(response) {
                            $.unblockUI();
                            if (response.success) {
                                setTimeout(function() {
                                    swal({
                                        title: 'Éxito',
                                        text: 'Solicitud devuelta correctamente',
                                        type: 'success',
                                        closeOnClickOutside: false,
                                        confirmButtonText: 'Aceptar',
                                        confirmButtonClass: 'btn btn-success'
                                    }, function() {
                                        if (response.data && response.data.redirect) {
                                            window.location.href = response.data.redirect;
                                        } else {
                                            location.reload();
                                        }
                                    });
                                }, 500);
                            } else {
                                setTimeout(function() {
                                    swal('Error', response.message, 'error');
                                }, 500);
                            }
                        },
                        function(error) {
                            $.unblockUI();
                            setTimeout(function() {
                                swal('Error', error.message || 'Error al procesar la solicitud', 'error');
                            }, 500);
                        }
                    );
                });
            };

            
        }





        jQuery(document).ready(function () {
            let pathParts = window.location.pathname.split("/");
            let etapa = pathParts[3];  // en-analisis
            let id = pathParts[4];     // 7
            
            console.log("Eaaaaaa:", etapa);
            console.log("ID:", id);
            console.log("URL:", '/solped/cliente/' + etapa + '/' + id + '/detail');

            $.blockUI();
            Services.Get('/solped/cliente/' + etapa + '/' + id + '/detail', {
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