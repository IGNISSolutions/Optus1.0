{extends 'empresas/main.tpl'}

<!-- ESTILOS -->
{block 'styles'}
    <link href="{asset('/global/plugins/datatables/datatables.min.css')}" rel="stylesheet" type="text/css" />
    <link href="{asset('/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css')}" rel="stylesheet"
        type="text/css" />
    <link href="{asset('/global/css/components-rounded.min.css')}" rel="stylesheet" id="style_components" type="text/css" />
{/block}

<!-- SCRIPTS PREVIOS A KNOCKOUT -->
{block 'pre-scripts'}
    <script src="{asset('/global/plugins/datatables/datatables.min.js')}" type="text/javascript"></script>
    <script src="{asset('/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js')}" type="text/javascript">
    </script>
    <script src="{asset('/global/plugins/bootstrap-maxlength/bootstrap-maxlength.js')}" type="text/javascript"></script>
    <script src="{asset('/global/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js')}" type="text/javascript"></script>
    <script src="{asset('/pages/scripts/components-bootstrap-maxlength.js')}" type="text/javascript"></script>
    <script src="{asset('/pages/scripts/jquery.form.js')}" type="text/javascript"></script>
    <script src="{asset('/global/plugins/bootbox/bootbox.min.js')}" type="text/javascript"></script>
{/block}

<!-- SCRIPTS POSTERIORES A KNOCKOUT -->
{block 'post-scripts'}{/block}

<!-- VISTA -->
{block 'company-list'}
    <div class="row">
        <div class="col-md-12">
            <div class="portlet light bordered">
                {* {if isAdmin()} *}
                    <div class="portlet-title">
                        <div class="btn-group">
                            <a href="/empresas/{$tipo}/nuevo" id="sample_editable_1_new" class="btn sbold green">
                                {if $tipo === 'offerer'}
                                    Agregar Nueva Empresa Proveedor
                                {elseif $tipo === 'client'}
                                    Agregar Nueva Empresa Cliente
                                {/if}
                                <i class="fa fa-plus"></i>
                            </a>
                        </div>
                    </div>
                {* {/if} *}

                <div class="portlet-body">
                    <table class="table table-striped table-bordered" id="listaEmpresas">
                        <thead>
                            <tr>
                                <th>
                                    Razón Social
                                </th>
                                <th class="text-center">
                                    CUIT
                                </th>
                                <th class="text-center">
                                    Estado
                                </th>
                                <th class="text-center">
                                    Acciones
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            data-bind="dataTablesForEach: { data: ListaEmpresas, as: 'company', options: { paging: true }}">
                            <tr>
                                <td data-bind="text: RazonSocial()"></td>
                                <td class="text-center" data-bind="text: Cuit()"></td>
                                <td>
                                    <span class="label label-sm labelAlign" data-bind="text: EstadoDescripcion, css: { 
                                        'label-success': Estado() === 'active', 
                                        'label-warning': Estado() === 'inactive', 
                                        'label-danger': Estado() === 'blocked' 
                                    }">
                                    </span>
                                </td>
                                <td class="text-center">
                                    {if $tipo === 'offerer' && isCustomer()}
                                        <!-- ko if: !IsAssociated() -->
                                        <a data-bind="click: $root.ToggleAssociation.bind($data, company)"
                                            class="btn btn-xs green" title="Asociar">
                                            Asociar
                                            <i class="fa fa-chain"></i>
                                        </a>
                                        <!-- /ko -->
                                        <!-- ko if: IsAssociated() -->
                                        <a data-bind="click: $root.ToggleAssociation.bind($data, company)"
                                            class="btn btn-xs btn-danger" title="Desasociar">
                                            Desasociar
                                            <i class="fa fa-chain-broken"></i>
                                        </a>
                                        <!-- /ko -->
                                    {/if}

                                    <a href="javascript:void(0);" class="btn btn-xs green"
                                        data-bind="click: function() { $root.VerDetalleEmpresa(Id()) }" title="Ver Detalle">
                                        Ver Detalle
                                        <i class="fa fa-eye"></i>
                                    </a>

                                    <a href="javascript:void(0);" class="btn btn-xs green"
                                        data-bind="click: function() { $root.EditarEmpresa(Id()) }" title="Editar">
                                        Editar
                                        <i class="fa fa-pencil"></i>
                                    </a>

                                    {if isAdmin() && $tipo === 'client'}
                                        <a href="javascript:void(0);" class="btn btn-xs purple"
                                            data-bind="click: function() { $root.ModulosEmpresa(Id()) }" title="Modulos">
                                            Modulos
                                            <i class="fa fa-sliders"></i>
                                        </a>
                                    {/if}

                                    <a
                                    data-bind="attr: {ldelim} href: '/empresas/{$tipo}/usuarios/' + Id() {rdelim}"
                                    class="btn btn-xs btn-warning"
                                    title="Usuarios">
                                    Ver Usuarios
                                    <i class="fa fa-user"></i>
                                    </a>

                                    {if isAdmin()}
                                        <a data-bind="click: $root.Delete.bind($data, Id())" class="btn btn-xs btn-danger"
                                            title="Eliminar">
                                            Eliminar
                                            <i class="fa fa-trash-o"></i>
                                        </a>
                                    {/if}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
{/block}

<!-- KNOCKOUT JS -->
{block 'knockout' append}
    <script type="text/javascript">
        var Filters = function(data) {
            var self = this;

            this.AssociatedList = ko.observableArray(data.associated_list);
            this.Associated = ko.observable(self.AssociatedList().some(a => a.default) ? self.AssociatedList().find(a =>
                a.default).id : []);
            this.Cuit = ko.observable(null);
            this.AreasList = ko.observableArray(data.areas_list);
            this.Areas = ko.observableArray(data.areas);
            this.CountriesList = ko.observableArray(data.countries_list);
            this.Countries = ko.observableArray(data.countries);
            this.ProvincesList = ko.observableArray(data.provinces_list);
            this.Provinces = ko.observableArray(data.provinces);
            this.CitiesList = ko.observableArray(data.cities_list);
            this.Cities = ko.observableArray(data.cities);
            this.CustomersList = ko.observableArray(data.customers_list);
            this.Customers = ko.observableArray(data.customers);

            self.Countries.subscribe((Countries) => {
                if (Countries.length > 0) {
                    $.blockUI();
                    var url = '/lists/provinces';
                    var data = {
                        UserToken: User.Token,
                        Countries: Countries
                    };
                    Services.Get(url, data,
                        (response) => {
                            $.unblockUI();
                            self.ProvincesList(response.data.list);
                        },
                        (error) => {
                            $.unblockUI();
                        },
                        null,
                        null
                    );
                } else {
                    self.Provinces([]);
                }
            });

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
                    self.Cities([]);
                }
            });
        }

        var Empresa = function(data) {
            var self = this;

            this.Id = ko.observable(data.Id);
            this.RazonSocial = ko.observable(data.RazonSocial);
            this.Cuit = ko.observable(data.Cuit);
            this.Estado = ko.observable(data.Estado);
            this.EstadoDescripcion = ko.observable(data.EstadoDescripcion);
            this.IsAssociated = ko.observable(data.IsAssociated);
        }

        var EmpresasListado = function(data) {
            var self = this;

            self.EditarEmpresa = function(id) {
                $.post('/empresas/guardar-id-edicion', { id: id }, function(response) {
                    if (response.success) {
                        window.location.href = '/empresas/' + params[1] + '/edicion/' + id;
                    } else {
                        swal('Error', response.message, 'error');
                    }
                }).fail(function () {
                    swal('Error', 'No se pudo iniciar la edición.', 'error');
                });
            };

            self.VerDetalleEmpresa = function(id) {
                $.post('/empresas/guardar-id-detalle', { id: id }, function(response) {
                    if (response.success) {
                        window.location.href = '/empresas/' + params[1] + '/detalle/' + id;
                    } else {
                        swal('Error', response.message, 'error');
                    }
                }).fail(function () {
                    swal('Error', 'No se pudo abrir el detalle.', 'error');
                });
            };

            self.ModulosEmpresa = function(id) {
                $.post('/empresas/guardar-id-edicion', { id: id }, function(response) {
                    if (response.success) {
                        window.location.href = '/empresas/' + params[1] + '/edicion/' + id + '#modulos';
                    } else {
                        swal('Error', response.message, 'error');
                    }
                }).fail(function () {
                    swal('Error', 'No se pudo abrir los modulos.', 'error');
                });
            };


            this.Filters = ko.observable();
            this.ListaEmpresas = ko.observableArray();
            this.TotalOptus = ko.observable(data.results.TotalOptus);
            this.TotalAsociados = ko.observable(data.results.TotalAsociados);
            this.Breadcrumbs = ko.observableArray(data.breadcrumbs);

            if (data.results.List.length > 0) {
                data.results.List.forEach(item => {
                    self.ListaEmpresas.push(new Empresa(item));
                });
            }

            this.filter = function() {
                $.blockUI();
                var url = '/empresas/' + params[1] + '/filter';
                Services.Post(url, {
                        UserToken: User.Token,
                        Filters: JSON.stringify(ko.toJS(self.Filters))
                    },
                    (response) => {
                        $.unblockUI();
                        if (response.success) {
                            self.ListaEmpresas.removeAll();
                            if (response.data.results.length > 0) {
                                var newResults = [];
                                response.data.results.forEach(item => {
                                    newResults.push(new Empresa(item));
                                });
                                self.ListaEmpresas(newResults);
                            }
                        }
                    },
                    (error) => {
                        $.unblockUI();
                        swal('Error', error.message, 'error');
                    }
                );
            }

            this.initFilters = function() {
                self.Filters(new Filters(data.filters));
            }

            this.cleanFilters = function() {
                self.initFilters();
                self.filter();
            }

            this.Delete = function(id) {
                $.blockUI();
                var data = {
                    UserToken: User.Token
                };
                var url = '/empresas/' + params[1] + '/delete/' + id;
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
                                } else {
                                    window.location.reload();
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
            }

            this.ToggleAssociation = function(company) {
                $.blockUI();
                var data = {
                    UserToken: User.Token,
                    Data: JSON.stringify(ko.toJS({
                        Id: company.Id()
                    }))
                };
                var url = '/empresas/' + params[1] + '/association';
                Services.Post(url, data,
                    (response) => {
                        if (response.success) {
                            window.location.reload();
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

            self.initFilters();
        };

        jQuery(document).ready(function() {
            $.blockUI();
            var url = '/empresas/' + params[1] + '/list';
            Services.Get(url, {
                    UserToken: User.Token
                },
                (response) => {
                    if (response.success) {
                        if (response.success) {
                            window.E = new EmpresasListado(response.data);
                            AppOptus.Bind(E);
                        }
                    }
                    $.unblockUI();
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