<!-- ko if: ConcursoEconomicas()[0]['oferenteItems'].length > 0 -->

<table class="table table-striped table-bordered" id="manualAdjudication">
    <thead class="text-center">
        <tr>
            <th class="text-center">
                Item
            </th>
            <th class="text-center">
                Cantidad solicitada
            </th>
            <th class="text-center">
                Proveedor
            </th>
            <th class="text-center">
                Cotización unitaria
            </th>
            <th class="text-center">
                Cantidad Cotizada
            </th>
            <th class="text-center">
                Cantidad Asignada
            </th>
            <th class="text-center">
                Cotización
            </th>
            <th class="text-center" data-bind="visible: !$root.Adjudicado()">
                Acciones
            </th>
        </tr>
    </thead>
    <tbody
        data-bind="dataTablesForEach: { data: ManualAdjudication().items, as: 'adjudication_item', options: { paging: false, searching: false, ordering: false }}">
        <tr>
            <td class="vertical-align-middle">
                <select data-bind="value: product_id, 
                        valueAllowUnset: true, 
                        options: $root.ManualAdjudication().products, 
                        optionsText: 'text', 
                        optionsValue: 'id', 
                        select2: { placeholder: 'Seleccionar...' }, disable: $root.Adjudicado()">
                </select>
            </td>
            <td class="vertical-align-middle text-center">
                <span data-bind="text: product() ? product().quantity : null"></span>
            </td>
            <td class="vertical-align-middle">
                <select
                    data-bind="value: offerer_id, 
                        valueAllowUnset: true, 
                        options: offerers, 
                        optionsText: 'text', 
                        optionsValue: 'id', 
                        select2: { placeholder: 'Seleccionar...', allowClear: true }, disable: !product_id() || $root.Adjudicado()">
                </select>
            </td>
            <td class="vertical-align-middle text-center">
                <span data-bind="text: offerer() ? offerer().priceShow : null"></span>
            </td>
            <td class="vertical-align-middle text-center">
                <span data-bind="text: offerer() ? offerer().quantity : null"></span>
            </td>
            <td class="vertical-align-middle">
                <input class="form-control placeholder-no-fix " type="number" data-bind="value: $root.Adjudicado() ? cantidadAdj : quantity, attr: { 
                        'min': 0, 
                        'max': offerer() ? offerer().quantity() : 0 
                    }, disable: !offerer_id() || $root.Adjudicado()" />
            </td>
            <td class="vertical-align-middle text-center">
                <span data-bind="text: total()"></span>
            </td>
            <td class="vertical-align-middle text-center" data-bind="visible: !$root.Adjudicado()">
                <button data-bind="click: $root.AdjudicationItemAddOrDelete.bind($data, 'delete', adjudication_item)"
                    class="btn btn-xl btn-danger" title="Eliminar">
                    <i class="fa fa-trash-o"></i>
                </button>
            </td>
        </tr>
    </tbody>
    <tbody data-bind="visible: ManualAdjudication().items().length > 0">
        <tr>
            <td colspan="6" class="text-bold vertical-align-middle text-right">
                <span>Oferta total</span>
            </td>
            <td class="text-bold vertical-align-middle text-center">
                <span data-bind="text: ManualAdjudication().total()"></span>
            </td>
            <td></td>
        </tr>
    </tbody>
    <!-- /ko -->
</table>
<!-- ko if: AlgunoPresentoEconomica() && !Adjudicado() && !Eliminado() -->
<div class="row">
    <div class="col-md-12">
        <div class="form-group text-right">
            <label class="control-label visible-ie8 visible-ie9" style="display: block;">&nbsp;</label>
            <button data-bind="click: AdjudicationItemAddOrDelete.bind($data, 'add')" class="btn btn-xl btn-success"
                title="Agregar Item">
                <i class="fa fa-plus"></i>
                Agregar Item
            </button>
        </div>
    </div>
</div>
<!-- /ko -->

<!-- /ko -->

<!-- ko if: ConcursoEconomicas()[0]['oferenteItems'].length == 0 -->
<div class="alert alert-success">
    No hay ofertas para adjudicar.
</div>
<!-- /ko -->