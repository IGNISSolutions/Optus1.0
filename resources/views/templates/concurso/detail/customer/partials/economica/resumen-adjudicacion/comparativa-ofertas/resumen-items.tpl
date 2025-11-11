<div class="m-heading-1 border-default m-bordered text-left">
    <h4 class="block bold" style="margin-top: 0; padding-top: 0;">Resumen por items</h4>
    

    <table class="table table-striped table-bordered" data-bind="attr: { id: 'ResumenItems-' + $index() }" style="width:100%">
        <thead style="background-color: #ccc; color: #000000;">
            <tr>
                <th class="dt-head-center" data-bind="text: 'Moneda: ' + $root.Moneda()" colspan="5"
                    data-dt-order="disable"></th>
                <!-- ko foreach: ConcursoEconomicas.proveedores -->
                <!-- ko ifnot: isRechazado || isVencido -->
                <th class="dt-head-center" data-bind="text: $data.razonSocial" colspan="6" data-dt-order="disable"></th>
                <!-- /ko -->
                <!-- /ko -->
            </tr>
            <tr>
                <th class="vertical-align-middle dt-head-center"></th>
                <th class="vertical-align-middle dt-head-center">ITEMS</th>
                <th class="vertical-align-middle dt-head-center">Cantidad Solicitada</th>
                <th class="vertical-align-middle dt-head-center">Costo Objetivo</th>
                <th class="vertical-align-middle dt-head-center">Descripción</th>
                <!-- ko foreach: ConcursoEconomicas.proveedores -->
                <!-- ko ifnot: isRechazado || isVencido -->
                <th class="vertical-align-middle dt-head-center">Plazo entrega</th>
                <th class="vertical-align-middle dt-head-center">Cantidad Cotizada</th>
                <th class="vertical-align-middle dt-head-center">Precio Unitario</th>
                <th class="vertical-align-middle dt-head-center">Total</th>
                <th data-bind="text: ($root.EsAscendente() || $root.EsVenta()) ? 'Ganancia Abs' : 'Ahorro Abs'"></th>
                <th data-bind="text: ($root.EsAscendente() || $root.EsVenta()) ? 'Ganancia %' : 'Ahorro %'"></th>
                <!-- /ko -->
                <!-- /ko -->
            </tr>
        </thead>
        <tbody data-bind="dataTablesForEach : { 
                data: ConcursoEconomicas.productos, 
                options: $root.configDataTables
            }">
            <tr>
                <td class="vertical-align-middle texto dt-head-center" data-bind="text: $index() + 1" style=""></td>
                <td data-bind="text: nombre"></td>
                <td data-bind="text: cantidad + ' - ' + unidad_medida.name"
                    class="text-center vertical-align-middle dt-head-center">
                </td>
                <td data-bind="number: targetcost, precision: 2"
                    class="text-center vertical-align-middle dt-head-center"></td>
                <!-- ko foreach: $parent.ConcursoEconomicas.proveedores -->
<!-- ko ifnot: isRechazado || isVencido -->
    <!-- ko if: $data.items && $data.items.length > $parentContext.$index() -->
        <td class="text-center vertical-align-middle texto dt-head-center"
            data-bind="text: $data.items[$parentContext.$index()].fecha == 0 ? '—' : $data.items[$parentContext.$index()].fecha,
                       style: { background: $data.items[$parentContext.$index()].isMenorPlazo ? '#c6e0b4' : '#ffffff' }">
        </td>
        <td class="text-center vertical-align-middle dt-head-center"
            data-bind="number: $data.items[$parentContext.$index()].cantidad, precision: 0,
                       style: { background: $data.items[$parentContext.$index()].isMenorCantidad ? '#c6e0b4' : '#ffffff' }">
        </td>
        <td class="text-center vertical-align-middle dt-head-center"
            data-bind="number: $data.items[$parentContext.$index()].cotizacion, precision: 2,
                       style: { background: $data.items[$parentContext.$index()].isMejorCotizacion ? '#c6e0b4' : '#ffffff' }">
        </td>
        <td class="text-center vertical-align-middle dt-head-center"
            data-bind="number: $data.items[$parentContext.$index()].subtotal, precision: 2">
        </td>
        <td class="text-center vertical-align-middle dt-head-center"
            data-bind="number: ($root.EsAscendente() || $root.EsVenta())
                            ? $data.items[$parentContext.$index()].ganancia_abs
                            : $data.items[$parentContext.$index()].ahorro_abs,
                    precision: 2">
        </td>

        <td class="text-center vertical-align-middle dt-head-center"
            data-bind="number: ($root.EsAscendente() || $root.EsVenta())
                            ? $data.items[$parentContext.$index()].ganancia_porc
                            : $data.items[$parentContext.$index()].ahorro_porc,
                    precision: 2">
        </td>
    <!-- /ko -->
    <!-- ko ifnot: $data.items && $data.items.length > $parentContext.$index() -->
        <td class="text-center vertical-align-middle texto dt-head-center">—</td>
        <td class="text-center vertical-align-middle dt-head-center">—</td>
        <td class="text-center vertical-align-middle dt-head-center">—</td>
        <td class="text-center vertical-align-middle dt-head-center">—</td>
        <td class="text-center vertical-align-middle dt-head-center">—</td>
        <td class="text-center vertical-align-middle dt-head-center">—</td>
    <!-- /ko -->
<!-- /ko -->
<!-- /ko -->
            </tr>
            <!-- /ko -->
        </tbody>
    </table>

</div>