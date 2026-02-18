<!-- ko if: IsGo() -->
<!-- ko if: !UrlMercadoPago()  -->
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-danger " role="alert">
            <span class="glyphicon glyphicon-warning-sign" aria-hidden="true"></span>
            Para poder pagar cargos por servicios debe configurar token de Mercado Pago.
        </div>
    </div>
</div>
<!-- /ko -->
<!-- /ko -->

<!-- ko if: !Eliminado() && !Rechazado() && (IsAdjudicacionPendiente() || IsAdjudicacionAceptada()) -->
<div class="m-heading-1 border-default m-bordered text-left">
    <h4 class="block bold" style="margin-top: 0; padding-top: 0;">Resultado del concurso</h4>
    <table class="table table-striped table-bordered" id="ListaConcursosEconomicas">
        <thead>
            <tr>
                <th class="text-center">
                    Item
                </th>
                <th class="text-center">
                    Moneda
                </th>
                <th class="text-center">
                    Precio unitario
                </th>
                <th class="text-center">
                    Unidad de medida
                </th>
                <th class="text-center">
                    Cantidad Solicitada
                </th>
                <th class="text-center">
                    Cantidad Adjudicada
                </th>
                <th class="text-center">
                    Cotización unitaria
                </th>
                <th class="text-center">
                    Plazo de entrega (días)
                </th>
            </tr>
        </thead>
        <tbody data-bind="foreach: Resultados">
            <tr>
                <td class="text-center" data-bind="text: nombre"></td>
                <td class="text-center" data-bind="text: moneda"></td>
                <td class="text-center" data-bind="number: valores.precio_unitario, precision: 2"></td>
                <td class="text-center" data-bind="text: valores.unidad"></td>
                <td class="text-center" data-bind="text: valores.cantidad_solicitada"></td>
                <td class="text-center" data-bind="number: valores.cantidad_adjudicada, precision: 0"></td>
                <td class="text-center" data-bind="number: valores.cotizacion, precision: 2"></td>
                <td class="text-center" data-bind="text: valores.plazo_dias"></td>
            </tr>
        </tbody>
        <tbody>
            <tr>
                <td colspan="6" class="text-right bold">Total Cotizaciones:</td>
                <td class="text-center" data-bind="number: TotalCotizaciones "></td>
                <td></td>
            </tr>
        </tbody>
        <tbody>
            <tr>
                <td colspan="9" class="col-md-2 text-center" style="vertical-align: middle;">
                    <!-- ko if: IsAdjudicacionPendiente() -->
                    <a data-bind="click: AdjudicationSend.bind($data, 'decline')" class="btn btn-lg default"
                        title="Rechazar adjudicación">
                        Rechazar adjudicación
                        <i class="fa fa-warning"></i>
                    </a>

                    <!-- ko if: !IsGo() || (IsGo() && EstadoTran() == 'approved') -->
                    <a id="btn-reload-ad"
                        data-bind="attr: { id: 'reloadbtn' }, click: AdjudicationSend.bind($data, 'accept')"
                        class="btn btn-lg green" title="Aceptar adjudicación">
                        Aceptar adjudicación
                        <i class="fa fa-send"></i>
                    </a>
                    <!-- /ko -->

                    <!-- ko if: IsGo()  -->
                    <!-- ko if: EstadoTran() != 'approved' -->
                    <a target="_blank" data-bind="attr: { href: UrlMercadoPago, title: 'Pagar cargos por servicios'}"
                        class="btn btn-lg btn-success" title="Pagar cargos por servicios">
                        Pagar cargos por servicios
                        <i class="fa fa-usd"></i>
                    </a>
                    <a target="_blank" data-bind="click: function() { CheckPay(); }" class="btn btn-lg btn-success"
                        title="Verificar pago">
                        Verificar pago
                        <i class="fa fa-usd"></i>
                    </a>
                    <!-- /ko -->
                    <!-- /ko -->

                    <!-- /ko -->
                </td>
            </tr>
        </tbody>
    </table>
    <h5 class="block bold" style="margin-top: 0; padding-top: 0; color: red;">**Precios sin IVA**</h5>
</div>

<!-- ko if: IsGo() -->
<!-- ko if: IsAdjudicacionAceptada() -->
<div class="m-heading-1 border-default m-bordered text-left">
    <h4 class="block bold" style="margin-top: 0; padding-top: 0;">Datos de adjudicación</h4>
    <table class="table table-striped table-bordered">
        <tbody data-bind="">
            <tr>
                <td data-bind="text: 'RAZON SOCIAL DEL COMITENTE: '" class="bold"
                    style="vertical-align: middle; background: #bfbfbf"></td>
                <td colspan="" data-bind="text: Administrador" class="col-md-2"
                    style="vertical-align: middle; background: #bfbfbf;"></td>
                </td>
            </tr>
            <tr>
                <td colspan="1" data-bind="text: 'Cuit'" class="col-md-2 bold" style="vertical-align: middle;"></td>
                <td colspan="1" data-bind="text: Cuit" class="col-md-2" style="vertical-align: middle;"></td>
            </tr>
            <tr>
                <td colspan="1" data-bind="text: 'Persona de Contacto'" class="col-md-2 bold"
                    style="vertical-align: middle;"></td>
                <td colspan="1" data-bind="text: PersonaContacto() + '  ' + Apellido()" class="col-md-2"
                    style="vertical-align: middle;"></td>
            </tr>
            <tr>
                <td colspan="1" data-bind="text: 'Telefono de Contacto'" class="col-md-2 bold"
                    style="vertical-align: middle;"></td>
                <td colspan="1" data-bind="text:  Telefono()" class="col-md-2" style="vertical-align: middle;"></td>
            </tr>
            <tr>
                <td colspan="1" data-bind="text: 'Email'" class="col-md-2 bold" style="vertical-align: middle;"></td>
                <td colspan="1" data-bind="text:  Email()" class="col-md-2" style="vertical-align: middle;"></td>
            </tr>

        </tbody>
    </table>

    <table class="table table-striped table-bordered border solid">
        <tbody data-bind="">
            <tr>
                <td colspan="4" data-bind="text: 'LUGAR DE CARGA Y ENTREGA '" class="col-md-4 bold"
                    style="vertical-align: middle; background: #bfbfbf"></td>
            </tr>
            <tr>
                <td data-bind="text: 'Lugar de Carga'" class="col-md-3" style="vertical-align: middle;"></td>
                <td data-bind="text: NombreDesde" class="col-md-3" style="vertical-align: middle;"></td>
                <td data-bind="text: 'Lugar de Entrega'" class="col-md-2" style="vertical-align: middle;"></td>
                <td data-bind="text: NombreHasta" class="col-md-4" style="vertical-align: middle;"></td>
            </tr>
            <tr>
                <td data-bind="text: 'Fecha Desde'" class="col-md-2" style="vertical-align: middle;"></td>
                <td data-bind="text: FechaDesde" class="col-md-2" style="vertical-align: middle;"></td>
                <td data-bind="text: 'Fecha Hasta'" class="col-md-2" style="vertical-align: middle;"></td>
                <td data-bind="text: FechaHasta" class="col-md-2" style="vertical-align: middle;"></td>
            </tr>
            <tr>
                <td data-bind="text: 'Horario Desde'" class="col-md-2" style="vertical-align: middle;"></td>
                <td data-bind="text: HoraDesde" class="col-md-2" style="vertical-align: middle;"></td>
                <td data-bind="text: 'Horario Hasta'" class="col-md-2" style="vertical-align: middle;"></td>
                <td data-bind="text: HoraHasta" class="col-md-2" style="vertical-align: middle;"></td>
            </tr>
            <tr>
                <td data-bind="text: 'Provincia Desde'" class="col-md-2" style="vertical-align: middle;"></td>
                <td data-bind="text: ProvinciaDesdeNombre" class="col-md-2" style="vertical-align: middle;"></td>
                <td data-bind="text: 'Provincia Hasta'" class="col-md-2" style="vertical-align: middle;"></td>
                <td data-bind="text: ProvinciaHastaNombre" class="col-md-2" style="vertical-align: middle;"></td>
            </tr>
            <tr>
                <td data-bind="text: 'Ciudad Desde'" class="col-md-2" style="vertical-align: middle;"></td>
                <td data-bind="text: CiudadDesdeNombre" class="col-md-2" style="vertical-align: middle;"></td>
                <td data-bind="text: 'Ciudad Hasta'" class="col-md-4" style="vertical-align: middle;"></td>
                <td data-bind="text: CiudadHastaNombre" class="col-md-2" style="vertical-align: middle;"></td>
            </tr>
            <tr>
                <td data-bind="text: 'Calle '" class="col-md-2" style="vertical-align: middle;"></td>
                <td data-bind="text: CalleDesde" class="col-md-2" style="vertical-align: middle;"></td>
                <td data-bind="text: 'Calle '" class="col-md-2" style="vertical-align: middle;"></td>
                <td data-bind="text: CalleHasta" class="col-md-2" style="vertical-align: middle;"></td>
            </tr>
            <tr>
                <td data-bind="text: 'Número '" class="col-md-2" style="vertical-align: middle;"></td>
                <td data-bind="text: NumeracionDesde" class="col-md-2" style="vertical-align: middle;"></td>
                <td data-bind="text: 'Número '" class="col-md-2" style="vertical-align: middle;"></td>
                <td data-bind="text: NumeracionHasta" class="col-md-2" style="vertical-align: middle;"></td>
            </tr>
            <tr>
                <td data-bind="text: 'Reseña'" class="col-md-1" style="vertical-align: middle;"></td>
                <td data-bind="html: Resena" class="col-md-1" style="vertical-align: middle;"></td>
            </tr>
            <tr>
                <td data-bind="text: 'Descripción'" class="col-md-1" style="vertical-align: middle;"></td>
                <td data-bind="html: Descripcion" class="col-md-1" style="vertical-align: middle;"></td>
            </tr>
        </tbody>
    </table>
</div>
<!-- /ko -->
<!-- /ko -->

<!-- /ko -->

<!-- ko if: IsAdjudicacionAceptada() -->
<div class="row">
    <div class="col-sm-12">
        <div class="alert alert-success">
            Adjudicación Aceptada
        </div>
    </div>
</div>

<!-- DOCUMENTOS EXCLUSIVOS DEL ADJUDICADO -->
<!-- ko if: MediaAdjudicado && MediaAdjudicado().length > 0 -->
<div class="row" style="margin-top: 20px;">
    <div class="col-md-12">
        <div class="portlet light bg-inverse">
            <div class="portlet-title">
                <div class="caption font-purple-seance">
                    <i class="fa fa-file-archive-o"></i>
                    <span class="caption-subject bold uppercase">
                        Documentos para Adjudicados
                    </span>
                </div>
            </div>
            <div class="portlet-body">
                <div class="alert alert-info">
                    <strong>Información:</strong> Los siguientes documentos han sido preparados específicamente para usted, como proveedor adjudicado en esta licitación.
                </div>
                <table class="table table-striped table-bordered table-light">
                    <thead>
                        <tr>
                            <th width="80%">Documento</th>
                            <th width="20%" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody data-bind="foreach: MediaAdjudicado">
                        <tr>
                            <td data-bind="text: nombre" class="vertical-align-middle"></td>
                            <td class="text-center vertical-align-middle">
                                <!-- ko if: imagen -->
                                <a data-bind="click: function() { $root.downloadFileAdjudicado(path); }"
                                    class="btn btn-xs green" title="Descargar">
                                    Descargar <i class="fa fa-download"></i>
                                </a>
                                <!-- /ko -->
                                <!-- ko if: !imagen -->
                                <span class="label label-danger">Sin archivo</span>
                                <!-- /ko -->
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- /ko -->

<!-- /ko -->
<!-- ko if: IsAdjudicacionRechazada() -->
<div class="row">
    <div class="col-sm-12">
        <div class="alert alert-danger">
            Adjudicación Rechazada
        </div>
    </div>
</div>
<!-- /ko -->