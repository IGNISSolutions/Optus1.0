<h4 class="block bold" style="margin-top: 0; padding-top: 0;">Calificación técnica</h4>
<table class="table table-striped table-bordered" id="ListaConcursosEconomicas">
    <thead>
        <tr>
            <th data-bind="html: TechnicalHeaderInfo.atributo"></th>
            <th class="text-center" data-bind="html: TechnicalHeaderInfo.puntaje"></th>
        </tr>
    </thead>
    <thead>
        <tr>
            <th> Atributo </th>
            <th> Ponderación </th>
        </tr>
    </thead>

    <tbody data-bind="foreach: EvaluacionTecnica">
    <!-- ko if: id != '0' && ponderacion != '' -->
        <tr>
            <td data-bind="text: atributo"></td>
            <td data-bind="text: ponderacion + ' %'"></td>
        </tr>
    <!-- /ko -->
    </tbody>
</table>