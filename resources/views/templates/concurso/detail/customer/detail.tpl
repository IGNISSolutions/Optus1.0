{extends 'concurso/main.tpl'}

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

{block 'title'}
    {if $tipo neq 'chat-muro-consultas'}
        <span data-bind="text: Steps().find(b => b.current).title"></span>
    {/if}
{/block}

<!-- VISTA -->
{block 'concurso-detail-offerer'}
    <div class="row margin-top-20">
    <div class="col-md-12 text-center">
        <h2 style="font-weight: bold; color: #555;">
        <!-- número en azul -->
        <span data-bind="text: '#' + IdConcurso()" style="color: #32C5D2;"></span>
        <!-- separador y nombre en gris oscuro -->
        <span> – </span>
        <span data-bind="text: Nombre()"></span>
        </h2>
    </div>
    </div>
    
    <div class="row margin-top-40">
        <div class="col-md-12 text-center">
            {if $tipo neq 'chat-muro-consultas'}
                <div class="row">
                    <div class="col-md-12 text-center">
                        <a href="/concursos/cliente" class="btn btn-xl green" title="Volver al listado" data-toggle="modal"
                            href="#large" style="margin-bottom: 30px;">
                            <i class="fa fa-backward"></i> Volver al listado
                        </a>
                    </div>
                </div>
            {/if}


            <!-- STEPS -->
            {include file='concurso/detail/partials/steps.tpl'}
            <!-- HEADER -->
            {include file='concurso/detail/customer/partials/header.tpl'}

            {if $tipo eq 'convocatoria-oferentes'}
                {include file='concurso/detail/customer/convocatoria.tpl'}
                {else if $tipo eq 'chat-muro-consultas'}
                    <chat-component
                        params='IdConcurso: IdConcurso(), IsClient: IsClient(), IsProv: IsProv(), ChatEnable: ChatEnable(), OferentesInvitados: OferentesInvitados(), FechaHoy: FechaHoy(), HoraHoy: HoraHoy(), CierreMuroConsultas: CierreMuroConsultas(), CierreMuroConsultasHora: CierreMuroConsultasHora()'>
                    </chat-component>
            {else if $tipo eq 'analisis-tecnicas'}
                {include file='concurso/detail/customer/tecnica.tpl'}
            {else if $tipo eq 'analisis-ofertas'}
                {include file='concurso/detail/customer/economica.tpl'}
            {else if $tipo eq 'evaluacion-reputacion'}
                {include file='concurso/detail/customer/evaluacion.tpl'}
            {else if $tipo eq 'informes'}
                {include file='concurso/detail/customer/informe.tpl'}
            {/if}
        </div>
    </div>

    <div class="row text-center">
        {if $tipo neq 'chat-muro-consultas'}
            <!-- ko if: UserType() != 'customer-approve' -->
            <!-- ko if: !Adjudicado() && !Eliminado() -->
            <div class="{if $tipo eq 'convocatoria-oferentes'}col-md-6{else}col-md-12{/if}">
                <div class="form-group">
                    <button type="button" class="btn btn-xl red" data-bind="click: CancelConcurso">
                        Cancelar Concurso
                    </button>
                </div>
            </div>
        {/if}

        {if $tipo eq 'convocatoria-oferentes'}
            <div class="col-md-6">
                <div class="form-group">
                    <a type="button" class="btn btn-xl btn-primary" data-toggle="modal" href="#InvitarNuevosOferentes"
                        data-bind="">
                        Invitar nuevo proveedor
                    </a>
                </div>
            </div>
        {/if}
        <!-- /ko -->
        <!-- /ko -->
        {if $tipo neq 'chat-muro-consultas'}
            <div class="col-md-12">
                <a href="/concursos/cliente" class="btn btn-xl green" title="Volver al listado" data-toggle="modal"
                    href="#large">
                    <i class="fa fa-backward"></i>
                    Volver al listado
                </a>
            </div>
        {/if}
    </div>
{/block}

<!-- KNOCKOUT JS -->
{block 'knockout' append}
    <script type="text/javascript">


        var ManualAdjudicationProduct = function(data = null) {
            var self = this;

            this.quantity = ko.observable(data ? data.quantity : null);
            this.cantidadAdj = ko.observable(data ? data.quantity : null);
        };

        var ManualAdjudicationOfferer = function(data = null) {
            var self = this;
            this.price = ko.observable(data ? data.price : null);
            this.priceShow = ko.observable(data ? data.price : null);

            this.quantity = ko.observable(data ? data.quantity : null);
        }

        var ManualAdjudicationItem = function(items, data = null) {
            var self = this;

            this.product_id = ko.observable(null);
            this.product = ko.observable(null);
            this.offerers = ko.observableArray([]);
            this.offerer_id = ko.observable(null);
            this.offerer = ko.observable(null);
            this.quantity = ko.observable(data ? data.quantity : null);
            this.cantidadAdj = ko.observable(data ? data.cantidadAdj : null);
            this.offererTotalQuantity = ko.computed(() => {
                total = 0;
                items()
                    .filter(i => i.product_id() === self.product_id() && i.offerer_id() === self.offerer_id())
                    .forEach(item => {
                        if (item.quantity()) {
                            total = total + parseInt(item.quantity());
                        }
                    });
                return total;
            });
            this.totalQuantity = ko.computed(() => {
                total = 0;
                items()
                    .filter(i => i.product_id() === self.product_id())
                    .forEach(item => {
                        if (item.quantity()) {
                            total = total + parseInt(item.quantity());
                        }
                    });
                return total;
            });
            this.total = ko.computed(() => {
                total = 0.00;
                if (self.offerer()) {
                    items()
                        .filter(i => i.product_id() === self.product_id() && i.offerer_id() === self
                            .offerer_id())
                        .forEach(item => {
                            if (self.quantity()) {
                                total = (self.offerer().price() * parseInt(self.quantity()));
                            }
                            if (self.cantidadAdj()) {
                                total = (self.offerer().price() * parseInt(self.cantidadAdj()));
                            }
                        });
                }
                return total;
            });

            self.quantity.subscribe((value) => {
                if (value) {
                    setTimeout(() => {
                        window.E.ManualAdjudicationCheck();
                    }, 100);
                }
            });

            self.product_id.subscribe((value) => {
                if (value) {
                    $.blockUI();
                    var url = '/concursos/adjudication/products/' + value;
                    var body = {
                        UserToken: User.Token
                    };
                    Services.Get(url, body,
                        (response) => {
                            $.unblockUI();
                            if (response.success) {
                                self.product(new ManualAdjudicationProduct(response.data.result));
                                // Evitamos permitir la repetición Producto - Oferente.
                                self.offerers(response.data.result.offerers);
                                if (data && data.offerer_id) {
                                    self.offerer_id(data.offerer_id);
                                    self.quantity(data.quantity);
                                } else {
                                    self.quantity(null);
                                    self.offerer_id(null);
                                }
                            }
                        },
                        (error) => {
                            $.unblockUI();
                        },
                        null,
                        null
                    );
                } else {
                    self.product(new ManualAdjudicationProduct());
                    self.offerers([]);
                    self.offerer(new ManualAdjudicationOfferer());
                    self.quantity(null);
                }
            });

            self.offerer_id.subscribe((value) => {
                if (value) {
                    $.blockUI();
                    var url = '/concursos/adjudication/products/' + self.product_id() + '/offerers/' + value;
                    var body = {
                        UserToken: User.Token
                    };
                    Services.Get(url, body,
                        (response) => {
                            $.unblockUI();
                            if (response.success) {
                                self.offerer(new ManualAdjudicationOfferer(response.data.result));
                            }
                        },
                        (error) => {
                            $.unblockUI();
                        },
                        null,
                        null
                    );
                } else {
                    self.offerer(new ManualAdjudicationOfferer());
                    self.quantity(null);
                }
            });

            if (data) {
                self.product_id(data.product_id);
            }
        }

        var ManualAdjudication = function(data) {
            var self = this;

            this.items = ko.observableArray([]);
            this.newItem = ko.observable(new ManualAdjudicationItem(self.items));
            this.products = ko.observableArray(data.ManualAdjudicationProductList);

            this.targetCost = ko.computed(() => {
                total = 0.00
                data.Productos.forEach(producto => {
                    total = total + (producto.targetcost * producto.cantidad)
                })
                return total > 0 ? total : 0.00;
            })

            this.total = ko.computed(() => {
                total = 0;
                if (self.items().length > 0) {
                    self.items().forEach(item => {
                        if (item.offerer()) {
                            total = total + (item.offerer().price() * item.quantity());
                        }
                    });
                }
                return total;
            });

            this.AhorroAbsoluto = ko.computed(() => {
                total = 0;
                if (this.targetCost() == 0.00) {
                    total = 0.00
                } else {
                    if (self.items().length > 0) {
                        self.items().forEach(item => {
                            if (item.offerer()) {
                                total = self.targetCost() - self.total();
                            }
                        });
                    }
                }
                return total != 0 ? total : 0.00;
            });

            this.AhorroRelativo = ko.computed(() => {
                total = 0;
                if (this.targetCost() == 0.00) {
                    total = 0.00
                } else {
                    if (self.items().length > 0) {
                        self.items().forEach(item => {
                            if (item.offerer()) {
                                total = ((self.AhorroAbsoluto() * 100) / self.targetCost()).toFixed(2);

                            }
                        });
                    }
                }
                return total != 0.00 ? total : 0.00;
            });

            if (data.ManualAdjudicationItems.length > 0) {
                data.ManualAdjudicationItems.forEach(item => {
                    self.items.push(new ManualAdjudicationItem(self.items, item));
                });
            }
        }

        var ConcursoPorEtapaCliente = function(data) {
        
            var self = this;

            this.goToChatMuroConToken = function () {
                $.blockUI();
                Services.Post('/concursos/guardar-token-acceso', {
                    UserToken: User.Token,
                    id: self.IdConcurso()
                }, 
                (response) => {
                    $.unblockUI();
                    if (response.success) {
                        window.location.href = self.UrlChatMuro();
                    } else {
                        swal('Error', 'No se pudo generar el token de acceso: ' + response.message, 'error');
                    }
                }, 
                (error) => {
                    $.unblockUI();
                    swal('Error', error.message || 'Error generando el token de acceso.', 'error');
                });
            }

            self.goBackWithToken = function () {
                $.blockUI();

                Services.Post('/concursos/guardar-token-acceso', {
                    UserToken: User.Token,
                    id: self.IdConcurso()
                },
                function (response) {
                    $.unblockUI();

                    if (response.success) {
                        // Usamos history.back() después de generar el token
                        window.history.back();
                    } else {
                        swal('Error', response.message, 'error');
                    }
                },
                function (error) {
                    $.unblockUI();
                    swal('Error', error.message || 'Error generando token.', 'error');
                });
            };

            this.IdConcurso = ko.observable(data.list.IdConcurso);
            this.Tipo = ko.observable(data.list.Tipo);
            this.Nombre = ko.observable(data.list.Nombre);
            this.Solicitante = ko.observable(data.list.Solicitante);
            this.Administrador = ko.observable(data.list.Administrador);
            this.Tipologia = ko.observable(data.list.Tipologia);
            this.TipoOperacion = ko.observable(data.list.TipoOperacion);
            this.Portrait = ko.observable(data.list.Portrait);
            this.ImagePath = ko.observable(data.list.ImagePath);
            this.FilePath = ko.observable(data.list.FilePath);
            this.FilePathOferente = ko.observable(data.list.FilePathOferente);
            this.Eliminado = ko.observable(data.list.Eliminado);
            this.UsuarioCancelacion = ko.observable(data.list.UsuarioCancelacion);
            this.FechaCancelacion = ko.observable(data.list.FechaCancelacion);
            this.CierreMuroConsultas = ko.observable(data.list.CierreMuroConsultas);
            this.CierreMuroConsultasHora = ko.observable(data.list.CierreMuroConsultasHora);
            this.IncluyeTecnica = ko.observable(data.list.IncluyeTecnica);
            this.PresentacionTecnicas = ko.observable(data.list.PresentacionTecnicas);
            this.PresentacionTecnicasHora = ko.observable(data.list.PresentacionTecnicasHora);
            this.PresentacionEconomicas = ko.observable(data.list.PresentacionEconomicas);
            this.PresentacionEconomicasHora = ko.observable(data.list.PresentacionEconomicasHora);

            this.IncluyeEconomicaSegundaRonda = ko.observable(data.list.IncluyeEconomicaSegundaRonda);
            this.PresentacionEconomicasSegundaRonda = ko.observable(data.list.PresentacionEconomicasSegundaRonda);
            this.PresentacionEconomicasSegundaRondaHora = ko.observable(data.list.PresentacionEconomicasSegundaRondaHora);

            this.IncluyeEconomicaTerceraRonda = ko.observable(data.list.IncluyeEconomicaTerceraRonda);
            this.PresentacionEconomicasTerceraRonda = ko.observable(data.list.PresentacionEconomicasTerceraRonda);
            this.PresentacionEconomicasTerceraRondaHora = ko.observable(data.list.PresentacionEconomicasTerceraRondaHora);
            this.IncluyeEconomicaCuartaRonda = ko.observable(data.list.IncluyeEconomicaCuartaRonda);
            this.PresentacionEconomicasCuartaRonda = ko.observable(data.list.PresentacionEconomicasCuartaRonda);
            this.PresentacionEconomicasCuartaRondaHora = ko.observable(data.list.PresentacionEconomicasCuartaRondaHora);
            this.IncluyeEconomicaQuintaRonda = ko.observable(data.list.IncluyeEconomicaQuintaRonda);
            this.PresentacionEconomicasQuintaRonda = ko.observable(data.list.PresentacionEconomicasQuintaRonda);
            this.PresentacionEconomicasQuintaRondaHora = ko.observable(data.list.PresentacionEconomicasQuintaRondaHora);

            this.InicioSubasta = ko.observable(data.list.InicioSubasta);
            this.InicioSubastaHora = ko.observable(data.list.InicioSubastaHora);

            this.Breadcrumbs = ko.observableArray(data.breadcrumbs);
            this.Steps = ko.observableArray(data.steps);


            this.IsGo = ko.observable(data.list.IsGo);
            this.IsSobrecerrado = ko.observable(data.list.IsSobrecerrado);
            this.IsOnline = ko.observable(data.list.IsOnline);
            this.IsSubastaciega = ko.observable(data.list.IsSubastaciega);
            this.DisponibleHabilitarSegundaRondaEconomica = ko.observable(data.list.DisponibleHabilitarSegundaRondaEconomica);

            this.isTotalMenor = ko.observable(1000000000);
            this.VehicleEnabled = ko.observable('');
            this.DriverEnabled = ko.observable('');
            this.AuctionEnabledVehicle = ko.observable(false);
            this.AuctionEnabledDriver = ko.observable(false);
            this.OferentesInvitados = ko.observableArray(data.list.OferentesInvitados);
            this.Media = ko.observableArray(data.list.Media);
            this.ConcursoEconomicasPrimeraRonda = ko.observableArray(data.list.ConcursoEconomicasPrimeraRonda);
            this.ConcursoEconomicasSegundaRonda = ko.observableArray(data.list.ConcursoEconomicasSegundaRonda);
            this.Adjudicado = ko.observable(data.list.Adjudicado);
            this.PlazoVencidoEconomica = ko.observable(data.list.PlazoVencidoEconomica);
            this.TodosPresentaronEconomica = ko.observable(data.list.TodosPresentaronEconomica);
            this.IsRevisado = ko.observable(data.list.IsRevisado);
            this.HabilitaSegundaRonda = ko.observable(data.list.HabilitaSegundaRonda)
            this.UserType = ko.observable(data.list.UserType)
            this.Moneda = ko.observable(data.list.Moneda);
            this.ZonaHoraria = ko.observable(data.list.ZonaHoraria);
            this.UrlChatMuro = ko.observable(data.list.urlChatMuro);
            this.HasNewMessage = ko.observable(false);
            this.ChatEnable = ko.observable(data.list.ChatEnable);
            this.concurso_fiscalizado = ko.observable(data.list.concurso_fiscalizado);
            this.emailSuper = ko.observable(data.list.emailSuper);
            this.ShowChatButton = ko.observable(self.IsSobrecerrado() ? true : data.list.ChatEnable);
            this.IsClient = ko.observable(true);
            this.IsProv = ko.observable(false);

            var ahora = new Date();
                // Formatear HoraHoy en formato hh:mm:ss
                this.HoraHoy = ko.observable(
                    ahora.getHours().toString().padStart(2, '0') + ':' +
                    ahora.getMinutes().toString().padStart(2, '0') + ':' +
                    ahora.getSeconds().toString().padStart(2, '0')
                );

                // Formatear FechaHoy en formato dd-mm-yyyy
                this.FechaHoy = ko.observable(
                    ahora.getDate().toString().padStart(2, '0') + '-' +  // dd
                    (ahora.getMonth() + 1).toString().padStart(2, '0') + '-' +  // mm
                    ahora.getFullYear());

            if (params[3] === 'convocatoria-oferentes') {
                this.Media = ko.observableArray(data.list.Media);
                this.AceptacionInvitacion = ko.observable(data.list.AceptacionInvitacion);
                this.OferenteAInvitar = ko.observable(data.list.OferenteAInvitar);
                this.OferentesAInvitar = ko.observable(data.list.OferentesAInvitar);

            } else if (params[3] === 'analisis-tecnicas') {
                this.TechnicalEvaluations = ko.observableArray(data.list.TechnicalEvaluations);
                this.TechnicalProposals = ko.observableArray(data.list.TechnicalProposals);
            } else if (params[3] === 'analisis-ofertas') {
                this.configDataTables = {
                    layout: {
                        topStart: {
                            buttons: [{
                                extend: 'excelHtml5',
                                className: 'btn btn-primary',
                                text: 'Exportar a Excel',
                                exportOptions: {
                                    format: {
                                        body: function (data, row, column, node) {
                                            // Si estamos en la columna 0 o 1, devolvemos el dato tal cual
                                            if (column === 0 || column === 1 || column === 2) {
                                                return data;
                                            }

                                            // Si no, aplicamos la conversión
                                            if (typeof data === 'string') {
                                                data = data.replace(/\./g, ''); // Sacar puntos de miles
                                                data = data.replace(/,/g, '.'); // Cambiar coma decimal a punto decimal
                                            }
                                            return data;
                                        }
                                    }
                                }
                            }]
                        },
                    },
                    deferRender: true,
                    paging: false,
                    searching: false,
                    fixedColumns: {
                        start: 3
                    },
                    scrollY: 500,
                    scrollCollapse: true,
                    autoWidth: true,
                }
                this.Countdown = ko.observable('');
                this.CountdownSeconds = ko.observable(data.list.Countdown);
                this.Timeleft = ko.observable('');
                this.TimeleftSeconds = ko.observable(data.list.Timeleft);
                this.UnidadMinima = ko.observable(data.list.UnidadMinima);

                this.SoloOfertasMejores = ko.observable(data.list.SoloOfertasMejores);
                this.PrecioMaximo = ko.observable(data.list.PrecioMaximo);
                this.PrecioMinimo = ko.observable(data.list.PrecioMinimo);
                this.Chat = ko.observable(data.list.Chat);
                this.Duracion = ko.observable(data.list.Duracion);
                this.TiempoAdicional = ko.observable(data.list.TiempoAdicional),
                this.ItemsMejores = ko.observable(data.list.ItemsMejores);
                this.Log = ko.observable(data.list.Log);
                this.CantidadOferentes = ko.observable(data.list.CantidadOferentes);
                this.Conectados = ko.observable('0');
                this.VerNumOferentesParticipan = ko.observable(data.list.VerNumOferentesParticipan);
                this.Oferentes = ko.observableArray(data.list.Oferentes);
                this.OferenteModalDetail = ko.observable(null);
                this.AdjudicacionAnticipada = ko.observable(data.list.AdjudicacionAnticipada);
                this.AlgunoPresentoEconomica = ko.observable(data.list.AlgunoPresentoEconomica);
                this.TipoAdjudicacion = ko.observable(data.list.TipoAdjudicacion);
                this.ExistenOfertas = ko.observable(data.list.ExistenOfertas);
                this.AdjudicacionComentario = ko.observable(null);
                this.IndividualAdjudication = ko.observable(null);
                this.ManualAdjudication = ko.observable(null);
                this.OferentesPrimeraRonda = ko.observable(data.list.OferentesPrimeraRonda);
                this.OferentesSegundaRonda = ko.observable([]);
                this.RondasOfertas = ko.observableArray(data.list.RondasOfertas);
                this.RondaActual = ko.observable(data.list.rondaActual);
                this.Ronda = ko.observable('Ronda ' + data.list.rondaTitle + ' de ' + data.list.maxRonda);
                this.NuevaRonda = ko.observable(data.list.nuevaRonda);
                this.ProveedoresRondaActual = ko.observableArray(self.RondasOfertas()[self.RondaActual()][
                    'ConcursoEconomicas'
                ]['proveedores']);
                this.IntegralAdjudication = ko.observable(self.RondasOfertas()[self.RondaActual()]['ConcursoEconomicas']
                    ['mejoresOfertas']['mejorIntegral']['idOferente']);
                this.IndividualAdjudication = ko.observable(self.RondasOfertas()[self.RondaActual()][
                    'ConcursoEconomicas'
                ]['mejoresOfertas']['mejorIndividual'][
                    'idOferentes'
                ]);
                this.verOfertasEnable = ko.observable(data.list.verOfertasEnable);
                this.EjecutarNuevaRonda = ko.observable(data.list.EjecutarNuevaRonda);
                this.Evaluaciones = ko.observableArray(data.list.Evaluaciones);
                this.TitleNewRound = ko.observable("Lanzar " + self.NuevaRonda());
                this.Proveedores = ko.observableArray(data.list.Proveedores);



                /// DATA AND DATE VALIDATION FOR NEW ROUND - INPUT COMES FROM VIEW ///
                this.FechaNewRound = ko.observable('').extend({ required: true });
                this.FechaMaximaCierreDeConsulta = ko.observable('').extend({ required: true });
                this.ComentarioNuevaRonda = ko.observable('').extend({ required: true });
                this.ThreeDaysFromTodayDate = ko.observable(new Date(Date.now() + 72 * 60 * 60 * 1000));
                this.TodayDate = ko.observable(new Date()); //Current date-time

                this.FechaMaximaCierreDeConsulta.subscribe(function(newValue) {
                    if (newValue) {
                        // Wait a moment for DOM to be ready
                        setTimeout(function() {
                            var $input = $('.form_datetime input[data-bind*="NuevaFechaCierreMuroConsulta"]');
                            $input.datetimepicker('setEndDate', newValue);
                        }, 100);
                    }
                });

                this.NuevaFechaCierreMuroConsulta = ko.observable('').extend({
                    required: true,
                    validation: {
                        validator: function (val) {
                            if (!val || !self.FechaNewRound()) return false;
                            var cierre = new Date(val);
                            var ronda = new Date(self.FechaNewRound());
                            var diffHours = (ronda - cierre) / (1000 * 60 * 60);
                            return diffHours >= 24;
                        },
                        message: "Debe ser al menos 24 horas antes de la fecha de la nueva ronda"
                    }
                });

                this.FechaNewRound.subscribe(function (newDateValue) {
                    if (newDateValue) {
                        // Clear the second date when first date changes
                        self.NuevaFechaCierreMuroConsulta('');
                        
                        var originalDate = moment(newDateValue, 'DD-MM-YYYY HH:mm').toDate();
                        var adjustedDate = new Date(originalDate.getTime() - 24 * 60 * 60 * 1000);
                        
                        if (!isNaN(adjustedDate.getTime())) {
                            self.FechaMaximaCierreDeConsulta(adjustedDate);
                        } else {
                            console.warn("Adjusted date is invalid:", adjustedDate);
                            self.FechaMaximaCierreDeConsulta('');
                        }
                    } else {
                        self.FechaMaximaCierreDeConsulta('');
                    }
                });

                ///


                self.ManualAdjudication(new ManualAdjudication(data.list));


                // SUBASTA
                if (params[2] === 'online') {
                    // Countdown
                    setInterval(function() {
                        if (self.CountdownSeconds() == 0) {
                            location.reload(1);
                        }
                        if (self.CountdownSeconds()) {
                            var seconds = self.CountdownSeconds();
                            var newTime = new Date();
                            newTime.setHours(0);
                            newTime.setMinutes(0);
                            newTime.setSeconds(seconds - 1);
                            self.Countdown(
                                newTime.getHours() + 'h ' +
                                newTime.getMinutes() + 'm ' +
                                newTime.getSeconds() + 's'
                            );
                            self.CountdownSeconds(seconds - 1);
                        }
                    }, 1000);

                    // Timeleft
                    setInterval(function() {
                        if (self.TimeleftSeconds() == 0) {
                            location.reload(1);
                        }
                        if (self.TimeleftSeconds()) {
                            var seconds = self.TimeleftSeconds();
                            var newTime = new Date();
                            newTime.setHours(0);
                            newTime.setMinutes(0);
                            newTime.setSeconds(seconds - 1);
                            self.Timeleft(
                                Math.floor(seconds / (3600 * 24)) + 'd ' +
                                newTime.getHours() + 'h ' +
                                newTime.getMinutes() + 'm ' +
                                newTime.getSeconds() + 's'
                            );
                            self.TimeleftSeconds(seconds - 1);
                        }
                    }, 1000);

                    // Conectar a la subasta online si esta ha iniciado.
                    if (self.CountdownSeconds()) {
                        var query = '?id_concurso=' + params[4] + '&id_cliente=' + User.Id;

                        var path = 'wss://' + location.host + '/wss/';

                        var subastaConn = new WebSocket(path + query);

                        subastaConn.onopen = function(e) {
                            swal('¡Has ingresado a la Subasta!');
                        };
                        subastaConn.onclose = function(e) {
                            swal({
                                title: 'Se ha cerrado la conexion con la Subasta.',
                                text: 'Por favor intente nuevamente en unos instantes.',
                                confirmButtonText: 'Aceptar',
                            }, function(response) {
                                window.location.href = '/concursos/cliente';
                            });
                        };
                        subastaConn.onerror = function(e) {
                            swal({
                                title: 'No se ha podido conectar con la Subasta.',
                                text: 'Por favor intente nuevamente en unos instantes.',
                                confirmButtonText: 'Aceptar',
                            }, function(response) {
                                window.location.href = '/concursos/cliente';
                            });
                        };
                        subastaConn.onmessage = function(e) {
                            var result = JSON.parse(e.data);

                            if (result.ItemsMejores) {
                                self.ItemsMejores(result.ItemsMejores);
                                self.Log(result.Log);
                            } else if (result.conectados) {
                                self.Conectados(result.conectados);
                            }

                            if (result.TiempoAdicional) {
                                self.CountdownSeconds(parseInt(self.CountdownSeconds()) + (parseInt(result
                                    .TiempoAdicional)));
                                self.Duracion(result.Duracion);
                            }

                            if (result.Mensajes) {
                                for (var mensaje of result.Mensajes) {
                                    self.showToastr(mensaje, 'warning');
                                }
                            }
                        };
                    }
                }
            } else if (params[3] === 'evaluacion-reputacion') {
                this.titleEvaluaciones = ko.observable(data.list.titleEvaluaciones);
                this.titleResultados = ko.observable(data.list.titleResultados);
                this.RondasOfertas = ko.observableArray(data.list.RondasOfertas);
                this.Ronda = ko.observable('Ronda ' + data.list.rondaTitle + ' de ' + data.list.maxRonda);
                this.Evaluaciones = ko.observableArray(data.list.Evaluaciones);
                this.TipoAdjudicacion = ko.observable(data.list.TipoAdjudicacion);
                this.AdjudicacionComentario = ko.observable(data.list.AdjudicacionComentario);
                this.AdjudicacionItems = ko.observableArray(data.list.AdjudicacionItems);
            }

            this.showToastr = function(message, type, duration) {
                toastr.options = {
                    'closeButton': true,
                    'debug': false,
                    'positionClass': 'toast-top-right',
                    'onclick': null,
                    'showDuration': '1000',
                    'hideDuration': '1000',
                    'timeOut': duration,
                    'extendedTimeOut': '1000',
                    'showEasing': 'swing',
                    'hideEasing': 'linear',
                    'showMethod': 'fadeIn',
                    'hideMethod': 'fadeOut'
                }
                switch (type) {
                    case 'warning':
                        toastr.warning(message);
                        break;
                    case 'info':
                        toastr.info(message);
                        break;
                    case 'error':
                        toastr.error(message);
                        break;
                    case 'success':
                        toastr.success(message);
                        break;
                    default:
                        toastr.success(message);
                        break;
                }
            }

            this.unblockUI = function() {
                $.unblockUI();
            }

            this.sendInvitation = function(idOfferer, isReminder = false, isNew = false) {
                if (isReminder && !isNew) {
                    var title = '¿Desea enviar el recordatorio?';
                    var url = '/concursos/invitations/reminder';
                } else {
                    var title = '¿Desea enviar la invitación?';
                    var url = '/concursos/invitations/send';
                }

                if (isNew) {
                    var title = '¿Desea enviar la invitación?';
                    var url = '/concursos/invitations/sendNew';
                }
                swal({
                    title: title,
                    text: 'Esta a punto de enviar una notificación al usuario para este concurso.',
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
                        Services.Post(url, {
                                UserToken: User.Token,
                                idOfferer: idOfferer,
                                IdConcurso: self.IdConcurso()
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
                                    swal('Error', error.message, 'error');
                                }, 500);
                            },
                            null,
                            null
                        );
                    }
                });
            }

            this.CancelConcurso = function() {
                swal({
                    title: 'Cancelación de Concurso',
                    text: '¿Por qué deseas cancelar el concurso?',
                    type: 'input',
                    inputPlaceholder: 'Escribe un motivo (obligatorio)',
                    closeOnClickOutside: false,
                    showCancelButton: true,
                    closeOnConfirm: false,
                    closeOnCancel: false,
                    confirmButtonText: 'Aceptar',
                    confirmButtonClass: 'btn btn-success',
                    cancelButtonText: 'Cancelar',
                    cancelButtonClass: 'btn btn-default'
                }, function(result) {
                    swal.close();
                    if (result !== false) {
                        $.blockUI();
                        var url = '/concursos/delete/' + self.IdConcurso();
                        Services.Post(url, {
                                UserToken: User.Token,
                                Reason: result
                            },
                            (response) => {
                                swal.close();
                                $.unblockUI();
                                if (response.success) {
                                    setTimeout(function() {
                                        swal({
                                            title: 'Hecho',
                                            text: response.message,
                                            type: 'success',
                                            closeOnClickOutside: false,
                                            closeOnConfirm: true,
                                            confirmButtonText: 'Aceptar',
                                            confirmButtonClass: 'btn btn-success'
                                        }, function(result) {
                                            if (response.data.redirect) {
                                                window.location.href = response.data
                                                    .redirect;
                                            } else {
                                                location.reload();
                                            }
                                        });
                                    }, 500);
                                } else {
                                    setTimeout(function() {
                                        swal('Error',
                                            'Han ocurrido errores al enviar los correos.',
                                            'error');
                                    }, 500);
                                }
                            },
                            (error) => {
                                swal.close();
                                $.unblockUI();
                                setTimeout(function() {
                                    swal('Error', error.message, 'error');
                                }, 500);
                            },
                            null,
                            null
                        );
                    }
                });
            }

            this.VerOfertas = function () {
                if (self.concurso_fiscalizado() == 'si') {
                    $.blockUI();
                    var url = '/concursos/setToken/' + self.IdConcurso();
                    Services.Post(url, { UserToken: User.Token },
                        (response) => {
                            $.unblockUI();
                            if (response.success) {
                                swal({
                                    title: "Concurso Fiscalizado",
                                    text: "Se ha enviado un e-mail a " + self.emailSuper() + ", con el código alfanumérico al fiscalizador del concurso. Póngase en contacto e introduzca el código para poder ver las ofertas de los proveedores. Recuerde: al abrir las ofertas, los proveedores no podrán editarlas.",
                                    type: "input",
                                    showCancelButton: true,
                                    closeOnConfirm: false,
                                    inputPlaceholder: "Introduzca el código"
                                }, function (inputValue) {
                                    if (inputValue === false) return false;
                                    if (inputValue === "") {
                                        swal.showInputError("Ingrese el token por favor");
                                        return false;
                                    }

                                    $.blockUI();
                                    var url = '/concursos/verOfertas/' + self.IdConcurso();
                                    Services.Post(url, {
                                        UserToken: User.Token,
                                        Token: inputValue
                                    },
                                        (response) => {
                                            $.unblockUI();
                                            swal.close();

                                            if (response.success) {
                                                // GUARDAR TOKEN DE ACCESO
                                                Services.Post('/concursos/oferente/guardar-token-acceso', {
                                                    UserToken: User.Token,
                                                    id: self.IdConcurso()
                                                }, (resToken) => {
                                                    if (resToken.success) {
                                                        setTimeout(function () {
                                                            swal({
                                                                title: 'Hecho',
                                                                text: response.message,
                                                                type: 'success',
                                                                closeOnClickOutside: false,
                                                                closeOnConfirm: true,
                                                                confirmButtonText: 'Aceptar',
                                                                confirmButtonClass: 'btn btn-success'
                                                            }, function (result) {
                                                                if (response.data.redirect) {
                                                                    window.location.href = response.data.redirect;
                                                                } else {
                                                                    location.reload();
                                                                }
                                                            });
                                                        }, 500);
                                                    } else {
                                                        swal('Error', 'No se pudo generar token de acceso.', 'error');
                                                    }
                                                }, (errToken) => {
                                                    swal('Error', 'Error generando token: ' + errToken.message, 'error');
                                                });

                                            } else {
                                                setTimeout(function () {
                                                    swal('Error', 'Token inválido', 'error');
                                                }, 500);
                                            }
                                        },
                                        (error) => {
                                            $.unblockUI();
                                            setTimeout(function () {
                                                swal('Error', error.message, 'error');
                                            }, 500);
                                        },
                                        null,
                                        null
                                    );
                                });
                            } else {
                                swal('Error', response.message, 'error');
                            }
                        },
                        (error) => {
                            $.unblockUI();
                            swal('Error', error.message, 'error');
                        },
                        null,
                        null
                    );
                } else {
                    // NO FISCALIZADO
                    swal({
                        title: 'Ver ofertas',
                        text: 'Al abrir las ofertas, los proveedores no podrán editarlas. ¿Está seguro?',
                        closeOnClickOutside: false,
                        showCancelButton: true,
                        closeOnConfirm: false,
                        closeOnCancel: false,
                        confirmButtonText: 'Aceptar',
                        confirmButtonClass: 'btn btn-success',
                        cancelButtonText: 'Cancelar',
                        cancelButtonClass: 'btn btn-default'
                    }, function (result) {
                        swal.close();
                        if (result !== false) {
                            $.blockUI();
                            var url = '/concursos/verOfertas/' + self.IdConcurso();
                            Services.Post(url, {
                                UserToken: User.Token
                            },
                                (response) => {
                                    $.unblockUI();
                                    swal.close();

                                    if (response.success) {
                                        //  GUARDAR TOKEN DE ACCESO
                                        Services.Post('/concursos/oferente/guardar-token-acceso', {
                                            UserToken: User.Token,
                                            id: self.IdConcurso()
                                        }, (resToken) => {
                                            if (resToken.success) {
                                                setTimeout(function () {
                                                    swal({
                                                        title: 'Hecho',
                                                        text: response.message,
                                                        type: 'success',
                                                        closeOnClickOutside: false,
                                                        closeOnConfirm: true,
                                                        confirmButtonText: 'Aceptar',
                                                        confirmButtonClass: 'btn btn-success'
                                                    }, function (result) {
                                                        if (response.data.redirect) {
                                                            window.location.href = response.data.redirect;
                                                        } else {
                                                            location.reload();
                                                        }
                                                    });
                                                }, 500);
                                            } else {
                                                swal('Error', 'No se pudo generar token de acceso.', 'error');
                                            }
                                        }, (errToken) => {
                                            swal('Error', 'Error generando token: ' + errToken.message, 'error');
                                        });

                                    } else {
                                        setTimeout(function () {
                                            swal('Error', 'Han ocurrido errores al enviar los correos.', 'error');
                                        }, 500);
                                    }
                                },
                                (error) => {
                                    $.unblockUI();
                                    setTimeout(function () {
                                        swal('Error', error.message, 'error');
                                    }, 500);
                                },
                                null,
                                null
                            );
                        }
                    });
                }
            }


            this.ModificarFechasSobres = function () {
                let tipoLicitacion = '';
                if (self.IsSobrecerrado()) {
                    tipoLicitacion = 'sobrecerrado';
                } else if (self.IsGo()) {
                    tipoLicitacion = 'go';
                } else if (self.IsOnline()) {
                    tipoLicitacion = 'online';
                }

                $.blockUI();
                Services.Post('/concursos/guardar-token-acceso', {
                    UserToken: User.Token,
                    id: self.IdConcurso()
                },
                (response) => {
                    $.unblockUI();
                    if (response.success) {
                        const url = '/concursos/' + tipoLicitacion + '/edicion/' + self.IdConcurso();
                        window.location.href = url;
                    } else {
                        swal('Error', response.message, 'error');
                    }
                },
                (error) => {
                    $.unblockUI();
                    swal('Error', error.message, 'error');
                });
            }

            this.CalificacionOferentes = ko.observableArray();
            this.CalcularTecnica = function(UserId) {
                var valor = 0;
                var values = "";
                var valores = "";
                var obj = {};

                var puntuacion = 0;
                $('.puntuacion_' + UserId).each(function() {
                    valor = parseInt($(this).val()) >= 1 ? parseInt($(this).val()) : false;
                    values += valor + ",";
                    if (valor) {
                        puntuacion += ((valor * parseInt($(this).attr('data'))) / 100) + 0.0001;
                        $("#puntos_" + UserId).html(puntuacion.toFixed(2));
                    }
                });
                valores = values.slice(0, -1);
                var puntajeMinimo = parseInt($(".minimo").val());
                if (self.CalificacionOferentes().length === 0) {
                    obj = {
                        UserId: UserId,
                        valores,
                        minimo: puntajeMinimo,
                        alcanzado: puntuacion
                    };
                    self.CalificacionOferentes.push(obj);
                } else {
                    self.CalificacionOferentes()[0].UserId = UserId;
                    self.CalificacionOferentes()[0].valores = valores;
                    self.CalificacionOferentes()[0].minimo = puntajeMinimo;
                    self.CalificacionOferentes()[0].alcanzado = puntuacion;
                }
                //
                if (puntuacion >= puntajeMinimo) {
                    $("#puntosT_" + UserId + ",#puntos_" + UserId).css("color", "green");
                    $("#puntosT_" + UserId).html("APROBADO");
                } else {
                    $("#puntosT_" + UserId + ",#puntos_" + UserId).css("color", "red");
                    $("#puntosT_" + UserId).html("REPROBADO");
                }
            }

            this.sendTechnicalEvaluation = function() {
                $.blockUI();
                Services.Post('/concursos/oferente/guardar-token-acceso', {
                    UserToken: User.Token,
                    id: self.IdConcurso()
                }, function(resp) {
                    $.unblockUI();
                    if (resp.success) {
                        const url = '/concursos/proposal/technical/acceptorreject';
                        const htmlBody =
                            '<p>Comentario</p> <textarea rows="3" cols="50" class="form-control" style="resize: none;" id="commentTechEvaluation"></textarea>';
                        swal({
                            title: '¿Desea enviar la evaluación al oferente?',
                            type: 'info',
                            ...(self.CalificacionOferentes()[0].alcanzado <= self.CalificacionOferentes()[0].minimo && {
                                text: htmlBody,
                                type: "input",
                                html: true,
                                inputPlaceholder: 'hidden',
                            }),
                            showCancelButton: true,
                            closeOnConfirm: false,
                        }, function(inputValue) {
                            if (inputValue === false) return false;
                            if (inputValue === "") {
                                swal.showInputError("Debe agregar un comentario");
                                return false;
                            }
                            swal.close();
                            $.blockUI();
                            const data = {
                                IdConcurso: self.IdConcurso(),
                                Calificacion: self.CalificacionOferentes,
                                ...(self.CalificacionOferentes()[0].alcanzado <= self.CalificacionOferentes()[0].minimo && {
                                    comentario: inputValue
                                }),
                            };
                            Services.Post(url, {
                                    UserToken: User.Token,
                                    Data: JSON.stringify(ko.toJS(data))
                                }, (response) => {
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
                                }, (error) => {
                                    $.unblockUI();
                                    setTimeout(function() {
                                        swal('Error', error.message, 'error');
                                    }, 500);
                                });
                        });
                    } else {
                        swal('Error', resp.message, 'error');
                    }
                }, function(error) {
                    $.unblockUI();
                    swal('Error', error.message, 'error');
                });
            };

            this.AdjudicationItemAddOrDelete = function(action, adjudication_item = null) {
                // Fetch table
                var newItems = [];
                self.ManualAdjudication().items().forEach(item => {
                    newItems.push(item);
                });

                switch (action) {
                    case 'add':
                        // Add new Product
                        var newItem = self.ManualAdjudication().newItem();
                        newItems.push(newItem);

                        // Reset inputs
                        self.ManualAdjudication().newItem(new ManualAdjudicationItem(self.ManualAdjudication()
                            .items));
                        // Update table
                        self.ManualAdjudication().items.removeAll();
                        self.ManualAdjudication().items(newItems);
                        break;
                    case 'delete':
                        // Delete from table
                        const index = newItems.findIndex(i => i === adjudication_item);
                        newItems.splice(index, 1);
                        // Update table
                        self.ManualAdjudication().items.removeAll();
                        self.ManualAdjudication().items(newItems);
                        break;
                }
            };

            this.ManualAdjudicationCheck = function() {
                if (self.Adjudicado()) {
                    return;
                }
                $.blockUI();
                var url = '/concursos/adjudication/products/check';
                var body = {
                    UserToken: User.Token,
                    Data: JSON.stringify(ko.toJS(self.ManualAdjudication().items))
                };
                Services.Post(url, body,
                    (response) => {
                        $.unblockUI();
                        if (!response.success) {
                            swal('Error', response.message, 'error');
                        }
                    },
                    (error) => {
                        $.unblockUI();
                    },
                    null,
                    null
                );
            }

            this.ShowModalNewRound = function() {
                $('#newRound').modal('show')
            }

            this.NewTechRound = function(prov_id, proposal_id) {
                $.blockUI();
                Services.Post('/concursos/oferente/guardar-token-acceso', {
                    UserToken: User.Token,
                    id: self.IdConcurso()
                }, function(resp) {
                    $.unblockUI();
                    if (resp.success) {
                        const proveedor = self.TechnicalEvaluations().find(function(proveedor) {
                            return proveedor.OferenteId == prov_id;
                        });
                        const proposal = proveedor.rondasTecnicas.find(function(proposal) {
                            return proposal.proposal == proposal_id;
                        });
                        const urlEdicion = '/concursos/' + self.Tipo() + '/edicion/' + self.IdConcurso();

                        if (proposal.tecnica_vencida) {
                            swal({
                                title: 'Etapa Técnica Vencida',
                                text: 'La fecha de la etapa técnica del presente concurso ha llegado a su fin, si desea solicitar una nueva ronda de evaluación debe actualizar la fecha editando el concurso, ¿Desea ir a la edición del concurso?',
                                closeOnClickOutside: false,
                                showCancelButton: true,
                                closeOnConfirm: false,
                                closeOnCancel: false,
                                confirmButtonText: 'Aceptar',
                                confirmButtonClass: 'btn btn-success',
                                cancelButtonText: 'Cancelar',
                                cancelButtonClass: 'btn btn-default'
                            }, function(result) {
                                swal.close();
                                window.location.href = urlEdicion;
                            });
                        } else {
                            const title = proposal.evaluation.newRound;
                            let htmlBody =
                                '<div class="container><div class="row"><div class="col-sm-12"><p>Añada un comentario para la nueva ronda técnica</p> <textarea rows="3" cols="50" class="form-control" style="resize: none;" id="commentNewTechRound"></textarea></div></div>';
                            htmlBody +=
                                '<div class="row"><div class="col-sm-12"><div class="alert alert-danger text-center">La etapa técnica vence el ' +
                                self.PresentacionTecnicas() + ' a las ' + self.PresentacionTecnicasHora() +
                                ' hs. Extienda el plazo de ser necesario editando la licitación.</div></div></div></div>';
                            const url = '/concursos/proposal/technical/newround';

                            swal({
                                title: title,
                                text: htmlBody,
                                type: "input",
                                html: true,
                                showCancelButton: true,
                                closeOnConfirm: false,
                                inputPlaceholder: 'hidden',
                            }, function(inputValue) {
                                if (inputValue === false) return false;
                                if (inputValue === "") {
                                    swal.showInputError("Por favor añada un comentario");
                                    return false;
                                }
                                swal.close();
                                $.blockUI();
                                const data = {
                                    IdConcurso: self.IdConcurso(),
                                    proveedor: prov_id,
                                    proposal: proposal_id,
                                    reason: inputValue
                                };
                                Services.Post(url, {
                                        UserToken: User.Token,
                                        Data: JSON.stringify(ko.toJS(data))
                                    },
                                    (response) => {
                                        swal.close();
                                        $.unblockUI();
                                        if (response.success) {
                                            setTimeout(() => {
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
                                            }, 500);
                                        } else {
                                            swal({
                                                title: "Error",
                                                text: response.message,
                                                type: "error",
                                                timer: 4000
                                            });
                                        }
                                    },
                                    (error) => {
                                        swal.close();
                                        $.unblockUI();
                                        swal({
                                            title: "Error",
                                            text: error.message,
                                            type: "error",
                                            timer: 4000
                                        });
                                    });
                            });
                        }
                    } else {
                        swal('Error', resp.message, 'error');
                    }
                }, function(error) {
                    $.unblockUI();
                    swal('Error', error.message, 'error');
                });
            };


            ///This funtion bellow sould end on Customer/ConcursoController => "sendSecondRound()"
            /// ur wellcome ;)
            this.SendNewRound = function() {
                var data = {
                    ConcursoId: self.IdConcurso(),
                    NewRoundDate: self.FechaNewRound(),
                    NuevaFechaCierreMuroConsulta: self.NuevaFechaCierreMuroConsulta(),
                    NuevaFechaLimitePropuestasEconomicas: self.FechaNewRound(),
                    CommentNewRound: self.ComentarioNuevaRonda(),
                };

                swal({
                    title: '¿Confirma la nueva ronda de ofertas?',
                    text: 'Una vez procesada la nueva ronda los proveedores deberan ofertar de nuevo',
                    type: 'success',
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
                        Services.Post('/concursos/cliente/SecondRound/send', {
                                UserToken: User.Token,
                                Data: JSON.stringify(ko.toJS(data))
                            },
                            (response) => {
                                $.unblockUI();
                                if (response.success) {
                                    setTimeout(() => {
                                        swal({
                                            title: 'Hecho',
                                            text: response.message,
                                            type: 'success',
                                            closeOnClickOutside: false,
                                            closeOnConfirm: true,
                                            confirmButtonText: 'Aceptar',
                                            confirmButtonClass: 'btn btn-success'
                                        }, function(result) {
                                            swal.close();
                                            if (response.data.redirect) {
                                                window.location.href = response.data
                                                    .redirect;
                                            } else {
                                                location.reload();
                                            }
                                        });
                                    }, 500);
                                } else {
                                    setTimeout(() => {
                                        swal('Error', response.message, 'error');
                                    }, 1000);
                                }
                            },
                            (error) => {
                                $.unblockUI();
                                setTimeout(() => {
                                    swal('Error', error.message, 'error');
                                }, 500);
                            },
                            null,
                            null
                        );
                    }
                });
            }

            this.AdjudicationSend = function(type, values) {

                var data = {
                    Comment: self.AdjudicacionComentario()
                };

                switch (type) {
                    case 'manual':
                        Object.assign(data, {
                            Type: 'manual',
                            IdConcurso: self.IdConcurso(),
                            Data: self.ManualAdjudication()
                        });
                        break;
                    case 'individual':
                        Object.assign(data, {
                            Type: 'individual',
                            IdConcurso: self.IdConcurso(),
                            Data: self.IndividualAdjudication()
                        });
                        break;
                    case 'integral':
                        Object.assign(data, {
                            Type: 'integral',
                            IdConcurso: self.IdConcurso(),
                            Data: self.IntegralAdjudication()
                        });
                        break;
                }

                swal({
                    title: '¿Confirma Ajudicación?',
                    text: 'Una vez procesada la adjudicación, esta no podrá ser modificada.',
                    type: 'success',
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
                        Services.Post('/concursos/adjudication/send', {
                                UserToken: User.Token,
                                Data: JSON.stringify(ko.toJS(data))
                            },
                            (response) => {
                                $.unblockUI();
                                if (response.success) {
                                    setTimeout(() => {
                                        swal({
                                            title: 'Hecho',
                                            text: response.message,
                                            type: 'success',
                                            closeOnClickOutside: false,
                                            closeOnConfirm: true,
                                            confirmButtonText: 'Aceptar',
                                            confirmButtonClass: 'btn btn-success'
                                        }, function(result) {
                                            swal.close();
                                            if (response.data.redirect) {
                                                window.location.href = response.data
                                                    .redirect;
                                            } else {
                                                location.reload();
                                            }
                                        });
                                    }, 500);
                                } else {
                                    setTimeout(() => {
                                        swal('Error', response.message, 'error');
                                    }, 500);
                                }
                            },
                            (error) => {
                                $.unblockUI();
                                setTimeout(() => {
                                    swal('Error', error.message, 'error');
                                }, 500);
                            },
                            null,
                            null
                        );
                    }
                });
            }

            this.EvaluationSend = function() {
            var valores = self.Evaluaciones;

            // 1. PRIMERO generás el token de acceso
            $.blockUI();
            Services.Post('/concursos/oferente/guardar-token-acceso', {
                        UserToken: User.Token,
                        id: self.IdConcurso()
                    },
            (resp) => {
                $.unblockUI();
                // Si el token se genera correctamente, sigue el flujo habitual
                if (resp.success) {
                    // 2. Pide confirmación al usuario
                    swal({
                        title: '¿Desea enviar la evaluación?',
                        type: 'info',
                        closeOnClickOutside: false,
                        showCancelButton: true,
                        closeOnConfirm: false,
                        confirmButtonText: 'Aceptar',
                        confirmButtonClass: 'btn btn-success',
                        cancelButtonText: 'Cancelar',
                        cancelButtonClass: 'btn btn-default',
                        buttonsStyling: false
                    }, function(result) {
                        swal.close();
                        if (result) {
                            $.blockUI();
                            var url = '/concursos/evaluations/save';
                            Services.Post(url, {
                                    UserToken: User.Token,
                                    Entity: {
                                        Id: self.IdConcurso(), // o params[4] si preferís
                                    },
                                    Evaluacion: valores
                                },
                                (response) => {
                                    $.unblockUI();
                                    setTimeout(function() {
                                        swal({
                                            title: response.message,
                                            type: 'success',
                                            closeOnClickOutside: false,
                                            showCancelButton: false,
                                            closeOnConfirm: true,
                                            confirmButtonText: 'Aceptar',
                                            confirmButtonClass: 'btn btn-success',
                                            buttonsStyling: false
                                        }, function(result) {
                                            if (response.success) {
                                                if (response.data.redirect) {
                                                    window.location.href = response.data.redirect;
                                                } else {
                                                    window.location.reload();
                                                }
                                            }
                                        });
                                    }, 500);
                                },
                                (error) => {
                                    $.unblockUI();
                                    setTimeout(function() {
                                        swal('Error', error.message, 'error');
                                    }, 500);
                                },
                                null,
                                null
                            );
                        }
                    });
                } else {
                    swal('Error', resp.message, 'error');
                }
            },
            (error) => {
                $.unblockUI();
                swal('Error', error.message, 'error');
            },
            null, null);
        };


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
                            window.open(response.data.public_path);
                        } else {
                            swal('Error', response.message, 'error');
                        }
                    },
                    (error) => {
                        $.unblockUI();
                        swal('Error', error.message, 'error');
                    },
                    null,
                    null
                );
            }

            this.openDocumentsDetail = function(oferente) {
                self.OferenteModalDetail(oferente);
                $('#documentsDetailModal').modal('show');
            }

            this.closeDocumentsDetail = function() {
                self.OferenteModalDetail(null);
                $('#documentsDetailModal').modal('hide');
            }

            this.sendDocumentationReminder = function(oferente) {
                self.closeDocumentsDetail();
                swal({
                    title: '¿Desea enviar notificacion a ' + oferente.razon_social + ' ?',
                    text: 'Esta a punto de enviar una notificacion para informarle el estado de la documentación.',
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
                        var url = '/concursos/documentation/reminder';
                        Services.Post(url, {
                                UserToken: User.Token,
                                IdConcurso: self.IdConcurso(),
                                Oferente: JSON.stringify(ko.toJS(oferente))
                            },
                            (response) => {
                                swal.close();
                                $.unblockUI();
                                if (response.success) {
                                    setTimeout(function() {
                                        swal('Hecho', response.message, 'success');
                                    }, 500);
                                } else {
                                    setTimeout(function() {
                                        swal('Error',
                                            'Ha ocurrido un error al enviar el correo.',
                                            'error');
                                    }, 500);
                                }
                            },
                            (error) => {
                                swal.close();
                                $.unblockUI();
                                setTimeout(function() {
                                    swal('Error', error.message, 'error');
                                }, 500);
                            },
                            null,
                            null
                        );
                    }
                });
            }

            this.rollbackFile = function(path) {
                var url = '/media/file/rollback';
                Services.Post(url, {
                        UserToken: User.Token,
                        path: path
                    },
                    (response) => {
                        $.unblockUI();
                        if (response.success) {
                            console.log(response.message);
                        }
                    },
                    (error) => {
                        $.unblockUI();
                    },
                    null,
                    null);
            }

            this.downloadZip = function() {
                swal({
                    title: '¿Desea descargar los archivos del concurso?',
                    type: 'info',
                    closeOnClickOutside: false,
                    showCancelButton: true,
                    closeOnConfirm: true,
                    cancelButtonText: 'Cancelar',
                    confirmButtonText: 'SI',
                    confirmButtonClass: 'btn btn-success',
                    cancelButtonClass: 'btn btn-danger',
                    buttonsStyling: false
                }, function() {
                    $.blockUI();
                    var url = '/media/file/zip/download';
                    Services.Post(url, {
                            UserToken: User.Token,
                            Entity: {
                                Id: params[4],
                            }
                        },
                        (response) => {
                            $.unblockUI();
                            if (response.success) {
                                swal.close();
                                setTimeout(function() {
                                    swal('Hecho', response.message, 'success');
                                }, 500);
                                //window.open(response.data.public_path);

                                var file_path = response.data.public_path;
                                var a = document.createElement('A');
                                a.href = file_path;
                                a.download = file_path.substr(file_path.lastIndexOf('/') + 1);
                                document.body.appendChild(a);
                                a.click();
                                document.body.removeChild(a);

                                {* self.rollbackFile(response.data.real_path); *}
                            }
                        },
                        (error) => {
                            $.unblockUI();
                            swal.close();
                            setTimeout(function() {
                                swal('Error', error.message, 'error');
                            }, 500);
                        },
                        null,
                        null);
                });
            }

            this.downloadReport = function() {
                swal({
                    title: '¿Desea descargar el informe del concurso?',
                    type: 'info',
                    closeOnClickOutside: false,
                    showCancelButton: true,
                    closeOnConfirm: true,
                    cancelButtonText: 'Cancelar',
                    confirmButtonText: 'SI',
                    confirmButtonClass: 'btn btn-success',
                    cancelButtonClass: 'btn btn-danger',
                    buttonsStyling: false
                }, function() {
                    $.blockUI();
                    $.get("/services/descarga-informe.php", { Tipo: params[1], Id: params[4] })
                        .done(function(data) {
                            $.unblockUI();
                            swal.close();
                            setTimeout(function() {
                                swal('Hecho', 'Archivo generado con éxito.', 'success');
                            }, 500);

                            var response = JSON.parse(data);
                            // var file_path = response.data.public_path;
                            // var a = document.createElement('A');
                            // a.href = file_path;
                            // a.download = file_path.substr(file_path.lastIndexOf('/') + 1);
                            // document.body.appendChild(a);
                            // a.click();
                            // document.body.removeChild(a);
                            window.open(response.data.public_path);
                            // espera 5 segundos antes de borrar
                            setTimeout(function() {
                            self.rollbackFile(response.data.real_path);
                            }, 5000);

                        }).fail(function(jqXHR, textStatus) {
                            console.log(jqXHR);
                            console.log(textStatus);
                            $.unblockUI();
                            swal.close();
                            setTimeout(function() {
                                swal('Error', 'Error al generar el archivo', 'error');
                            }, 500);
                        });
                });
            }

            var checkRead = () => {
                var url = '/concursos/chat/check';
                var data = {
                    IdConcurso: self.IdConcurso()
                }
                Services.Post(url, {
                        UserToken: User.Token,
                        Data: JSON.stringify(ko.toJS(data))
                    },
                    (response) => {
                        if (response.success) {
                            self.HasNewMessage(response.data.new_messages);
                        }
                    },
                    (error) => {
                        swal('Error', error.message, 'error');
                    },
                    null,
                    null
                );
            }

            var tipo = '{$tipo}';
            if (tipo != 'chat-muro-consultas') {
                const concurso_id = params[4];
                var query = '?concurso_id=' + concurso_id + '&user_id=' + User.Id + '&vista=concurso' + '&isClient=' +
                    self.IsClient();
                var path = 'wss://' + location.host + '/wss/chat';
                var chatConn = new WebSocket(path + query);

                chatConn.onopen = function(e) {

                };

                chatConn.onclose = function(e) {

                };

                chatConn.onerror = function(e) {

                };

                chatConn.onmessage = function(e) {
                    data = JSON.parse(e.data)
                    if (data.tipo == 'newMessageProv' || data.tipo == 'newRespProv') {
                        checkRead()
                    }
                    {* self.HasNewMessage(e.data) *}
                    // var result = JSON.parse(e.data);

                    // if (result.ItemsMejores) {
                    //     self.ItemsMejores(result.ItemsMejores);
                    //     self.Log(result.Log);
                    // } else if (result.conectados) {
                    //     self.Conectados(result.conectados);
                    // }

                    // if (result.TiempoAdicional) {
                    //     self.CountdownSeconds(parseInt(self.CountdownSeconds()) + (parseInt(result
                    //         .TiempoAdicional)));
                    //     self.Duracion(result.Duracion);
                    // }

                    // if (result.Mensajes) {
                    //     for (var mensaje of result.Mensajes) {
                    //         self.showToastr(mensaje, 'warning');
                    //     }
                    // }
                };
            }
            checkRead();

        };




        jQuery(document).ready(function() {
            $('body').on('change', '#comentario', function() {
                // Obtén el valor del textarea
                var valorTextarea = $(this).val();

                // Encuentra el input con placeholder="hola" y establece su valor
                $(this).closest('.sweet-alert')
                    .find('div.form-group input.form-control[placeholder="hidden"]')
                    .val(valorTextarea);
            });
            $('body').on('change', '#commentNewTechRound', function() {
                // Obtén el valor del textarea
                var valorTextarea = $(this).val();

                // Encuentra el input con placeholder="hola" y establece su valor
                $(this).closest('.sweet-alert')
                    .find('div.form-group input.form-control[placeholder="hidden"]')
                    .val(valorTextarea);
            });
            $('body').on('change', '#commentTechEvaluation', function() {
                // Obtén el valor del textarea
                var valorTextarea = $(this).val();

                // Encuentra el input con placeholder="hola" y establece su valor
                $(this).closest('.sweet-alert')
                    .find('div.form-group input.form-control[placeholder="hidden"]')
                    .val(valorTextarea);
            });
            $.blockUI();
            var url = '/concursos/cliente/' + params[1] + '/' + params[3] + '/' + params[4] + '/detail';
            Services.Get(url, {
                    UserToken: User.Token
                },
                (response) => {
                    if (response.success) {
                        window.E = new ConcursoPorEtapaCliente(response.data);
                        AppOptus.Bind(E);
                    }
                    $.unblockUI();
                },
                (error) => {
                    $.unblockUI();
                    swal('Error', error.message, 'error');
                },
                null,
                null
            );
        });

        // Chrome allows you to debug it thanks to this
        {chromeDebugString('dynamicScript')}
    </script>
{/block}