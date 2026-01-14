{extends 'solped/solicitante/main.tpl'}

<!-- ESTILOS -->
{block 'styles'}
    <link href="{asset('/global/plugins/bootstrap-summernote/summernote.css')}" rel="stylesheet" type="text/css" />
    <link href="{asset('/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css')}" rel="stylesheet" type="text/css" />
    <link href="{asset('/global/plugins/bootstrap-tagsinput/bootstrap-tagsinput.css')}" rel="stylesheet" type="text/css" />
    <link href="{asset('/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css')}" rel="stylesheet" type="text/css" />
    <link href="{asset('/global/plugins/bootstrap-fileinput-kartik/css/fileinput.min.css')}" rel="stylesheet" type="text/css" />
    <link href="{asset('/global/plugins/datatables/datatables.min.css')}" rel="stylesheet" type="text/css" />
    <link href="{asset('/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css')}" rel="stylesheet" type="text/css" />
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
        var ServerBreadcrumbs = {$breadcrumbs|default:[]|@json_encode nofilter};
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
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCUtr9Ist4jejEMf2czdImyxk_EXoyWBgo&callback=initMapsolped&libraries=places&v=weekly">
    </script>

    <script>
        Inputmask.extendAliases({
            monto: {
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
<script>
(function () {
  // Binding SELECT2 seguro: no invoca allBindings.value()
  ko.bindingHandlers.select2Safe = {
    init: function (el, valueAccessor, allBindingsAccessor) {
      try {
        $.fn.select2.defaults.set('theme', 'bootstrap');
        $.fn.select2.defaults.set('language', 'es');
      } catch (_) {}
      var optsAcc = (typeof allBindingsAccessor === 'function' ? allBindingsAccessor() : allBindingsAccessor) || {};
      var opts = ko.utils.unwrapObservable(optsAcc.select2Safe) || {};
      $(el).select2(Object.assign({ width: 'auto', language: 'es' }, opts));

      // Limpieza
      ko.utils.domNodeDisposal.addDisposeCallback(el, function () {
        try { if ($(el).data('select2')) $(el).select2('destroy'); } catch(e){}
      });
    },
    update: function (el, valueAccessor, allBindingsAccessor) {
      // Dejamos que el binding KO `value:` haga su trabajo.
      // Solo re-sync si el select2 se perdió.
      if (!$(el).hasClass('select2-hidden-accessible') || !$(el).data('select2')) {
        var optsAcc = (typeof allBindingsAccessor === 'function' ? allBindingsAccessor() : allBindingsAccessor) || {};
        var opts = ko.utils.unwrapObservable(optsAcc.select2Safe) || {};
        try { if ($(el).data('select2')) $(el).select2('destroy'); } catch(e){}
        $(el).select2(Object.assign({ width: 'auto', language: 'es' }, opts));
      }
    }
  };
})();
</script>


{/block}



{block 'new-solped-form'}

    <div class="row" style="margin-top:20px;">
        <div class="col-md-12">
            <div class="portlet light bg-inverse">
                <div class="portlet-title">
                    <div class="caption font-red-sunglo">
                        <span class="caption-subject bold uppercase">{$title}</span>
                    </div>
                </div>
                <div class="portlet-body form">
                    {include file='solped/general.tpl'}
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="portlet light bg-inverse">
                <div class="portlet-title">
                    <div class="caption font-red-sunglo">
                        <span class="caption-subject bold uppercase">Descripción General</span>
                    </div>
                </div>
                <div class="portlet-body form">
                    {include file='solped/descripcion-general.tpl'}
                </div>
            </div>
        </div>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="portlet light bg-inverse">
          <div class="portlet-title">
            <div class="caption font-red-sunglo">
              <span class="caption-subject bold uppercase">Multimedia</span>
            </div>
          </div>
          <div class="portlet-body form">
            {include file='solped/multimedia.tpl'}
          </div>
        </div>
      </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="portlet light bg-inverse">
                <div class="portlet-title">
                    <div class="caption font-red-sunglo">
                        <span class="caption-subject bold uppercase">Configuración General</span>
                    </div>
                </div>
                <div class="portlet-body form">
                    {include file='solped/conf-general.tpl'}
                </div>
            </div>
        </div>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="portlet light bg-inverse">
          <div class="portlet-title">
            <div class="caption font-red-sunglo">
              <span class="caption-subject bold uppercase">ITEMS DE LA SOLICITUD DE PEDIDO</span>
            </div>
          </div>
           <div class="portlet-body form">
              {include file='solped/items.tpl'}
            </div> 
        </div>
      </div>
    </div> 
    

    <!-- ACCIONES -->
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group" style="margin-top:8px;">
                Todos los campos marcados con <b>*</b> son obligatorios para poder enviar las invitaciones.
            </div>
        </div>
        <div class="col-sm-6 text-right">
            <div class="form-group" style="display:inline-block; margin-left:10px;">
                <button type="button" class="btn btn-primary" data-bind="click: store">
                    Guardar Datos
                </button>
            </div>
            <div class="form-group" style="display:inline-block; margin-left:10px;">
                <button type="button" class="btn btn-success"
                        data-bind="click: sendSolpeds">
                    Enviar Solicitud
                </button>
            </div>
        </div>
    </div>

{/block}




{block 'knockout' append}
    <script >
        ko.validation.locale('es-ES');
        ko.validation.init({
            insertMessages: false,
            messagesOnModified: false,
            decorateElement: false,
            errorElementClass: 'wrong-field'
        }, false);
        
    
    // Sub-VM de formulario
    function Form(data, parent) {
        data = data || {}; data.list = data.list || {};
        var self = this;
        console.log("datos", data)

        this.Id = ko.observable(data.list.Id || 0);
        this.Nombre        = ko.observable(data.list.Nombre || '').extend({ required: true });
        this.Descripcion = ko.observable(data.list.Descripcion || '')
        this.CodigoInterno = ko.observable(data.list.CodigoInterno || '');
        this.Pais = ko.observable(data.list.Pais || '');
        this.Provincia = ko.observable(data.list.Provincia || '');
        this.Ciudad = ko.observable(data.list.Ciudad || '');

        this.TipoCompra = ko.observable(
        data.list.TipoCompraId != null ? Number(data.list.TipoCompraId) : null
        ).extend({ required: true });



        this.AreaSolicitante = ko.observable(
            data.list.AreaSolicitante != null ? Number(data.list.AreaSolicitante) : null
        ).extend({ required: true });


        this.Descripcion = ko.observable(data.list.Descripcion || '');

        // id del comprador seleccionado (único)
        this.CompradorSugeridoSelected = ko.observable(
            data.list.CompradorSugeridoId != null ? Number(data.list.CompradorSugeridoId) : null
        );

        // lista de opciones (id + text)
        this.CompradoresSugeridos = ko.observableArray(
            (data.list.CompradoresSugeridos || []).map(function (c) {
                return { id: Number(c.id), text: String(c.text) };
            })
        );

        // Fechas de resolución y entrega
        this.FechaResolucion = ko.observable(data.list.FechaResolucion || null).extend({ required: true });
        this.FechaEntrega = ko.observable(data.list.FechaEntrega || null).extend({ required: true });


        this.DescripcionDescription = this.Descripcion;

        this.DescriptionLimit = ko.observable(300);
        this.DescripcionImagePath = ko.observable(data.list.DescripcionImagePath || '');
        this.DescripcionPortrait = ko.observable(new Portrait());
            if (data.list.DescripcionPortrait) {
                self.DescripcionPortrait(new Portrait(data.list.DescripcionPortrait));
            }
        this.Portrait = ko.observable(new Portrait());
            if (data.list.Portrait) {
                self.Portrait(new Portrait(data.list.Portrait));
            }
        


        this.removeAll = function () {
            self.CompradorSugeridoSelected();

            var el = document.getElementById('compradores_sugeridos_list');
            if (el) { $(el).val(null).trigger('change.select2'); } 
            };
        this.Products = ko.observableArray(
            (data.list.Products || []).map(function (p) { return new Product(p); })
        );


    }
    var Portrait = function(filename = null) {
            var self = this;

            this.filename = ko.observable(filename ? filename : null);
            this.action = ko.observable(null);
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

    function Solped(data) {
        var self = this;
        data = data || {}; data.list = data.list || {};

        this.Breadcrumbs = ko.observableArray(
        (typeof ServerBreadcrumbs !== 'undefined' && Array.isArray(ServerBreadcrumbs))
            ? ServerBreadcrumbs
            : []
        );

        
        this.ReadOnly = ko.observable(false);
        this.NewProduct = ko.observable(new Product());


        // Estas listas son locales (estáticas) — se muestran cuando KO bindea
        this.TipoComprasDisponibles = ko.observableArray([
            { id: 1, text: 'Normal' },
            { id: 2, text: 'Urgencia' },
            { id: 3, text: 'Regularización' }
        ]);

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
            { id: 12, text: 'Informática' }
        ]);

        // Catálogos server (si llegan)
        this.ProductMeasurementList = ko.observableArray(data.list.ProductMeasurementList || []);

        this.ProductAddOrDelete = function(action, product = null) {
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
                            Products: newProducts
                        };
                        var data = {
                            Data: JSON.stringify(ko.toJS(body))
                        };
                        Services.Post('/solped/products/check', data,
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

        // SubVM
        this.Entity = new Form(data, self);

        this.sendSolpeds = function() {
                swal({
                    title: '¿Desea enviar la Solicitud de Pedido?',
                    text: 'Esta a punto de enviar las notificaciones para esta solicitud.',
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
                        var url = '/solped/invitations/send';
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
        this.store = function(){ /* TODO */ };

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
        }
        this.Filters = ko.observable();

        this.initFilters = function() {
            self.Filters(new Filters(data.filters));
        }

        this.clearFilters = function() {
            self.initFilters();
            self.filter();
        }

        self.initFilters();

        self.store = function () {
        // Validación en cliente con detalle de faltantes
        var entityErrors = ko.validation.group(self.Entity, { deep: true });
        var hasItems = self.Entity.Products().length > 0;

        var missing = [];
        if (!self.Entity.Nombre()) missing.push('Nombre');
        if (!self.Entity.AreaSolicitante()) missing.push('Área solicitante');
        if (!self.Entity.TipoCompra()) missing.push('Tipo de compra');
        if (!self.Entity.FechaResolucion()) missing.push('Fecha de resolución');
        if (!self.Entity.FechaEntrega()) missing.push('Fecha de entrega');
        if (!hasItems) missing.push('Al menos un ítem (producto)');

        // Regla: FechaResolucion < FechaEntrega y diferencia mínima 7 días
        var fechaResolStr = ko.unwrap(self.Entity.FechaResolucion);
        var fechaEntregaStr = ko.unwrap(self.Entity.FechaEntrega);
        var fechaResol = null, fechaEntrega = null;
        var fechaError = null;
        try {
            // Formato esperado: 'DD-MM-YYYY HH:mm' o similar
            var partsR = (fechaResolStr || '').split(/[- :]/);
            var partsE = (fechaEntregaStr || '').split(/[- :]/);
            if (partsR.length >= 3 && partsE.length >= 3) {
                // year, month-1, day, hour, minute
                fechaResol = new Date(parseInt(partsR[2],10), parseInt(partsR[1],10)-1, parseInt(partsR[0],10), parseInt(partsR[3]||'0',10), parseInt(partsR[4]||'0',10));
                fechaEntrega = new Date(parseInt(partsE[2],10), parseInt(partsE[1],10)-1, parseInt(partsE[0],10), parseInt(partsE[3]||'0',10), parseInt(partsE[4]||'0',10));
            }
            if (fechaResol && fechaEntrega) {
                var diffMs = fechaEntrega.getTime() - fechaResol.getTime();
                var diffDays = diffMs / (1000 * 60 * 60 * 24);
                if (diffMs <= 0) {
                    fechaError = 'La fecha de resolución debe ser anterior a la fecha de entrega.';
                } else if (diffDays < 7) {
                    fechaError = 'La diferencia entre resolución y entrega debe ser de al menos 7 días.';
                }
            }
        } catch (e) {
            // Ignorar parse errors, ya se valida campos requeridos
        }

        if ((entityErrors && entityErrors().length) || !hasItems) {
            if (entityErrors) { entityErrors.showAllMessages(true); }

            var msgParts = [];
            if (missing.length) {
                msgParts.push('Faltan completar: ' + missing.join(', '));
            }
            if (fechaError) {
                msgParts.push(fechaError);
            }
            var msg = msgParts.join('\n');
            swal('Faltan datos', msg || 'Por favor, complete los campos obligatorios.', 'warning');
            return;
        }

        // Si hay error de fechas pero no faltantes, bloquear envío también
        if (fechaError) {
            swal('Fechas inválidas', fechaError, 'warning');
            return;
        }

        $.blockUI();

        var actionFromVM = ko.unwrap(self.action); 
        var action = actionFromVM || (params[2] === 'edicion' ? 'edit' : 'create');

        var id = ko.unwrap(self.Entity.Id) || 0;

        var url = '/solped/save';

        // Armamos payload “limpio”
        var payload = {
            UserToken: User.Token,
            Data: JSON.stringify({
            Action: action,               // 'create' | 'edit'
            Id: id,                       // 0 si create
            Entity: ko.toJS(self.Entity), // tu formulario
            Filters: self.Filters ? ko.toJS(self.Filters()) : null 
            })
        };

        Services.Post(
            url,
            payload,
            function (response) {
            $.unblockUI();
            if (response && response.success) {
                swal({
                title: 'Hecho',
                text: response.message || 'Guardado correctamente.',
                type: 'success',
                closeOnClickOutside: false,
                closeOnConfirm: true,
                confirmButtonText: 'Aceptar',
                confirmButtonClass: 'btn btn-success'
                }, function () {
                window.history.back();
                });
            } else {
                swal('Error', (response && response.message) || 'Se produjo un error inesperado.', 'error');
            }
            },
            function (error) {
            $.unblockUI();
            swal('Error', (error && error.message) || 'Se produjo un error inesperado.', 'error');
            },
            null,
            null
        );
        };

    }



    var matchStart = function(params, data) {
        if ($.trim(params.term) === '') {
            return data;
        }

        if (typeof data.children === 'undefined') {
            return null;
        }

        var filteredChildren = [];

        $.each(data.children, function(idx, child) {
            if (data.text.toUpperCase().indexOf(params.term.toUpperCase()) !== -1 || child.text
                .toUpperCase().indexOf(params.term.toUpperCase()) !== -1 && filteredChildren
                .indexOf(child) === -1) {
                filteredChildren.push(child);
            }
        });

    if (filteredChildren.length) {
        var modifiedData = $.extend({}, data, true);
        modifiedData.children = filteredChildren;

        return modifiedData;
    }

    return null;
    };

    var is_init = true;

    var Product = function(data = null) {
        var self = this;

        this.id = ko.observable(data ? (data.id ?? null) : null);
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

    

jQuery(function () {
  $.blockUI();

  // id inyectado por el render (serveCreate/serveEdit)
  var solpedId = {$id|json_encode}; // número o 0

  // si hay id > 0, vamos al endpoint de edición; si no, al de creación
  var url = (solpedId && Number(solpedId) > 0)
    ? '/solped/edit/' + solpedId   // devuelve JSON con datos guardados
    : '/solped/create';            // JSON para nuevo

  console.log('[SOLPED] GET:', url);

  Services.Get(
    url,
    { UserToken: User.Token },
    function (response) {
      console.log('[SOLPED] response:', response);
      if (response && response.success) {
        // importante: Solped VM debe estar cargado antes de este script
        window.E = new Solped(response.data);
        E.action = ko.observable(solpedId ? 'edit' : 'create');
        AppOptus.Bind(E);

        if (response.data && response.data.breadcrumbs && E.Breadcrumbs) {
          E.Breadcrumbs(response.data.breadcrumbs);
        }
      } else {
        swal('Error', (response && response.message) || 'No se pudo cargar la Solped', 'error');
      }
      $.unblockUI();
    },
    function (error) {
      console.error('[SOLPED] error:', error);
      $.unblockUI();
      swal('Error', error.message || 'Fallo la carga', 'error');
    }
  );

  // (opcional) tus extras de UI:
  $('textarea[name="maxlength_textarea"]').on('keypress', function(event){
    var regex = new RegExp("^[a-zA-Z0-9 ]+$");
    var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
    if (!regex.test(key)) { event.preventDefault(); return false; }
  });

  $('.summernote').summernote({
    toolbar: [
      ['style', ['italic','underline','clear']],
      ['font', ['strikethrough','superscript','subscript']],
      ['fontsize', ['fontsize']],
      ['color', ['color']],
      ['para', ['ul','ol','paragraph']],
      ['height', ['height']]
    ]
  });

  {chromeDebugString('dynamicScript')}
});
    </script>
{/block}
