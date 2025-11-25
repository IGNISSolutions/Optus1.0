<!-- ko if: $root.UserType() === 'customer' || $root.UserType() === 'supervisor'-->
<div class="m-heading-1 border-default m-bordered text-left">
    <h4 class="block bold" style="margin-top: 0; padding-top: 0;" data-bind="text:TipoAdjudicacion()"></h4>
    <!-- ko if: AdjudicacionItems().length > 0 -->
    <table class="table table-striped table-bordered" id="ListaConcursosEconomicas">
        <thead class="text-center">
            <tr>
                <th class="text-center"> Producto </th>
                <th class="text-center"> Precio<br>Unitario </th>
                <th class="text-center"> Precio<br>Total </th>
                <th class="text-center"> Cantidad<br>Adjudicada </th>
                <th class="text-center"> Proveedor </th>
            </tr>
        </thead>
        <tbody data-bind="foreach: { data: AdjudicacionItems(), as:'item' }">
            <tr>
                <td data-bind="text: itemNombre" class="text-center vertical-align-middle"></td>
                <td data-bind="number: cotUnitaria, precision: 0" class="text-center vertical-align-middle"></td>
                <td data-bind="number: cotizacion, precision: 0" class="text-center vertical-align-middle">
                </td>
                <td data-bind="number: cantidadAdj" class="text-center vertical-align-middle"></td>
                <td data-bind="text: razonSocial" class="text-center vertical-align-middle">
                </td>
            </tr>
        </tbody>
        <tbody>
            <tr>
                <td data-bind="text: 'Comentario Adjudicación'" class="text-center bold vertical-align-middle"></td>
                <td colspan="4" data-bind="text:$root.AdjudicacionComentario()"></td>
            </tr>
        </tbody>
    </table>
    <!-- /ko -->

    <!-- ko if: AdjudicacionItems().length == 0 -->
    <div class="alert alert-success">
        Aun no existe adjudicación
    </div>
    <!-- /ko -->
</div>
<!-- /ko -->