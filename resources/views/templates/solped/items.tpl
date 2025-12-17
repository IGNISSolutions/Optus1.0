 <!-- ko if: !$root.ReadOnly() -->
<div class="row margin-bottom-40">
  <div class="col-md-6 form-group required" data-bind="validationElement: NewProduct().name">
    <label for="item">Nombre Item</label>
    <div class="input-group">
      <span class="input-group-btn">
        <a class="btn btn-primary" data-toggle="modal" data-target="#modal-filtros-materiales">Buscar</a>
      </span>
      <textarea rows="2" class="form-control"
        data-bind="value: NewProduct().name, attr: { title: NewProduct().name }"
        name="item" style="resize:none;"></textarea>
    </div>
  </div>

  <div class="col-md-6 form-group">
    <label for="descripcion">Descripción</label>
    <textarea name="descripcion" rows="2" class="form-control"
      data-bind="value: NewProduct().description, attr: { title: NewProduct().description }"
      style="resize:none;"></textarea>
  </div>

  <!-- Modal filtros materiales -->
  <div class="modal fade" id="modal-filtros-materiales" style="display:none;">
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
            <div class="card-header" id="headingCategorias"></div>
            <div class="card-body">
              <div class="form-group">
                <div class="col-md-12">
                  <!-- Envolvemos el control en with: Filters para no evaluar hasta que exista -->
                  <!-- ko with: Filters -->
                  <select class="form-control" id="filters_materiales" data-bind="
                      value: Material,
                      valueAllowUnset: true,
                      select2Safe: { placeholder: 'Seleccionar...', allowClear: true, matcher: matchStart },
                      disable: $root.ReadOnly()">
                    <!-- ko foreach: MaterialesList -->
                    <!-- OJO: cambiar 'materiales' si tu payload usa otro nombre -->
                    <optgroup data-bind="attr: { label: text }, foreach: materiales">
                      <option data-bind="value: id, text: text"></option>
                    </optgroup>
                    <!-- /ko -->
                  </select>
                  <!-- /ko -->
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline dark sbold"
                  data-bind="click: filtermaterial.bind($data, false)" data-dismiss="modal">Aplicar</button>
          <button type="button" class="btn btn-outline dark sbold" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- /ko -->

<div class="row margin-bottom-40">
  <div class="col-md-2 form-group required" data-bind="validationElement: NewProduct().quantity">
    <label class="control-label visible-ie8 visible-ie9" style="display:block;">Cantidad</label>
    <input type="number" class="form-control" min="0" data-bind="value: NewProduct().quantity">
  </div>

  <div class="col-md-2 form-group required" data-bind="validationElement: NewProduct().minimum_quantity">
    <label>Cantidad mínima</label>
    <input type="number" class="form-control"
      data-bind="value: NewProduct().minimum_quantity, attr: { 'min': 0, 'max': NewProduct().quantity }">
  </div>

  <div class="col-md-2 form-group required" data-bind="validationElement: NewProduct().measurement_id">
    <label class="control-label visible-ie8 visible-ie9" style="display:block;">Unidad de medida</label>
    <div class="selectRequerido">
      <select data-bind="
          value: NewProduct().measurement_id,
          valueAllowUnset: true,
          options: ProductMeasurementList,
          optionsText: 'text',
          optionsValue: 'id',
          select2Safe: { placeholder: 'Seleccionar...', allowClear: true },
          disable: ReadOnly()">
      </select>
    </div>
  </div>

  <div class="col-md-2 form-group">
    <label class="control-label visible-ie8 visible-ie9" style="display:block;">Cost obj unit</label>
    <input class="form-control placeholder-no-fix" data-bind="
        inputmask: { alias: 'monto' },
        value: NewProduct().targetcost,
        disable: $root.ReadOnly()" />
  </div>

  <div class="col-md-2">
    <div class="form-group text-right">
      <label class="control-label visible-ie8 visible-ie9" style="display:block;">&nbsp;</label>
      <a data-bind="click: ProductAddOrDelete.bind($data, 'add')"
         class="btn btn-xl btn-primary" title="Crear Item">
        <i class="fa fa-plus"></i>Crear
      </a>
    </div>
  </div>
</div>

<!-- Lista de items -->
<div class="table-responsive">
  <table class="table table-bordered paleRows" id="products" style="table-layout:fixed; width:100%;">
    <thead>
      <tr>
        <th class="text-center vertical-align-middle" style="white-space:nowrap;">Nombre</th>
        <th class="text-center vertical-align-middle" style="white-space:nowrap;">Descripción</th>
        <th class="text-center vertical-align-middle" style="white-space:nowrap;">Cantidad solicitada</th>
        <th class="text-center vertical-align-middle" style="white-space:nowrap;">Oferta mínima</th>
        <th class="text-center vertical-align-middle" style="white-space:nowrap;">Unidad de Medida</th>
        <th class="text-center vertical-align-middle" style="white-space:nowrap;">Costo objetivo</th>
        <!-- ko if: !$root.ReadOnly() -->
        <th class="text-center vertical-align-middle" style="white-space:nowrap;"></th>
        <!-- /ko -->
      </tr>
    </thead>

    <!-- ko if: !$root.ReadOnly() -->
    <tbody data-bind="dataTablesForEach: { data: Entity.Products, as: 'product', options: { paging: true, searching: false, ordering: false }}">
      <tr>
        <td>
          <textarea class="form-control placeholder-no-fix" rows="2" style="resize:none;"
            data-bind="value: name, attr: { title: name }, readonly: $root.ReadOnly()"></textarea>
        </td>
        <td>
          <textarea class="form-control placeholder-no-fix" rows="2" style="resize:none;"
            data-bind="value: description, attr: { title: description }, readonly: $root.ReadOnly()"></textarea>
        </td>
        <td>
          <input class="form-control placeholder-no-fix" type="number" min="0"
            data-bind="value: quantity, disable: $root.ReadOnly()" />
        </td>
        <td>
          <input class="form-control placeholder-no-fix" type="number"
            data-bind="value: minimum_quantity, attr: { 'min': 0, 'max': quantity }, disable: $root.ReadOnly()" />
        </td>
        <td>
          <select data-bind="
              value: measurement_id,
              valueAllowUnset: true,
              options: $root.ProductMeasurementList,
              optionsText: 'text',
              optionsValue: 'id',
              select2Safe: { placeholder: 'Seleccionar...', allowClear: true },
              disable: $root.ReadOnly()">
          </select>
        </td>
        <td>
          <input class="form-control placeholder-no-fix" data-bind="
              inputmask: { alias: 'monto' },
              value: targetcost,
              disable: $root.ReadOnly()" />
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
    <tbody data-bind="dataTablesForEach: { data: Entity.Products, as: 'product', options: { paging: true, searching: false, ordering: false }}">
      <tr>
        <td class="text-justify vertical-align-middle" style="white-space:normal;" data-bind="text: name"></td>
        <td class="text-justify vertical-align-middle" style="white-space:normal;" data-bind="text: description"></td>
        <td class="text-center vertical-align-middle" data-bind="text: quantity"></td>
        <td class="text-center vertical-align-middle" data-bind="text: minimum_quantity"></td>
        <td class="text-center vertical-align-middle" data-bind="text: $root.measurementName(measurement_id)"></td>
        <td class="text-center vertical-align-middle" data-bind="inputmask: { alias: 'monto' }, value: targetcost"></td>
      </tr>
    </tbody>
    <!-- /ko -->
  </table>
</div> 

