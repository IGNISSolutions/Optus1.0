{extends 'concurso/main.tpl'}

<!-- ESTILOS -->
{block 'styles'}
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
    <script src="{asset('/global/plugins/datatables/datatables.min.js')}" type="text/javascript"></script>
    <script src="{asset('/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js')}" type="text/javascript">
    </script>
    <script src="{asset('/global/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js')}" type="text/javascript">
    </script>
    <script src="{asset('/global/plugins/jquery-inputmask/inputmask/inputmask.date.extensions.min.js')}"
        type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.1/xlsx.full.min.js"></script>
    
{/block}

<!-- SCRIPTS POSTERIORES A KNOCKOUT -->
{block 'post-scripts'}
    {if $tipo eq 'invitacion'}
        <script src="{asset('/js/geo.js')}" type="text/javascript"></script>
        <script async defer
            src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD3xU2zO42h1qL1s6bFkHsdhtv_hpvfxBo&callback=initMapConcursoInvitacion">
        </script>
    {/if}
    <script src="{asset('/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js')}" type="text/javascript"></script>
    <script src="{asset('/global/plugins/bootstrap-fileinput-kartik/js/fileinput.min.js')}"></script>
    <script src="{asset('/global/plugins/bootstrap-fileinput-kartik/js/locales/es.js')}"></script>
    <script src="{asset('/global/plugins/bootstrap-toastr/toastr.min.js')}" type="text/javascript"></script>
    <script src="{asset('/global/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js')}" type="text/javascript"></script>
    <script src="{asset('/pages/scripts/components-bootstrap-maxlength.js')}" type="text/javascript"></script>
    <script src="{asset('/pages/scripts/jquery.form.js')}" type="text/javascript"></script>
    <script src="{asset('/global/plugins/bootbox/bootbox.min.js')}" type="text/javascript"></script>

    <script>
        $(function() {
            $('.slimScrollDiv').slimScroll({
                alwaysVisible: true,
                height: '250px',
                wheelStep: 30,
            });
        });

        function validarCotizacion(e) {
            if (Number.isNaN(parseInt(e.value, 10)))
                e.value = parseInt(e.min, 10);
            else if (parseInt(e.value, 10) > parseInt(e.max, 10))
                e.value = parseInt(e.max, 10);
            else if (parseInt(e.value, 10) < parseInt(e.min, 10))
                e.value = parseInt(e.min, 10);
            //e.value|=0;
        }

        Inputmask.extendAliases({
            monto: {
                //prefix: "₱ ",
                //prefix: "$ ",
                groupSeparator: ".",
                radixPoint: ",",
                alias: "numeric",
                placeholder: "0",
                autoGroup: true,
                digits: 4,
                digitsOptional: true,
                clearMaskOnLostFocus: true,
                autoUnmask: true,
                onUnMask: function(maskedValue, unmaskedValue, opts) {

                    var processValue = maskedValue.replaceAll(opts.groupSeparator, "");
                    processValue = processValue.replaceAll(opts.radixPoint, ".");
                    return parseFloat(processValue);
                }
            },
            cant: {
                groupSeparator: ".",
                radixPoint: ",",
                alias: "numeric",
                placeholder: "0",
                autoGroup: true,
                digits: 4,
                digitsOptional: true,
                clearMaskOnLostFocus: true,
                autoUnmask: true,
                onUnMask: function(maskedValue, unmaskedValue, opts) {

                    var processValue = maskedValue.replaceAll(opts.groupSeparator, "");
                    processValue = processValue.replaceAll(opts.radixPoint, ".");
                    return parseFloat(processValue);
                }
            }
        });
    </script>
    <script src="{asset('/global/plugins/jquery.pulsate.min.js')}" type="text/javascript"></script>
    <script>
        $(document).ready(function() {
            $("#pulsate-regular").pulsate({
                color: "#ff0000",
                reach: 5,
                speed: 500,
                pause: 0,
                glow: true,
                repeat: true,
                onHover: false
            });
        });
    </script>
{/block}

{block 'title'}
    {if $tipo neq 'chat-muro-consultas'}
        <span data-bind="text: Steps().find(b => b.current).title"></span>
    {/if}
{/block}

<!-- VISTA -->
{block 'concurso-detail-offerer'}
    <div class="row margin-top-40">
        <div class="col-md-12 text-center">

        {if $tipo neq 'chat-muro-consultas'}
            <div class="row">
                <div class="col-md-12 text-center">
                    <a href="/concursos/oferente" class="btn btn-xl green" title="Volver al listado" style="margin-bottom: 30px;">
                        <i class="fa fa-backward"></i> Volver al listado
                    </a>
                </div>
            </div>
            {/if}

            <!-- STEPS -->
            {include file='concurso/detail/partials/steps.tpl'}
            <!-- HEADER -->
            <div class="row">
                <div class="col-sm-12">
                    {include file='concurso/detail/offerer/partials/header.tpl'}
                </div>
            </div>

            {if $tipo eq 'invitacion'}

                {include file='concurso/detail/offerer/invitacion.tpl'}

            {else if $tipo eq 'chat-muro-consultas'}

                <chat-component params='IdConcurso: IdConcurso(), IsClient: IsClient(), IsProv: IsProv(), ChatEnable: ChatEnable(), FechaHoy: FechaHoy(), HoraHoy: HoraHoy(), CierreMuroConsultas: CierreMuroConsultas(), CierreMuroConsultasHora: CierreMuroConsultasHora()'></chat-component>

            {else if $tipo eq 'tecnica'}

                <!-- ko if: ShowTechnical() -->
                {include file='concurso/detail/offerer/tecnica.tpl'}
                <!-- /ko -->

            {else if $tipo eq 'economica'}

                <!-- ko if: ShowEconomic() -->
                {include file='concurso/detail/offerer/economica.tpl'}
                <!-- /ko -->

            {else if $tipo eq 'analisis'}

                <!-- ko if: HasEconomicaPresentada() -->
                {include file='concurso/detail/offerer/analisis.tpl'}
                <!-- /ko -->

            {else if $tipo eq 'adjudicado'}

                <!-- ko if: HasEconomicaPresentada() -->
                {include file='concurso/detail/offerer/adjudicado.tpl'}
                <!-- /ko -->

            {/if}

        </div>
    </div>

    {if $tipo neq 'chat-muro-consultas'}
        <div class="row">
            <div class="col-md-12 text-center">
                <a href="#" class="btn btn-xl green" title="Volver al listado" onclick="goBack()" style="margin-bottom: 30px;">
                    <i class="fa fa-backward"></i> Volver al listado
                </a>
            </div>
        </div>
    {/if}
{/block}

<!-- KNOCKOUT JS -->
{block 'knockout' append}
    <script type="text/javascript">
        var GoDocument = function(data) {
            var self = this;

            self.filename = ko.observable(data.filename);
            self.cuit = ko.observable(data.cuit)
            self.document_id = ko.observable(data.document_id)
            self.id = ko.observable(data.id)
            self.name = ko.observable(data.name)
            self.razon_social = ko.observable(data.razon_social)
            self.success = ko.observable(data.success)
            self.message = ko.observable(data.message)
            self.action = ko.observable(data.action)
            self.types = ko.observable(data.types)
        }

        var Document = function(data) {
            var self = this;

            self.filename = ko.observable(data.filename);
            self.id = ko.observable(data.id);
            self.type_id = ko.observable(data.type_id);
            self.name = ko.observable(data.name);
            self.action = ko.observable(null);
        }

        var TechnicalProposal = function(data) {
            var self = this;
            this.title = ko.observable(data.title);
            this.active = ko.observable(data.active);
            this.refRound = ko.observable(data.refRound);
            this.comentario = ko.observable(data.comentario);
            this.comentarioNuevaRonda = ko.observable(data.comentarioNuevaRonda);
            this.evaluacion = ko.observable(data.evaluacion);
            this.presented = ko.observable(data.presented);
            this.declinated = ko.observable(data.declinated);
            this.rejected = ko.observable(data.rejected);
            this.pending = ko.observable(data.pending);
            this.comentarioDeclinacion = ko.observable(data.comentarioDeclinacion);
            this.fechaDeclinacion = ko.observable(data.fechaDeclinacion);

            this.documents = ko.observableArray([]);

            if (data.documents.length > 0) {
                data.documents.forEach(item => {
                    self.documents.push(new Document(item));
                });
            }
        }

        var EconomicProposalProduct = function(data, currentRound) {
            var self = this;
            // Fixed fields
            self.product_id = ko.observable(data.product_id);
            self.product_name = ko.observable(data ? (data.product_name ? data.product_name: data["Nombre Producto"]) : null);
            self.product_description = ko.observable(data.product_description);
            self.total_quantity = ko.observable(data ? (data.total_quantity ? data.total_quantity : data["Cantidad Solicitada"]) : null);
            self.minimum_quantity = ko.observable(data.minimum_quantity);
            self.currency_id = ko.observable(data.currency_id);
            self.currency_name = ko.observable(data.currency_name);
            self.measurement_id = ko.observable(data.measurement_id);
            self.measurement_name = ko.observable(data.measurement_name);
            
            self.ProductSelected = ko.observable(
                currentRound === 1 ? true : parseFloat(data.cotizacion) > 0
            );
            self.cotizacion = ko.observable(data.cotizacion);
            self.maximum_cotizacion = ko.observable(data.maximum_cotizacion);
            self.cantidad = ko.observable(data.cantidad);
            self.fecha = ko.observable(data.fecha);
            self.creado = ko.observable(data.creado);

            self.ProductSelected.subscribe(function() {
                if (!self.ProductSelected()) {
                    self.cotizacion(0);
                    self.cantidad(0);
                    self.fecha(0);
                } else {
                    self.cotizacion(data.cotizacion);
                    self.cantidad(data.cantidad);
                    self.fecha(data.fecha);
                }
            });

            self.isValid = ko.computed(function () {
                if (!self.ProductSelected()) {
                    return true; // No está seleccionado, entonces no validamos
                }

                // Validaciones si el switch está activado
                const cot = parseFloat(self.cotizacion());
                const cant = parseFloat(self.cantidad());
                const fec = parseInt(self.fecha());

                return (
                    !isNaN(cot) && cot > 0 &&
                    !isNaN(cant) && cant > 0 &&
                    (!isNaN(fec) && fec > 0 && fec <= 365) // Solo si tu lógica lo requiere
                );
            });

        }

        var EconomicProposal = function(data, currentRound) {
            var self = this;
            this.comment = ko.observable(data.comment);
            this.PlazosPago = ko.observableArray(data.plazosPagos);
            this.CondicionesPago = ko.observableArray(data.condicionesPago);
            this.PlazoPago = ko.observable(data.payment_deadline);
            this.CondicionPago = ko.observable(data.payment_condition);
            this.documents = ko.observableArray([]);
            self.values = ko.observableArray(
                data.values.map(item => new EconomicProposalProduct(item, currentRound))
            );

            this.title1 = ko.observable(data.title1);
            this.active1 = ko.observable(data.active1);
            this.refRound1 = ko.observable(data.refRound1);

            if (data.documents.length > 0) {
                data.documents.forEach(item => {
                    self.documents.push(new Document(item));
                });
            }

        }

        var AuctionItemValues = function(data) {
            var self = this;

            this.producto = ko.observable(data ? data.producto : null);
            this.cotizacion = ko.observable(data ? data.cotizacion : null);
            this.cantidad = ko.observable(data ? data.cantidad : null);
            this.fecha = ko.observable(data ? data.fecha : null);
            this.creado = ko.observable(data ? data.creado : null);

        }

        var AuctionItemValuesBest = function(data) {
            var self = this;

            this.cotizacion = ko.observable(data ? data.cotizacion : null);
            this.cantidad = ko.observable(data ? data.cantidad : null);
            this.oferente = ko.observable(data ? data.oferente : null);
            this.hora = ko.observable(data ? data.hora : null);
        }

        var AuctionItemRanking = function(data) {
            var self = this;

            this.oferente_id = ko.observable(data.oferente_id);
            this.oferta_puesto = ko.observable(data.oferta_puesto);
            this.empatado = ko.observable(data.empatado);
        }

        var AuctionItem = function(data) {
            var self = this;

            this.id = ko.observable(data.id);
            this.id_oferente = ko.observable(data.id_oferente);
            this.nombre = ko.observable(data.nombre);
            this.descripcion = ko.observable(data.descripcion);
            this.cantidad = ko.observable(data.cantidad);
            this.oferta_minima = ko.observable(data.oferta_minima);
            this.oferta_puesto = ko.observable(data.oferta_puesto);
            this.empatado = ko.observable(data.empatado);
            this.unidad = ko.observable(data.unidad);
            this.unidadID = ko.observable(data.unidadID);
            this.unidades = ko.observableArray(data.unidades);
            this.valores = ko.observable(new AuctionItemValues(data.valores));
            this.valores_mejor = ko.observable(new AuctionItemValuesBest(data.valores_mejor));
            this.ranking = ko.observableArray();

            if (data.ranking.length > 0) {
                data.ranking.forEach(item => {
                    self.ranking.push(new AuctionItemRanking(item));
                });
            }
        }

        var ConcursoPorEtapaOferente = function(data) {
            var self = this; 
            
            this.goToChatMuroConToken = function () {
                $.blockUI();
                Services.Post('/concursos/oferente/guardar-token-acceso', {
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

                Services.Post('/concursos/oferente/guardar-token-acceso', {
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
            this.Breadcrumbs = ko.observableArray(data.breadcrumbs);
            this.Steps = ko.observableArray(data.steps);

            this.IsGo = ko.observable(data.list.IsGo);
            this.IsOnline = ko.observable(data.list.IsOnline);
            this.IsSobrecerrado = ko.observable(data.list.IsSobrecerrado);

            this.IsInvitacionPendiente = ko.observable(data.list.IsInvitacionPendiente);
            this.IsInvitacionRechazada = ko.observable(data.list.IsInvitacionRechazada);
            this.IsTecnicaPendiente = ko.observable(data.list.IsTecnicaPendiente);
            this.IsTecnicaPresentada = ko.observable(data.list.IsTecnicaPresentada);
            this.IsEconomicaPendiente = ko.observable(data.list.IsEconomicaPendiente);
            this.IsEconomicaPendienteSegundaRonda = ko.observable(data.list.IsEconomicaPendienteSegundaRonda);
            this.IsEconomicaPresentada = ko.observable(data.list.IsEconomicaPresentada);
            this.HasEconomicaRevisada = ko.observable(data.list.HasEconomicaRevisada);
            this.IsAdjudicacionPendiente = ko.observable(data.list.IsAdjudicacionPendiente);
            this.IsAdjudicacionAceptada = ko.observable(data.list.IsAdjudicacionAceptada);
            this.IsAdjudicacionRechazada = ko.observable(data.list.IsAdjudicacionRechazada);
            this.HasEconomicaPresentada = ko.observable(data.list.HasEconomicaPresentada);
            this.HasEconomicaVencida = ko.observable(data.list.HasEconomicaVencida);
            this.HasTecnicaPresentada = ko.observable(data.list.HasTecnicaPresentada);
            this.HasTecnicaAprobada = ko.observable(data.list.HasTecnicaAprobada);
            this.ShowTechnical = ko.observable(data.list.ShowTechnical);
            this.ShowEconomic = ko.observable(data.list.ShowEconomic);
            this.EnableTechnical = ko.observable(data.list.EnableTechnical);
            this.EnableEconomic = ko.observable(data.list.EnableEconomic);
            this.Rechazado = ko.observable(data.list.Rechazado);
            this.IsGo = ko.observable(data.list.IsGo);
            this.Nombre = ko.observable(data.list.Nombre);
            this.Solicitante = ko.observable(data.list.Solicitante);
            this.Administrador = ko.observable(data.list.Administrador);
            this.TipoConcurso = ko.observable(data.list.TipoConcurso);
            this.TipoOperacion = ko.observable(data.list.TipoOperacion);
            this.Portrait = ko.observable(data.list.Portrait);
            this.ZonaHoraria = ko.observable(data.list.ZonaHoraria);
            this.Resena = ko.observable(data.list.Resena);
            this.Descripcion = ko.observable(data.list.Descripcion);
            this.TipoConcursoPath = ko.observable(data.list.TipoConcursoPath);
            this.ImagePath = ko.observable(data.list.ImagePath);
            this.FilePath = ko.observable(data.list.FilePath);
            this.FilePathOferente = ko.observable(data.list.FilePathOferente + '/');
            this.FechaDesde = ko.observable(data.list.FechaDesde);
            this.FechaHasta = ko.observable(data.list.FechaHasta);
            this.HoraDesde = ko.observable(data.list.HoraDesde);
            this.HoraHasta = ko.observable(data.list.HoraHasta);
            this.ProvinciaDesdeNombre = ko.observable(data.list.ProvinciaDesdeNombre);
            this.ProvinciaHastaNombre = ko.observable(data.list.ProvinciaHastaNombre);
            this.CiudadDesdeNombre = ko.observable(data.list.CiudadDesdeNombre);
            this.CiudadHastaNombre = ko.observable(data.list.CiudadHastaNombre);
            this.AceptacionInvitacion = ko.observable(data.list.AceptacionInvitacion);
            this.AceptacionInvitacionHora = ko.observable(data.list.AceptacionInvitacionHora);
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
            this.EstadoSubasta = ko.observable(data.list.EstadoSubasta);
            this.Pais = ko.observable(data.list.Pais);
            this.Provincia = ko.observable(data.list.Provincia);
            this.Localidad = ko.observable(data.list.Localidad);
            this.Direccion = ko.observable(data.list.Direccion);
            this.Cp = ko.observable(data.list.Cp);
            this.Latitud = ko.observable(data.list.Latitud);
            this.Longitud = ko.observable(data.list.Longitud);
            this.TerminosCondiciones = ko.observable(data.list.TerminosCondiciones);
            this.ImagePath = ko.observable(data.list.ImagePath);
            this.Portrait = ko.observable(data.list.Portrait);
            this.Adjudicado = ko.observable(data.list.Adjudicado);
            this.Eliminado = ko.observable(data.list.Eliminado);
            this.AceptoInvitacion = ko.observable(data.list.AceptoInvitacion);
            this.IsInvitacionPendiente = ko.observable(data.list.IsInvitacionPendiente);
            this.HasTecnicaVencida = ko.observable(data.list.HasTecnicaVencida);
            this.EstadoTecnica = ko.observable(data.list.EstadoTecnica);
            this.EstadoEconomica = ko.observable(data.list.EstadoEconomica);
            this.EstadoChat = ko.observable(data.list.EstadoChat);
            this.UserId = ko.observable(data.list.UserId);
            this.OferenteId = ko.observable(data.list.OferenteId);
            this.Estado = ko.observable(data.list.Estado);
            this.Moneda = ko.observable(data.list.Moneda);
            this.IncluyeTecnica = ko.observable(data.list.IncluyeTecnica);
            this.Tipo = ko.observable(data.list.Tipo);
            this.TimeZone = ko.observable(data.list.ZonaHoraria);
            this.DescripcionTitle = ko.observable(data.list.DescripcionTitle);
            this.DescripcionDescription = ko.observable(data.list.DescripcionDescription);
            this.DescripcionUrl = ko.observable(data.list.DescripcionUrl);
            this.DescripcionImagen = ko.observable(data.list.DescripcionImagen);
            this.AdjudicacionAnticipada = ko.observable(data.list.AdjudicacionAnticipada);
            this.reasonDeclination = ko.observable(null);
            this.MuroConsultaActive = ko.observable(data.list.MuroConsultaActive);
            this.UrlChatMuro = ko.observable(data.list.urlChatMuro);
            this.ChatEnable = ko.observable(data.list.ChatEnable);
            //this.ShowChatButton = ko.observable(data.list.ShowChatButton);
            this.ShowChatButton = ko.observable(self.IsSobrecerrado() ? true : data.list.ChatEnable);
            this.HasNewMessage = ko.observable(false);
            this.IsClient = ko.observable(false);
            this.IsProv = ko.observable(true);

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
                ahora.getFullYear()  // yyyy
            );



            switch (params[3]) {
                case 'invitacion':
                    this.Media = ko.observableArray(data.list.Media);
                    this.PlazoVencidoAceptacion = ko.observable(data.list.PlazoVencidoAceptacion);
                    this.AceptacionTerminos = ko.observable(data.list.AceptacionTerminos);
                    this.Productos = ko.observableArray(data.list.Productos);


                    break;
                case 'tecnica':
                    this.Amount = ko.observable(data.list.Amount);
                    if (!self.IsGo()) {
                        this.TechnicalProposal = ko.observable(new TechnicalProposal(data.list.TechnicalProposal));
                        this.PropuestasTecnicas = ko.observableArray([]);
                        if (self.IncluyeTecnica()) {
                            data.list.PropuestasTecnicas.rondas.forEach(
                                item => {
                                    self.PropuestasTecnicas.push(new TechnicalProposal(item));
                                });
                        }
                        this.SeguroCaucion = ko.observable(data.list.SeguroCaucion);
                        this.DiagramaGant = ko.observable(data.list.DiagramaGant);

                        this.ListaProveedores = ko.observable(data.list.ListaProveedores);
                        this.CertificadoVisitaObra = ko.observable(data.list.CertificadoVisitaObra);

                        this.EntregaDocEvaluacion = ko.observable(data.list.EntregaDocEvaluacion);
                        this.RequisitosLegales = ko.observable(data.list.RequisitosLegales);
                        this.ExperienciaYReferencias = ko.observable(data.list.ExperienciaYReferencias); 
                        this.DocumentacionREPSE = ko.observable(data.list.DocumentacionREPSE);
                        this.Alcance = ko.observable(data.list.Alcance);
                        this.FormaPago = ko.observable(data.list.FormaPago);
                        this.TiempoFabricacion = ko.observable(data.list.TiempoFabricacion);
                        this.FichaTecnica = ko.observable(data.list.FichaTecnica);
                        this.Garantias = ko.observable(data.list.Garantias);



                        this.BaseCondiciones = ko.observable(data.list.BaseCondiciones);
                        this.CondicionesGenerales = ko.observable(data.list.CondicionesGenerales);
                        this.PliegoTecnico = ko.observable(data.list.PliegoTecnico);
                        this.Confidencialidad = ko.observable(data.list.Confidencialidad);
                        this.LegajoImpositivo = ko.observable(data.list.LegajoImpositivo);
                        this.Antecedentes = ko.observable(data.list.Antecedentes);



                        this.ReporteAccidentes = ko.observable(data.list.ReporteAccidentes);
                        this.EnvioMuestra = ko.observable(data.list.EnvioMuestra);
                        this.nom251 = ko.observable(data.list.nom251);
                        this.distintivo = ko.observable(data.list.distintivo);
                        this.filtros_sanitarios = ko.observable(data.list.filtros_sanitarios);
                        this.repse = ko.observable(data.list.repse);
                        this.poliza = ko.observable(data.list.poliza);
                        this.primariesgo = ko.observable(data.list.primariesgo);
                        this.obras_referencias = ko.observable(data.list.obras_referencias);
                        this.obras_organigrama = ko.observable(data.list.obras_organigrama);
                        this.obras_equipos = ko.observable(data.list.obras_equipos);
                        this.obras_cronograma = ko.observable(data.list.obras_cronograma);
                        this.obras_memoria = ko.observable(data.list.obras_memoria);
                        this.obras_antecedentes = ko.observable(data.list.obras_antecedentes);
                        this.tarima_ficha_tecnica = ko.observable(data.list.tarima_ficha_tecnica);
                        this.tarima_licencia = ko.observable(data.list.tarima_licencia);
                        this.tarima_nom_144 = ko.observable(data.list.tarima_nom_144);
                        this.tarima_acreditacion = ko.observable(data.list.tarima_acreditacion);
                        this.EvaluacionTecnica = data.list.PropuestasTecnicas.TechnicalEvaluations;
                        this.edificio_balance = ko.observable(data.list.edificio_balance);
                        this.edificio_iva = ko.observable(data.list.edificio_iva);
                        this.edificio_cuit = ko.observable(data.list.edificio_cuit);
                        this.edificio_brochure = ko.observable(data.list.edificio_brochure);
                        this.edificio_organigrama = ko.observable(data.list.edificio_organigrama);
                        this.edificio_organigrama_obra = ko.observable(data.list.edificio_organigrama_obra);
                        this.edificio_subcontratistas = ko.observable(data.list.edificio_subcontratistas);
                        this.edificio_gestion = ko.observable(data.list.edificio_gestion);
                        this.edificio_maquinas = ko.observable(data.list.edificio_maquinas);
                        this.concurso_fiscalizado = ko.observable(data.list.concurso_fiscalizado);
                    }
                    this.DriverDocuments = ko.observableArray('');
                    this.VehicleDocuments = ko.observableArray('');
                    this.TrailerDocuments = ko.observableArray('');
                    this.DriverNoGcgDocuments = ko.observableArray('');
                    this.VehicleNoGcgDocuments = ko.observableArray('');
                    this.AdditionalDriverDocuments = ko.observableArray('');
                    this.AdditionalVehicleDocuments = ko.observableArray('');
                    this.DriverSelected = ko.observable(data.list.DriverSelected);
                    this.VehicleSelected = ko.observable(data.list.VehicleSelected);
                    this.TrailerSelected = ko.observable(data.list.TrailerSelected);
                    if (data.list.DriverDocuments != null) {
                        for (var i = 0; i < data.list.DriverDocuments.length; i++) {
                            self.DriverDocuments.push(new GoDocument(data.list.DriverDocuments[i]));
                        }
                    }
                    if (data.list.VehicleDocuments != null) {
                        for (var i = 0; i < data.list.VehicleDocuments.length; i++) {
                            self.VehicleDocuments.push(new GoDocument(data.list.VehicleDocuments[i]));
                        }
                    }
                    if (data.list.TrailerDocuments != null) {
                        for (var i = 0; i < data.list.TrailerDocuments.length; i++) {
                            self.TrailerDocuments.push(new GoDocument(data.list.TrailerDocuments[i]));
                        }
                    }
                    if (data.list.DriverNoGcgDocuments != null) {
                        for (var i = 0; i < data.list.DriverNoGcgDocuments.length; i++) {
                            self.DriverNoGcgDocuments.push(new GoDocument(data.list.DriverNoGcgDocuments[i]));
                        }
                    }
                    if (data.list.AdditionalDriverDocuments != null) {
                        for (var i = 0; i < data.list.AdditionalDriverDocuments.length; i++) {
                            self.AdditionalDriverDocuments.push(new GoDocument(data.list.AdditionalDriverDocuments[i]));
                        }
                    }
                    if (data.list.AdditionalVehicleDocuments != null) {
                        for (var i = 0; i < data.list.AdditionalVehicleDocuments.length; i++) {
                            self.AdditionalVehicleDocuments.push(new GoDocument(data.list.AdditionalVehicleDocuments[
                                i]));
                        }
                    }
                    break;
                case 'economica':
                    this.Costs = ko.observable(data.list.Costs);
                    this.AnalisisApu = ko.observable(data.list.AnalisisApu);
                    this.CondicionPago = ko.observable(data.list.CondicionPago);
                    this.RondaActual = ko.observable(data.list.RondaActual);
                    this.EconomicProposal = ko.observable(
                        new EconomicProposal(data.list.EconomicProposal, this.RondaActual())
                    );
                    this.Items = ko.observableArray();
                    if (self.IsOnline()) {
                        if (data.list.Items.length > 0) {
                            data.list.Items.forEach(item => {
                                self.Items.push(new AuctionItem(item));
                            });
                        }
                    } else {
                        self.Items(data.list.Items);
                    }

                    this.PermiteAnularOferta = ko.observable(data.list.PermiteAnularOferta);
                    this.Descendente = ko.observable(data.list.Descendente);
                    this.CantidadOferentes = ko.observable(data.list.CantidadOferentes);
                    this.Duracion = ko.observable(data.list.Duracion);
                    this.TiempoAdicional = ko.observable(data.list.TiempoAdicional);
                    this.Countdown = ko.observable('');
                    this.Timeleft = ko.observable('');
                    this.CountdownSeconds = ko.observable(data.list.Countdown);
                    this.TimeleftSeconds = ko.observable(data.list.Timeleft);
                    this.Conectados = ko.observable('0');
                    this.UnidadMinima = ko.observable(data.list.UnidadMinima);
                    this.VerNumOferentesParticipan = ko.observable(data.list.VerNumOferentesParticipan);
                    this.VerOfertaGanadora = ko.observable(data.list.VerOfertaGanadora);
                    this.VerRanking = ko.observable(data.list.VerRanking);
                    this.VerTiempoRestante = ko.observable(data.list.VerTiempoRestante);
                    this.Chat = ko.observable(data.list.Chat);
                    this.SoloOfertasMejores = ko.observable(data.list.SoloOfertasMejores);
                    this.PrecioMaximo = ko.observable(data.list.PrecioMaximo);
                    this.PrecioMinimo = ko.observable(data.list.PrecioMinimo);
                    this.Title = ko.observable(data.list.Title);

                    // SUBASTA
                    if (params[2] === 'online') {
                        // Countdown
                        setInterval(function() {
                            if (self.CountdownSeconds() === 0) {
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
                            if (self.TimeleftSeconds() === 0) {
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
                            var query = '?id_concurso=' + params[4] + '&id_oferente=' + User.Id;

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
                                    window.location.href = '/concursos/oferente';
                                });
                            };
                            subastaConn.onerror = function(e) {
                                swal({
                                    title: 'No se ha podido conectar con la Subasta.',
                                    text: 'Por favor intente nuevamente en unos instantes.',
                                    confirmButtonText: 'Aceptar',
                                }, function(response) {
                                    window.location.href = '/concursos/oferente';
                                });
                            };
                            subastaConn.onmessage = function(e) {
                                var result = JSON.parse(e.data);
                                if (result.Items && result.Items.length > 0) {
                                    self.Items.removeAll();

                                    result.Items.forEach(item => {
                                        if (item.id_oferente == self.UserId()) {
                                            self.Items.push(new AuctionItem(item));
                                        }
                                    });
                                } else if (result.conectados) {
                                    self.Conectados(result.conectados);
                                }

                                if (result.TiempoAdicional) {
                                    self.CountdownSeconds(parseInt(self.CountdownSeconds()) + (parseInt(result
                                        .TiempoAdicional)));
                                }

                                if (result.Mensajes) {
                                    for (var mensaje of result.Mensajes) {
                                        self.showToastr(mensaje, 'warning');
                                    }
                                }

                            };
                        }
                    }

                    break;
                case 'analisis':
                    this.Items = ko.observable(data.list.Items);

                    break;
                case 'adjudicado':
                    this.Resultados = ko.observable(data.list.Resultados);
                    this.AceptoAdjudicacion = ko.observable(data.list.AceptoAdjudicacion);
                    this.EstadoTran = ko.observable(data.list.EstadoTran);
                    this.UrlMercadoPago = ko.observable(data.list.UrlMercadoPago);
                    this.Cuit = ko.observable(data.list.Cuit);
                    this.PersonaContacto = ko.observable(data.list.PersonaContacto);
                    this.Apellido = ko.observable(data.list.Apellido);
                    this.Telefono = ko.observable(data.list.Telefono);
                    this.Email = ko.observable(data.list.Email);
                    this.Items = ko.observable(data.list.Items);

                    
                    this.TotalCotizaciones = ko.computed(function() {
                        var total = 0;
                        self.Resultados().forEach(function(item) {
                            total += item.valores.cotizacion || 0; // Asegúrate de que 'cotizacion' esté definido
                        });
                        return total;
                    });

                    break;
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

            this.AcceptRejectInvitation = function(action) {
                const htmlBody = '<p>Por favor, indique la razón de su declinación de la invitación</p> <textarea rows="3" cols="50" class="form-control" style="resize: none;" id="invitationDeclination"></textarea>';
                const title = action == 'reject' ? '¿Desea rechazar la invitación?' : '¿Desea aceptar la invitación?';

                const swalAlert = action == 'reject' ? {
                    title: title,
                    type: "input",
                    html: true,
                    text: htmlBody,
                    inputPlaceholder: 'hidden',
                    closeOnClickOutside: false,
                    showCancelButton: true,
                    closeOnConfirm: false,
                    confirmButtonText: 'Aceptar',
                    confirmButtonClass: 'btn btn-success',
                    cancelButtonText: 'Cancelar',
                    cancelButtonClass: 'btn btn-default',
                    buttonsStyling: false
                } : {
                    title: title,
                    type: 'info',
                    closeOnClickOutside: false,
                    showCancelButton: true,
                    closeOnConfirm: true,
                    confirmButtonText: 'Aceptar',
                    confirmButtonClass: 'btn btn-success',
                    cancelButtonText: 'Cancelar',
                    cancelButtonClass: 'btn btn-default',
                    buttonsStyling: false
                };

                const swalFunction = action == 'reject' ?
                    function(inputValue) {
                        if (inputValue === false) return false;
                        if (inputValue === "") {
                            swal.showInputError("Necesita describir la razón de su declinación");
                            return false;
                        }
                        swal.close();
                        $.blockUI();
                        var data = {
                            UserToken: User.Token,
                            Action: action,
                            IdConcurso: self.IdConcurso(),
                            reason: inputValue
                        };
                        Services.Post('/concursos/invitations/acceptorreject', {
                                UserToken: User.Token,
                                Data: JSON.stringify(ko.toJS(data))
                            },
                            (response) => {
                                $.unblockUI();
                                swal.close();
                                setTimeout(function() {
                                    // Guardar token antes de redirigir
                                    Services.Post('/concursos/oferente/guardar-token-acceso', {
                                        UserToken: User.Token,
                                        id: self.IdConcurso()
                                    }, 
                                    (responseToken) => {
                                        if (responseToken.success) {
                                            window.location.href = response.data.redirect;
                                        } else {
                                            swal('Error', 'Error generando token: ' + responseToken.message, 'error');
                                        }
                                    }, 
                                    (errorToken) => {
                                        swal('Error', errorToken.message, 'error');
                                    });
                                }, 500);
                            },
                            (error) => {
                                $.unblockUI();
                                swal('Error', typeof error.message !== 'undefined' ? error.message : error.responseJSON.message, 'error');
                            });
                    }
                    :
                    function(result) {
                        if (result) {
                            $.blockUI();
                            var data = {
                                UserToken: User.Token,
                                Action: action,
                                IdConcurso: self.IdConcurso()
                            };
                            Services.Post('/concursos/invitations/acceptorreject', {
                                    UserToken: User.Token,
                                    Data: JSON.stringify(ko.toJS(data))
                                },
                                (response) => {
                                    $.unblockUI();
                                    swal.close();
                                    setTimeout(function() {
                                        // Guardar token antes de redirigir
                                        Services.Post('/concursos/oferente/guardar-token-acceso', {
                                            UserToken: User.Token,
                                            id: self.IdConcurso()
                                        }, 
                                        (responseToken) => {
                                            if (responseToken.success) {
                                                window.location.href = response.data.redirect;
                                            } else {
                                                swal('Error', 'Error generando token: ' + responseToken.message, 'error');
                                            }
                                        }, 
                                        (errorToken) => {
                                            swal('Error', errorToken.message, 'error');
                                        });
                                    }, 500);
                                },
                                (error) => {
                                    $.unblockUI();
                                    swal('Error', typeof error.message !== 'undefined' ? error.message : error.responseJSON.message, 'error');
                                });
                        }
                    };

                swal(swalAlert, swalFunction);
            };


            this.TechnicalSend = function(isUpdate = false) {
                const propuestasTecnicas = ko.toJS(self.PropuestasTecnicas());
                const rondaActiva = propuestasTecnicas.find(ronda => ronda.active === true);

                // 1. Primero generamos el token de acceso
                $.blockUI();
                Services.Post('/concursos/oferente/guardar-token-acceso', {
                    UserToken: User.Token,
                    id: self.IdConcurso()
                },
                (resp) => {
                    $.unblockUI();
                    if (resp.success) {

                        // 2. Si el token fue generado, seguimos con la lógica normal
                        const url = isUpdate
                            ? '/concursos/proposal/technical/update'
                            : '/concursos/proposal/technical/send';

                        const title = isUpdate
                            ? '¿Desea guardar los cambios?'
                            : '¿Desea enviar propuesta técnica?';

                        swal({
                            title: title,
                            type: 'info',
                            closeOnClickOutside: false,
                            showCancelButton: true,
                            closeOnConfirm: true,
                            confirmButtonText: 'Aceptar',
                            confirmButtonClass: 'btn btn-success',
                            cancelButtonText: 'Cancelar',
                            cancelButtonClass: 'btn btn-default',
                            buttonsStyling: false
                        }, function(result) {
                            if (result) {
                                $.blockUI();
                                Services.Post(url, {
                                        UserToken: User.Token,
                                        ConcursoId: self.IdConcurso(),
                                        Entity: JSON.stringify(rondaActiva)
                                    },
                                    (response) => {
                                        swal.close();
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
                                                        location.reload();
                                                    }
                                                }
                                            });
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


            this.checkDocuments = function() {
                $.blockUI();
                var data = {
                    Id: params[4],
                    DriverSelected: self.DriverSelected,
                    VehicleSelected: self.VehicleSelected,
                    TrailerSelected: self.TrailerSelected
                };
                var url = '/concursos/documentation/check';

                Services.Post(url, {
                        UserToken: User.Token,
                        Entity: JSON.stringify(ko.toJS(data))
                    },
                    (response) => {
                        self.DriverDocuments().forEach(document => {
                            if (response.data && response.data.driver) {
                                document.message(response.data.driver.habilitacion);
                                document.success(response.data.driver.habilitado);
                            } else {
                                document.message('Sin Verificar');
                                document.success(false);
                            }
                        });
                        self.VehicleDocuments().forEach(document => {
                            if (response.data && response.data.vehicle) {
                                document.message(response.data.vehicle.habilitacion);
                                document.success(response.data.vehicle.habilitado);
                            } else {
                                document.message('Sin Verificar');
                                document.success(false);
                            }
                        });
                        self.TrailerDocuments().forEach(document => {
                            if (response.data && response.data.trailer) {
                                document.message(response.data.trailer.habilitacion);
                                document.success(response.data.trailer.habilitado);
                            } else {
                                document.message('Sin Verificar');
                                document.success(false);
                            }
                        });
                        $.unblockUI();
                    },
                    (error) => {
                        $.unblockUI();
                    },
                    null,
                    null
                );
            }

            if (params[3] == 'tecnica') {
                // self.checkDocuments();
            }

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
                        swal('Error', typeof error.message != 'undefined' ? error.message : error.responseJSON
                            .message, 'error');
                    },
                    null,
                    null
                );
            }
        
            this.EconomicSend = function(isUpdate = false) {
                // Validar campos de ítems seleccionados
                const items = self.EconomicProposal().values();

                // Separar errores de fecha
                const erroresFecha = items.filter(item => {
                    if (!item.ProductSelected()) return false;

                    const fec = parseInt(item.fecha());
                    return self.IsSobrecerrado() && (isNaN(fec) || fec < 1 || fec > 365);
                });

                // Validar todos los campos
                const itemsInvalidos = items.filter(item => {
                    if (!item.ProductSelected()) return false;

                    const cot = parseFloat(item.cotizacion());
                    const cant = parseFloat(item.cantidad());
                    const fec = parseInt(item.fecha());

                    return (
                        isNaN(cot) || cot <= 0 ||
                        isNaN(cant) || cant <= 0 ||
                        (self.IsSobrecerrado() && (isNaN(fec) || fec <= 0 || fec > 365))
                    );
                });

                // Mensaje específico si hay errores en la fecha
                if (erroresFecha.length > 0) {
                    swal({
                        title: "Plazo de entrega inválido",
                        text: "El campo 'Plazo de entrega' debe estar entre 1 y 365 días.",
                        type: "error",
                        confirmButtonText: "Aceptar",
                        confirmButtonClass: 'btn btn-danger',
                        buttonsStyling: false
                    });
                    return;
                }

                // Mensaje genérico si hay otros errores
                if (itemsInvalidos.length > 0) {
                    swal({
                        title: "Error",
                        text: "Existen ítems seleccionados con campos obligatorios incompletos o inválidos. Por favor, complete la información requerida o deseleccione esos ítems.",
                        type: "error",
                        confirmButtonText: "Aceptar",
                        confirmButtonClass: 'btn btn-danger',
                        buttonsStyling: false
                    });
                    return;
                }

                // Continuar con envío si todo está válido
                var url = isUpdate ? '/concursos/proposal/economic/update' : '/concursos/proposal/economic/send';
                var title = isUpdate ? '¿Desea guardar los cambios?' : '¿Desea enviar propuesta económica?';

                swal({
                    title: title,
                    type: 'info',
                    closeOnClickOutside: false,
                    showCancelButton: true,
                    closeOnConfirm: true,
                    confirmButtonText: 'Aceptar',
                    confirmButtonClass: 'btn btn-success',
                    cancelButtonText: 'Cancelar',
                    cancelButtonClass: 'btn btn-default',
                    buttonsStyling: false
                }, function(result) {
                    if (result) {
                        $.blockUI();
                        var body = {
                            IdConcurso: self.IdConcurso,
                            IsGo: self.IsGo,
                            IsSobrecerrado: self.IsSobrecerrado,
                            IsOnline: self.IsOnline,
                            EconomicProposal: self.EconomicProposal
                        };
                        Services.Post(url, {
                                UserToken: User.Token,
                                Data: JSON.stringify(ko.toJS(body))
                            },
                            (response) => {
                                swal.close();
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
                                            window.location.href = '/concursos/oferente';
                                        }
                                    });
                                }, 500)
                            },
                            (error) => {
                                swal.close();
                                $.unblockUI();
                                setTimeout(function() {
                                    swal('Error', typeof error.message != 'undefined' ? error.message : error.responseJSON.message, 'error');
                                }, 500)
                            },
                            null,
                            null
                        );
                    }
                });
            };


             

           

            // Subasta: Acciones
            this.AuctionUpdate = function(index = null, action) {
                switch (action) {
                    case 'cotizar':
                        var url = '/concursos/proposal/auction/cotizar';
                        var title = '¿Desea enviar esta oferta?';
                        var type = 'success';
                        var data = {
                            IdConcurso: params[4],
                            Index: index,
                            Items: self.Items()
                        };
                        break;
                    case 'anular':
                        var url = '/concursos/proposal/auction/anular';
                        var title = '¿Desea anular esta oferta?';
                        var type = 'warning';
                        var data = {
                            IdConcurso: params[4],
                            Index: index,
                            Items: self.Items()
                        };
                        break;
                    case 'update':
                        var url = '/concursos/proposal/auction/update';
                        var title = '¿Desea actualizar la subasta?';
                        var data = {
                            IdConcurso: params[4],
                            EconomicProposal: self.EconomicProposal()
                        };
                        break;
                }
                swal({
                    title: title,
                    type: type,
                    closeOnClickOutside: false,
                    showCancelButton: true,
                    closeOnConfirm: true,
                    confirmButtonText: 'Aceptar',
                    confirmButtonClass: 'btn btn-success',
                    cancelButtonText: 'Cancelar',
                    cancelButtonClass: 'btn btn-default',
                    buttonsStyling: false
                }, function(result) {
                    if (result) {
                        $.blockUI();
                        Services.Post(url, {
                                UserToken: User.Token,
                                Entity: JSON.stringify(ko.toJS(data))
                            },
                            (response) => {
                                swal.close();
                                $.unblockUI();
                                setTimeout(function() {
                                    swal({
                                        title: response.message,
                                        type: 'success',
                                        showCancelButton: false,
                                        closeOnConfirm: true,
                                        confirmButtonText: 'Aceptar',
                                        confirmButtonClass: 'btn btn-success',
                                        buttonsStyling: false
                                    }, function(result) {
                                        if (response.success) {
                                            swal.close();
                                            switch (action) {
                                                case 'cotizar':
                                                case 'update':
                                                    var additional_time = null;
                                                    // Sumamos tiempo adicional.
                                                    if (self.CountdownSeconds() < 60 &&
                                                        self.TiempoAdicional() && self
                                                        .TiempoAdicional() > 0) {
                                                        additional_time = self
                                                            .TiempoAdicional();
                                                    }
                                                    // Actualizacion de datos.
                                                    var producto = self.Items()[0]
                                                        .nombre()

                                                    if (typeof producto !==
                                                        'undefined') {
                                                        producto = self.Items()[0]
                                                            .nombre();
                                                    } else {
                                                        producto = self.Items()[index]
                                                            .nombre();
                                                    }
                                                    subastaConn.send([self.IdConcurso(),
                                                        self.OferenteId(),
                                                        producto, 'cotizar',
                                                        additional_time
                                                    ]);
                                                    break;

                                                case 'anular':
                                                    var additional_time = null;
                                                    // Sumamos tiempo adicional.
                                                    if (self.CountdownSeconds() < 60 &&
                                                        self.TiempoAdicional() && self
                                                        .TiempoAdicional() > 0) {
                                                        additional_time = self
                                                            .TiempoAdicional();
                                                    }
                                                    // Actualizacion de datos.
                                                    var producto = self.Items()[index]
                                                        .nombre();
                                                    subastaConn.send([self.IdConcurso(),
                                                        self.OferenteId(),
                                                        producto, 'anular',
                                                        additional_time
                                                    ]);
                                                    break;
                                            }
                                        }
                                    });
                                }, 500)
                            },
                            (error) => {
                                swal.close();
                                $.unblockUI();
                                setTimeout(function() {
                                    swal('Error', typeof error.message != 'undefined' ? error
                                        .message : error.responseJSON.message, 'error');
                                }, 500)
                            },
                            null,
                            null
                        );
                    }
                });
            }

            this.AdjudicationSend = function(action) {
                var title, type;
                if (action === 'accept') {
                    title = 'Usted está por aceptar la adjudicación de los items detallados. Esto implica su compromiso para cumplir con los pliegos, bases y condiciones del concurso, como así también el contenido de sus ofertas técnicas y económicas. ¿Desea continuar?';
                    type = 'success';
                } else {
                    title = 'Usted está por rechazar la adjudicación de todos los items adjudicados a su empresa. ¿Desea continuar?';
                    type = 'error';
                }

                swal({
                    title: title,
                    type: type,
                    closeOnClickOutside: false,
                    showCancelButton: true,
                    closeOnConfirm: true,
                    confirmButtonText: 'Aceptar',
                    confirmButtonClass: 'btn btn-success',
                    cancelButtonText: 'Cancelar',
                    cancelButtonClass: 'btn btn-default',
                    buttonsStyling: false
                }, function(result) {
                    if (!result) return;

                    $.blockUI();

                    const data = {
                        IdConcurso: self.IdConcurso(),
                        Action: action
                    };

                    const postAdjudicacion = () => {
                        const url = '/concursos/oferente/adjudication/send';

                        Services.Post(url, {
                            UserToken: User.Token,
                            Entity: JSON.stringify(ko.toJS(data))
                        },
                        (response) => {
                            $.unblockUI();
                            swal.close();
                            setTimeout(() => {
                                swal({
                                    title: response.message,
                                    type: 'success',
                                    closeOnClickOutside: false,
                                    showCancelButton: false,
                                    closeOnConfirm: true,
                                    confirmButtonText: 'Aceptar',
                                    confirmButtonClass: 'btn btn-success',
                                    buttonsStyling: false
                                }, function () {
                                    if (response.success && response.data.redirect) {
                                        window.location.href = response.data.redirect;
                                    }
                                });
                            }, 500);
                        },
                        (error) => {
                            $.unblockUI();
                            swal.close();
                            setTimeout(() => {
                                swal('Error',
                                    typeof error.message !== 'undefined' ? error.message : error.responseJSON.message,
                                    'error');
                            }, 500);
                        });
                    };

                    //  Primero guardar el token antes de enviar adjudicación
                    Services.Post('/concursos/oferente/guardar-token-acceso', {
                        UserToken: User.Token,
                        id: self.IdConcurso()
                    },
                    (resToken) => {
                        if (resToken.success) {
                            postAdjudicacion();
                        } else {
                            $.unblockUI();
                            swal('Error', 'No se pudo generar token de acceso.', 'error');
                        }
                    },
                    (errToken) => {
                        $.unblockUI();
                        swal('Error', 'Error generando token: ' + errToken.message, 'error');
                    });
                });
            }


            /*
            * CheckPay CheckPaySuccess function verify transaction MP
            */
            this.CheckPaySuccess = function() {
                swal({
                    title: 'Pago verificado',
                    text: 'Pago verificado correctamente.',
                    type: 'success'
                }, function() {
                    window.location.reload();
                });
            }

            this.CheckPayError = function() {
                swal({
                    title: 'No se pudo completar la verificación',
                    text: 'Comuníquese con el administrador del sitio o verifique en MercadoPago la transacción.',
                    type: 'error'
                }, function() {
                    window.location.reload();
                });
            }

            this.CheckPay = function() {
                swal({
                    title: '¿Desea comprobar pago?',
                    text: '',
                    type: 'success',
                    closeOnClickOutside: false,
                    showCancelButton: true,
                    cancelButtonText: 'Cancelar',
                    confirmButtonText: 'SI',
                    confirmButtonClass: 'btn btn-success',
                    cancelButtonClass: 'btn btn-danger',
                    buttonsStyling: false,
                }, function() {
                    $.blockUI();
                    var url = '/concursos/oferente/payments/verify';
                    Services.Post(url, {
                            UserToken: User.Token
                        },
                        (response) => {
                            $.unblockUI();
                            self.CheckPaySuccess();
                        },
                        (error) => {
                            $.unblockUI();
                            self.CheckPayError();
                        },
                        null,
                        null
                    );
                });
            }

            this.RejectParticipation = function(action) {
                swal({
                    title: 'Declinar participación',
                    text: '<p>Por favor, indique la razón de su declinación</p> <textarea rows="3" cols="50" class="form-control" style="resize: none;" id="reasonDeclination"></textarea>',
                    type: "input",
                    html: true,
                    showCancelButton: true,
                    closeOnConfirm: false,
                    inputPlaceholder: 'hidden',
                }, function(inputValue) {
                    if (inputValue === false) return false;
                    if (inputValue === "") {
                        swal.showInputError("Necesita describir la razon de su declinación");
                        return false
                    }
                    swal.close();
                    $.blockUI();
                    var data = {
                        IdConcurso: self.IdConcurso(),
                        reason: inputValue
                    };
                    Services.Post('/concursos/oferente/declination', {
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
                                        if (result)
                                            window.location.href = response.data
                                            .redirect;
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
                        },
                        null,
                        null
                    );
                });
            }

            if (self.ChatEnable()){
                var tipo = '{$tipo}';
                if(tipo != 'chat-muro-consultas'){
                    const concurso_id = params[4];
                    var query = '?concurso_id=' + concurso_id + '&user_id=' + User.Id + '&vista=concurso' + '&isClient=' + self.IsClient();
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
                        
                        if(data.tipo == 'newMessageClient' || data.tipo == 'newMessageProvApproved' || data.tipo == 'newRespClient'){
                            checkRead()
                        }
                        // self.HasNewMessage(e.data)
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

            checkRead()
            
        };


        self.DownloadEmptyExcel = function () {
            const data = [];
            const tableRows = document.querySelectorAll("#ListaConcursosEconomicas tbody tr"); // Seleccionar las filas del tbody

            // Iterar sobre cada fila y extraer los valores
            tableRows.forEach((row, index) => {
                // Solo agregar filas que no estén en el rango de las filas 2 a 10
                if (index < 0 || index > 21) {  // Excluir las filas 2 a 10 (índices 1 a 9)
                    const cells = row.querySelectorAll("td"); // Obtener las celdas de la fila
                    data.push({
                        "Item": cells[1]?.innerText.trim() || "", // Columna 1: Item
                        "Unidad": cells[2]?.innerText.trim() || "", // Columna 2: Unidad
                        "Cant Sol": cells[3]?.innerText.trim() || "", // Columna 3: Cant Sol
                        "Cant Min": cells[4]?.innerText.trim() || "", // Columna 4: Cant Min
                        "Precio Unit": cells[5]?.innerText.trim() || "", // Columna 5: Precio Unit
                        "Cant Cot": cells[6]?.innerText.trim() || "", // Columna 6: Cant Cot
                        "Pl. Entr (días)": cells[7]?.innerText.trim() || "", // Columna 7: Pl. Entr
                        "Nro Item": (index - 20) + 1, // Columna 8: Nro Item (autoincremental al final)
                    });
                }
            });

            // Crear y descargar el archivo Excel
            try {
                const worksheet = XLSX.utils.json_to_sheet(data);
                const workbook = XLSX.utils.book_new();

                // Establecer estilo para las columnas
                const range = XLSX.utils.decode_range(worksheet['!ref']); // Obtiene el rango de celdas

                // Colorear las columnas A, B, C, D (índices 0, 1, 2, 3) de gris claro
                for (let row = range.s.r; row <= range.e.r; row++) {
                    for (let col = 0; col < 4; col++) { // A, B, C, D (primeras 4 columnas)
                        const cellAddress = { r: row, c: col };
                        const cellRef = XLSX.utils.encode_cell(cellAddress);
                        if (!worksheet[cellRef]) worksheet[cellRef] = {}; // Si la celda no existe, crearla
                        worksheet[cellRef].s = {
                            fill: { fgColor: { rgb: "D3D3D3" } }, // Color gris claro
                            border: {
                                top: { style: 'thin', color: { rgb: '000000' } },
                                left: { style: 'thin', color: { rgb: '000000' } },
                                bottom: { style: 'thin', color: { rgb: '000000' } },
                                right: { style: 'thin', color: { rgb: '000000' } }
                            }
                        };
                    }
                }

                // Colorear la última columna (índice 7) de azul marino
                for (let row = range.s.r; row <= range.e.r; row++) {
                    const cellAddress = { r: row, c: range.e.c }; // Última columna
                    const cellRef = XLSX.utils.encode_cell(cellAddress);
                    if (!worksheet[cellRef]) worksheet[cellRef] = {}; // Si la celda no existe, crearla
                    worksheet[cellRef].s = {
                        fill: { fgColor: { rgb: "BFF7FF" } }, // Color azul marino
                        border: {
                            top: { style: 'thin', color: { rgb: '000000' } },
                            left: { style: 'thin', color: { rgb: '000000' } },
                            bottom: { style: 'thin', color: { rgb: '000000' } },
                            right: { style: 'thin', color: { rgb: '000000' } }
                        }
                    };
                }

                // Colorear la primera columna (índice 0) de gris claro
                for (let row = range.s.r; row <= range.e.r; row++) {
                    const cellAddress = { r: row, c: 0 }; // Primera columna
                    const cellRef = XLSX.utils.encode_cell(cellAddress);
                    if (!worksheet[cellRef]) worksheet[cellRef] = {}; // Si la celda no existe, crearla
                    worksheet[cellRef].s = {
                        fill: { fgColor: { rgb: "D3D3D3" } }, // Color gris claro
                        border: {
                            top: { style: 'thin', color: { rgb: '000000' } },
                            left: { style: 'thin', color: { rgb: '000000' } },
                            bottom: { style: 'thin', color: { rgb: '000000' } },
                            right: { style: 'thin', color: { rgb: '000000' } }
                        }
                    };
                }

                // Ajustar tamaño de las columnas y filas (solo para la primera fila de las leyendas)
                for (let col = 0; col < range.e.c; col++) {
                    const cellAddress = { r: 0, c: col };
                    const cellRef = XLSX.utils.encode_cell(cellAddress);
                    if (worksheet[cellRef]) {
                        worksheet[cellRef].s = {
                            ...worksheet[cellRef].s,
                            alignment: { horizontal: "center", vertical: "center" } // Alineación centrada
                        };
                    }
                }

                // Ajustar tamaño de las filas para que el texto sea legible
                worksheet['!rows'] = [
                    { hpt: 30 } // Establecer altura de la primera fila (encabezados)
                ];

                // Añadir la hoja al libro
                XLSX.utils.book_append_sheet(workbook, worksheet, "PropuestaEconomica");

                // Descargar el archivo
                XLSX.writeFile(workbook, "Propuesta_Economica.xlsx");
                console.log("Archivo Excel generado desde el DOM con éxito.");
            } catch (error) {
                console.error("Error al generar el archivo Excel desde el DOM:", error);
            }
        };

        self.uploadFile = ko.observable(null);
        self.uploadName = ko.computed(function() {
        return !!self.uploadFile() ? self.uploadFile().name : '-';
        });

        self.uploadFileclear = function() {
            self.uploadFile(null);
        };

        self.uploadFileProcesar = function () {
            console.log("Starting uploadFileProcesar function");

            // Obtén el archivo seleccionado
            var selectedFile = self.uploadFile();
            console.log("Selected file for processing:", selectedFile);

            if (!selectedFile) {
                swal('Error', 'No se ha seleccionado ningún archivo', 'error');
                return;
            }

            // Configuración del lector de archivos
            var fileReader = new FileReader();

            fileReader.onload = (event) => {
                console.log("File read completed");

                // Leer datos binarios del archivo
                var data = event.target.result;

                // Parsear el archivo Excel
                var workbook = XLSX.read(data, { type: "binary" });
                console.log("Workbook created:", workbook);

                // Extraer datos de la hoja 'PropuestaEconomica'
                var excelData = null;

                workbook.SheetNames.forEach(sheet => {
                    console.log("Checking sheet:", sheet);
                    if (sheet === 'PropuestaEconomica') {
                        excelData = XLSX.utils.sheet_to_row_object_array(workbook.Sheets[sheet]);
                        console.log("Data from 'PropuestaEconomica' sheet:", excelData);
                    }
                });

                if (!excelData) {
                    console.log("Error: Sheet 'PropuestaEconomica' not found");
                    swal('Error', 'No se encontró la hoja "PropuestaEconomica"', 'error');
                    return;
                }

                // Cargar los datos en los inputs del formulario
                cargarDatosEnInputs(excelData);
            };

            // Leer el archivo como cadena binaria
            fileReader.readAsBinaryString(selectedFile);
            console.log("Reading file as binary string");
        };

        // Función para cargar los datos en los inputs del formulario
        function cargarDatosEnInputs(excelData) {
    console.log("Datos a cargar en los inputs:", excelData);

    // Seleccionar el tbody de la tabla
    const tabla = document.querySelector("#ListaConcursosEconomicasXD tbody"); 
    if (!tabla) {
        return;
    }

    // Seleccionar todas las filas dentro del tbody
    const filas = tabla.querySelectorAll("tr");
    console.log("Filas de la tabla:", filas);

    // Iterar sobre los datos del Excel y las filas de la tabla
    excelData.forEach((producto, index) => {
        if (index < filas.length) { // Evitar errores si hay más datos que filas en la tabla
            const fila = filas[index];

            // Seleccionar los inputs dentro de esta fila por su clase
            const inputCotizacion = fila.querySelector(".cotizacion");
            const inputCantidad = fila.querySelector(".cantidad");
            const inputFecha = fila.querySelector(".fecha");

            // Verificar si los inputs existen y asignar los valores
            if (inputCotizacion) {
                inputCotizacion.value = producto["Precio Unit"] || '';
                inputCotizacion.dispatchEvent(new Event('input')); // Disparar el evento
                inputCotizacion.dispatchEvent(new Event('change')); // Disparar evento change
            } else {
                console.error("No se encontró el input de cotización en la fila", index + 1);
            }

            if (inputCantidad) {
                // Si la cantidad es menor que la cantidad mínima, dejar el valor en blanco
                if (parseFloat(producto["Cant Cot"]) < parseFloat(producto["Cant Min"])) {
                    inputCantidad.value = ''; // Dejar en blanco si es menor
                } else {
                    inputCantidad.value = producto["Cant Cot"] || '';
                }
                inputCantidad.dispatchEvent(new Event('input')); // Disparar el evento
                inputCantidad.dispatchEvent(new Event('change'));
            } else {
                console.error("No se encontró el input de cantidad en la fila", index + 1);
            }

            if (inputFecha) {
                inputFecha.value = producto["Pl. Entr (días)"] || '';
                inputFecha.dispatchEvent(new Event('change')); 
            } else {
                console.error("No se encontró el input de fecha en la fila", index + 1);
            }
        } 
    });
}


                jQuery(document).ready(function() {


                    $('body').on('change', '#reasonDeclination', function() {
                        // Obtén el valor del textarea
                        var valorTextarea = $(this).val();

                        // Encuentra el input con placeholder="hola" y establece su valor
                        $(this).closest('.sweet-alert')
                            .find('div.form-group input.form-control[placeholder="hidden"]')
                            .val(valorTextarea);
                    });
                    $('body').on('change', '#invitationDeclination', function() {
                        // Obtén el valor del textarea
                        var valorTextarea = $(this).val();

                        // Encuentra el input con placeholder="hola" y establece su valor
                        $(this).closest('.sweet-alert')
                            .find('div.form-group input.form-control[placeholder="hidden"]')
                            .val(valorTextarea);
                    });
                    $.blockUI();
                    
                    var etapas = [
                        'invitacion',
                        'chat-muro-consultas',
                        'tecnica',
                        'economica',
                        'analisis',
                        'resultados',
                        'adjudicado',
                    ];
                    if (etapas.indexOf(params[3]) >= 0) {
                        var url = '/concursos/oferente/' + params[1] + '/' + params[3] + '/' + params[4] + '/detail';
                        
                        var data = {
                            Entity: {
                                Tipo: params[1],
                                Etapa: params[3],
                                Id: params[4]
                            }
                        };

                        Services.Get(url, {
                                UserToken: User.Token,
                                Entity: JSON.stringify(ko.toJS(data))
                            },
                            (response) => {
                                if (response.success) {
                                    window.E = new ConcursoPorEtapaOferente(response.data);
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
                    }
                });

            
            </script>
        {/block}