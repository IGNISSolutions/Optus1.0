<!-- ko if: Items().length > 0 -->
<div class="table-responsive">
    <table class="table table-bordered" id="ListaConcursosEconomicas">
        <thead>
            <tr class="success">
                <th class="text-center uppercase" style="white-space: nowrap;">
                    ITEM
                </th>
                <th class="text-center uppercase" style="white-space: nowrap;">
                    UNIDAD
                </th>
                <th class="text-center uppercase" style="white-space: nowrap;">
                    MONEDA
                </th>
                <th class="text-center uppercase" style="white-space: nowrap;">
                    CANTIDAD SOLICITADA
                </th>
                <th class="text-center uppercase" style="white-space: nowrap;">
                    CANTIDAD MÍNIMA
                </th>
            </tr>
        </thead>
        <tbody data-bind="foreach: Items">
            <tr>
                <td style="white-space: nowrap;" class="text-center bold valign-middle" data-bind="text: nombre"></td>
                <td data-bind="text: unidad" class="text-center bold valign-middle"></td>
                <td style="white-space: nowrap;" class="text-center bold valign-middle" data-bind="text: $parent.Moneda"></td>
                <td data-bind="text: cantidad" class="text-center bold valign-middle"></td>
                <td data-bind="text: oferta_minima" class="text-center bold valign-middle"></td>
            </tr>
            <tr>
                <td colspan="5">
                    <table class="table table-bordered table-advance">
                        <thead>
                            <tr>
                                <th class="text-center" style="white-space: nowrap;">
                                    Mejor oferta presentada
                                </th>
                                <th class="text-center" style="white-space: nowrap;">
                                    Posición de su oferta
                                </th>
                                <th class="text-center" style="white-space: nowrap;">
                                    Precio unitario
                                </th>
                                <th class="text-center" style="white-space: nowrap;">
                                    Cantidad a ofertar
                                </th>
                                <th class="text-center" style="white-space: nowrap;">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <input type="hidden" data-bind="attr: { value: valores().producto }">
                                <td 
                                    class="text-center valign-middle" 
                                    data-bind="number: 
                                        valores_mejor().cotizacion && $parent.VerOfertaGanadora() ? 
                                        valores_mejor().cotizacion : 
                                        'No disponible', precision: 2">
                                </td>
                                <!-- ko if: $parent.VerRanking() -->
                                <td class="text-center valign-middle" data-bind="text: 
                                    oferta_puesto() ? 
                                    oferta_puesto() + '° Puesto' + (empatado() ? ' (empatado)' : '') : 
                                    'No disponible'">
                                </td>
                                <!-- /ko -->
                                <!-- ko if: !$parent.VerRanking() -->
                                <td class="text-center valign-middle" data-bind="text: 
                                    oferta_puesto() == '1' ? 
                                    '1° Puesto' + (empatado() ? ' (empatado)' : '') : 
                                    (
                                        oferta_puesto() ? 
                                        'Su oferta no es ganadora' : 
                                        'No disponible'
                                    )">
                                </td>
                                <!-- /ko -->
                                <td class="valign-middle">
                                    <input class="form-control" type="number" data-bind="
                                        attr: { 
                                            min: 1 
                                        }, 
                                        value: valores().cotizacion, 
                                        disable: $parent.EnableEconomic() ?
                                            (($parent.Descendente() && valores().cotizacion == $parent.PrecioMinimo()) ||
                                            (!$parent.Descendente() && valores().cotizacion == $parent.PrecioMaximo()) ? true : false) :
                                            true">
                                </td>
                                <td class="valign-middle">
                                    <input 
                                        class="form-control" 
                                        type="number" 
                                        data-bind="attr: { 
                                            id: 'cantidad_' + $index() 
                                        }, 
                                        value: valores().cantidad, 
                                        disable: !$parent.EnableEconomic()">
                                </td>
                                <td class="text-center valign-middle">
                                    <!-- ko if: $parent.EnableEconomic() -->

                                    <a 
                                        class="btn btn-md green-jungle" 
                                        title="Enviar propuesta económica" 
                                        data-bind="click: $parent.AuctionUpdate.bind($data, $index(), 'cotizar')">
                                        <i class="fa fa-send"></i>
                                        Ofertar
                                    </a>
                                    <!-- ko if: $parent.PermiteAnularOferta() -->
                                    <a 
                                        class="btn btn-md yellow-casablanca" 
                                        title="Enviar propuesta económica" 
                                        data-bind="click: $parent.AuctionUpdate.bind($data, $index(), 'anular')">
                                        <i class="fa fa-trash-o"></i>
                                        Anular
                                    </a>
                                    <!-- /ko -->
                                    
                                    <!-- /ko -->
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<!-- /ko -->