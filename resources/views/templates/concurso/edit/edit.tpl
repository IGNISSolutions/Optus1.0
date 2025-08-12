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
    <link href="{asset('/global/plugins/datatables/datatables.min.css')}" rel="stylesheet" type="text/css" />
    <link href="{asset('/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css')}" rel="stylesheet"
        type="text/css" />
    <link href="{asset('/global/css/components-rounded.min.css')}" rel="stylesheet" id="style_components" type="text/css" />
{/block}

<!-- SCRIPTS PREVIOS A KNOCKOUT -->
{block 'pre-scripts'}
    <script src="{asset('/global/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js')}" type="text/javascript"></script>
    <script src="{asset('/global/plugins/datatables/datatables.min.js')}" type="text/javascript"></script>
    <script src="{asset('/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js')}" type="text/javascript">
    </script>
    <script src="{asset('/pages/scripts/components-bootstrap-maxlength.js')}" type="text/javascript"></script>
    <script src="{asset('/global/plugins/bootstrap-summernote/summernote.min.js')}" type="text/javascript"></script>
    <script src="{asset('/pages/scripts/components-editors.js')}" type="text/javascript"></script>
    <script>
        var IdUsuario = {$id};
        var TipoUsuario = '{$tipo}';
        var Accion = '{$accion}';
        var IsCopy = '{$isCopy}';
    </script>
    <script src="{asset('/global/scripts/knockout.plugins.js')}" type="text/javascript"></script>

    <script src="{asset('/global/plugins/jquery.pulsate.min.js')}" type="text/javascript"></script>

    <script src="{asset('/global/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js')}" type="text/javascript">
    </script>
    <script src="{asset('/global/plugins/jquery-inputmask/inputmask/inputmask.date.extensions.min.js')}"
        type="text/javascript"></script>
    <script src="{asset('/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js')}" type="text/javascript"></script>
    <script src="{asset('/pages/scripts/jquery.form.js')}" type="text/javascript"></script>

    <script src="{asset('/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js')}" type="text/javascript"></script>
    <script src="{asset('/global/plugins/bootstrap-fileinput-kartik/js/fileinput.min.js')}"></script>
    <script src="{asset('/global/plugins/bootstrap-fileinput-kartik/js/locales/es.js')}"></script>
    <script src="{asset('/global/scripts/xlsx.full.min.js')}"></script>
{/block}

<!-- SCRIPTS POSTERIORES A KNOCKOUT -->
{block 'post-scripts'}
    <script src="{asset('/js/geo.js')}" type="text/javascript"></script>
    <script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCUtr9Ist4jejEMf2czdImyxk_EXoyWBgo&callback=initMapConcurso&libraries=places&v=weekly">
    </script>

    <script>
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
            }
        });
    </script>
{/block}

<!-- VISTA -->
{block 'concurso-edit'}
    <div class=" row" style="margin-top: 20px">
        <div class="col-md-12 ">
            <div class="portlet light bg-inverse">
                <div class="portlet-title">
                    <div class="caption font-red-sunglo">
                        <span class="caption-subject bold uppercase">
                            {$title}
                        </span>
                    </div>
                </div>
                <div class="portlet-body form">
                    {include file='concurso/edit/general.tpl'}
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 ">
            <div class="portlet light bg-inverse">
                <div class="portlet-title">
                    <div class="caption font-red-sunglo">
                        <span class="caption-subject bold uppercase"> Descripción General</span>
                    </div>
                </div>

                <div class="portlet-body form">
                    {include file='concurso/edit/descripcion-general.tpl'}
                </div>
            </div>
        </div>
    </div>

    <!-- ko if: IsGo() -->
    <div class="row">
        <div class="col-md-12 ">
            <div class="portlet light bg-inverse">
                <div class="portlet-title">
                    <div class="caption font-red-sunglo">
                        <span class="caption-subject bold uppercase"> Condiciones Comerciales</span>
                    </div>
                </div>
                <div class="portlet-body form">
                    {include file='concurso/edit/go/commercial-conditions.tpl'}
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 ">
            <div class="portlet light bg-inverse">
                <div class="portlet-title">
                    <div class="caption font-red-sunglo">
                        <span class="caption-subject bold uppercase"> Lugar de Carga y Entrega</span>
                    </div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                </div>

                <div class="portlet-body form">
                    <div class="row">
                        <div class="col-md-6">
                            {include file='concurso/edit/go/location-from.tpl'}
                        </div>
                        <div class="col-md-6">
                            {include file='concurso/edit/go/location-to.tpl'}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="portlet light bg-inverse">
                <div class="portlet-title">
                    <div class="caption font-red-sunglo">
                        <span class="caption-subject bold uppercase"> Seguros y Custodias</span>
                    </div>
                </div>
                <div class="portlet-body form">
                    {include file='concurso/edit/go/insurance.tpl'}
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 ">
            <div class="portlet light bg-inverse">
                <div class="portlet-title">
                    <div class="caption font-red-sunglo">
                        <span class="caption-subject bold uppercase"> Documentación Requerida al Transportista</span>
                    </div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                </div>

                <div class="portlet light bg-inverse">
                    <div class="portlet-body form expandir-1">
                        {include file='concurso/edit/go/documentation.tpl'}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /ko -->

    <div class="row">
        <div class="col-md-12 ">
            <div class="portlet light bg-inverse">
                <!-- ko ifnot: ReadOnly() -->
                <div class="portlet-title">
                    <div class="caption font-red-sunglo">
                        <span class="caption-subject bold uppercase"> Multimedia</span>
                    </div>
                </div>
                <!-- /ko -->
                <div class="portlet-body form">
                    <div class="row">
                        <div class="col-md-12">
                            <!-- ko ifnot: ReadOnly() -->
                            {include file='concurso/edit/multimedia.tpl'}
                            <!-- /ko -->
                        </div>
                    </div>

                    <!-- ko if: IsSobrecerrado() || IsOnline() -->
                    {include file='concurso/edit/location.tpl'}
                    <!-- /ko -->
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 ">
            <div class="portlet light bg-inverse">
                <div class="portlet-title">
                    <div class="caption font-red-sunglo">
                        <span class="caption-subject bold uppercase"> Configuración General</span>
                    </div>
                </div>

                <div class="portlet-body form">
                    {include file='concurso/edit/configuration-general.tpl'}
                </div>
            </div>
        </div>
    </div>

    <!-- ko if: IsSobrecerrado() || IsOnline() -->
    <div class="row" id="ConfiguracionEvaluacion" style="display: block;">
        <div class="col-md-12">
            <div class="portlet light bg-inverse">
                <div class="portlet-title">
                    <div class="caption font-red-sunglo">
                        <span class="caption-subject bold uppercase"> Configuración Precalificación Técnica</span>
                    </div>
                </div>

                <div class="portlet-body form">
                    {include file='concurso/edit/configuration-evaluation.tpl'}
                </div>
            </div>
        </div>
    </div>
    <!-- /ko -->

    <div class="row">
        <div class="col-md-12 ">
            <div class="portlet light bg-inverse">
                <div class="portlet-title">
                    <div class="caption font-red-sunglo">
                        <span class="caption-subject bold uppercase">
                            <!-- ko if: IsGo() || IsSobrecerrado() -->
                            Configuración
                            <!-- /ko -->
                            <!-- ko if: IsOnline() -->
                            Configuración Subasta
                            <!-- /ko -->
                    </div>
                </div>
                <div class="portlet-body form">
                    {include file='concurso/edit/configuration.tpl'}
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 ">
            <div class="portlet light bg-inverse">
                <div class="portlet-title">
                    <div class="caption font-red-sunglo">
                        <span class="caption-subject bold uppercase">
                            Configuración de Fechas
                    </div>
                </div>
                <div class="portlet-body form">
                    {include file='concurso/edit/configuration-dates.tpl'}
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 ">
            <div class="portlet light bg-inverse">
                <div class="portlet-title">
                    <div class="caption font-red-sunglo">
                        <span class="caption-subject bold uppercase">
                            ITEMS DEL CONCURSO
                        </span>
                    </div>
                </div>
                <div class="portlet-body form">
                    {include file='concurso/edit/items.tpl'}
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="form-group pull-left">
                Todos los campos marcados con <b>*</b> son oblgatorios para poder enviar las invitaciones.
            </div>
            <div class="form-group pull-right">
                <button type="button" class="btn btn-success"
                    data-bind="click: sendInvitations, disable: !Entity.HabilitaEnvioInvitaciones()">
                    Enviar Invitaciones
                </button>
            </div>
            <div class="form-group pull-right" style="margin: 0 10px;">
                <button type="button" class="btn btn-primary" data-bind="click: store">
                    Guardar Datos
                </button>
            </div>
        </div>
    </div>
{/block}

<!-- KNOCKOUT JS -->
{block 'knockout' append}
    <script type="text/javascript">
        ko.validation.locale('es-ES');
        ko.validation.init({
            insertMessages: false,
            messagesOnModified: false,
            decorateElement: false,
            errorElementClass: 'wrong-field'
        }, false);

        var plantillaTecnicaTotalPonderacion = function(val) {
            if (val) {
                return val == 100;
            } else {
                return false;
            }
        };

        var ProductMeasurement = function(data) {
            var self = this;

            this.id = ko.observable(data.id);
            this.name = ko.observable(data.name);
        }

        var Product = function(data = null) {
            var self = this;
            this.id = ko.observable(data ? (IsCopy === '1' ? null : data.id) : null);
            this.name = ko.observable(data ? (data.name ? data.name : data["Nombre Producto"]) : null)
                .extend({ required: true });
            this.quantity = ko.observable(data ? (data.quantity ? data.quantity : data["Cantidad Solicitada"]) :
                    null)
                .extend({ required: true });
            this.minimum_quantity = ko.observable(data ? (data.minimum_quantity ? data.minimum_quantity : data[
                "Cantidad Mínima"]) : null).extend({ required: true });
            this.measurement_id = ko.observable(data ? (data.measurement_id ? data.measurement_id : data[
                "Unidad de Medida"]) : null).extend({ required: true });
            this.targetcost = ko.observable(
                data ? (data.targetcost ? data.targetcost : data["Costo Objetivo Unitario"]) : null);
            this.description = ko.observable(data ? (data.description ? data.description : data["Descripcion"]) :
                null);

            

            self.quantity.subscribe((value) => {
                self.minimum_quantity(value);
            });
        }

        var ProductMasive = function(data = null) {
            var self = this;

            this.id = ko.observable(data ? data.id : null);
            this.name = ko.observable(data ? data["Nombre Producto"] : null).extend({ required: true });
            this.quantity = ko.observable(data ? data["Cantidad Solicitada"] : null).extend({ required: true });
            this.minimum_quantity = ko.observable(data ? data["Cantidad Mínima"] : null)
                .extend({ required: true });
            this.measurement_id = ko.observable(data ? data["Unidad de Medida"] : null)
                .extend({ required: true });
            this.targetcost = ko.observable(data ? data["Costo Objetivo Unitario"] : null);
            this.description = ko.observable(data ? data["Descripcion"] : null);

            self.quantity.subscribe((value) => {
                self.minimum_quantity(value);
            });
        }

        var Portrait = function(filename = null) {
            var self = this;

            this.filename = ko.observable(filename ? filename : null);
            this.action = ko.observable(null);
        }

        var Sheet = function(data) {
            var self = this;

            this.id = ko.observable(data.id);
            this.filename = ko.observable(data.filename);
            this.type_id = ko.observable(data.type_id);
            this.type_name = ko.observable(data.type_name);
            this.action = ko.observable(null);
        }

        var TechnicalPayrollItem = function(data, is_template = false) {
            var self = this;

            this.id = ko.observable(data.id);
            this.atributo = ko.observable(data.atributo);
            //this.ponderacion = ko.observable(is_template ? null : data.ponderacion);
            this.ponderacion = ko.observable(is_template ? 0 : data.ponderacion);
            this.puntaje = ko.observable(data.puntaje);
            this.id_plantilla = ko.observable(data.id_plantilla);
        }

        var TechnicalPayroll = function(data, is_template = false) {
            var self = this;
            this.payroll = ko.observableArray();
            this.puntaje_minimo = ko.observable(is_template ? 0 : data[0].puntaje);
            this.total = ko.computed(() => {
                var total = 0;
                self.payroll().forEach(item => {
                    if (item.ponderacion()) {
                        total = total + parseInt(item.ponderacion());
                    }
                });
                return total;
            });
            this.total.extend({
                required: true,
                validation: {
                    validator: plantillaTecnicaTotalPonderacion,
                    message: 'La Ponderación total debe ser 100%.'
                }
            });

            if (data.length > 0) {
                console.log(data)
                data.forEach(item => {
                    if(item.id == 0 || ((item.id_plantilla == 1) && item.id == 1)){
                        return;    
                    }
                    self.payroll.push(new TechnicalPayrollItem(item, is_template));
                });
            }
        }

        var Filters = function(data) {
            var self = this;

            //this.CategoriasList = ko.observableArray(data.categorias_list);
            //this.Categorias = ko.observableArray(data.categorias);
            //this.AreasList = ko.observableArray(data.areas_list);
            this.AreasList = ko.observableArray(data.categorias_con_areas_list);
            this.Areas = ko.observableArray(data.areas);
            this.MaterialesList = ko.observableArray(data.catalogo_de_materiales_list);
            this.Material = ko.observable(data.material);
            //this.CountriesList = ko.observableArray(data.countries_list);
            //this.Countries = ko.observableArray(data.countries);
            //this.ProvincesList = ko.observableArray(data.provinces_list);
            this.ProvincesList = ko.observableArray(data.paises_con_provincias_list);
            this.Provinces = ko.observableArray(data.provinces);
            this.CitiesList = ko.observableArray(data.cities_list);
            this.Cities = ko.observableArray(data.cities);

            self.Provinces.subscribe((Provinces) => {
                if (Provinces.length > 0) {
                    $.blockUI();
                    var url = '/lists/cities';
                    var data = {
                        UserToken: User.Token,
                        Provinces: Provinces
                    };
                    Services.Get(url, data,
                        (response) => {
                            $.unblockUI();
                            self.CitiesList(response.data.list);
                        },
                        (error) => {
                            $.unblockUI();
                        },
                        null,
                        null
                    );
                } else {
                    self.CitiesList([]);
                }
            });
        }

        var Form = function(data, parent) {
            var self = this;

            /**
         * COMMON
         */
            this.Id = ko.observable(data.list.Id);
            this.Tipo = ko.observable(data.list.Tipo);
            this.TypeDescription = ko.observable(data.list.TypeDescription);

            this.Nombre = ko.observable(data.list.Nombre).extend({ required: true });
            this.FechaAlta = ko.observable(data.list.FechaAlta);

            this.ImagePath = ko.observable(data.list.ImagePath).extend({ required: true });
            this.Portrait = ko.observable(new Portrait());
            if (data.list.Portrait) {
                self.Portrait(new Portrait(data.list.Portrait));
            }
            this.SolicitudCompra = ko.observable(data.list.SolicitudCompra);
            this.OrdenCompra = ko.observable(data.list.OrdenCompra);
            this.Resena = ko.observable(data.list.Resena);
            this.DescripcionPortrait = ko.observable(new Portrait());
            if (data.list.DescripcionPortrait) {
                self.DescripcionPortrait(new Portrait(data.list.DescripcionPortrait));
            }
           
            this.AreaUsr = ko.observable(data.list.AreaUsr).extend({ required: true });

            this.DescripcionImagePath = ko.observable(data.list.ImagePath).extend({ required: true });
            this.DescripcionTitle = ko.observable(data.list.DescripcionTitle);
            this.DescripcionDescription = ko.observable(data.list.DescripcionDescription);
            this.DescripcionURL = ko.observable(data.list.DescripcionURL);
            this.DescripcionImagePath = ko.observable(data.list.DescripcionImagePath);
            this.DescriptionLimit = ko.observable(data.list.DescriptionLimit);

            this.Sheets = ko.observableArray([]);
            if (data.list.Sheets.length > 0) {
                data.list.Sheets.forEach(item => {
                    self.Sheets.push(new Sheet(item));
                });
            }

            this.Pais = ko.observable(data.list.Pais);
            this.Provincia = ko.observable(data.list.Provincia);
            this.Localidad = ko.observable(data.list.Localidad);
            this.Direccion = ko.observable(data.list.Direccion);
            this.Cp = ko.observable(data.list.Cp);
            this.Latitud = ko.observable(data.list.Latitud);
            this.Longitud = ko.observable(data.list.Longitud);

            this.Products = ko.observableArray([]);
            if (data.list.Products.length > 0) {
                data.list.Products.forEach(item => {
                    self.Products.push(new Product(item));
                });
            }


            this.TipoConvocatorias = ko.observableArray(data.list.TipoConvocatorias);
            this.TipoConvocatoria = ko.observable(data.list.TipoConvocatoria).extend({ required: true });

            this.FinalizacionConsultas = ko.observable(data.list.FinalizacionConsultas)
                .extend({ required: true });
            this.AceptacionTerminos = ko.observable(data.list.AceptacionTerminos);
            this.SeguroCaucion = ko.observable(data.list.SeguroCaucion);

            this.DiagramaGant = ko.observable(data.list.DiagramaGant);

            this.ListaProveedores = ko.observable(data.list.ListaProveedores);
            this.CertificadoVisitaObra = ko.observable(data.list.CertificadoVisitaObra);

            this.Aperturasobre = ko.observable(data.list.Aperturasobre);
            this.BaseCondicionesFirmado = ko.observable(data.list.BaseCondicionesFirmado);
            this.CondicionesGenerales = ko.observable(data.list.CondicionesGenerales);
            this.PliegoTecnico = ko.observable(data.list.PliegoTecnico);
            this.AcuerdoConfidencialidad = ko.observable(data.list.AcuerdoConfidencialidad);
            this.LegajoImpositivo = ko.observable(data.list.LegajoImpositivo);
            this.AntecedentesReferencias = ko.observable(data.list.AntecedentesReferencias);
            this.ReporteAccidentes = ko.observable(data.list.ReporteAccidentes);
            this.EstructuraCostos = ko.observable(data.list.EstructuraCostos);
            this.Apu = ko.observable(data.list.Apu);
            this.TecnicoOfertas = ko.observable(data.list.TecnicoOfertas);
            this.CondicionPago = ko.observable(data.list.CondicionPago);
            this.EnvioMuestras = ko.observable(data.list.EnvioMuestras);
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
            
            this.OferentesAInvitar = ko.observableArray([]).extend({ required: true });
            this.OferentesAInvitarList = ko.observableArray([]);
            this.UsuarioCalificaReputacion = ko.observableArray([]);
            this.UsuariosCalificanReputacion = ko.observableArray(data.list.UsuariosCalificanReputacion);
            this.UsuariosSupervisores = ko.observableArray(data.list.UsuariosSupervisores);
            this.IncluyePrecalifTecnica = ko.observable(data.list.IncluyePrecalifTecnica);
            self.IncluyePrecalifTecnica.subscribe(
                (value) => {
                    if (value == 'no') {
                        self.PlantillaTecnica(null);
                        self.UsuarioEvaluaTecnica(null);
                    }
                }
            )

            this.PlantillasTecnicas = ko.observable(data.list.PlantillasTecnicas);
            this.PlantillaTecnica = ko.observable(data.list.PlantillaTecnica);
            this.PlantillaTecnica.extend({
                required: {
                    onlyIf: function() { return (self.IncluyePrecalifTecnica() == 'si'); }
                }
            });

            this.PlantillaTecnicaSeleccionada = ko.observable(null);
            if (data.list.PlantillaTecnicaSeleccionada) {
                self.PlantillaTecnicaSeleccionada(new TechnicalPayroll(data.list.PlantillaTecnicaSeleccionada));
            }

            self.PlantillaTecnica.subscribe(function(value) {
                if (!value) {
                    self.PlantillaTecnicaSeleccionada(null);
                    return;
                }
                $.blockUI();
                var url = '/concursos';
                if (params[3] && self.PlantillaTecnicaSeleccionada() && self
                    .IncluyePrecalifTecnica() ===
                    'si') {
                        url += '/payrolls/edit/' + value;
                } else {
                    url += '/payrolls/edit/' + value;
                }
                Services.Get(url, {
                        UserToken: User.Token
                    },
                    (response) => {
                        if (response.success) {
                            self.ListaProveedores('no');
                            self.CertificadoVisitaObra('no');
                            if (value == 1) {
                                self.nom251('no');
                                self.distintivo('no');
                                self.filtros_sanitarios('no');
                                self.repse('no');
                                self.poliza('no');
                                self.primariesgo('no');
                                self.obras_referencias('no')
                                self.obras_organigrama('no')
                                self.obras_equipos('no')
                                self.obras_cronograma('no')
                                self.obras_memoria('no')
                                self.obras_antecedentes('no')
                                self.tarima_ficha_tecnica('no')
                                self.tarima_licencia('no')
                                self.tarima_nom_144('no')
                                self.tarima_acreditacion('no')
                                self.edificio_balance('no')
                                self.edificio_iva('no')
                                self.edificio_cuit('no')
                                self.edificio_brochure('no')
                                self.edificio_organigrama('no')
                                self.edificio_organigrama_obra('no')
                                self.edificio_subcontratistas('no')
                                self.edificio_gestion('no')
                                self.edificio_maquinas('no')
                            }
                            if (value == 2) {
                                self.SeguroCaucion('no');
                                self.DiagramaGant('no');
                                self.BaseCondicionesFirmado('no');
                                self.CondicionesGenerales('no');
                                self.PliegoTecnico('no');
                                self.AcuerdoConfidencialidad('no');
                                self.LegajoImpositivo('no');
                                self.AntecedentesReferencias('no');
                                self.ReporteAccidentes('no');
                                self.EnvioMuestras('no');
                                self.obras_referencias('no')
                                self.obras_organigrama('no')
                                self.obras_equipos('no')
                                self.obras_cronograma('no')
                                self.obras_memoria('no')
                                self.obras_antecedentes('no')
                                self.tarima_ficha_tecnica('no')
                                self.tarima_licencia('no')
                                self.tarima_nom_144('no')
                                self.tarima_acreditacion('no')
                                self.edificio_balance('no')
                                self.edificio_iva('no')
                                self.edificio_cuit('no')
                                self.edificio_brochure('no')
                                self.edificio_organigrama('no')
                                self.edificio_organigrama_obra('no')
                                self.edificio_subcontratistas('no')
                                self.edificio_gestion('no')
                                self.edificio_maquinas('no')
                            }
                            if (value == 3) {
                                self.SeguroCaucion('no');
                                self.DiagramaGant('no');
                                self.BaseCondicionesFirmado('no');
                                self.CondicionesGenerales('no');
                                self.PliegoTecnico('no');
                                self.AcuerdoConfidencialidad('no');
                                self.LegajoImpositivo('no');
                                self.AntecedentesReferencias('no');
                                self.ReporteAccidentes('no');
                                self.EnvioMuestras('no');
                                self.nom251('no');
                                self.distintivo('no');
                                self.filtros_sanitarios('no');
                                self.repse('no');
                                self.poliza('no');
                                self.primariesgo('no');
                                self.tarima_ficha_tecnica('no');
                                self.tarima_licencia('no');
                                self.tarima_nom_144('no');
                                self.tarima_acreditacion('no');
                                self.edificio_balance('no');
                                self.edificio_iva('no');
                                self.edificio_cuit('no');
                                self.edificio_brochure('no');
                                self.edificio_organigrama('no');
                                self.edificio_organigrama_obra('no');
                                self.edificio_subcontratistas('no');
                                self.edificio_gestion('no');
                                self.edificio_maquinas('no');
                            }
                            if (value == 4) {
                                self.SeguroCaucion('no');
                                self.DiagramaGant('no');
                                self.BaseCondicionesFirmado('no');
                                self.CondicionesGenerales('no');
                                self.PliegoTecnico('no');
                                self.AcuerdoConfidencialidad('no');
                                self.LegajoImpositivo('no');
                                self.AntecedentesReferencias('no');
                                self.ReporteAccidentes('no');
                                self.EnvioMuestras('no');
                                self.nom251('no');
                                self.distintivo('no');
                                self.filtros_sanitarios('no');
                                self.repse('no');
                                self.poliza('no');
                                self.primariesgo('no');
                                self.obras_referencias('no')
                                self.obras_organigrama('no')
                                self.obras_equipos('no')
                                self.obras_cronograma('no')
                                self.obras_memoria('no')
                                self.obras_antecedentes('no')
                                self.edificio_balance('no')
                                self.edificio_iva('no')
                                self.edificio_cuit('no')
                                self.edificio_brochure('no')
                                self.edificio_organigrama('no')
                                self.edificio_organigrama_obra('no')
                                self.edificio_subcontratistas('no')
                                self.edificio_gestion('no')
                                self.edificio_maquinas('no')
                            }
                            if (value == 5) {
                                self.SeguroCaucion('no');
                                self.DiagramaGant('no');
                                self.BaseCondicionesFirmado('no');
                                self.CondicionesGenerales('no');
                                self.PliegoTecnico('no');
                                self.AcuerdoConfidencialidad('no');
                                self.LegajoImpositivo('no');
                                self.AntecedentesReferencias('no');
                                self.ReporteAccidentes('no');
                                self.EnvioMuestras('no');
                                self.nom251('no');
                                self.distintivo('no');
                                self.filtros_sanitarios('no');
                                self.repse('no');
                                self.poliza('no');
                                self.primariesgo('no');
                                self.tarima_ficha_tecnica('no');
                                self.tarima_licencia('no');
                                self.tarima_nom_144('no');
                                self.tarima_acreditacion('no');
                                self.obras_referencias('no')
                                self.obras_organigrama('no')
                                self.obras_equipos('no')
                                self.obras_cronograma('no')
                                self.obras_memoria('no')
                                self.obras_antecedentes('no')
                            }
                            if (response.data.list.PlantillaTecnicaSeleccionada.length > 0) {
                                self.PlantillaTecnicaSeleccionada(new TechnicalPayroll(response.data
                                    .list
                                    .PlantillaTecnicaSeleccionada, true));
                            }
                        }
                        $.unblockUI();
                    },
                    (error) => {
                        $.unblockUI();
                    },
                    null,
                    null
                );
            });

            this.UsuariosEvaluanTecnica = ko.observableArray(data.list.UsuariosEvaluanTecnica);
            this.UsuarioEvaluaTecnica = ko.observableArray([]);
            this.UsuarioEvaluaTecnica.extend({
                required: {
                    onlyIf: function() { return (self.IncluyePrecalifTecnica() == 'si'); }
                }
            });


            /// CONFIGURATION-DATES OBSERVABLES ///
            this.FechaLimite = ko.observable(data.list.FechaLimite).extend({ required: true });
            this.FechaLimiteTecnica = ko.observable(data.list.FechaLimiteTecnica);
            this.FechaLimiteEconomicas = ko.observable(data.list.FechaLimiteEconomicas).extend({ required: true });
            this.ronda_actual = ko.observable(data.list.ronda_actual);
            this.segunda_ronda_fecha = ko.observable(data.list.segunda_ronda_fecha).extend({ required: true });
            this.tercera_ronda_fecha_limite = ko.observable(data.list.tercera_ronda_fecha_limite).extend({ required: true });
            this.cuarta_ronda_fecha_limite = ko.observable(data.list.cuarta_ronda_fecha_limite).extend({ required: true });
            this.quita_ronda_fecha_limite = ko.observable(data.list.quita_ronda_fecha_limite).extend({ required: true });

            this.isRoundEditable = function(roundNumber) {
                if (self.ronda_actual() == 'no') {
                    return true;
                }
                return self.ronda_actual() === roundNumber;
            };


            this.isRoundVisible = function(roundNumber) {
                return self.ronda_actual() >= roundNumber;
            };

            this.FechaLimiteTecnica.extend({
                required: {
                    onlyIf: function() { return (self.IncluyePrecalifTecnica() == 'si'); }
                }
            });


            //Get the last visible round date
            function getLastVisibleRoundDate() {
                if (self.isRoundVisible(5)) return self.quita_ronda_fecha_limite;
                if (self.isRoundVisible(4)) return self.cuarta_ronda_fecha_limite;
                if (self.isRoundVisible(3)) return self.tercera_ronda_fecha_limite;
                if (self.isRoundVisible(2)) return self.segunda_ronda_fecha;
                return null;
            }

            //Subscribe to changes on the last visible round
            function setupFechaLimiteEconomicasSync() {
                const lastRound = getLastVisibleRoundDate();
                if (!lastRound) return;

                //Clean up previous subscriptions (just in case)
                if (self._fechaLimiteEconomicasSub) {
                    self._fechaLimiteEconomicasSub.dispose();
                }

                self._fechaLimiteEconomicasSub = lastRound.subscribe(function(newValue) {
                    self.FechaLimiteEconomicas(newValue);
                });

                self.FechaLimiteEconomicas(lastRound());
            }

            //Re-run this logic whenever ronda_actual changes
            self.ronda_actual.subscribe(setupFechaLimiteEconomicasSync);

            //Initial run
            setupFechaLimiteEconomicasSync();

            ///

            this.FinalizarSiOferentesCompletaronEconomicas = ko.observable(data.list.FinalizarSiOferentesCompletaronEconomicas);
            this.OfertasParcialesPermitidas = ko.observable(data.list.OfertasParcialesPermitidas);
            this.OfertasParcialesCantidadMin = ko.observable(data.list.OfertasParcialesCantidadMin);

            this.Monedas = ko.observableArray(data.list.Monedas);
            this.Moneda = ko.observable(data.list.Moneda).extend({ required: true });
            this.UsuarioSupervisor = ko.observable(data.list.UsuarioSupervisor);
            this.HabilitaEnvioInvitaciones = ko.observable(data.list.HabilitaEnvioInvitaciones);
            this.Countries = ko.observableArray(data.list.Countries);
            this.CountrySelected = ko.observable();
            self.CountrySelected.subscribe(function(value) {
                var newCountry = self.Countries().find(({ code }) => code === value);
                var oldCountry = self.Pais();
                if (newCountry.text != oldCountry && oldCountry != '') {
                    self.Pais(newCountry.text);
                    self.Provincia("");
                    self.Localidad("");
                    self.Direccion("");
                    self.Cp("");
                }
            })
            this.ManOnTheMap = ko.observable();
            self.Direccion.subscribe(function(newValue) {
                self.ManOnTheMap(false)
                setAddress();
            });


            /**
         * ONLINE
         */
            this.InicioSubasta = ko.observable(data.list.InicioSubasta).extend({ required: true });
            this.Duracion = ko.observable(data.list.Duracion).extend({ required: true });
            this.TiempoAdicional = ko.observable(data.list.TiempoAdicional).extend({ required: true });
            this.TiposValoresOfertar = ko.observableArray(data.list.TiposValoresOfertar);
            this.TipoValorOfertar = ko.observable(data.list.TipoValorOfertar);
            this.Chat = ko.observable(data.list.Chat);
            this.VerNumOferentesParticipan = ko.observable(data.list.VerNumOferentesParticipan);
            this.VerOfertaGanadora = ko.observable(data.list.VerOfertaGanadora);
            this.VerRanking = ko.observable(data.list.VerRanking);
            this.VerTiempoRestante = ko.observable(data.list.VerTiempoRestante);
            this.PermitirAnularOferta = ko.observable(data.list.PermitirAnularOferta);
            this.SubastaVistaCiega = ko.observable(data.list.SubastaVistaCiega);
            this.PrecioMinimo = ko.observable(data.list.PrecioMinimo);
            this.PrecioMaximo = ko.observable(data.list.PrecioMaximo);
            this.SoloOfertasMejores = ko.observable(data.list.SoloOfertasMejores);
            this.UnidadesMinimas = ko.observable(data.list.UnidadesMinimas);
            this.UnidadMinima = ko.observable(data.list.UnidadMinima);

            /**
         * GO
         */
            this.CotizarSeguro = ko.observable(data.list.CotizarSeguro);
            this.SumaAsegurada = ko.observable(data.list.SumaAsegurada);
            this.CotizarArmada = ko.observable(data.list.CotizarArmada);
            this.ClausulaBeneficiario = ko.observable(data.list.ClausulaBeneficiario);
            this.GoLoadTypes = ko.observableArray(data.list.GoLoadTypes);
            this.GoLoadType = ko.observable(data.list.GoLoadType);
            this.Peso = ko.observable(data.list.Peso);
            this.Ancho = ko.observable(data.list.Ancho);
            this.Largo = ko.observable(data.list.Largo);
            this.Alto = ko.observable(data.list.Alto);
            this.UnidadesBultos = ko.observable(data.list.UnidadesBultos);
            this.PaymentMethod = ko.observable(data.list.PaymentMethod);
            this.PaymentMethods = ko.observableArray(data.list.PaymentMethods);
            this.PlazoPago = ko.observable(data.list.PlazoPago);
            this.NombreDesde = ko.observable(data.list.NombreDesde);
            this.NombreHasta = ko.observable(data.list.NombreHasta);
            this.FechaDesde = ko.observable(data.list.FechaDesde);
            this.FechaHasta = ko.observable(data.list.FechaHasta);
            this.CalleDesde = ko.observable(data.list.CalleDesde);
            this.CalleHasta = ko.observable(data.list.CalleHasta);
            this.NumeracionDesde = ko.observable(data.list.NumeracionDesde);
            this.NumeracionHasta = ko.observable(data.list.NumeracionHasta);
            this.ProvinciaDesdeSelect = ko.observable(null);
            this.ProvinciaHastaSelect = ko.observable(null);
            this.CiudadDesdeSelect = ko.observable(null);
            this.CiudadHastaSelect = ko.observable(null);

            this.DriverDocuments = ko.observableArray(data.list.DriverDocuments),
            this.DriverDocumentsSelected = ko.observableArray(data.list.DriverDocumentsSelected);
            this.VehicleDocuments = ko.observableArray(data.list.VehicleDocuments),
            this.VehicleDocumentsSelected = ko.observableArray(data.list.VehicleDocumentsSelected);
            this.Amount = ko.observableArray(data.list.Amount);
            this.AmountSelect = ko.observable(data.list.AmountSelect);
            this.Ratio = ko.observable(data.list.Ratio);
            this.ClausulaArt = ko.observable(data.list.ClausulaArt);
            this.CuitDoc = ko.observable(data.list.CuitDoc);
            this.RazonSocialDoc = ko.observable(data.list.RazonSocialDoc);
            this.ClausulaBeneficiario = ko.observable(data.list.ClausulaBeneficiario);
            this.CuitBeneficiario = ko.observable(data.list.CuitBeneficiario);
            this.RazonSocialBeneficiario = ko.observable(data.list.RazonSocialBeneficiario);
            this.AdditionalDriverDocuments = ko.observableArray(data.list.AdditionalDriverDocuments);
            this.AdditionalVehicleDocuments = ko.observableArray(data.list.AdditionalVehicleDocuments);
            this.AmountsRatio = ko.observable(0);
            this.AmountsVisible = ko.observable(false);
        }

        var Concurso = function(data) {
            var self = this;

            this.Filters = ko.observable();
            this.Breadcrumbs = ko.observableArray(data.breadcrumbs);
            this.IsGo = ko.observable(data.list.IsGo);
            this.IsSobrecerrado = ko.observable(data.list.IsSobrecerrado);
            this.IsOnline = ko.observable(data.list.IsOnline);
            this.ReadOnly = ko.observable(data.list.ReadOnly);

            this.BloquearInvitacionOferentes = ko.observable(data.list.BloquearInvitacionOferentes);
            this.BloquearCamposTecnica = ko.observable(!!data.list.BloquearCamposTecnica);

            this.FilePath = ko.observable(data.list.FilePath);
            this.ProductMeasurementList = ko.observableArray(data.list.ProductMeasurementList);
            this.NewProduct = ko.observable(new Product());
            this.ProvinciasDesde = ko.observableArray(data.list.ProvinciasDesde);
            this.ProvinciasHasta = ko.observableArray(data.list.ProvinciasHasta);
            this.CiudadesDesde = ko.observableArray([]);
            this.CiudadesHasta = ko.observableArray([]);
            this.areasDisponibles = ko.observableArray([
                { id: 1, text: 'Administración' },
                { id: 2, text: 'Comercial' },
                { id: 3, text: 'Compras' },
                { id: 4, text: 'Almacenes' },
                { id: 5, text: 'Logística' },
                { id: 6, text: 'Produccion' },
                { id: 7, text: 'Mantenimiento' },
                { id: 8, text: 'Calidad' },
                { id: 9, text: 'Seguridad de las Personas' },
                { id: 10, text: 'Medio Ambiente' },
                { id: 11, text: 'Oficina Técnica' },
                { id: 12, text: 'Informática' },
            ]);

            this.Entity = new Form(data, self);
            this.FirstTimeProvinciaDesdeSelect = true;
            self.Entity.ProvinciaDesdeSelect.subscribe((Province) => {
                if (Province > 0) {
                    $.blockUI();
                    var url = '/lists/cities';
                    var body = {
                        UserToken: User.Token,
                        Provinces: [Province]
                    };
                    Services.Get(url, body,
                        (response) => {
                            $.unblockUI();
                            if (self.FirstTimeProvinciaDesdeSelect) {
                                self.Entity.CiudadDesdeSelect(data.list.CiudadDesdeSelect);
                                self.FirstTimeProvinciaDesdeSelect = false;
                            } else {
                                self.Entity.CiudadDesdeSelect(null);
                            }
                            self.CiudadesDesde(response.data.list);
                        },
                        (error) => {
                            $.unblockUI();
                        },
                        null,
                        null
                    );
                } else {
                    self.Entity.CiudadDesdeSelect(null);
                    self.CiudadesDesde([]);
                }
            });

            this.FirstTimeProvinciaHastaSelect = true;
            self.Entity.ProvinciaHastaSelect.subscribe((Province) => {
                if (Province > 0) {
                    $.blockUI();
                    var url = '/lists/cities';
                    var body = {
                        UserToken: User.Token,
                        Provinces: [Province]
                    };
                    Services.Get(url, body,
                        (response) => {
                            $.unblockUI();
                            if (self.FirstTimeProvinciaHastaSelect) {
                                self.Entity.CiudadHastaSelect(data.list.CiudadHastaSelect);
                                self.FirstTimeProvinciaHastaSelect = false;
                            } else {
                                self.Entity.CiudadHastaSelect(null);
                            }
                            self.CiudadesHasta(response.data.list);
                        },
                        (error) => {
                            $.unblockUI();
                        },
                        null,
                        null
                    );
                } else {
                    self.Entity.CiudadHastaSelect(null);
                    self.CiudadesHasta([]);
                }
            });

            self.Entity.ProvinciaDesdeSelect(data.list.ProvinciaDesdeSelect);
            self.Entity.ProvinciaHastaSelect(data.list.ProvinciaHastaSelect);

            this.FirstTimeFilter = true;
            this.filter = function() {
                $.blockUI();
                var url = '/concursos/invitations/filter';
                Services.Post(url, {
                        UserToken: User.Token,
                        Filters: JSON.stringify(ko.toJS(self.Filters))
                    },
                    (response) => {
                        $.unblockUI();
                        if (response.success) {
                            self.Entity.OferentesAInvitar([]);
                            self.Entity.OferentesAInvitarList([]);
                            if (response.data.results.length > 0) {
                                response.data.results.forEach(item => {
                                    self.Entity.OferentesAInvitarList.push(item);
                                });
                                if (self.FirstTimeFilter) {
                                    self.Entity.OferentesAInvitar(data.list.OferentesAInvitar);
                                }
                            }
                        }
                        self.FirstTimeFilter = true;
                    },
                    (error) => {
                        $.unblockUI();
                        swal('Error', error.message, 'error');
                    }
                );
            }


            this.filtermaterial = function() {
                for (let categoria of self.Filters().MaterialesList()) {
                    var material = categoria.meteriales.find(element => element.id.toString() === self
                        .Filters()
                        .Material().toString());
                    if (material !== undefined) {
                        self.NewProduct().name(material.text.toString());
                        self.NewProduct().measurement_id(material.unidad);
                        self.NewProduct().targetcost(material.targetcost);
                        break;
                    }
                }
                //self.NewProduct().name(self.Filters().Material().toString());
            }

            this.initFilters = function() {
                self.Filters(new Filters(data.filters));
            }

            this.clearFilters = function() {
                self.initFilters();
                self.filter();
            }

            this.addAll = function() {
                var newResults = [];
                self.Entity.OferentesAInvitarList().forEach(item => {
                    newResults.push(item.id);
                });
                self.Entity.OferentesAInvitar(newResults);
            }

            this.removeAll = function() {
                self.Entity.OferentesAInvitar([]);
            }

            self.PersonalAmountDocument = ko.computed(function() {
                var ops = self.Entity.DriverDocuments();
                var opsSelect = self.Entity.DriverDocumentsSelected();
                var valor = [];
                for (var i in ops) {
                    for (var s in opsSelect) {
                        if (ops[i].id == opsSelect[s]) {
                            valor.push(ops[i].code);
                        }
                    }
                }
                if (valor.find(element => element === 'OPTUS_CUOTA AP')) {
                    self.Entity.AmountsVisible(true);
                } else if (valor.find(element => element === 'OPTUS_CUOTA AP') === undefined) {
                    self.Entity.AmountsVisible(false);
                }
            }, this);

            self.Entity.DriverDocumentsSelected.subscribe(function(value) {
                var ops = self.Entity.DriverDocuments();
                var opsSelect = self.Entity.DriverDocumentsSelected();
                var valor = [];
                for (var i in ops) {
                    for (var s in opsSelect) {
                        if (ops[i].id == opsSelect[s]) {
                            valor.push(ops[i].code);
                        }
                    }
                }
                if (valor.find(element => element === 'OPTUS_CUOTA AP')) {
                    self.Entity.AmountsVisible(true);
                } else if (valor.find(element => element === 'OPTUS_CUOTA AP') === undefined) {
                    self.Entity.AmountsVisible(false);
                }
            });

            self.Entity.AmountSelect.subscribe(function(value) {
                var ops = self.Entity.Amount();
                var opsSelect = self.Entity.AmountSelect();
                for (var i in ops) {
                    for (var s in opsSelect) {
                        if (ops[i].id == opsSelect[s]) {
                            var ratio = (ops[i].ratio * 10) / 100;
                            if (isNaN(ratio)) {
                                self.Entity.AmountsRatio('');
                            } else {
                                self.Entity.AmountsRatio(ratio);
                            }
                        }
                    }
                }
            });

            this.validarform = function() {

                return (
                    this.Entity.Nombre.isValid() &&
                    this.Entity.OferentesAInvitar.isValid() &&
                    this.Entity.FechaLimite.isValid() &&
                    this.Entity.FinalizacionConsultas.isValid() &&
                    this.Entity.FechaLimiteTecnica.isValid() &&
                    this.Entity.PlantillaTecnica.isValid() &&
                    this.Entity.UsuarioEvaluaTecnica.isValid() &&
                    this.Entity.Moneda.isValid() &&
                    (
                        self.Entity.PlantillaTecnicaSeleccionada() ? self.Entity
                        .PlantillaTecnicaSeleccionada()
                        .total.isValid() : true
                    )
                );

            };

            this.validarformProduct = function() {
                return (
                    this.NewProduct().name.isValid() &&
                    this.NewProduct().quantity.isValid() &&
                    this.NewProduct().minimum_quantity.isValid() &&
                    this.NewProduct().measurement_id.isValid()
                );
            };

            self.isValid = ko.computed(function() {
                return ko.validation.group(
                    self, {
                        observable: true,
                        deep: true
                    }).showAllMessages(true);
            }, self);

            self.IsDisableIncluyePrecalifTecnica = ko.computed(function() {
                if (self.Entity.IncluyePrecalifTecnica() == 'no') {
                    $("#idFechaLimiteTecnica").removeClass("required");
                    $("#idPlantillaTecnica").removeClass("required");
                    $("#idUsuarioEvaluaTecnica").removeClass("required");
                    return true;
                } else {
                    $("#idFechaLimiteTecnica").addClass("required");
                    $("#idPlantillaTecnica").addClass("required");
                    $("#idUsuarioEvaluaTecnica").addClass("required");
                    return false;
                }
            });

            self.IsDisableSuper = ko.computed(function() {
                if (self.Entity.concurso_fiscalizado() == 'no') {
                    $("#idSuper").removeClass("required");
                    return true;
                } else {
                    $("#idSuper").addClass("required");
                    return false;
                }
            });
            // Suscribirse a cambios en la opción de fiscalizador
            self.Entity.concurso_fiscalizado.subscribe(function(value) {
                if (value === 'no') {
                    self.Entity.UsuarioSupervisor(null); // Restablecer el valor del fiscalizador
                }
            });


            self.IsdisableSave = ko.computed(function() {
                return !self.validarform();
            });

            self.IsVisibleSaveProduct = ko.computed(function() {
                return self.validarformProduct();
            });

            /**
             * GUARDA DATOS CONCURSO
             */
            self.store = function() {
                $.blockUI();
                var url = '/concursos/' + params[1] + '/save' + (params[3] ? '/' + params[3] : '');
                var data = {
                    UserToken: User.Token,
                    Entity: JSON.stringify(ko.toJS(self.Entity))
                };
                
                Services.Post(url, data,
                    (response) => {
                        if (response.success) {
                            $.unblockUI();
                            swal({
                                title: 'Hecho',
                                text: response.message,
                                type: 'success',
                                closeOnClickOutside: false,
                                closeOnConfirm: true,
                                confirmButtonText: 'Aceptar',
                                confirmButtonClass: 'btn btn-success'
                            }, function(result) {
                                window.history.back();
                            });
                        } else {
                            $.unblockUI();
                            swal('Error', error.message || 'Se produjo un error inesperado.', 'error');
                        }
                    },
                    (error) => {
                        $.unblockUI();
                        swal('Error', error.message || 'Se produjo un error inesperado.', 'error');
                    },
                    null,
                    null
                );
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
                        swal('Error', typeof error.message != 'undefined' ? error.message : error
                            .responseJSON
                            .message, 'error');
                    },
                    null,
                    null
                );
            }

            this.sendInvitations = function() {
                swal({
                    title: '¿Desea enviar las invitaciones?',
                    text: 'Esta a punto de enviar las notificaciones para este concurso.',
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
                        var url = '/concursos/invitations/send';
                        Services.Post(url, {
                                UserToken: User.Token,
                                IdConcurso: self.Entity.Id()
                            },
                            (response) => {
                                swal.close();
                                $.unblockUI();
                                setTimeout(function() {
                                    if (response.success) {
                                        swal('Hecho', response.message, 'success');
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

            this.ProductAddOrDelete = function(action, product = null) {
                // Fetch table
                var newProducts = [];

                self.Entity.Products().forEach(item => {
                    newProducts.push(item);
                });

                switch (action) {
                    case 'add':
                        $.blockUI();
                        // Add new Product
                        var newProduct = self.NewProduct();
                        
                        newProducts.push(newProduct);
                        // Check
                        var body = {
                            Id: self.Entity.Id(),
                            Products: newProducts,
                            IsSobrecerrado: self.IsSobrecerrado(),
                            IsOnline: self.IsOnline(),
                            IsGo: self.IsGo()
                        };
                        var data = {
                            UserToken: User.Token,
                            Data: JSON.stringify(ko.toJS(body))
                        };
                        Services.Post('/concursos/products/check', data,
                            (response) => {
                                if (response.success) {
                                    // Reset inputs
                                    self.NewProduct(new Product());
                                    // Update table
                                    self.Entity.Products.removeAll();
                                    self.Entity.Products(newProducts);
                                    $.unblockUI();
                                } else {
                                    $.unblockUI();
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
                        break;
                    case 'delete':
                        // Delete from table
                        const index = newProducts.findIndex(i => i === product);
                        newProducts.splice(index, 1);
                        // Update table
                        self.Entity.Products.removeAll();
                        self.Entity.Products(newProducts);
                        break;
                }
            };

            self.initFilters();
            self.filter(true);


            this.measurementName = function(measurement_id) {

                var measurement = ko.utils.arrayFirst(this.ProductMeasurementList(), function(item) {
                    return item.id == measurement_id();
                });
                return measurement ? measurement.text : "";
            }

        };

        var matchStart = function(params, data) {
            // If there are no search terms, return all of the 
            if ($.trim(params.term) === '') {
                return data;
            }

            // Skip if there is no 'children' property
            if (typeof data.children === 'undefined') {
                return null;
            }

            // `data.children` contains the actual options that we are matching against
            var filteredChildren = [];

            $.each(data.children, function(idx, child) {
                if (data.text.toUpperCase().indexOf(params.term.toUpperCase()) !== -1 || child.text
                    .toUpperCase().indexOf(params.term.toUpperCase()) !== -1 && filteredChildren
                    .indexOf(child) === -1) {
                    filteredChildren.push(child);
                }
            });

            // If we matched any of the timezone group's children, then set the matched children on the group and return the group object
        if (filteredChildren.length) {
            var modifiedData = $.extend({}, data, true);
            modifiedData.children = filteredChildren;

            // You can return modified objects from here
            // This includes matching the `children` how you want in nested data sets
            return modifiedData;
        }

        // Return `null` if the term should not be displayed
        return null;
    };

    var is_init = true;

    self.downloadPlantillaExcel = function() {
        var materiales = this.Filters().MaterialesList();
        var unidades = this.ProductMeasurementList();

        var materialesList = materiales.flatMap(item => item.meteriales.map(m => ({
            text: m.text,
            targetcost: m.targetcost,
            unidad: m.unidad
        })));

        let sheetMaterials = [];
        let sheetUnits = [];

        for (let i = 0; i < materialesList.length; i++) {
            stringArray = materialesList[i].text + ',' + materialesList[i].targetcost + ',' + materialesList[i]
                .unidad
            sheetMaterials.push(stringArray);
        }
        for (let i = 0; i < unidades.length; i++) {
            stringArray = unidades[i].id + ',' + unidades[i].text
            sheetUnits.push(stringArray);
        }


        var wb = XLSX.utils.book_new();
        wb.Props = {
            Title: "Plantilla para importación de Productos a Cotizar",
            Subject: "Plantilla para importación de Productos a Cotizar",
            Author: "Optus",
            CreatedDate: new Date()
        };
        wb.SheetNames.push("Productos");
        wb.SheetNames.push("Lista - Productos");
        wb.SheetNames.push("Lista - Unidades");
        var ws_data = [
            [
                'Nombre Producto',
                'Descripcion',
                'Cantidad Solicitada',
                'Cantidad Mínima',
                'Unidad de Medida',
                'Costo Objetivo Unitario'
            ]
        ];

        var ws_dataLC = [
            [
                'Nombre Producto',
                'Costo Objetivo Unitario',
                'Unidad Id'
            ]
        ];
        materialesList.forEach(item => {
            var vItem = [];
            vItem.push(item['text'], item['targetcost'], item['unidad']);
            ws_dataLC.push(vItem);
        });


        var ws_dataLU = [
            [
                'Unidad Id',
                'Nombre'
            ]
        ];
        unidades.forEach(item => {
            var vItem = [];
            vItem.push(item['id'], item['text']);
            ws_dataLU.push(vItem);
        });

        var ws = XLSX.utils.aoa_to_sheet(ws_data);
        var wsLC = XLSX.utils.aoa_to_sheet(ws_dataLC);
        var wsLU = XLSX.utils.aoa_to_sheet(ws_dataLU);

        if (!ws.A1.c) ws.A1.c = [];
        ws.A1.c.hidden = true;
        ws.A1.c.push({
            a: "SheetJS",
            t: "Ingrese el nombre del producto (Obligatorio)"
        });

        if (!ws.B1.c) ws.B1.c = [];
        ws.B1.c.hidden = true;
        ws.B1.c.push({
            a: "SheetJS",
            t: "Ingrese la Descripción del producto"
        });

        if (!ws.C1.c) ws.C1.c = [];
        ws.C1.c.hidden = true;
        ws.C1.c.push({
            a: "SheetJS",
            t: "Ingrese la Cantidad a cotizar (Obligatorio)"
        });

        if (!ws.D1.c) ws.D1.c = [];
        ws.D1.c.hidden = true;
        ws.D1.c.push({
            a: "SheetJS",
            t: "Ingrese la Cantidad minima que puede ser ofertada (Obligatorio)"
        });

        if (!ws.E1.c) ws.E1.c = [];
        ws.E1.c.hidden = true;
        ws.E1.c.push({
            a: "SheetJS",
            t: "Ingrese el Id de la unidad de medida, puede ver la lista en la Hoja Lista - Unidades  (Obligatorio)"
        });

        if (!ws.F1.c) ws.F1.c = [];
        ws.F1.c.hidden = true;
        ws.F1.c.push({
            a: "SheetJS",
            t: "Ingrese el costo objetivo por unidad"
        });

        wb.Sheets["Productos"] = ws;
        wb.Sheets["Lista - Productos"] = wsLC;
        wb.Sheets["Lista - Unidades"] = wsLU;

        XLSX.writeFile(wb, 'Productos_Importacion.xlsx');

    };

    self.uploadFile = ko.observable(null);
    self.uploadName = ko.computed(function() {
        return !!self.uploadFile() ? self.uploadFile().name : '-';
    });

    self.uploadFileclear = function() {
        self.uploadFile(null);
    };

    self.uploadFileProcesar = function() {
        var file = self.uploadFile();
        var formData = new FormData();
        formData.append('file', file);

        //alert(self.uploadFile().name);
        //alert(window.location.pathname);

        var selectedFile;
        selectedFile = self.uploadFile();

        var data = [{
            "name": "jayanth",
            "data": "scd",
            "abc": "sdef"
        }];
        var datatype = {
            "type": "binary"
        };

        var rowObject = null;
        var loads = [];

        XLSX.utils.json_to_sheet(data, 'out.xlsx');
        if (selectedFile) {
            var fileReader = new FileReader();
            fileReader.readAsBinaryString(selectedFile);

            rowObject = fileReader.onload = (event) => {
                var data = event.target.result;
                var workbook = XLSX.read(data, datatype);
                var resultRowObject = null;

                workbook.SheetNames.forEach(sheet => {
                    if (sheet === 'Productos')
                        resultRowObject = XLSX.utils.sheet_to_row_object_array(workbook.Sheets[
                            sheet]);
                });
                if (resultRowObject === null) {
                    swal('Error', 'No se encontro la hoja "Productos"', 'error');
                } else {
                    var newProducts = [];

                    this.Entity.Products().forEach(item => {
                        newProducts.push(item);
                    });
                    resultRowObject.forEach(item => {
                        product = new ProductMasive(item)
                        newProducts.push(product);
                    });
                    this.Entity.Products.removeAll();
                    this.Entity.Products(newProducts);
                }
                self.uploadFile(null);
            };
        }
    };

    jQuery(document).ready(function() {
        $.blockUI();
        var action = '';
        var concurso_id = '{$concurso_id}'

        switch (params[2]) {
            case 'nuevo':
                action = 'create';
                break;
            case 'edicion':
                action = 'edit';
                break;
        }
        let type = params[1];
        let id_concurso = '';
        if (concurso_id) {
            id_concurso = concurso_id
        } else if (params[3]) {
            id_concurso = params[3];
        }

        var url = '/concursos/' + type + '/' + action + (id_concurso ? '/' + id_concurso : '');

        Services.Get(url, {
                UserToken: User.Token
            },
            (response) => {
                if (response.success) {
                    window.E = new Concurso(response.data);
                    E.action = ko.observable(action);
                    AppOptus.Bind(E);
                    E.Entity.UsuarioCalificaReputacion(response.data.list.UsuarioCalificaReputacion);
                    E.Entity.UsuarioEvaluaTecnica(response.data.list.UsuarioEvaluaTecnica);
                }
                is_init = false;
                $.unblockUI();



            },
            (error) => {
                $.unblockUI();
                swal('Error', error.message, 'error');
            },
            null,
            null
        );

        $('textarea[name="maxlength_textarea"]').bind('keypress', function(event) {
            var regex = new RegExp("^[a-zA-Z0-9 ]+$");
            var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
            if (!regex.test(key)) {
                event.preventDefault();
                return false;
            }
        });

        $('.summernote').summernote({
            toolbar: [
                // [groupName, [list of button]]
                ['style', ['italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']]
            ]
        });
    });

    // Chrome allows you to debug it thanks to this
    {chromeDebugString('dynamicScript')}
    </script>
{/block}