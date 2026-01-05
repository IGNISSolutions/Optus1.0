{extends 'empresas/main.tpl'}

<!-- ESTILOS -->
{block 'styles'}
    <link href="{asset('/global/plugins/jquery-ui/jquery-ui.min.css')}" rel="stylesheet" type="text/css" />
    <link href="{asset('/global/plugins/icheck/skins/all.css')}" rel="stylesheet" type="text/css" />
{/block}

<!-- SCRIPTS PREVIOS A KNOCKOUT -->
{block 'pre-scripts'}
    <script>
        var IdUsuario = {$id};
        var TipoUsuario = '{$tipo}';
    </script>
    <script src="{asset('/global/scripts/knockout.plugins.js')}" type="text/javascript"></script>
    <script src="{asset('/global/plugins/jquery.pulsate.min.js')}" type="text/javascript"></script>
{/block}

<!-- SCRIPTS POSTERIORES A KNOCKOUT -->
{block 'post-scripts'}
    <script src="{asset('/global/plugins/icheck/icheck.min.js')}" type="text/javascript"></script>
    <script src="{asset('/pages/scripts/form-icheck.min.js')}" type="text/javascript"></script>
{/block}

<!-- VISTA -->
{block 'company-edit'}
    <div class="row" style="margin-top: 25px;">
        <div class="col-md-12 ">
            <div class="portlet light bg-inverse">
                <div class="portlet-title">
                    <div class="caption font-red-sunglo">
                        <span class="caption-subject bold uppercase">General</span>
                    </div>
                    <div class="tools">
                        <a href="javascript:;" class="collapse" data-original-title="Retraer/Expandir"
                            title="Retraer/Expandir"> </a>
                    </div>
                </div>
                <div class="portlet-body form expandir-1" style="display: block;">
                    <div class="row">
                        {if $tipo eq 'client'}
                            <div class="col-md-4">
                                <div class="form-group required" data-bind="validationElement: Entity.Cuit">
                                    <label class="control-label visible-ie8 visible-ie9" style="display: block;">Código Fiscal</label>
                                    <input 
                                        data-bind="textInput: Entity.Cuit, event: { blur: onCuitBlur, change: onCuitBlur }" 
                                        class="form-control placeholder-no-fix"
                                        type="text" name="cuit" id="cuit" maxlength="20" 
                                        placeholder="Ej: 20123456789 o ABC123456"
                                        autocomplete="off" 
                                        style="text-transform: uppercase" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group required" data-bind="validationElement: Entity.RazonSocial">
                                    <label class="control-label visible-ie8 visible-ie9" style="display: block;">Razón
                                        Social</label>
                                    <input data-bind="textInput: Entity.RazonSocial" class="form-control placeholder-no-fix"
                                        type="text" name="razon-social" id="razon-social" style="text-transform: uppercase"
                                        maxlength="150" placeholder="Por ejemplo, Grupo Fernández, S.A" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group required" data-bind="validationElement: Entity.TimeZone">
                                    <label class="control-label visible-ie8 visible-ie9" style="display: block;">Zona
                                        Horaria
                                    </label>
                                    <div class="selectRequerido">
                                        <select
                                            data-bind="value: Entity.TimeZone, valueAllowUnset: true, options: Entity.TimeZones, optionsText: 'text', optionsValue: 'id', select2: { placeholder: 'Seleccionar...' }">
                                        </select>
                                    </div>
                                </div>
                            </div>
                        {/if}

                        {if $tipo eq 'offerer'}
                            <div class="col-md-4">
                                <div class="form-group required" data-bind="validationElement: Entity.Cuit">
                                    <label class="control-label visible-ie8 visible-ie9" style="display: block;">Código
                                        Fiscal</label>
                                        <input 
                                            data-bind="textInput: Entity.Cuit, event: { blur: onCuitBlur, change: onCuitBlur }" 
                                            class="form-control placeholder-no-fix"
                                            type="text" 
                                            name="cuit" 
                                            id="cuit" 
                                            maxlength="20" 
                                            placeholder="Ej: 20123456789 o ABC123456"
                                            autocomplete="off" 
                                            style="text-transform: uppercase" />

                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group required" data-bind="validationElement: Entity.RazonSocial">
                                    <label class="control-label visible-ie8 visible-ie9" style="display: block;">Razón
                                        Social</label>
                                    <input data-bind="textInput: Entity.RazonSocial" class="form-control placeholder-no-fix"
                                        type="text" name="razon-social" id="razon-social" style="text-transform: uppercase"
                                        maxlength="150" placeholder="Por ejemplo, Grupo Fernández, S.A" />
                                </div>
                            </div>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 ">
            <div class="portlet light bg-inverse">
                <div class="portlet-title">
                    <div class="caption font-red-sunglo">
                        <span class="caption-subject bold uppercase">Localización</span>
                    </div>
                    <div class="tools">
                        <a href="javascript:;" class="collapse" data-original-title="Retraer/Expandir"
                            title="Retraer/Expandir"> </a>
                    </div>
                </div>
                <div class="portlet-body form expandir-3" style="display: block;">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="tab-content">
                                <div class="tab-pane active" id="tab_1">
                                    <div class="form-group col-md-2">
                                        <label class="control-label visible-ie8 visible-ie9"
                                            style="display: block;">País</label>
                                        <input type="text" class="form-control placeholder-no-fix" id="pais" name="pais"
                                            data-bind="textInput: Entity.Pais" />
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label class="control-label visible-ie8 visible-ie9"
                                            style="display: block;">Provincia</label>
                                        <input type="text" class="form-control placeholder-no-fix" id="provincia"
                                            name="provincia" data-bind="textInput: Entity.Provincia" />
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label class="control-label visible-ie8 visible-ie9"
                                            style="display: block;">Localidad</label>
                                        <input type="text" class="form-control placeholder-no-fix" id="localidad"
                                            name="localidad" data-bind="textInput: Entity.Localidad" />
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="control-label visible-ie8 visible-ie9"
                                            style="display: block;">Dirección</label>
                                        <input type="text" class="form-control placeholder-no-fix" id="direccion"
                                            name="direccion" data-bind="textInput: Entity.Direccion" />
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label class="control-label visible-ie8 visible-ie9" style="display: block;">Código
                                            Postal</label>
                                        <input type="text" class="form-control placeholder-no-fix placeholder-no-fix"
                                            name="cp" id="cp" data-bind="textInput: Entity.Cp" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        {* <div class="col-md-6" hidden>
                        <div class="form-group">
                            <label class="control-label visible-ie8 visible-ie9" style="display: block;">Google Map</label>
                            <div id="map-canvas-1" style="width: 100%; height: 406px; background: #ccc;"></div>
                        </div>
                    </div> *}

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 ">
            <div class="portlet light bg-inverse">
                <div class="portlet-title">
                    <div class="caption font-red-sunglo">
                        <span class="caption-subject bold uppercase">Contacto</span>
                    </div>
                    <div class="tools">
                        <a href="javascript:;" class="collapse" data-original-title="Retraer/Expandir"
                            title="Retraer/Expandir"> </a>
                    </div>
                </div>
                <div class="portlet-body form expandir-4">
                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group required" data-bind="validationElement: Entity.Nombre">
                                <label class="control-label visible-ie8 visible-ie9" style="display: block;">Nombre</label>
                                <input data-bind="textInput: Entity.Nombre" class="form-control placeholder-no-fix"
                                    type="text" name="nombre" id="nombre" maxlength="100" placeholder="Por ejemplo, Juan" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group required" data-bind="validationElement: Entity.Apellido">
                                <label class="control-label visible-ie8 visible-ie9"
                                    style="display: block;">Apellido</label>
                                <input data-bind="textInput: Entity.Apellido" class="form-control placeholder-no-fix"
                                    type="text" name="apellido" id="apellido" maxlength="100"
                                    placeholder="Por ejemplo, Gonzales" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label visible-ie8 visible-ie9"
                                    style="display: block;">Teléfono</label>
                                <input class="form-control placeholder-no-fix" type="text" name="telefono" id="telefono"
                                    data-bind="textInput: Entity.Telefono" maxlength="15"
                                    placeholder="Por ejemplo, 3514567890" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label visible-ie8 visible-ie9" style="display: block;">Celular</label>
                                <input class="form-control placeholder-no-fix" type="text" name="celular" id="celular"
                                    data-bind="textInput: Entity.Celular" maxlength="15"
                                    placeholder="Por ejemplo, 3514567890" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group required" data-bind="validationElement: Entity.Email">
                                <label class="control-label visible-ie8 visible-ie9" style="display: block;">Email</label>
                                <input data-bind="textInput: Entity.Email" class="form-control placeholder-no-fix"
                                    type="email" name="emailEmpresaOferente" id="emailEmpresaOferente" maxlength="150"
                                    placeholder="Por ejemplo, dsalas@gmail.com" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label visible-ie8 visible-ie9" style="display: block;">Sitio
                                    Web</label>
                                <input class="form-control placeholder-no-fix" type="text" name="sitioweb" id="sitioweb"
                                    data-bind="textInput: Entity.SitioWeb" maxlength="100"
                                    placeholder="Por ejemplo, miweb.com.ar" />
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 ">
            <div class="portlet light bg-inverse">
                <div class="portlet-title">
                    <div class="caption font-red-sunglo">
                        <span class="caption-subject bold uppercase">Varios</span>
                    </div>
                    <div class="tools">
                        <a href="javascript:;" class="collapse" data-original-title="Retraer/Expandir"
                            title="Retraer/Expandir"> </a>
                    </div>
                </div>
                <div class="portlet-body form expandir-5">
                    <div class="row">

                        {if $tipo eq 'offerer'}
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label visible-ie8 visible-ie9" style="display: block;">
                                        Rubro
                                    </label>
                                    <select id="rubros_selected" data-bind="selectedOptions: 
                                        Entity.RubrosSelected, 
                                        options: Rubros, 
                                        valueAllowUnset: true, 
                                        optionsText: 'text', 
                                        optionsValue: 'id', 
                                        select2: { placeholder: 'Seleccionar...', multiple: true, allowClear: true }">
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="tabbable-custom nav-justified">
                                    <ul class="nav nav-tabs nav-justified">
                                        <li class="active">
                                            <a href="#tab_1" data-toggle="tab">Alcance de la prestación del servicio</a>
                                        </li>
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="tab_1">
                                            <div class="form-group">
                                                <label style="display: block;" class="control-label visible-ie8 visible-ie9">
                                                    Paises
                                                </label>
                                                <select id="paises_selected"
                                                    data-bind="selectedOptions: 
                                                    Entity.PaisesSelected, 
                                                    options: Paises, 
                                                    valueAllowUnset: true, 
                                                    optionsText: 'text', 
                                                    optionsValue: 'id', 
                                                    select2: { placeholder: 'Seleccionar...', multiple: true, allowClear: true }">
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label style="display: block;" class="control-label visible-ie8 visible-ie9">
                                                    Provincias
                                                </label>
                                                <select id="provincias_selected"
                                                    data-bind="selectedOptions: 
                                                    Entity.ProvinciasSelected, 
                                                    options: Provincias,
                                                    valueAllowUnset: true, 
                                                    optionsText: 'text', 
                                                    optionsValue: 'id', 
                                                    select2: { placeholder: 'Seleccionar...', multiple: true, allowClear: true }, disable: Entity.PaisesSelected().length == 0">
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label style="display: block;" class="control-label visible-ie8 visible-ie9">
                                                    Ciudades
                                                </label>
                                                <select id="ciudades_selected"
                                                    data-bind="selectedOptions: 
                                                    Entity.CiudadesSelected, 
                                                    options: Ciudades, 
                                                    valueAllowUnset: true, 
                                                    optionsText: 'text', 
                                                    optionsValue: 'id', 
                                                    select2: { placeholder: 'Seleccionar...', multiple: true, allowClear: true }, disable: Entity.ProvinciasSelected().length == 0">
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {if isAdmin()}
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label visible-ie8 visible-ie9" style="display: block;">
                                            Clientes Asociados
                                        </label>
                                        <select data-bind="selectedOptions: 
                                        Entity.ClienteAsociado, 
                                        options: clientAsociados, 
                                        valueAllowUnset: true, 
                                        optionsText: 'text', 
                                        optionsValue: 'id', 
                                        select2: { placeholder: 'Seleccionar...', multiple: true, allowClear: true }">
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label visible-ie8 visible-ie9" style="display: block;">Código
                                            Proveedor</label>
                                        <input class="form-control placeholder-no-fix" type="text" name="codigo-proveedor"
                                            id="codigo-proveedor" data-bind="textInput: Entity.CodigoProveedor"
                                            placeholder="Por ejemplo, 0000000001">
                                    </div>
                                </div>
                            {/if}
                        {else}
                            <div class="col-md-12">
                                <div class="form-group required" data-bind="validationElement: Entity.Tarifario">
                                    <label class="control-label visible-ie8 visible-ie9" style="display: block;">Sistema de
                                        Cobro</label>
                                    <div class="selectRequerido">
                                        <select data-bind="value: 
                                            Entity.Tarifario, 
                                            valueAllowUnset: true, 
                                            options: Tarifarios, 
                                            optionsText: 'text', 
                                            optionsValue: 'id', 
                                            select2: { placeholder: 'Seleccionar...' }">
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="sistema-cobro" name="sistema-cobro" value="0">
                        {/if}

                        {if isAdmin()}
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label visible-ie8 visible-ie9"
                                        style="display: block;">Observaciones</label>
                                    <textarea class="form-control" id="observaciones" name="observaciones" style="height: 80px;"
                                        maxlength="255" data-bind="textInput: Entity.Observaciones"></textarea>
                                </div>
                            </div>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="form-group pull-right">
                <button type="button" class="btn sbold btn-primary" data-bind="click: Save, disable: IsdisableSave">
                    Guardar Datos
                </button>
            </div>
            <div class="form-group pull-right">
                <a href="javascript:window.history.back();" type="button" class="btn sbold default"
                    style="margin-right: 10px;">
                    Volver a Listado
                </a>
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

        var Form = function(data) {
            var self = this;

            this.Id = ko.observable(data.list.Id);
            this.Tipo = ko.observable(data.list.Tipo);

            this.Estados = ko.observable(data.list.Estados);
            this.Estado = 1,
            this.RazonSocial = ko.observable(data.list.RazonSocial).extend({ required: true });
            this.Cuit = ko.observable(data.list.Cuit).extend({ required: true });

            this.Pais = ko.observable(data.list.Pais);
            this.Provincia = ko.observable(data.list.Provincia);
            this.Localidad = ko.observable(data.list.Localidad);
            this.Direccion = ko.observable(data.list.Direccion);
            this.Cp = ko.observable(data.list.Cp);
            this.Latitud = ko.observable(data.list.Latitud);
            this.Longitud = ko.observable(data.list.Longitud);

            this.Nombre = ko.observable(data.list.Nombre).extend({ required: true });
            this.Apellido = ko.observable(data.list.Apellido).extend({ required: true });
            this.Telefono = ko.observable(data.list.Telefono);
            this.Celular = ko.observable(data.list.Celular);
            this.Email = ko.observable(data.list.Email).extend({ required: true, email: true });
            this.SitioWeb = ko.observable(data.list.SitioWeb);

            this.RubrosSelected = ko.observableArray([]);

            this.PaisesSelected = ko.observableArray([]);
            this.ProvinciasSelected = ko.observableArray([]);
            this.CiudadesSelected = ko.observableArray([]);

            this.ClienteAsociado = ko.observableArray([]);

            this.CodigoProveedor = ko.observable(data.list.CodigoProveedor);
            this.Tarifario = ko.observable(data.list.Tarifario).extend({ required: true });
            this.Observaciones = ko.observable(data.list.Observaciones);
            this.TimeZone = ko.observable(data.list.timeZone).extend({ required: true });
            this.TimeZones = ko.observable(data.list.timeZones);


        }

        var Empresa = function(data) {
            var self = this;

            this.onlyNumbers = function(data, event) {
                var charCode = (event.which) ? event.which : event.keyCode;
                if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                    event.preventDefault();
                    return false;
                }
                return true;
            };

            // Variable para controlar debounce de verificación de CUIT
            this.cuitVerificationTimeout = null;

            this.Breadcrumbs = ko.observableArray(data.breadcrumbs);
            this.Rubros = ko.observableArray(data.list.Rubros);
            this.Tarifarios = ko.observableArray(data.list.Tarifarios);
            this.clientAsociados = ko.observableArray(data.list.ClientesAsociados);

            this.Paises = ko.observableArray(data.list.Paises);
            this.Provincias = ko.observableArray([]);
            this.Ciudades = ko.observableArray([]);

            this.Entity = new Form(data);

            self.isValid = ko.computed(function() {
                return ko.validation.group(
                    self, {
                        observable: true,
                        deep: true
                    }).showAllMessages(true);
            }, self);

            if (params[1] == 'offerer') {
                this.FirstTimePaisesSelected = true;
                self.Entity.PaisesSelected.subscribe((Countries) => {
                    // Cuando no es la primera vez y se limpian los países
                    if (!self.FirstTimePaisesSelected && Countries.length === 0) {
                        // Limpiar provincias y ciudades en cascada
                        self.Entity.ProvinciasSelected([]);
                        self.Provincias([]);
                        self.Entity.CiudadesSelected([]);
                        self.Ciudades([]);
                        return;
                    }

                    if (Countries.length > 0) {
                        $.blockUI();
                        var url = '/lists/provinces';
                        var body = {
                            UserToken: User.Token,
                            Countries: Countries
                        };
                        Services.Get(url, body,
                            (response) => {
                                $.unblockUI();
                                self.Provincias(response.data.list);
                                if (self.FirstTimePaisesSelected) {
                                    self.Entity.ProvinciasSelected(data.list.ProvinciasSelected || []);
                                    self.FirstTimePaisesSelected = false;
                                }
                            },
                            (error) => {
                                $.unblockUI();
                            },
                            null,
                            null
                        );
                    } else if (self.FirstTimePaisesSelected) {
                        // Primera carga sin países seleccionados
                        self.Provincias([]);
                        self.Ciudades([]);
                    }
                });

                this.FirstTimeProvinciasSelected = true;
                self.Entity.ProvinciasSelected.subscribe((Provinces) => {
                    // Cuando no es la primera vez y se limpian las provincias
                    if (!self.FirstTimeProvinciasSelected && Provinces.length === 0) {
                        // Limpiar ciudades en cascada
                        self.Entity.CiudadesSelected([]);
                        self.Ciudades([]);
                        return;
                    }

                    if (Provinces.length > 0) {
                        $.blockUI();
                        var url = '/lists/cities';
                        var body = {
                            UserToken: User.Token,
                            Provinces: Provinces
                        };
                        Services.Get(url, body,
                            (response) => {
                                $.unblockUI();
                                self.Ciudades(response.data.list);
                                if (self.FirstTimeProvinciasSelected) {
                                    self.Entity.CiudadesSelected(data.list.CiudadesSelected || []);
                                    self.FirstTimeProvinciasSelected = false;
                                }
                            },
                            (error) => {
                                $.unblockUI();
                            },
                            null,
                            null
                        );
                    } else if (self.FirstTimeProvinciasSelected) {
                        // Primera carga sin provincias seleccionadas
                        self.Ciudades([]);
                    }
                });

            }

            // Inicializamos los observables múltiples.
            setTimeout(() => {
                self.Entity.RubrosSelected(data.list.RubrosSelected || []);
                self.Entity.ClienteAsociado(data.list.ClienteAsociado || []);
                self.Entity.PaisesSelected(data.list.PaisesSelected || []);
            }, 1000);

            this.validarform = function() {
                if (params[1] == 'offerer') {
                    return (
                        self.Entity.RazonSocial.isValid() &&
                        self.Entity.Cuit.isValid() &&
                        self.Entity.Nombre.isValid() &&
                        self.Entity.Apellido.isValid() &&
                        self.Entity.Email.isValid()
                    );
                } else {
                    return (
                        self.Entity.RazonSocial.isValid() &&
                        self.Entity.Cuit.isValid() &&
                        self.Entity.Nombre.isValid() &&
                        self.Entity.Apellido.isValid() &&
                        self.Entity.Email.isValid() &&
                        self.Entity.Tarifario.isValid() &&
                        self.Entity.TimeZone.isValid()
                    );
                }
            };

            self.IsdisableSave = ko.computed(function() {
                return !self.validarform();
            });

            this.Save = function() {
                if (!self.validarform()) {
                    swal('Alerta!', 'Por favor complete los campos obligatorios.', 'error');
                    return false;
                }

                // Validación adicional: verificar si es nuevo y si el CUIT ya existe
                var isnew = params[2] === 'nuevo';
                if (isnew && params[1] === 'offerer') {
                    var cuit = String(self.Entity.Cuit() || '').trim().replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
                    if (cuit.length >= 2) {
                        console.log('Verificación preventiva de CUIT duplicado antes de guardar:', cuit);
                        // Hacer una validación rápida antes de guardar
                        $.blockUI();
                        var verifyUrl = '/empresas/offerer/by-cuit/' + encodeURIComponent(cuit);
                        Services.Get(
                            verifyUrl,
                            { UserToken: User.Token },
                            function(verifyResponse) {
                                $.unblockUI();
                                
                                // Si encuentra algo, mostrar alerta y no continuar
                                if (verifyResponse.success || (verifyResponse.data && verifyResponse.data.already_associated)) {
                                    swal({
                                        title: 'Proveedor duplicado',
                                        text: 'Este proveedor ya existe en el sistema. Por favor, busque y asocie el existente.',
                                        type: 'warning',
                                        confirmButtonText: 'Aceptar'
                                    });
                                    return false;
                                }
                                
                                // Si no existe, proceder con el guardado
                                self.proceedWithSave();
                            },
                            function(error) {
                                $.unblockUI();
                                // En caso de error de conexión, permitir continuar (puede ser timeout)
                                swal({
                                    title: 'Advertencia',
                                    text: 'No se pudo verificar si el proveedor existe. ¿Desea continuar?',
                                    type: 'warning',
                                    showCancelButton: true,
                                    confirmButtonText: 'Sí, continuar',
                                    cancelButtonText: 'Cancelar'
                                }, function(shouldContinue) {
                                    if (shouldContinue) {
                                        self.proceedWithSave();
                                    }
                                });
                            }
                        );
                        return false;
                    }
                }
                
                // Para ediciones o si no es offerer nuevo, ir directo al guardado
                self.proceedWithSave();
            };

            this.proceedWithSave = function() {
                $.blockUI();
                var url = '/empresas/' + params[1] + '/save';
                switch (params[2]) {
                    case 'edicion':
                        url += '/' + params[3];
                        break;
                }
                
                // Asegurar que los arrays de alcance siempre existan antes de enviar
                var entityData = ko.toJS(self.Entity);
                if (!entityData.PaisesSelected) entityData.PaisesSelected = [];
                if (!entityData.ProvinciasSelected) entityData.ProvinciasSelected = [];
                if (!entityData.CiudadesSelected) entityData.CiudadesSelected = [];
                if (!entityData.RubrosSelected) entityData.RubrosSelected = [];
                if (!entityData.ClienteAsociado) entityData.ClienteAsociado = [];
                
                // Debug: Verificar datos de alcance antes de enviar
                console.log('Datos de alcance a enviar:', {
                    Paises: entityData.PaisesSelected,
                    Provincias: entityData.ProvinciasSelected,
                    Ciudades: entityData.CiudadesSelected
                });
                
                var data = {
                    UserToken: User.Token,
                    Data: JSON.stringify(entityData)
                };
                Services.Post(url, data,
                    (response) => {
                        $.unblockUI();
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
                                if (response.data.redirect) {
                                    window.location = response.data.redirect;
                                }
                            });
                        } else {
                            swal('Error', response.message, 'error');
                        }
                    },
                    (error) => {
                        $.unblockUI();
                        swal('Error', error.message, 'error');
                    }
                );
            };

            this.onCuitBlur = function () {
            console.log('onCuitBlur ejecutada - userType:', '{$userType}', 'params[1]:', params[1], 'params[2]:', params[2]);
            
            // Solo ejecutar en contexto de cliente nuevo asociando proveedor
            if ('{$userType}' !== 'customer') {
                console.log('Saltado: userType no es customer');
                return;
            }
            if (params[1] !== 'offerer') {
                console.log('Saltado: params[1] no es offerer');
                return;
            }
            if (params[2] !== 'nuevo') {
                console.log('Saltado: params[2] no es nuevo');
                return;
            }

            var raw = self.Entity.Cuit() || '';
            // IMPORTANTE: Ahora permitimos letras y números (alfanumérico) para códigos fiscales internacionales
            // Solo removemos espacios y caracteres especiales, pero mantenemos letras y números
            var cuit = String(raw).trim().replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
            
            console.log('Código fiscal ingresado:', raw, 'Procesado:', cuit, 'Longitud:', cuit.length);
            
            // Validación de longitud mínima (2 caracteres alfanuméricos)
            if (cuit.length < 2) {
                console.log('Saltado: Código fiscal muy corto (menos de 2 caracteres)');
                return;
            }
            
            // Evitar búsquedas duplicadas con debounce
            if (self.cuitVerificationTimeout) {
                clearTimeout(self.cuitVerificationTimeout);
                console.log('Debounce: cancelada búsqueda anterior');
            }
            
            self.cuitVerificationTimeout = setTimeout(function() {
                console.log('Iniciando verificación de código fiscal:', cuit);
                $.blockUI();
                // Usar encodeURIComponent para manejar correctamente caracteres especiales
                var url = '/empresas/offerer/by-cuit/' + encodeURIComponent(cuit);

                Services.Get(
                    url,
                    { UserToken: User.Token },
                    function (response) {
                        $.unblockUI();
                        console.log('Respuesta del servidor:', response);

                        // 1) Detectar "ya asociado" sin depender de success/data.id
                        var msg = (response && response.message) || '';
                        var alreadyAssociated =
                            /asociad[oa]/i.test(msg)
                            || (response && response.data && response.data.already_associated === true)
                            || (response && response.code === 'ALREADY_ASSOCIATED');

                        console.log('¿Ya asociado?', alreadyAssociated, 'Mensaje:', msg);

                        if (alreadyAssociated) {
                            swal({
                                title: 'Proveedor ya asociado',
                                text: 'Este proveedor ya se encuentra asociado a tu empresa.',
                                type: 'info',
                                confirmButtonText: 'Aceptar'
                            }, function () {
                                self.Entity.Cuit('');
                                setTimeout(function(){ document.getElementById('cuit')?.focus(); }, 0);
                            });
                            return;
                        }

                        // 2) Existe proveedor (pero no está asociado)
                        var exists = !!(response && response.success && response.data && response.data.id);
                        console.log('¿Proveedor existe?', exists);
                        
                        if (exists) {
                            self.showOffererExistsModal(response.data);
                            return;
                        }

                        // 3) No existe: seguir con el alta
                        console.log('Proveedor no existe, proceder con registro nuevo');
                    },
                    function (error) {
                        $.unblockUI();
                        console.error('Error en verificación de código fiscal:', error);
                        var emsg = (error && error.message) ? error.message : 'No se pudo verificar el código fiscal en este momento.';
                        swal('Aviso', emsg, 'warning');
                    }
                );
            }, 800); // Debounce de 800ms
            };



        this.showOffererExistsModal = function(offerer) {
            // offerer = { id, business_name, cuit, nombre, apellido, email}
            var htmlMsg =
                '<div style="text-align:left;">' +
                    '<p>Este proveedor ya se encuentra registrado:</p>' +
                    '<p><b>Razón social:</b> ' + (offerer.business_name || '—') + '</p>' +
                    '<p><b>Código fiscal:</b> ' + (offerer.cuit || '—') + '</p>' +
                    '<p><b>Info. de Contacto:</b></p>' +
                    '<p><b> - Nombre:</b> ' + (offerer.nombre + ' ' + offerer.apellido || '—') + '</p>' +
                    '<p><b> - Email:</b> ' + (offerer.email || '—') + '</p>' +
                '</div>';

            swal({
                title: "Proveedor existente",
                text: htmlMsg,
                html: true,                  // SweetAlert v1
                type: "warning",
                showCancelButton: true,
                confirmButtonText: "Agregar proveedor",
                cancelButtonText: "Cancelar",
                closeOnConfirm: false,
                closeOnCancel: true,
                confirmButtonColor: "#34a853"
            }, function(isConfirm) {
                if (isConfirm) {
                    self.associateExisting(offerer.id);
                } else {
                    //limpiar CUIT y devolver el foco al input
                    self.Entity.Cuit('');
                    setTimeout(function(){ document.getElementById('cuit')?.focus(); }, 0);
                }
            });
        };

        this.associateExisting = function(offererId) {
            $.blockUI();

            var data = {
                UserToken: User.Token,
                Data: JSON.stringify({ Id: offererId })
            };

            // Ya usabas esta ruta en tu código
            var url = '/empresas/' + params[1] + '/association';

            Services.Post(url, data,
                function(response) {
                    $.unblockUI();
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
                            if (response.data && response.data.redirect) {
                                window.location.href = response.data.redirect;
                            }
                        });
                    } else {
                        swal('Error', response.message || 'No se pudo asociar el proveedor.', 'error');
                    }
                },
                function(error) {
                    $.unblockUI();
                    swal('Error', error.message || 'No se pudo asociar el proveedor.', 'error');
                }
            );
        };


        };

        jQuery(document).ready(function() {
            $.blockUI();
            var url = '/empresas/' + params[1] + '/' + params[2] + '/data';
            if (typeof params[3] !== 'undefined') {
                url += '/' + params[3];
            }
            Services.Get(url, { UserToken: User.Token },
                (response) => {
                    $.unblockUI();
                    if (response.success) {
                        window.E = new Empresa(response.data);
                        AppOptus.Bind(E);
                    }
                },
                (error) => {
                    $.unblockUI();
                    swal('Error', error.message, 'error');
                }
            );


        });

        // Chrome allows you to debug it thanks to this
        {chromeDebugString('dynamicScript')}
    </script>
{/block}