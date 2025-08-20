 {if $tipo neq 'chat-muro-consultas'}
<div class="row">
    <!-- === Nueva sección: Seguimiento Invitaciones === -->
<div class="col-sm-12">
  <div class="m-heading-1 border-default m-bordered text-left">
    <h4 class="block bold" style="margin-top: 0; padding-top: 0;">
      Seguimiento Invitaciones
    </h4>
    <table class="table table-striped table-bordered" id="ListaSeguimientoInvitaciones">
      <thead>
        <tr>
          <th class="text-center vertical-align-middle" data-bind="text: ($root.EsAscendente() ? 'Oferentes invitados' : 'Proveedores invitados')"></th>
          <th class="text-center vertical-align-middle">Fecha Invitación</th>
          <th class="text-center vertical-align-middle">Fecha Aceptación / Rechazo</th>
          <th class="text-center vertical-align-middle">Invitación</th>
        </tr>
      </thead>
      <tbody data-bind="dataTablesForEach: {
                         data: OferentesInvitados,
                         options: {
                           paging: false,
                           ordering: false,
                           info: false,
                           searching: false,
                           stripeClasses: ['odd','even']
                         }
                       }">
        <tr>
          <!-- 1) Proveedor -->
          <td class="col-md-3 text-center vertical-align-middle"
              data-bind="text: Nombre"></td>

          <!-- 2) Fecha Invitación -->
          <td class="col-md-3 text-center vertical-align-middle"
              data-bind="text: FechaConvocatoria"></td>

          <!-- 3) Fecha Aceptación / Rechazo -->
          <td class="col-md-3 text-center vertical-align-middle"
              data-bind="text: FechaAceptacionRechazo"></td>

          <!-- 4) Invitación (color según Description) -->
          <td class="col-md-3 text-center vertical-align-middle">
            <span class="label label-sm"
                  data-bind="
                    text: Description,
                    css: {
                    'label-success': Description === 'Aceptada',
                    'label-warning': Description === 'Pendiente',
                    'label-danger':  Description === 'Rechazada'
                    }">
            </span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
<!-- === /Fin sección Invitaciones === -->

    <div class="col-sm-12">
        <div class="m-heading-1 border-default m-bordered text-left">
            <h4 class="block bold" style="margin-top: 0; padding-top: 0;">Etapas fechas</h4>
            <table class="table table-striped table-bordered" id="ListaConcursosEconomicas">
                <thead>
                    <tr>
                        <th>Fechas límites</th>
                        <th class="text-center">Fecha </th>
                        <th class="text-center">Hora </th>
                        <th class="text-center">Zona Horaria</th>
                    </tr>
                </thead>
                <tbody data-bind="">
                    <tr>
                        <td data-bind="text: 'Cierre muro de consultas'" class="vertical-align-middle col-md-3"></td>
                        <td data-bind="text: CierreMuroConsultas" class="text-center vertical-align-middle col-md-2"></td>
                        <td data-bind="text: CierreMuroConsultasHora" class="text-center vertical-align-middle col-md-1"></td>
                        <td data-bind="text: ZonaHoraria" class="text-center vertical-align-middle col-md-3"></td>
                    </tr>
                    <!-- ko if: IsSobrecerrado() || IsOnline() -->
                        <!-- ko if: IncluyeTecnica() -->
                        <tr>
                            <td data-bind="text: 'Presentación oferta técnica'" class="vertical-align-middle col-md-3"></td>
                            <td data-bind="text: PresentacionTecnicas" class="text-center vertical-align-middle col-md-2"></td>
                            <td data-bind="text: PresentacionTecnicasHora" class="text-center vertical-align-middle col-md-1"></td>
                            <td data-bind="text: ZonaHoraria" class="text-center vertical-align-middle col-md-3"></td>
                        </tr>
                        <!-- /ko -->
                    <!-- /ko -->
                    <!-- ko if: IsSobrecerrado() || IsGo() -->
                    <tr>
                        <td data-bind="text: 'Presentación oferta económica'" class="vertical-align-middle col-md-3"></td>
                        <td data-bind="text: PresentacionEconomicas" class="text-center vertical-align-middle col-md-2"></td>
                        <td data-bind="text: PresentacionEconomicasHora" class="text-center vertical-align-middle col-md-1"></td>
                        <td data-bind="text: ZonaHoraria" class="text-center vertical-align-middle col-md-3"></td>
                    </tr>

                        <!-- ko if: IncluyeEconomicaSegundaRonda() -->
                        <tr>
                            <td data-bind="text: 'Presentación segunda oferta económica'" class="vertical-align-middle col-md-3"></td>
                            <td data-bind="text: PresentacionEconomicasSegundaRonda" class="text-center vertical-align-middle col-md-2"></td>
                            <td data-bind="text: PresentacionEconomicasSegundaRondaHora" class="text-center vertical-align-middle col-md-1"></td>
                            <td data-bind="text: ZonaHoraria" class="text-center vertical-align-middle col-md-3"></td>
                        </tr>
                        <!-- /ko -->
                        <!-- ko if: IncluyeEconomicaTerceraRonda -->
                        <tr>
                            <td data-bind="text: 'Presentación tercera oferta económica'" class="vertical-align-middle col-md-3"></td>
                            <td data-bind="text: PresentacionEconomicasTerceraRonda" class="text-center vertical-align-middle col-md-2"></td>
                            <td data-bind="text: PresentacionEconomicasTerceraRondaHora" class="text-center vertical-align-middle col-md-1"></td>
                            <td data-bind="text: ZonaHoraria" class="text-center vertical-align-middle col-md-3"></td>
                        </tr>
                        <!-- /ko -->
                        <!-- ko if: IncluyeEconomicaCuartaRonda -->
                        <tr>
                            <td data-bind="text: 'Presentación cuarta oferta económica'" class="vertical-align-middle col-md-3"></td>
                            <td data-bind="text: PresentacionEconomicasCuartaRonda" class="text-center vertical-align-middle col-md-2"></td>
                            <td data-bind="text: PresentacionEconomicasCuartaRondaHora" class="text-center vertical-align-middle col-md-1"></td>
                            <td data-bind="text: ZonaHoraria" class="text-center vertical-align-middle col-md-3"></td>
                        </tr>
                        <!-- /ko -->

                        <!-- ko if: IncluyeEconomicaQuintaRonda -->
                        <tr>
                            <td data-bind="text: 'Presentación quinta oferta económica'" class="vertical-align-middle col-md-3"></td>
                            <td data-bind="text: PresentacionEconomicasQuintaRonda" class="text-center vertical-align-middle col-md-2"></td>
                            <td data-bind="text: PresentacionEconomicasQuintaRondaHora" class="text-center vertical-align-middle col-md-1"></td>
                            <td data-bind="text: ZonaHoraria" class="text-center vertical-align-middle col-md-3"></td>
                        </tr>
                        <!-- /ko -->


                    <!-- /ko -->
                    <!-- ko if: IsOnline() -->
                    <tr>
                        <td data-bind="text: 'Inicio Subasta'" class="vertical-align-middle col-md-3"></td>
                        <td data-bind="text: InicioSubasta" class="text-center vertical-align-middle col-md-2"></td>
                        <td data-bind="text: InicioSubastaHora" class="text-center vertical-align-middle col-md-1"></td>
                        <td data-bind="text: ZonaHoraria" class="text-center vertical-align-middle col-md-3"></td>
                    </tr>
                    <!-- /ko -->
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-sm-12">
        <div class="m-heading-1 border-default m-bordered text-left">
            <h4 class="block bold" style="margin-top: 0; padding-top: 0;">Información del concurso</h4>
            <table class="table table-striped table-bordered" id="ListaConcursosEconomicas">
                <tbody>
                    <tr>
                        <td data-bind="text: 'Nombre'" class="col-md-4 vertical-align-middle"></td>
                        <td data-bind="text: Nombre" class="col-md-4 vertical-align-middle"></td>
                        <!-- ko if: !IsGo() -->
                        <td 
                            rowspan="5" 
                            class="col-md-4 vertical-align-middle text-center" 
                            data-bind="style: {literal}{
                                backgroundImage: 'url(' + ImagePath() + Portrait() + ')'
                            }{/literal}" 
                            style="width: auto; height: 300px; background-repeat: no-repeat; background-position: center center;background-size:cover;">
                        </td>
                        <!-- /ko -->    
                    </tr>

                    <!-- ko if: !IsGo() -->
                    <tr>
                        <td data-bind="text: 'Solicitante'" class="col-md-4 vertical-align-middle"></td>
                        <td data-bind="text: Solicitante" class="col-md-4 vertical-align-middle"></td>
                    </tr>
                    <tr>
                        <td data-bind="text: 'Administrador'" class="col-md-4 vertical-align-middle"></td>
                        <td data-bind="text: Administrador" class="col-md-4 vertical-align-middle"></td>
                    </tr>
                    <tr>
                        <td data-bind="text: 'Tipología'" class="col-md-4 vertical-align-middle"></td>
                        <td data-bind="text: Tipologia" class="col-md-4 vertical-align-middle"></td>
                    </tr>
                    <tr>
                        <td data-bind="text: 'Tipo de operación'" class="col-md-4 vertical-align-middle"></td>
                        <td data-bind="text: TipoOperacion" class="col-md-4 vertical-align-middle"></td>
                    </tr>
                    <!-- /ko -->    
                    <!-- ko if: Eliminado() -->
                    <tr>
                        <td colspan="3" style="text-align: center;">
                            <span style="color: #f00; font-weight: bold; font-size: 16px;" data-bind="text: 'Este concurso ha sido eliminado o cancelado'"></span>
                            <br>
                            <span style="color: #f00; font-weight: bold; font-size: 16px;" data-bind="text: UsuarioCancelacion ? 'Por el usuario ' + UsuarioCancelacion : 'Proceso automático'"></span>
                            <br>
                            <span style="color: #f00; font-weight: bold; font-size: 16px;" data-bind="text: 'El día ' + FechaCancelacion"></span>
                        </td>
                    </tr>   
                    <!-- /ko -->
                </tbody>
            </table>
        </div>
    </div>
</div>
{/if}