<!-- ko if: !$root.ReadOnly() -->
<div class="row margin-bottom-40">
    <div class="col-md-6 form-group required" data-bind="validationElement: NewProduct().name">
        <label for="item">Nombre Item</label>
        <div class="input-group">
            <span class="input-group-btn">
                <!--<button type="button" class="btn btn-default" data-target="#modal-filtros-materiales">Buscar</button>-->
                <a class="btn btn-primary" data-toggle="modal" data-target="#modal-filtros-materiales">
                    Buscar
                </a>
            </span>
            <textarea rows="2" class="form-control"
                data-bind="value: NewProduct().name, attr: { title: NewProduct().name }" name="item"
                style="resize:none;"></textarea>
        </div>
    </div>

    <div class="col-md-6 form-group">
        <label for="descripcion">Descripción</label>
        <textarea name="descripcion" rows="2" class="form-control"
            data-bind="value: NewProduct().description, attr: { title: NewProduct().description }"
            style="resize:none;"></textarea>
    </div>

    <div class="modal fade" id="modal-filtros-materiales" style="display: none;">
        <div class="modal-dialog" style="height:80%;">
            <div class="modal-content c-square">
                <div class="modal-header" style="height:10px;">
                    <h4 class="modal-title bold">Seleccione el item</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body" style="height:200px;">
                    <div class="card">
                        <div class="card-header" id="headingCategorias">
                        </div>
                        <!--<div id="collapseCategorias" class="collapse" aria-labelledby="headingCategorias" data-parent="#accordion">-->
                        <div class="card-body">
                            <div class="form-group">
                                <div class="col-md-12">
                                    <select class="form-control" id="filters_materiales" data-bind="
                                                value: Filters().Material, 
                                                valueAllowUnset: true,
                                                select2: { placeholder: 'Seleccionar...', allowClear: true, matcher:matchStart },
                                                disable: ReadOnly()">
                                        <!-- ko foreach: Filters().MaterialesList -->
                                        <optgroup data-bind="attr: { label: text }, foreach: meteriales">
                                            <option data-bind="value: id, text: text"></option>
                                        </optgroup>
                                        <!-- /ko -->
                                    </select>
                                </div>
                            </div>
                        </div>
                        <!--</div>-->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline dark sbold"
                        data-bind="click: filtermaterial.bind($data, false)" data-dismiss="modal">
                        Aplicar
                    </button>
                    <button type="button" class="btn btn-outline dark sbold" data-dismiss="modal">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row margin-bottom-40">
    <div class="col-md-2 form-group required" data-bind="validationElement: NewProduct().quantity">

        <label class="control-label visible-ie8 visible-ie9" style="display: block;">
            Cantidad
        </label>
        <input type="number" class="form-control" min="0" data-bind="value: NewProduct().quantity">

    </div>
    <!-- ko if: IsSobrecerrado() || IsOnline() -->
    <div class="col-md-2 form-group required" data-bind="validationElement: NewProduct().minimum_quantity">

        <label>
            Cantidad miníma
        </label>
        <input type="number" class="form-control"
            data-bind="value: NewProduct().minimum_quantity, attr: { 'min': 0, 'max': NewProduct().quantity }">

    </div>
    <!-- /ko -->
    <div class="col-md-2 form-group required" data-bind="validationElement: NewProduct().measurement_id">
        <label class="control-label visible-ie8 visible-ie9" style="display: block;">
            Unidad de medida
        </label>
        <div class="selectRequerido">
            <select
                data-bind="value: NewProduct().measurement_id, valueAllowUnset: true, options: ProductMeasurementList, optionsText: 'text', optionsValue: 'id', select2: { placeholder: 'Seleccionar...', allowClear: true }, disable: ReadOnly()">
            </select>
        </div>
    </div>

    <div class="col-md-2 form-group">

        <label class="control-label visible-ie8 visible-ie9" style="display: block;">
            Cost obj unit
        </label>

        <input class="form-control placeholder-no-fix" data-bind="inputmask: { 
                alias: 'monto'
            }, value: NewProduct().targetcost,
            disable: $root.ReadOnly()" 
        />
        

    </div>

    <div class="col-md-2">
        <div class="form-group text-right">
            <label class="control-label visible-ie8 visible-ie9" style="display: block;">&nbsp;</label>
            <a data-bind="click: ProductAddOrDelete.bind($data, 'add'), visible: IsVisibleSaveProduct()"
                class="btn btn-xl btn-primary" title="Crear Item">
                <i class="fa fa-plus"></i>Crear
                <!--Crear Item-->
            </a>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="btn-group">
                    <a data-bind="click: downloadPlantillaExcel" download class="btn sbold blue"
                        title="Exportar Plantilla">
                        Exportar la plantilla para la importación
                        <i class="fa fa-download" aria-hidden="true"></i>
                    </a>
                </div>
            </div>

            <div class="portlet-title">
                <table class="table table-striped table-bordered" id="ListaConcursosEconomicas">
                    <tbody>
                        <tr>
                            <td colspan="3">
                                <h4>Instrucciones para importar materiales desde excel</h4>
                                <ul class="list-unstyled">
                                    <li>1. Adjunte archivo con sus materiales en formato XLS, XLSX.</li>
                                    <li>2. Procese el archivo.</li>
                                    <li>4. En caso de existir inconsistencias en la estructura del archivo se indicará
                                        error y todos los registros serán rechazados.</li>
                                </ul>
                            </td>
                        </tr>
                    </tbody>
                    <tbody>
                        <tr>
                            <td class="col-md-5 vertical-align-middle">
                                <input type="file" data-bind="fileUploadExcel: uploadFile">
                            </td>
                            <td class="col-md-3 text-center vertical-align-middle">
                                <!-- ko if: uploadFile -->
                                <a data-bind="click: uploadFileProcesar" download class="btn btn-xl green"
                                    title="Importar">
                                    Importar
                                    <i class="fa fa-download"></i>
                                </a>
                                <a data-bind="click: uploadFileclear" download class="btn btn-xl red" title="Limpiar">
                                    Limpiar
                                    <i class="fa fa-download"></i>
                                </a>
                                <!-- /ko -->
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="portlet-body">
                <div class="table-toolbar">
                    <div class="row">
                        <div class="col-md-6">
                        </div>
                        <div class="col-md-6">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /ko -->
<div class="row">
    <div class="col-md-12">
        <div class="portlet light bordered">
            <div class="portlet-body">
                <div class="table-toolbar">
                    <div class="row">
                        <div class="col-md-6">
                            <!-- ko if: !$root.ReadOnly() && Entity.Products().length > 0 -->
                            <a data-bind="click: deleteAllProducts" class="btn sbold red"
                                title="Borrar todos los items">
                                <i class="fa fa-trash"></i> Borrar todos los items
                            </a>
                            <!-- /ko -->
                        </div>
                        <div class="col-md-6">
                            <!-- Campo de búsqueda personalizado -->
                            <div class="form-group" style="margin-bottom:0;">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                    <input type="text" class="form-control" id="customProductSearch" placeholder="Buscar por nombre o descripción...">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default" title="Limpiar búsqueda" id="clearCustomSearch">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <table class="table table-bordered paleRows" id="products">
                    <thead>
                        <tr>
                            <th class="text-center vertical-align-middle" style="white-space: nowrap;">
                                Nombre
                            </th>
                            <th class="text-center vertical-align-middle" style="white-space: nowrap;">
                                Descripción
                            </th>
                            <th class="text-center vertical-align-middle" style="white-space: nowrap;">
                                Cantidad solicitada
                            </th>
                            <th class="text-center vertical-align-middle" style="white-space: nowrap;"
                                data-bind="visible: $root.IsSobrecerrado() || $root.IsOnline()">
                                Oferta mínima
                            </th>
                            <th class="text-center vertical-align-middle" style="white-space: nowrap;">
                                Unidad de Medida
                            </th>
                            <th class="text-center vertical-align-middle" style="white-space: nowrap;">
                                Costo objetivo
                            </th>
                            <!-- ko if: !$root.ReadOnly() -->
                            <th class="text-center vertical-align-middle" style="white-space: nowrap;"></th>
                            <!-- /ko -->
                        </tr>
                    </thead>
                    <!-- ko if: !$root.ReadOnly() -->
                    <tbody
                        data-bind="dataTablesForEach: { data: Entity.Products, as: 'product', options: { paging: true, searching: false, ordering: false }}">
                        <tr>
                            <td>
                                <textarea class="form-control placeholder-no-fix" rows="2" style="resize:none;"
                                    data-bind="value: name, attr: { title: name }, readonly: $root.ReadOnly()"></textarea>
                            </td>
                            <td>
                                <textarea class="form-control placeholder-no-fix" rows="2" style="resize:none;"
                                    data-bind="value: description, attr: { title: description }, readonly: $root.ReadOnly()"></textarea>
                            </td>
                            <td class="col-md-1">
                                <input class="form-control placeholder-no-fix" type="number" min="0"
                                    data-bind="value: quantity, disable: $root.ReadOnly()" />
                            </td>
                            <td class="col-md-1" data-bind="visible: $root.IsSobrecerrado() || $root.IsOnline()">
                                <input class="form-control placeholder-no-fix" type="number"
                                    data-bind="value: minimum_quantity, attr: { 'min': 0, 'max': quantity }, disable: $root.ReadOnly()" />
                            </td>
                            <td class="col-md-1">
                                <select
                                    data-bind="value: measurement_id, 
                                valueAllowUnset: true, 
                                options: $root.ProductMeasurementList, 
                                optionsText: 'text', 
                                optionsValue: 'id', 
                                select2: { placeholder: 'Seleccionar...', allowClear: true }, disable: $root.ReadOnly()">
                                </select>
                            </td>
                            <td class="col-md-1">
                                <input class="form-control placeholder-no-fix" data-bind="inputmask: {
                                alias: 'monto'
                            }, value: targetcost,disable: $root.ReadOnly()" />
                            </td>
                            <td class="text-center" data-bind="visible: !$root.ReadOnly()">
                                <a data-bind="click: $root.ProductAddOrDelete.bind($data, 'delete', product)"
                                    class="btn btn-xl btn-danger" title="Eliminar">
                                    <i class="fa fa-trash-o"></i>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                    <!-- /ko -->
                    <!-- ko if: $root.ReadOnly() -->
                    <tbody
                        data-bind="dataTablesForEach: { data: Entity.Products, as: 'product', options: { paging: true, searching: false, ordering: false }}">
                        <tr>
                            <td class="text-justify vertical-align-middle" style="white-space: normal;"
                                data-bind="text: name">
                            </td>
                            <td class="text-justify vertical-align-middle" style="white-space: normal;"
                                data-bind="text: description">
                            </td>
                            <td class="text-center vertical-align-middle col-md-1" data-bind="text: quantity">

                            </td>
                            <td class="text-center vertical-align-middle col-md-1"
                                data-bind="visible: $root.IsSobrecerrado() || $root.IsOnline(), text: minimum_quantity">

                            </td>
                            <td class="text-center vertical-align-middle col-md-1"
                                data-bind="text: $root.measurementName(measurement_id)">
                            </td>
                            <td class="text-center vertical-align-middle col-md-1" data-bind="inputmask: {
                                alias: 'monto'
                            }, value: targetcost">
                            </td>
                        </tr>
                    </tbody>
                    <!-- /ko -->
                </table>
            </div>
        </div>
    </div>
</div>