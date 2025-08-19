<!-- Encabezado h4 a la izquierda -->
<h4 class="bold" style="margin-top: 0; padding-top: 0;">
  Comparativas de ofertas
</h4>

<table class="table table-striped table-bordered text-xsmall" id="ListaConcursosEconomicas">
  <thead class="text-center">
    <tr style="background: #ccc;">
      <th class="text-center"
          data-bind="text: ($root.EsAscendente() ? 'Oferente' : 'Proveedor')">
      </th>
      <th class="text-center">Fecha presentación</th>
      <th class="text-center">Comentario</th>
      <th class="text-center">Propuesta Económica</th>
      <th class="text-center">Estructura de Costos</th>
      <th class="text-center">Análisis de Precios Unitarios (APU)</th>
    </tr>
  </thead>
  <tbody>
    <!-- ko foreach: ConcursoEconomicas.proveedores -->
    <tr style="background: #fff;">

      <!-- Caso NORMAL (ni rechazado ni vencido) -->
      <!-- ko ifnot: isRechazado -->
      <!-- ko ifnot: isVencido -->
        <td class="text-center vertical-align-middle" data-bind="text: razonSocial"></td>
        <td class="text-center vertical-align-middle" data-bind="text: fechaPresentacion"></td>
        <td class="text-center vertical-align-middle" data-bind="text: comentarios"></td>

        <td class="text-center vertical-align-middle">
          <!-- ko if: porpuesta_economica -->
            <a data-bind="click: $root.downloadFile.bind($data, porpuesta_economica, 'oferente', OferenteId)"
               download class="btn btn-xl green" title="Descargar">
              Descargar <i class="fa fa-download"></i>
            </a>
          <!-- /ko -->
          <!-- ko ifnot: porpuesta_economica -->
            <span class="label label-danger">Sin archivo</span>
          <!-- /ko -->
        </td>

        <td class="text-center vertical-align-middle">
          <!-- ko if: planilla_costos -->
            <a data-bind="click: $root.downloadFile.bind($data, planilla_costos, 'oferente', OferenteId)"
               download class="btn btn-xl green" title="Descargar">
              Descargar <i class="fa fa-download"></i>
            </a>
          <!-- /ko -->
          <!-- ko ifnot: planilla_costos -->
            <span class="label label-danger">Sin archivo</span>
          <!-- /ko -->
        </td>

        <td class="text-center vertical-align-middle">
          <!-- ko if: analisis_apu -->
            <a data-bind="click: $root.downloadFile.bind($data, analisis_apu, 'oferente', OferenteId)"
               download class="btn btn-xl green" title="Descargar">
              Descargar <i class="fa fa-download"></i>
            </a>
          <!-- /ko -->
          <!-- ko ifnot: analisis_apu -->
            <span class="label label-danger">Sin archivo</span>
          <!-- /ko -->
        </td>
      <!-- /ko --> <!-- /ifnot isVencido -->
      <!-- /ko --> <!-- /ifnot isRechazado -->

      <!-- Caso VENCIDO -->
      <!-- ko if: isVencido -->
        <td class="text-center vertical-align-middle" data-bind="text: razonSocial"></td>
        <td class="text-center vertical-align-middle" data-bind="text: fechaPresentacion"></td>
        <td colspan="4" class="text-center vertical-align-middle" style="color: crimson;">
          El proveedor no presentó la propuesta antes de la fecha establecida
        </td>
      <!-- /ko -->

      <!-- Caso RECHAZADO -->
      <!-- ko if: isRechazado -->
        <td class="text-center vertical-align-middle" data-bind="text: razonSocial"></td>
        <td class="text-center vertical-align-middle" data-bind="text: fechaPresentacion"></td>
        <td colspan="4" class="text-center vertical-align-middle" style="color: crimson;">
          El proveedor declinó su participación
        </td>
      <!-- /ko -->

    </tr>
    <!-- /ko -->
  </tbody>
</table>

{include file='concurso/detail/customer/partials/economica/resumen-adjudicacion/comparativa-ofertas/resumen-proveedor.tpl'}
{include file='concurso/detail/customer/partials/economica/resumen-adjudicacion/comparativa-ofertas/resumen-items.tpl'}
