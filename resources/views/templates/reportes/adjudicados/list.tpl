{extends 'reportes/adjudicados/main.tpl'}

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
{/block}

<!-- VISTA -->
{block 'reports-list'}
    <div class="row">
        <div class="col-md-12">
            <div class="portlet light bordered">
                <div class="portlet-body">
                    <div class="table-toolbar">
                        <div class="row">
                            <div class="col-md-6">
                            </div>
                            <div class="col-md-6">
                            </div>
                        </div>
                    </div>
                    <table class="table table-striped table-bordered" id="ConcursoAdj">
                        <thead>
                            <tr>
                                <th>Nº Concurso</th>
                                <th>Nombre Licitación</th>
                                <th>Tipo Adjudicación</th>
                                <th>Proveedor/es Adjudicados</th>
                            </tr>
                        </thead>
                        <tbody
                            data-bind="dataTablesForEach : { data: Concursos, options: { paging: true, searching: false, pageLength: 10, bLengthChange: false }}">
                            <tr>
                                <td class="col-md-1" data-bind="text: Id"></td>
                                <td class="col-md-5" data-bind="text: Nombre"></td>
                                <td class="col-md-2" data-bind="text: Tipo"></td>
                                <td class="col-md-4" data-bind="text: Proveedores"></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="btn-group">
                        <a data-bind="click: downloadReport" download class="btn sbold blue" title="Descargar Reporte">
                            Descargar Reporte
                            <i class="fa fa-download" aria-hidden="true"></i>
                        </a>
                    </div>
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
            this.Desde = ko.observable();
            this.Hasta = ko.observable();
            this.Compradores = ko.observable(data.customers);
            this.Proveedores = ko.observable(data.offerers)
            this.CompradoresSelected = ko.observable(data.customersSelected);
            this.ProveedoresSelected = ko.observable(data.offerersSelected)

            self.CompradoresSelected.subscribe((CompradoresSelected) => {
                if (CompradoresSelected.length > 0) {
                    var compradores = self.Compradores()
                    var offerersSelected = compradores.filter(comprador => CompradoresSelected.includes(comprador.id)).map(comprador => comprador.offerers).flat();
                    self.Proveedores(offerersSelected)
                }
            })
        }

        var ConcursoAdj = function(data) {
            var self = this;

            this.Id = ko.observable(data.Id);
            this.Nombre = ko.observable(data.Nombre);
            this.Tipo = ko.observable(data.Tipo);
            this.Proveedores = ko.observable(data.Proveedores);
        }

        var ConcursoReport = function(data) {
            this.id = ko.observable(data.id)
            this.nombre = ko.observable(data.nombre)
            this.areaSol = ko.observable(data.areaSol)
            this.pos = ko.observable(data.pos)
            this.item = ko.observable(data.item)
            this.cant = ko.observable(data.cant)
            this.unidad = ko.observable(data.unidad)
            this.unitario = ko.observable(data.unitario)
            this.costoObj = ko.observable(data.costoObj)
            this.ahorro = ko.observable(data.ahorro)
            this.ahorroRel = ko.observable(data.ahorroRel)
            this.plazoP = ko.observable(data.plazoP)
            this.plazoE = ko.observable(data.plazoE)
            this.prov = ko.observable(data.prov)
            this.aceptAdj = ko.observable(data.aceptAdj)
            this.FechaAdj = ko.observable(data.FechaAdj)
            this.tipo = ko.observable(data.tipo)
            this.comentario = ko.observable(data.comentario)
            this.evalTech = ko.observable(data.evalTech)
            this.userAdj = ko.observable(data.userAdj)
        }

        var ConfiguracionesConcursoAdj = function(data) {
            var self = this;

            this.Filters = ko.observable();
            this.Breadcrumbs = ko.observableArray(data.breadcrumbs);
            this.Concursos = ko.observableArray();
            this.Details = ko.observableArray();


            if (data.list.length > 0) {
                data.list.forEach(item => {
                    self.Concursos.push(new ConcursoAdj(item));
                    item.Detalles.forEach(i => {
                        self.Details.push(new ConcursoReport(i));
                    })
                });
            }

            self.downloadReport = function() {
                var concursos = this.Details();

                var wb = XLSX.utils.book_new();
                wb.Props = {
                    Title: "Reporte concursos adjudicados",
                    Subject: "Reporte concursos adjudicados",
                    Author: "Optus",
                    CreatedDate: new Date()
                };
                wb.SheetNames.push("Concursos");
                var ws_data = [
                    [
                        'Nº Concurso',
                        'Nombre Licitación',
                        'Área Solicitante',
                        'Referencia Producto',
                        'Producto',
                        'Cant',
                        'Unidad',
                        'Precio Unitario',
                        'Costo Objetivo',
                        'Ahorro',
                        'Ahorro %',
                        'Plazo Pago',
                        'Plazo Entrega',
                        'Proveedor Adjudicado',
                        'Acepta Adjudicación',
                        'Fecha Adjudicación',
                        'Tipo Adjudicación',
                        'Comentario Concurso',
                        'Calificación Tecnica',
                        'Usuario Adjudicación'
                    ]
                ];

                concursos.forEach(item => {
                    var vItem = [];
                    vItem.push(
                        item['id'](),
                        item['nombre'](),
                        item['areaSol'](),
                        item['pos'](),
                        item['item'](),
                        item['cant'](),
                        item['unidad'](),
                        item['unitario'](),
                        item['costoObj'](),
                        item['ahorro'](),
                        item['ahorroRel'](),
                        item['plazoP'](),
                        item['plazoE'](),
                        item['prov'](),
                        item['aceptAdj'](),
                        item['FechaAdj'](),
                        item['tipo'](),
                        item['comentario'](),
                        item['evalTech'](),
                        item['userAdj']()
                    );
                    ws_data.push(vItem);
                });

                var ws = XLSX.utils.aoa_to_sheet(ws_data);

                wb.Sheets["Concursos"] = ws;
                var wscols = [
                    { wch: 11 },
                    { wch: 50 },
                    { wch: 20 },
                    { wch: 17 },
                    { wch: 50 },
                    { wch: 10 },
                    { wch: 10 },
                    { wch: 15 },
                    { wch: 15 },
                    { wch: 10 },
                    { wch: 10 },
                    { wch: 10 },
                    { wch: 15 },
                    { wch: 20 },
                    { wch: 20 },
                    { wch: 20 },
                    { wch: 20 },
                    { wch: 20 },
                    { wch: 20 },
                    { wch: 20 }
                ];

                ws['!cols'] = wscols;

                XLSX.writeFile(wb, 'Concursos_Adjudicados.xlsx');
            };

            this.filter = function() {
                $.blockUI();
                var url = '/reportes/adjudicados/filter';
                Services.Post(url, {
                        UserToken: User.Token,
                        Filters: JSON.stringify(ko.toJS(self.Filters))
                    },
                    (response) => {
                        $.unblockUI();
                        if (response.success) {
                            self.Concursos.removeAll();
                            self.Details.removeAll();
                            if (response.data.list.length > 0) {
                                var newResults = [];
                                var newDetails = [];
                                response.data.list.forEach(item => {
                                    newResults.push(new ConcursoAdj(item));
                                    item.Detalles.forEach(i => {
                                        newDetails.push(new ConcursoReport(i));
                                    })
                                });
                                self.Concursos(newResults);
                                self.Details(newDetails);
                            }
                        }
                    },
                    (error) => {
                        $.unblockUI();
                        swal('Error', error.message, 'error');
                    }
                );
            }

            this.cleanFilters = function() {
                self.initFilters();
                self.filter();
            }

            this.initFilters = function() {
                self.Filters(new Filters(data.filtros));
            }

            self.initFilters();
        };



        jQuery(document).ready(function() {
            $.blockUI();
            var data = {
                UserToken: User.Token
            };
            var url = '/reportes/adjudicados/list';
            Services.Get(url, data,
                (response) => {
                    if (response.success) {
                        window.E = new ConfiguracionesConcursoAdj(response.data);
                        AppOptus.Bind(E);
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