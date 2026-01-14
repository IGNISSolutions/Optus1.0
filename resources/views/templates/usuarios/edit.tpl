{extends 'usuarios/main.tpl'}

<!-- ESTILOS -->
{block 'styles'}
    <link href="{asset('/global/plugins/jquery-ui/jquery-ui.min.css')}" rel="stylesheet" type="text/css" />
    
    
{/block}

<!-- SCRIPTS PREVIOS A KNOCKOUT -->
{block 'pre-scripts'}
    <script>
        var IdUsuario = {$id};
    </script>
    <script src="{asset('/global/plugins/jquery-ui/jquery-ui.min.js')}" type="text/javascript"></script>
    <script src="{asset('/global/plugins/jquery.pulsate.min.js')}" type="text/javascript"></script>
{/block}

<!-- SCRIPTS POSTERIORES A KNOCKOUT -->
{block 'post-scripts'}
{/block}

{block 'title'}
    {$title} "<span data-bind="text: FullName()"></span>"
{/block}

<!-- VISTA -->
{block 'user-edit'}
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
                <div class="portlet-body form">

                    <div class="row">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group required" data-bind="validationElement: Entity.Estado">
                                    <label class="control-label visible-ie8 visible-ie9" style="display: block;">
                                        Estado
                                    </label>
                                    <div class="selectRequerido">
                                        <select
                                            data-bind="value: Entity.Estado, valueAllowUnset: true, options: Estados, optionsText: 'text', optionsValue: 'id', select2: { placeholder: 'Seleccionar...' }">
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group required" data-bind="validationElement: Entity.Tipo">
                                    <label class="control-label visible-ie8 visible-ie9" style="display: block;">
                                        Tipo
                                    </label>
                                    <div class="selectRequerido">
                                        <select data-bind="
                                        value: Entity.Tipo, 
                                        valueAllowUnset: true, 
                                        options: Tipos, 
                                        optionsText: 'text', 
                                        optionsValue: 'id', 
                                        select2: { placeholder: 'Seleccionar...' }">
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div id="idEmpresaAsociada" class="form-group required"
                                    data-bind="validationElement: Entity.Empresa">
                                    <label class="control-label visible-ie8 visible-ie9" style="display: block;">
                                        Empresa Asociada
                                    </label>
                                    <div class="selectRequerido">
                                        <select data-bind="
                                        value: Entity.Empresa, 
                                        valueAllowUnset: true, 
                                        options: Empresas, 
                                        optionsText: 'text', 
                                        optionsValue: 'id', 
                                        select2: { placeholder: 'Seleccionar...' }, 
                                        disable: IsdisableEmpresa()">
                                        </select>
                                    </div>
                                </div>
                            </div>
                            {if $type == 'client'}
                            <div class="col-md-4">
                                <div class="form-group" data-bind="validationElement: Entity.Area">
                                    <label class="control-label visible-ie8 visible-ie9" style="display: block;">
                                        Área
                                    </label>
                                    <div class="select">
                                        <select data-bind="
                                        value: Entity.Area, 
                                        valueAllowUnset: true, 
                                        options: Areas, 
                                        optionsText: 'text', 
                                        optionsValue: 'text', 
                                        select2: { placeholder: 'Seleccionar...' }">
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Rol -->
                            <div class="col-md-4">
                                <div class="form-group" data-bind="validationElement: Entity.Rol">
                                    <label class="control-label visible-ie8 visible-ie9" style="display: block;">
                                        Rol
                                    </label>
                                    <div class="select">
                                        <select data-bind="
                                        value: Entity.Rol, 
                                        valueAllowUnset: true, 
                                        options: Roles, 
                                        optionsText: 'text', 
                                        optionsValue: 'text', 
                                        select2: { placeholder: 'Seleccionar...' }">
                                        </select>
                                    </div>
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
                            <span class="caption-subject bold uppercase">
                                Info / Acceso
                            </span>
                        </div>
                        <div class="tools">
                            <a href="javascript:;" class="collapse" data-original-title="Retraer/Expandir"
                                title="Retraer/Expandir"> </a>
                        </div>
                    </div>

                    <div class="portlet-body form">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group required" data-bind="validationElement: Entity.Nombre">
                                    <label class="control-label visible-ie8 visible-ie9" style="display: block;">
                                        Nombre
                                    </label>
                                    <input class="form-control placeholder-no-fix" type="text" name="nombre"
                                        data-bind="value: Entity.Nombre" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group required" data-bind="validationElement: Entity.Apellido">
                                    <label class="control-label visible-ie8 visible-ie9" style="display: block;">
                                        Apellido
                                    </label>
                                    <input class="form-control placeholder-no-fix" type="text" name="apellido"
                                        data-bind="value: Entity.Apellido" />
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group required" data-bind="validationElement: Entity.Username">
                                    <label class="control-label visible-ie8 visible-ie9" style="display: block;">
                                        Usuario
                                    </label>
                                    <input class="form-control placeholder-no-fix" type="text" name="username"
                                        data-bind="value: Entity.Username" />
                                </div>
                            </div>

                            <div class="col-md-6"></div>
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
                            <span class="caption-subject bold uppercase">
                                Contacto
                            </span>
                        </div>
                        <div class="tools">
                            <a href="javascript:;" class="collapse" data-original-title="Retraer/Expandir"
                                title="Retraer/Expandir"> </a>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label visible-ie8 visible-ie9"
                                        style="display: block;">Teléfono</label>
                                    <input class="form-control placeholder-no-fix" type="text" name="telefono"
                                        data-bind="value: Entity.Telefono" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label visible-ie8 visible-ie9"
                                        style="display: block;">Celular</label>
                                    <input class="form-control placeholder-no-fix" type="text" name="celular"
                                        data-bind="value: Entity.Celular" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group required" data-bind="validationElement: Entity.Email">
                                    <label class="control-label visible-ie8 visible-ie9"
                                        style="display: block;">Email</label>
                                    <input class="form-control placeholder-no-fix" type="email" name="email"
                                        data-bind="value: Entity.Email" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 text-right">
                <a href="{$urlBack}" type="button" class="btn default">
                    Volver a Listado
                </a>
                <button type="button" class="btn btn-primary" data-bind="click: Save, disable: IsdisableSave">
                    Guardar Datos
                </button>
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
                decorateElement: true,
                errorElementClass: 'wrong-field'
            }, false);

            var Usuario = function(data) {
                var self = this;

                this.Breadcrumbs = ko.observable(data.breadcrumbs);
                this.Estados = ko.observable(data.list.Estados);
                this.Tipos = ko.observable(data.list.Tipos);
                this.Empresas = ko.observableArray(data.list.Empresas);
                this.Areas = ko.observableArray(data.list.Areas); // Nuevo
                this.Roles = ko.observableArray(data.list.Roles); // Nuevo
                this.FullName = ko.observable(data.list.FullName);

                this.Entity = {
                    Id: ko.observable(0),
                    Estado: ko.observable('').extend({ required: true }),
                    Tipo: ko.observable('').extend({ required: true }),
                    Empresa: ko.observable(''),
                    Area: ko.observable(''),// Nuevo
                    Rol: ko.observable(''), // Nuevo
                    Nombre: ko.observable('').extend({ required: true }),
                    Apellido: ko.observable('').extend({ required: true }),
                    Username: ko.observable('').extend({ required: true }),
                    Telefono: ko.observable(''),
                    Celular: ko.observable(''),
                    Email: ko.observable('').extend({ required: true, email: true })
                };
                if ('{$type}' === 'client') {
                this.Entity.Area.extend({ required: true });
                this.Entity.Rol.extend({ required: true });
            }

                self.Entity.Empresa.extend({
                    required: {
                        onlyIf: function() { return (self.Empresas().length > 0); }
                    }
                });

                this.setEntity = function(data) {
                    self.Entity.Id(data.list.Id);
                    self.Entity.Estado(data.list.Estado);
                    self.Entity.Tipo(data.list.Tipo);
                    self.Entity.Empresa(data.list.Empresa);
                    self.Entity.Area(data.list.Area || '');
                    self.Entity.Rol(data.list.Rol || '');
                    self.Entity.Nombre(data.list.Nombre);
                    self.Entity.Apellido(data.list.Apellido);
                    self.Entity.Username(data.list.Username);
                    self.Entity.Telefono(data.list.Telefono);
                    self.Entity.Celular(data.list.Celular);
                    self.Entity.Email(data.list.Email);
                };
                self.setEntity(data);

                self.Entity.Tipo.subscribe(function(Tipo) {
                    $.blockUI();
                    if (Tipo) {
                        var url = '/lists/companies';
                        var data = {
                            UserToken: User.Token,
                            TypeId: Tipo
                        };
                        Services.Get(url, data,
                            (response) => {
                                if (response.success) {
                                    self.Empresas.removeAll();
                                    self.Empresas(response.data.list);
                                }
                                $.unblockUI();
                            },
                            (error) => {
                                self.Empresas.removeAll();
                                self.Entity.Empresa(null);
                                $.unblockUI();
                            },
                            null,
                            null
                        );
                    }
                });

                this.Areas = ko.observableArray([
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
                    { id: 13, text: 'Siniestros' },
                ]);

                this.Roles = ko.observableArray([
                    { id: 1, text: 'Jefe' },
                    { id: 2, text: 'Gerente' },
                    { id: 3, text: 'Gerente General' },
                    { id: 4, text: 'Comprador' },
                    { id: 5, text: 'Solicitante' },
                ]);


                //alert(Area)
                this.validarform = function() {
                var isValid = (
                    this.Entity.Estado.isValid() &&
                    this.Entity.Tipo.isValid() &&
                    this.Entity.Empresa.isValid() &&
                    this.Entity.Nombre.isValid() &&
                    this.Entity.Apellido.isValid() &&
                    this.Entity.Username.isValid() &&
                    this.Entity.Email.isValid()
                );

                // Agregar validación condicional para Area y Rol
                if ('{$type}' === 'client') {
                    isValid = isValid && this.Entity.Area.isValid() && this.Entity.Rol.isValid();
                }

                return isValid;
            };  

                self.isValid = ko.computed(function() {
                    return ko.validation.group(
                        self, {
                            observable: true,
                            deep: true
                        }).showAllMessages(true);
                }, self);

                self.IsdisableEmpresa = ko.computed(function() {
                    if (self.Empresas().length == 0) {
                        $("#idEmpresaAsociada").removeClass("required");
                        return true;
                    } else {
                        $("#idEmpresaAsociada").addClass("required");
                        return false;
                    }
                });

                self.IsdisableSave = ko.computed(function() {
                    return !self.validarform();
                });

                self.Save = function() {
                    if (!self.validarform()) {
                        swal('Alerta!', 'Por favor complete los campos obligatorios.', 'error');
                        return false;
                    }

                    $.blockUI();
                    var url = '/usuarios/save';
                    switch (params[1]) {
                        case 'edicion':
                            url += '/' + params[2];
                            break;
                    }
                    var data = {
                        UserToken: User.Token,
                        Data: JSON.stringify(ko.toJS(self.Entity))
                    };
                    Services.Post(url, data,
                        (response) => {
                            console.log(response)
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
                                });s
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

            };

            jQuery(document).ready(function() {
                $.blockUI();
                console.log(params)

                var url = '/usuarios';
                switch (params[1]) {
                    case 'nuevo':
                        url += '/nuevo/{$type}/data';
                        break;
                    default:
                        url += '/edicion/{$type}/data/' + params[2];
                        break;
                }

                var data = {
                    UserToken: User.Token
                };
                console.log([url, data])
                Services.Get(url, data,
                    (response) => {

                        $.unblockUI();
                        if (response.success) {
                            window.E = new Usuario(response.data);
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