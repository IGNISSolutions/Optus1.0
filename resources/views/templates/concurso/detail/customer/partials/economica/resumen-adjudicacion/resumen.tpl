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
            <th class= "text-center" data-bind="text: $root.EsAscendente() ? 'Ganancia %' : 'Ahorro %'"></th>
            <!-- Mejor oferta integral (% dinámico) -->
            <td class="text-center vertical-align-middle text-bold"
                data-bind="number: $root.EsAscendente()
                                    ? ConcursoEconomicas.mejoresOfertas.mejorIntegral.ganancia_porc
                                    : ConcursoEconomicas.mejoresOfertas.mejorIntegral.ahorro_porc,
                            precision: 2, symbol: '%', after: true,
                            style: { color: (
                                    $root.EsAscendente()
                                    ? ConcursoEconomicas.mejoresOfertas.mejorIntegral.ganancia_porc
                                    : ConcursoEconomicas.mejoresOfertas.mejorIntegral.ahorro_porc
                                    ) == 0 ? 'black' : (
                                    (
                                        $root.EsAscendente()
                                        ? ConcursoEconomicas.mejoresOfertas.mejorIntegral.ganancia_porc
                                        : ConcursoEconomicas.mejoresOfertas.mejorIntegral.ahorro_porc
                                    ) > 0 ? 'green' : 'red'
                                    ) }"></td>

            <!-- Mejor oferta individual (% dinámico) -->
            <td class="text-center vertical-align-middle text-bold"
                data-bind="number: $root.EsAscendente()
                                    ? ConcursoEconomicas.mejoresOfertas.mejorIndividual.ganancia_porc
                                    : ConcursoEconomicas.mejoresOfertas.mejorIndividual.ahorro_porc,
                            precision: 2, symbol: '%', after: true,
                            style: { color: (
                                    $root.EsAscendente()
                                    ? ConcursoEconomicas.mejoresOfertas.mejorIndividual.ganancia_porc
                                    : ConcursoEconomicas.mejoresOfertas.mejorIndividual.ahorro_porc
                                    ) == 0 ? 'black' : (
                                    (
                                        $root.EsAscendente()
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
                data-bind="number: $root.EsAscendente()
                                    ? $root.ManualAdjudication().GananciaRelativa()
                                    : $root.ManualAdjudication().AhorroRelativo(),
                        precision: 2, symbol: '%', after: true,
                        style: { color: (
                                    $root.EsAscendente()
                                    ? $root.ManualAdjudication().GananciaRelativa()
                                    : $root.ManualAdjudication().AhorroRelativo()
                                ) == 0 ? 'black' : (
                                    (
                                    $root.EsAscendente()
                                    ? $root.ManualAdjudication().GananciaRelativa()
                                    : $root.ManualAdjudication().AhorroRelativo()
                                    ) > 0 ? 'green' : 'red'
                                ) }">
            </td>

            <!-- /ko -->
        </tr>
        <tr>
            <th class= "text-center" data-bind="text: $root.EsAscendente() ? 'Ganancia abs' : 'Ahorro abs'"></th>
            <!-- Mejor oferta integral (abs dinámico) -->
            <td class="text-center vertical-align-middle text-bold"
                data-bind="number: $root.EsAscendente()
                                    ? ConcursoEconomicas.mejoresOfertas.mejorIntegral.ganancia_abs
                                    : ConcursoEconomicas.mejoresOfertas.mejorIntegral.ahorro_abs,
                            precision: 2,
                            style: { color: (
                                    $root.EsAscendente()
                                    ? ConcursoEconomicas.mejoresOfertas.mejorIntegral.ganancia_abs
                                    : ConcursoEconomicas.mejoresOfertas.mejorIntegral.ahorro_abs
                                    ) == 0 ? 'black' : (
                                    (
                                        $root.EsAscendente()
                                        ? ConcursoEconomicas.mejoresOfertas.mejorIntegral.ganancia_abs
                                        : ConcursoEconomicas.mejoresOfertas.mejorIntegral.ahorro_abs
                                    ) > 0 ? 'green' : 'red'
                                    ) }"></td>

            <!-- Mejor oferta individual (abs dinámico) -->
            <td class="text-center vertical-align-middle text-bold"
                data-bind="number: $root.EsAscendente()
                                    ? ConcursoEconomicas.mejoresOfertas.mejorIndividual.ganancia_abs
                                    : ConcursoEconomicas.mejoresOfertas.mejorIndividual.ahorro_abs,
                            precision: 2,
                            style: { color: (
                                    $root.EsAscendente()
                                    ? ConcursoEconomicas.mejoresOfertas.mejorIndividual.ganancia_abs
                                    : ConcursoEconomicas.mejoresOfertas.mejorIndividual.ahorro_abs
                                    ) == 0 ? 'black' : (
                                    (
                                        $root.EsAscendente()
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
                data-bind="number: $root.EsAscendente()
                                    ? $root.ManualAdjudication().GananciaAbsoluta()
                                    : $root.ManualAdjudication().AhorroAbsoluto(),
                        precision: 2,
                        style: { color: (
                                    $root.EsAscendente()
                                    ? $root.ManualAdjudication().GananciaAbsoluta()
                                    : $root.ManualAdjudication().AhorroAbsoluto()
                                ) == 0 ? 'black' : (
                                    (
                                    $root.EsAscendente()
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
                    data-bind="click: $root.AdjudicationSend.bind($data, 'integral', ConcursoEconomicas.mejoresOfertas.mejorIntegral.idOferente)">
                    Adjudicar Integral
                </button>
                <!-- /ko -->
                <!-- /ko -->
                <!-- ko if: ConcursoEconomicas.mejoresOfertas.mejorIndividual.individual.length > 0 -->
                <!-- ko if: $root.UserType() !== 'customer-read' -->

                <button type="button" class="btn btn-primary"
                    data-bind="click: $root.AdjudicationSend.bind($data, 'individual', ConcursoEconomicas.mejoresOfertas.idOferentes)">
                    Adjudicar Individual
                </button>
                <!-- /ko -->
                <!-- /ko -->
                <!-- ko if: ConcursoEconomicas.proveedores.length > 0 -->
                <!-- ko if: $root.UserType() !== 'customer-read' -->

                <button type="button" class="btn btn-primary"
                    data-bind="click: $root.AdjudicationSend.bind($data, 'manual'), disable: $root.ManualAdjudication().total() == 0">
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

<div class="modal fade bs-modal-md" id="newRound" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <!-- HEADER -->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title text-center" data-bind="text:'¿Confirma la ' + $root.NuevaRonda() + '?'"></h4>
            </div>

            <div class="modal-body text-center">
                 <!-- FECHA LIMITE NUEVA RONDA -->
                <span> Indica la fecha limite de la nueva ronda <b style="color: red;">*</b></span>
                    <br>
                <span>(Minimo 72 hs a partir de hoy)</span>

                <div class="form-group required" data-bind="validationElement: $root.FechaNewRound()">
                    <div class="input-group date form_datetime bs-datetime" style="margin: auto;">
                        <input class="form-control" size="36" type="text" data-bind="dateTimePicker: $root.FechaNewRound, dateTimePickerOptions: {
                            format: 'dd-mm-yyyy hh:ii',
                            momentFormat: 'DD-MM-YYYY HH:mm',
                            startDate: $root.ThreeDaysFromTodayDate(),
                            value: $root.FechaNewRound(),
                            todayBtn: false
                        }">
                    </div>
                </div>

                <!-- FECHA LIMITE CIERRE MURO DE CONSULTA -->
                <span class="text-center" data-bind="text:'Indica la fecha limite para el cierre del muro de consultas'"></span>
                    <br>
                <span>(24 hs antes de la fecha limite de la nueva ronda)</span>

                <div class="form-group required" data-bind="validationElement: $root.NuevaFechaCierreMuroConsulta()">
                    <div class="input-group date form_datetime bs-datetime" style="margin: auto;">
                        <input id="NuevaFechaCierreMuroConsulta" class="form-control" size="36" type="text" 
                            data-bind="dateTimePicker: $root.NuevaFechaCierreMuroConsulta, 
                                        dateTimePickerOptions: {
                                            format: 'dd-mm-yyyy hh:ii',
                                            momentFormat: 'DD-MM-YYYY HH:mm',
                                            startDate: $root.TodayDate(),
                                            endDate: $root.FechaMaximaCierreDeConsulta(),
                                            value: $root.NuevaFechaCierreMuroConsulta(),
                                            todayBtn: false
                                        },
                                        enable: $root.FechaNewRound()">
                    </div>
                </div>
                
                <!-- BOX DE COMENTARIO -->
                <p>Añada un comentario para la nueva ronda <b style="color: red;">*</b></p>
                    <textarea rows="3" cols="50" class="form-control" style="resize: none;"data-bind="textInput: $root.ComentarioNuevaRonda">
                </textarea>

                <span>
                        Todos los campos marcados con <b style="color: red;">*</b> son oblgatorios
                    <br>
                </span>
            </div>

            <!-- FOOTER CON BOTONES -->
            <div class="modal-footer">
                <button type="button" class="btn red BTNC" data-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn green"  data-dismiss="modal"
                    data-bind="click: $root.SendNewRound">
                    Aceptar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- /ko -->
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