<h4 class="block bold" style="margin-top: 0; padding-top: 0;">Mejor oferta individual</h4>
<!-- ko if: ConcursoEconomicas.mejoresOfertas.mejorIndividual.individual.length > 0 -->
<table class="table table-striped table-bordered" id="ListaConcursosEconomicas">
    <thead class="text-center">
        <tr>
            <th class="text-center"> Item </th>
            <th class="text-center"> Cantidad<br>Solicitada </th>
            <th class="text-center"> Costo<br>Objetivo </th>
            <th class="text-center"> Precio<br>Unitario </th>
            <th class="text-center"> Precio<br>Total </th>
            <th class= "text-center" data-bind="text: $root.EsAscendente() ? 'Ganancia %' : 'Ahorro %'"></th>
            <th class= "text-center" data-bind="text: $root.EsAscendente() ? 'Ganancia abs' : 'Ahorro abs'"></th>
            <th class= "text-center" data-bind="text: $root.EsAscendente() ? 'Oferente' : 'Proveedor'"></th>
        </tr>
    </thead>
    <tbody data-bind="foreach: { data: ConcursoEconomicas.mejoresOfertas.mejorIndividual.individual, as:'item' }">
        <tr>
            <td data-bind="text: itemNombre" class="text-center vertical-align-middle"></td>
            <td data-bind="number: itemCantidad, precision: 0" class="text-center vertical-align-middle">
            </td>
            <td data-bind="number: targetcost, precision: 2" class="text-center vertical-align-middle"></td>
            <td data-bind="number: itemCotizacion, precision: 2" class="text-center vertical-align-middle">
            </td>
            <td data-bind="number: itemCotizacion * itemCantidad, precision: 2"
                class="text-center vertical-align-middle"></td>
            <!-- ko if: targetcost == 0 -->
            <td data-bind="text: 'No aplica'" class="text-center vertical-align-middle"></td>
            <td data-bind="text: 'No aplica'" class="text-center vertical-align-middle"></td>
            <!-- /ko -->
            <!-- ko if: targetcost > 0 -->
            <td class="text-center vertical-align-middle"
                data-bind="number: $root.EsAscendente() ? ganancia_porc : ahorro_porc,
                        precision: 2, symbol: '%', after: true,
                        style: { color: ($root.EsAscendente() ? ganancia_porc : ahorro_porc) == 0 
                                        ? 'black' 
                                        : (($root.EsAscendente() ? ganancia_porc : ahorro_porc) > 0 ? 'green' : 'red') }">
            </td>

            <td class="text-center vertical-align-middle"
                data-bind="number: $root.EsAscendente() ? ganancia_abs : ahorro_abs,
                        precision: 2,
                        style: { color: ($root.EsAscendente() ? ganancia_abs : ahorro_abs) == 0 
                                        ? 'black' 
                                        : (($root.EsAscendente() ? ganancia_abs : ahorro_abs) > 0 ? 'green' : 'red') }">
            </td>
            <!-- /ko -->
            <td data-bind="html: razonSocial" class="text-center vertical-align-middle"></td>
        </tr>
        <!-- ko if: $index() === ($parent.ConcursoEconomicas.mejoresOfertas.mejorIndividual.individual.length - 1) -->
        <tr>
            <td data-bind="text: 'Oferta total'" class="text-center bold vertical-align-middle"></td>
            <td></td>
            <td></td>
            <td></td>
            <td data-bind="number: $parent.ConcursoEconomicas.mejoresOfertas.mejorIndividual.total1, precision: 2"
                class="text-center bold vertical-align-middle"></td>
            <td data-bind="text: ''" class="text-center vertical-align-middle"></td>
        </tr>
        <!-- /ko -->
    </tbody>
</table>
<!-- /ko -->
<!-- ko if: ConcursoEconomicas.mejoresOfertas.mejorIndividual.individual.length == 0 -->
<div class="alert alert-success">
    No hay mejor oferta individual.
</div>
<!-- /ko -->