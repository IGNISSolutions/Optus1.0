<!-- ko if: ItemsMejores().length > 0 -->
<div class="row">
    <div class="col-sm-12">
        <h4 class="block bold">
            Valores actuales en vivo
        </h4>

        <table class="table table-striped table-bordered">
            <thead>
                <tr class="success">
                    <th class="text-center bold" style="white-space: nowrap;"> Item </th>
                    <th class="text-center bold" style="white-space: nowrap;"> Unidad de Medida </th>
                    <th class="text-center bold" style="white-space: nowrap;"> Cant. Solicitada </th>
                    <th class="text-center bold" style="white-space: nowrap;"> Cant. Mínima </th>
                    <th class="text-center bold" style="white-space: nowrap;"> Mejor Oferta </th>
                    <th class="text-center bold" style="white-space: nowrap;"> Hora </th>
                    <th class="text-center bold" style="white-space: nowrap;"> Oferente </th>
                    <th class="text-center bold" style="white-space: nowrap;"> Cant. Ofertada </th>
                </tr>
            </thead>

            <tbody data-bind="foreach: ItemsMejores">
                <tr>
                    <td class="bold text-center" style="white-space: nowrap;vertical-align: middle;" data-bind="text: producto"></td>
                    <td class="bold text-center" data-bind="text: sol_unidad" style="vertical-align: middle;"></td>
                    <td class="bold text-center" data-bind="text: sol_cantidad" style="vertical-align: middle;"></td>
                    <td class="bold text-center" data-bind="text: sol_oferta_minima" style="vertical-align: middle;"></td>
                    <td data-bind="number: cotizacion, precision: 0" class="text-center" style="white-space: nowrap;vertical-align: middle;"></td>
                    <td data-bind="text: hora" class="text-center" style="vertical-align: middle;"></td>
                    <td data-bind="text: razon_social" class="text-center" style="white-space: nowrap;vertical-align: middle;"></td>
                    <td data-bind="text: cantidad" class="text-center" style="vertical-align: middle;"></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<!-- /ko -->

<!-- ko if: Log().length > 0 -->
<div class="row">
    <div class="col-sm-12">
        <h4 class="block bold">
            Registro de Ofertas
        </h4>

        <table id="subastaLog" class="table table-striped table-bordered order-column dataTable" role="grid">
            <thead>
                <tr class="active" role="row">
                    <th class="text-center bold sorting" tabindex="0" style="white-space: nowrap;"> Item </th>
                    <th class="text-center bold sorting" tabindex="0" style="white-space: nowrap;"> Oferta </th>
                    <th class="text-center bold sorting" tabindex="0" style="white-space: nowrap;"> Hora </th>
                    <th class="text-center bold sorting" tabindex="0" style="white-space: nowrap;" data-bind="text: ($root.EsAscendente() ? 'Oferente' : 'Proveedor')"> </th>
                    <th class="text-center bold sorting" tabindex="0" style="white-space: nowrap;"> Cant. Ofertada </th>
                </tr>
            </thead>

            <tbody data-bind="dataTablesForEach : { data: Log, options: { paging: false, searching: false, scrollY: '500px', scrollCollapse: true }}">
                <tr role="row">
                    <td class="bold text-center" data-bind="text: producto" style="white-space: nowrap;vertical-align: middle;" ></td>
                    <td data-bind="number: cotizacion, precision: 0" class="text-center" style="white-space: nowrap;vertical-align: middle;"></td>
                    <td data-bind="text: creado" class="text-center" style="vertical-align: middle;"></td>
                    <td data-bind="text: razon_social" class="text-center" style="white-space: nowrap;vertical-align: middle;"></td>
                    <td data-bind="text: cantidad" class="text-center" style="vertical-align: middle;"></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<!-- /ko -->