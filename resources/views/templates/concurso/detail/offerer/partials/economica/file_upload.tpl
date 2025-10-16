<table class="table table-striped table-bordered" id="ListaConcursosEconomicas">
    <!-- ko if: EnableEconomic() -->
    

    <tbody>
        <tr>
                <h4>Instrucciones</h4>
                <ul class="list-unstyled">
                    <li>1. Adjunte archivo con su propuesta económica en formato PDF, JPG, GIF o ZIP.</li>
                    <li>2. Complte los campos en blanco obligatorios.</li>
                    <li>3. Asegúrese de respetar las unidades de medidas y monedas definidas para este concurso.</li>
                    <li>4. En caso de existir inconsistencias en la información proporcionada, su propuesta será
                        descartada.</li>
                </ul>
            </td>
        </tr>
    </tbody>
    <!-- /ko -->

    <tbody data-bind="foreach: EconomicProposal().documents()">
        <tr>
            <!-- ko if:
                (name() !== 'Planilla Estructura de Costos' && name() !== 'Análisis de Precios Unitarios') || 
            (name() === 'Planilla Estructura de Costos' && $root.Costs() === 'si') || 
            (name() === 'Análisis de Precios Unitarios' && $root.AnalisisApu() === 'si')
                
            -->
            <td data-bind="text: name" class="col-md-2 vertical-align-middle text-center"></td>
            <!-- ko if: $root.EnableEconomic() -->
            <td class="col-md-6 text-center vertical-align-middle">
                <input id="input-700" data-bind="fileinput: $data, fileinputOptions: {
                    uploadUrl: '/media/file/upload',
                    initialCaption: filename() ? filename() : [],
                    uploadExtraData: {
                        UserToken: User.Token,
                        path: $parent.FilePathOferente(),
                        concurso_id: $root.IdConcurso(),
                        concurso_nombre: $root.Nombre() 
                    },
                    
                    initialPreview: filename() ? [$parent.FilePathOferente() + filename()] : [],
                    allowedFileExtensions: ['jpg', 'jpeg', 'png', 'pdf', 'zip', 'rar', 'doc', 'docx', 'xls', 'xlsx', 'dwg']
                },
                    " name="file[]" type="file">
            </td>
            <!-- /ko -->
            <td class="col-md-2 vertical-align-middle text-center">
                <!-- ko if: filename() -->
                <a data-bind="click: $root.downloadFile.bind($data, filename(), 'oferente', $root.OferenteId())"
                    download class="btn btn-xl green" title="Descargar">
                    Descargar
                    <i class="fa fa-download"></i>
                </a>
                <!-- /ko -->
                <!-- ko if: !filename() -->
                <span class="label label-danger">Sin archivo</span>
                <!-- /ko -->
            </td>
            
            <!-- /ko -->
        </tr>
    <tbody>
        <tr>
            <td class="col-md-2 vertical-align-middle text-center">
                Comentarios
            </td>
            <td colspan="2" class="col-md-10 vertical-align-middle text-center">
                <div class="form-group">
                    <label class="control-label visible-ie8 visible-ie9" style="display: block;">Máximo 5000
                        caracteres</label>
                    <textarea class="form-control placeholder-no-fix" maxlength="5000" rows="3" id="maxlength_textarea"
                        name="comentario_economica"
                        data-bind="value: EconomicProposal().comment, disable: !EnableEconomic() ">
                    </textarea>
                    <td class="col-md-3 text-center vertical-align-middle">           
                    </td>
                    <!-- /ko -->
                </td>
                </div>
                
            </td>
        </tbody>
        <tbody>
            <td>
           
            </td>
            <td>
            <table>
            <tr>
              <td class="instructions">
                <div class="title">Instrucciones Importación Excel:</div>
                <span class="step">1.</span> Descargue el archivo Excel con los ítems a cotizar. <br>
                <span class="step">2.</span> Complete las columnas en color BLANCO con: <br>
                &emsp;• Precio unitario <br>
                &emsp;• Cantidad <br>
                &emsp;• Plazo de entrega <br>
                <span class="step">3.</span> Respete las cantidades mínimas y totales solicitadas. <br>
                <span class="step">4.</span> No modifique el orden de las filas y columnas . <br>
                <span class="step">5.</span> Suba el archivo. <br>
                <span class="step">6.</span> Si quedan casilleros vacíos, revise que estén correctas las cantidades. <br>
                <span class="step">7.</span> Revise el Total Parcial y Total cotizado.
              </td>
            </tr>
          </table>
          

        </tbody>
        <tbody data-bind="css: { 'disabled-section': !EnableEconomic() }">
            <td>
                <span style="display: block; text-align: center;">Importación Excel</span>
            </td>
            <td colspan="3" class="text-left">
                <div class="btn-group" role="group" aria-label="Botones de Excel" style="display: flex; justify-content: flex-start; gap: 5px;">
                    <button data-bind="click: DownloadEmptyExcel, enable: EnableEconomic" class="btn btn-xl green" style="border-radius: 5px;" id="EmptyExcelButton">
                        Descargar Excel
                        <i class="fa fa-download"> </i>
                    </button>

                    <input type="file" data-bind="fileUploadExcel: uploadFile, enable: EnableEconomic" class="btn default btn-file" style="border-radius: 5px;">

                    <a data-bind="click: uploadFileProcesar, enable: EnableEconomic" class="btn btn-xl green" title="Procesar" style="border-radius: 5px;">
                        Importar
                        <i class="fa fa-download"></i>
                    </a>

                    <a data-bind="click: uploadFileclear, enable: EnableEconomic" class="btn btn-default btn-secondary fileinput-remove fileinput-remove-button limpiar-btn" title="Quitar" style="border-radius: 5px;">
                        Quitar
                        <i class="glyphicon glyphicon-trash"></i>
                    </a>
                </div>
            </td>
        </tbody>

        <tr>
            <td colspan="9">
                <table style="width: 100%;">
                    <tr>
                        <td style="width: calc(100% / 5);"></td> <!-- salta las primeras 5 columnas -->
                        <td style="text-align: center; font-size: 20px;">
                            <span><strong>Moneda:</strong> <span data-bind="text: Moneda"></span><strong> -</strong></span>
                            <span style="color: red; font-weight: bold;">PRECIOS SIN IVA</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>


        <tr>
            <td colspan="4">
                <table class="table table-striped table-bordered" id="ListaConcursosEconomicasXD">
                    <thead>
                        <tr>
                            <th class="text-center vertical-align-middle" style="white-space: nowrap;">

                            </th>
                            <th class="text-center vertical-align-middle" style="white-space: nowrap;">
                                Item
                            </th>
                            <th class="text-center vertical-align-middle" style="white-space: nowrap;">
                                Unidad
                            </th>
                            <th class="text-center vertical-align-middle" style="white-space: nowrap;">
                                Cant Sol
                            </th>
                            <th class="text-center vertical-align-middle" style="white-space: nowrap;">
                                Cant Min
                            </th>
                            <th class="text-center vertical-align-middle" style="white-space: nowrap;">
                                Precio Unitario
                            </th>
                            <th class="text-center vertical-align-middle" style="white-space: nowrap;">
                                Cant Cot
                            </th>

                            <th data-bind="visible: $root.IsSobrecerrado()" class="text-center vertical-align-middle"
                                style="white-space: nowrap;">
                                Pl. Entr (días)
                            </th>
                            <th class="text-center vertical-align-middle" style="white-space: nowrap;">
                                Total Parcial
                            </th>
                        </tr>
                    </thead>
                    <tbody data-bind="foreach: EconomicProposal().values()">
                        <tr>
                            <td class="text-center vertical-align-middle col-md-1">
                                <div class="onoffswitch">
                                    <input type="checkbox" class="onoffswitch-checkbox"
                                        data-bind="attr: { id: product_id }, checked: ProductSelected, disable: !$root.EnableEconomic()" />
                                    <label class="onoffswitch-label" data-bind="attr: { for: product_id }">
                                        <span class="onoffswitch-inner"></span>
                                        <span class="onoffswitch-switch"></span>
                                    </label>
                                </div>
                            </td>
                            <td class="text-justify vertical-align-middle col-md-2" style="white-space: normal;">
                                <div style="position: relative;">
                                    <p data-bind="text: product_name" style="padding-right: 10%;"></p>
                                    <!-- ko if: (product_description() != null) -->
                                    <span data-bind="attr: { 'data-toggle': 'tooltip', 'title': product_description }"
                                        style="position: absolute; top: 50%; right: 0;transform: translate(-50%, -50%);">
                                        <i class="fa fa-info-circle" aria-hidden="true">
                                        </i>
                                    </span>
                                    <!-- /ko -->
                                </div>
                            </td>
                            <td class="text-center vertical-align-middle col-md-1" data-bind="text: measurement_name">
                            </td>
                            <td class="text-center vertical-align-middle col-md-1" data-bind="text: total_quantity">
                            </td>
                            <td class="text-center vertical-align-middle col-md-1" data-bind="text: minimum_quantity">
                            </td>
                            <td  class="text-center vertical-align-middle col-md-2">
                                <input id="cotizacion" class="cotizacion form-control placeholder-no-fix"  data-bind="inputmask: {
                                        value: cotizacion,
                                        alias: 'monto',
                                    },
                                    attr: { required: ProductSelected() ? true : null },
                                    disable:!$root.EnableEconomic() || !ProductSelected() "/>
                            </td>
                            <td class="text-center vertical-align-middle col-md-1">
                                <input id="cantidad" class="cantidad form-control placeholder-no-fix"   data-bind="inputmask: {
                                        alias: 'monto',
                                    },
                                    textInput: cantidad,
                                    attr: { required: ProductSelected() ? true : null },
                                    disable:!$root.EnableEconomic() || !ProductSelected()" />
                            </td>
                            <!-- ko if: $root.IsSobrecerrado() -->
                            <td class="text-center vertical-align-middle col-md-1">
                                <input id="fecha" class="fecha form-control placeholder-no-fix"  data-bind="inputmask: {
                                        alias: 'cant',
                                    },
                                    textInput: fecha,
                                    attr: { required: ProductSelected() ? true : null },
                                    disable:!$root.EnableEconomic() || !ProductSelected() "  />
                            </td>
                            <td class="text-center vertical-align-middle col-md-2">
                                <input class="form-control" 
                                    data-bind="value: (cantidad() * cotizacion()), 
                                            inputmask: { alias: 'monto' }, 
                                            disable: true" />
                            </td>


                            </td>
                            <!-- /ko -->
                        </tr>
                    </tbody>
                </table>
                
                <div class="col-md-3">
                    <label class="control-label visible-ie8 visible-ie9" style="display: block;">
                        Anticipo
                    </label>
                    <div class="selectRequerido">
                        <select data-bind="
                            value:  EconomicProposal().CondicionPago, 
                            valueAllowUnset: true, 
                            options: EconomicProposal().CondicionesPago, 
                            optionsText: 'text', 
                            optionsValue: 'id', 
                            disable: !$root.EnableEconomic() || $root.CondicionPago() === 'no',
                            select2: { placeholder: 'Seleccionar...' }">
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="control-label visible-ie8 visible-ie9" style="display: block;">
                        Plazo de pago
                    </label>
                    <div class="selectRequerido">
                        <select data-bind="
                            value: EconomicProposal().PlazoPago, 
                            valueAllowUnset: true, 
                            options: EconomicProposal().PlazosPago, 
                            optionsText: 'text', 
                            optionsValue: 'id', 
                            select2: { placeholder: 'Seleccionar...' },
                            disable: !EnableEconomic()">
                        </select>
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; margin-top: 15px;">
                    <div style="width: 253px;">
                        <label class="control-label" style="display: block;">
                            Total Cotizado
                        </label>
                        <input type="text" class="form-control" style="font-size: 13px;" 
                            data-bind="value: EconomicProposal().values().reduce(function(total, item) { 
                                return total + (item.cantidad() * item.cotizacion()); 
                            }, 0), 
                                    inputmask: { alias: 'monto' }, 
                                    disable: true" />
                    </div>
                </div>
            </td>
        </tr>
    </tbody>
</table>


<!-- ko if: EnableEconomic() -->
<div class="row">
    <div class="col-sm-12">
        <!-- Botones -->
        <table class="table table-striped table-bordered" id="ListaConcursosGo">
            <tbody>
                <tr>
                    <td colspan="3" class="col-md-2 text-center vertical-align-middle">
                        <!-- ko if: !HasEconomicaRevisada() -->
                        <button type="button" class="btn btn-lg green" title="Enviar propuesta económica"
                    data-bind="click: EconomicSend.bind($data, false)">
                            Enviar propuesta económica
                            <i class="fa fa-send"></i>
                        </button>


                        <button type="button" class="btn btn-lg default" title="Guardar sin enviar"
                            data-bind="click: EconomicSend.bind($data, true)">
                            Guardar sin enviar
                            <i class="fa fa-save"></i>
                        </button>
                        <!-- /ko -->
                        <button type="button" class="btn btn-lg red" title="Declinar Participación"
                            data-bind="click: $root.RejectParticipation.bind($data, 'rechazar')">
                            Declinar Participación
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Agregar el siguiente estilo CSS -->
<style>
.limpiar-btn:hover {
    background-color: red;
    color: white;
}

#EmptyExcelButton
{
    background-color: green;
    color: white;
    border-color: green;
}

#EmptyExcelButton:hover
{
    background-color: darkgreen;
}

table {
  width: 100%;
  border-collapse: collapse;
}

td.instructions {
  text-align: justify;
  padding: 15px;
  width: 100%;
  max-width: 100%;
  font-size: 16px;
  line-height: 1.5;
}

.title {
  font-size: 20px;
  color: #333;
  margin-bottom: 10px;
}

.step {
  color: #555;
}


</style>



<!-- /ko -->