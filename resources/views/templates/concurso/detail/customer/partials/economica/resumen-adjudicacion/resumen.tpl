<h4 class="block bold" style="margin-top: 0; padding-top: 0;">Resumen</h4>
<table class="table table-striped table-bordered" id="ListaConcursosEconomicas">
    <thead class="text-center">
        <tr>
            <th class="text-center"></th>
            <th class="text-center"> Mejor oferta integral </th>
            <th class="text-center"> Mejor oferta individual </th>
            <th class="text-center"> Adjudicación manual </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th> Monto</th>
            <td data-bind="number: ConcursoEconomicas.mejoresOfertas.mejorIntegral.total, precision: 2"
                class="vertical-align-middle text-center text-bold"></td>
            <td data-bind="number: ConcursoEconomicas.mejoresOfertas.mejorIndividual.total1, precision: 2"
                class="vertical-align-middle text-center text-bold"></td>
            <!-- ko if: $root.ManualAdjudication().total() == 0  -->
            <td data-bind="text:'-'" class="vertical-align-middle text-center text-bold"></td>
            <!-- /ko -->
            <!-- ko if: $root.ManualAdjudication().total() > 0  -->
            <td data-bind="number:$root.ManualAdjudication().total(), precision: 2"
                class="vertical-align-middle text-center text-bold"></td>
            <!-- /ko -->
        </tr>
        <tr>
            <th class= "text-center" data-bind="text: ($root.EsAscendente() || $root.EsVenta()) ? 'Ganancia %' : 'Ahorro %'"></th>
            <!-- Mejor oferta integral (% dinámico) -->
            <td class="text-center vertical-align-middle text-bold"
                data-bind="number: ($root.EsAscendente() || $root.EsVenta())
                                    ? ConcursoEconomicas.mejoresOfertas.mejorIntegral.ganancia_porc
                                    : ConcursoEconomicas.mejoresOfertas.mejorIntegral.ahorro_porc,
                            precision: 2, symbol: '%', after: true,
                            style: { color: (
                                    ($root.EsAscendente() || $root.EsVenta())
                                    ? ConcursoEconomicas.mejoresOfertas.mejorIntegral.ganancia_porc
                                    : ConcursoEconomicas.mejoresOfertas.mejorIntegral.ahorro_porc
                                    ) == 0 ? 'black' : (
                                    (
                                        ($root.EsAscendente() || $root.EsVenta())
                                        ? ConcursoEconomicas.mejoresOfertas.mejorIntegral.ganancia_porc
                                        : ConcursoEconomicas.mejoresOfertas.mejorIntegral.ahorro_porc
                                    ) > 0 ? 'green' : 'red'
                                    ) }"></td>

            <!-- Mejor oferta individual (% dinámico) -->
            <td class="text-center vertical-align-middle text-bold"
                data-bind="number: ($root.EsAscendente() || $root.EsVenta())
                                    ? ConcursoEconomicas.mejoresOfertas.mejorIndividual.ganancia_porc
                                    : ConcursoEconomicas.mejoresOfertas.mejorIndividual.ahorro_porc,
                            precision: 2, symbol: '%', after: true,
                            style: { color: (
                                    ($root.EsAscendente() || $root.EsVenta())
                                    ? ConcursoEconomicas.mejoresOfertas.mejorIndividual.ganancia_porc
                                    : ConcursoEconomicas.mejoresOfertas.mejorIndividual.ahorro_porc
                                    ) == 0 ? 'black' : (
                                    (
                                        ($root.EsAscendente() || $root.EsVenta())
                                        ? ConcursoEconomicas.mejoresOfertas.mejorIndividual.ganancia_porc
                                        : ConcursoEconomicas.mejoresOfertas.mejorIndividual.ahorro_porc
                                    ) > 0 ? 'green' : 'red'
                                    ) }"></td>

            <!-- ko if: $root.ManualAdjudication().total() == 0 -->
            <td data-bind="text:'-'" class="vertical-align-middle text-center text-bold"></td>
            <!-- /ko -->
            <!-- ko if: $root.ManualAdjudication().total() > 0  -->
            <!-- Manual: % dinámico (ganancia / ahorro) -->
            <td class="text-center vertical-align-middle text-bold"
                data-bind="number: ($root.EsAscendente() || $root.EsVenta())
                                    ? $root.ManualAdjudication().GananciaRelativa()
                                    : $root.ManualAdjudication().AhorroRelativo(),
                        precision: 2, symbol: '%', after: true,
                        style: { color: (
                                    ($root.EsAscendente() || $root.EsVenta())
                                    ? $root.ManualAdjudication().GananciaRelativa()
                                    : $root.ManualAdjudication().AhorroRelativo()
                                ) == 0 ? 'black' : (
                                    (
                                    ($root.EsAscendente() || $root.EsVenta())
                                    ? $root.ManualAdjudication().GananciaRelativa()
                                    : $root.ManualAdjudication().AhorroRelativo()
                                    ) > 0 ? 'green' : 'red'
                                ) }">
            </td>

            <!-- /ko -->
        </tr>
        <tr>
            <th class= "text-center" data-bind="text: ($root.EsAscendente() || $root.EsVenta()) ? 'Ganancia abs' : 'Ahorro abs'"></th>
            <!-- Mejor oferta integral (abs dinámico) -->
            <td class="text-center vertical-align-middle text-bold"
                data-bind="number:($root.EsAscendente() || $root.EsVenta())
                                    ? ConcursoEconomicas.mejoresOfertas.mejorIntegral.ganancia_abs
                                    : ConcursoEconomicas.mejoresOfertas.mejorIntegral.ahorro_abs,
                            precision: 2,
                            style: { color: (
                                    ($root.EsAscendente() || $root.EsVenta())
                                    ? ConcursoEconomicas.mejoresOfertas.mejorIntegral.ganancia_abs
                                    : ConcursoEconomicas.mejoresOfertas.mejorIntegral.ahorro_abs
                                    ) == 0 ? 'black' : (
                                    (
                                        ($root.EsAscendente() || $root.EsVenta())
                                        ? ConcursoEconomicas.mejoresOfertas.mejorIntegral.ganancia_abs
                                        : ConcursoEconomicas.mejoresOfertas.mejorIntegral.ahorro_abs
                                    ) > 0 ? 'green' : 'red'
                                    ) }"></td>

            <!-- Mejor oferta individual (abs dinámico) -->
            <td class="text-center vertical-align-middle text-bold"
                data-bind="number: ($root.EsAscendente() || $root.EsVenta())
                                    ? ConcursoEconomicas.mejoresOfertas.mejorIndividual.ganancia_abs
                                    : ConcursoEconomicas.mejoresOfertas.mejorIndividual.ahorro_abs,
                            precision: 2,
                            style: { color: (
                                    ($root.EsAscendente() || $root.EsVenta())
                                    ? ConcursoEconomicas.mejoresOfertas.mejorIndividual.ganancia_abs
                                    : ConcursoEconomicas.mejoresOfertas.mejorIndividual.ahorro_abs
                                    ) == 0 ? 'black' : (
                                    (
                                        ($root.EsAscendente() || $root.EsVenta())
                                        ? ConcursoEconomicas.mejoresOfertas.mejorIndividual.ganancia_abs
                                        : ConcursoEconomicas.mejoresOfertas.mejorIndividual.ahorro_abs
                                    ) > 0 ? 'green' : 'red'
                                    ) }"></td>
            
            <!-- ko if: $root.ManualAdjudication().total() == 0 -->
            <td data-bind="text:'-'" class="vertical-align-middle text-center text-bold"></td>
            <!-- /ko -->
            <!-- ko if: $root.ManualAdjudication().total() > 0  -->
            <!-- Manual: abs dinámico (ganancia / ahorro) -->
            <td class="text-center vertical-align-middle text-bold"
                data-bind="number: ($root.EsAscendente() || $root.EsVenta())
                                    ? $root.ManualAdjudication().GananciaAbsoluta()
                                    : $root.ManualAdjudication().AhorroAbsoluto(),
                        precision: 2,
                        style: { color: (
                                    ($root.EsAscendente() || $root.EsVenta())
                                    ? $root.ManualAdjudication().GananciaAbsoluta()
                                    : $root.ManualAdjudication().AhorroAbsoluto()
                                ) == 0 ? 'black' : (
                                    (
                                    ($root.EsAscendente() || $root.EsVenta())
                                    ? $root.ManualAdjudication().GananciaAbsoluta()
                                    : $root.ManualAdjudication().AhorroAbsoluto()
                                    ) > 0 ? 'green' : 'red'
                                ) }">
            </td>

            <!-- /ko -->
        </tr>
        <!-- ko if: active -->
        <tr>
            <td colspan="4" class="col-md-10 vertical-align-middle">
                <label class="control-label visible-ie8 visible-ie9" style="display: block;">
                    Comentarios
                </label>
                <textarea class="form-control placeholder-no-fix" maxlength="1000" rows="3" id="maxlength_textarea"
                    data-bind="value: $root.AdjudicacionComentario, attr: { 'placeholder': $root.Adjudicado() ? '' : 'Máximo 1000 caracteres' }, disable: $root.Adjudicado() || !active">
                </textarea>
            </td>
        </tr>
        <!-- /ko -->
        <!-- ko if: !$root.Adjudicado() && !$root.Eliminado() && active -->
        <tr>
            <td class="text-center" colspan="4">
                <!-- ko if: ConcursoEconomicas.mejoresOfertas.mejorIntegral.items.length > 0  -->
                <!-- ko if: $root.UserType() !== 'customer-read' -->
                <button type="button" class="btn btn-primary"
                    data-bind="click: $root.AdjudicationSend.bind($data, 'integral', ConcursoEconomicas.mejoresOfertas.mejorIntegral.idOferente), disable: $root.BotonesAdjudicacionDeshabilitados()">
                    Adjudicar Integral
                </button>
                <!-- /ko -->
                <!-- /ko -->
                <!-- ko if: ConcursoEconomicas.mejoresOfertas.mejorIndividual.individual.length > 0 -->
                <!-- ko if: $root.UserType() !== 'customer-read' -->

                <button type="button" class="btn btn-primary"
                    data-bind="click: $root.AdjudicationSend.bind($data, 'individual', ConcursoEconomicas.mejoresOfertas.idOferentes), disable: $root.BotonesAdjudicacionDeshabilitados()">
                    Adjudicar Individual
                </button>
                <!-- /ko -->
                <!-- /ko -->
                <!-- ko if: ConcursoEconomicas.proveedores.length > 0 -->
                <!-- ko if: $root.UserType() !== 'customer-read' -->

                <button type="button" class="btn btn-primary"
                    data-bind="click: $root.AdjudicationSend.bind($data, 'manual'), disable: $root.ManualAdjudication().total() == 0 || $root.BotonesAdjudicacionDeshabilitados()">
                    Adjudicar Manual
                </button>
                <!-- /ko -->
                <!-- /ko -->

            </td>
        </tr>
        <!-- /ko -->
    </tbody>
</table>

<!-- ko if: active && $root.EjecutarNuevaRonda() -->
<!-- ko if: $root.UserType() !== 'customer-read' -->
<button type="button" class="btn btn-primary" style="width: 100%;"
    data-bind="text:$root.TitleNewRound(), click: $root.ShowModalNewRound, visible: !($root.Adjudicado() || $root.Eliminado())">
</button>
<!-- /ko -->
<!-- /ko -->

<!-- Tabla de Cadena de Aprobación -->
<!-- ko if: $root.EstrategiaHabilitada() && $root.NivelesAprobacion().length > 0 -->
<div style="margin-top: 20px; padding: 15px; border: 1px solid #e0e0e0; border-radius: 4px; background-color: #fafafa;">
    <h4 class="block bold" style="margin-top: 0; padding-top: 0;">Cadena de Aprobación</h4>
    <!-- ko if: $root.MontoEnDolares() !== null -->
    <p style="margin-bottom: 10px;">
        <strong>Valor (En dólares):</strong> <span data-bind="text: 'USD ' + $root.MontoEnDolares().toFixed(2)"></span>
        <!-- ko if: $root.TipoAdjudicacionSeleccionada() -->
        <span style="margin-left: 20px;"><strong>Tipo de Adjudicación:</strong> <span data-bind="text: $root.TipoAdjudicacionSeleccionada()"></span></span>
        <!-- /ko -->
    </p>
    <!-- /ko -->
    <table class="table table-striped table-bordered" id="TablaCadenaAprobacion">
        <thead class="text-center">
            <tr>
                <th class="text-center" style="white-space: nowrap; width: 1%;">Nivel</th>
                <th class="text-center" style="white-space: nowrap; width: 1%;">Usuario</th>
                <th class="text-center" style="width: 100px;">Estado</th>
                <th class="text-center">Fecha de aprobación/rechazo</th>
                <th class="text-center">Motivo</th>
            </tr>
        </thead>
        <tbody data-bind="foreach: $root.NivelesAprobacion()">
            <tr>
                <td class="text-center" style="white-space: nowrap;" data-bind="text: rol"></td>
                <td class="text-center" style="white-space: nowrap;" data-bind="text: usuario || '-'"></td>
                <td class="text-center vertical-align-middle">
                    <span class="label label-sm"
                          data-bind="
                            text: estado,
                            css: {
                                'label-warning': estado === 'Pendiente',
                                'label-success': estado === 'Aprobado',
                                'label-danger':  estado === 'Rechazado'
                            }">
                    </span>
                </td>
                <td class="text-center" data-bind="text: fecha || '-'"></td>
                <td class="text-center" data-bind="text: motivo || '-'"></td>
            </tr>
        </tbody>
    </table>
</div>
<!-- /ko -->

<!-- ko if: $root.Adjudicado()  -->
<div class="alert alert-success"
    data-bind="text: 'Este concurso ya ha sido adjudicado de manera ' + $root.TipoAdjudicacion() + '.'">
</div>
<!-- /ko -->

<!-- ko if: $root.Eliminado()  -->
<div class="alert alert-success" data-bind="text: 'Este concurso ha sido eliminado'">
</div>
<!-- /ko -->
