<div class="row">

    <div class="col-md-6">
        <div class="form-group">
            <label>¿Incluye etapa de precalificación técnica?</label>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <label class="radio-inline">
                <input type="radio" name="IncluyePrecalifTecnica" value="si"
                    data-bind="checked: Entity.IncluyePrecalifTecnica, disable: ReadOnly(), disable: ReadOnly()">
                SI
            </label>
            <label class="radio-inline">
                <input type="radio" name="IncluyePrecalifTecnica" value="no"
                    data-bind="checked: Entity.IncluyePrecalifTecnica, disable: ReadOnly(), disable: ReadOnly() || $root.BloquearCamposTecnica()">
                NO
            </label>
        </div>
    </div>
</div>
<!-- ko if: IsSobrecerrado() || IsOnline() -->
<div class="row">
    <div class="col-md-12">
        <div id=idPlantillaTecnica class="form-group" data-bind="validationElement: Entity.PlantillaTecnica">
            <label class="control-label visible-ie8 visible-ie9" style="display: block;">Plantilla de
                precalificación
                técnica</label>
            <div class="selectRequerido">
                <select data-bind="
                value: Entity.PlantillaTecnica, 
                valueAllowUnset: true, 
                options: Entity.PlantillasTecnicas, 
                optionsText: 'text', 
                optionsValue: 'id', 
                select2: { placeholder: 'Seleccionar...', allowClear: true}, 
                disable: IsDisableIncluyePrecalifTecnica() || ReadOnly() || $root.BloquearCamposTecnica()">
                </select>
            </div>
        </div>

        <!-- ko if: Entity.PlantillaTecnicaSeleccionada() !== null -->
        <div class="tabbable-custom nav-justified">

            <ul class="nav nav-tabs nav-justified">
                <li class="active">
                    <a href="#tab_1" data-toggle="tab"></a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab_1">
                    <table class="table table-striped table-bordered" id="ListaConcursosEconomicas">
                        <thead>
                            <tr>
                                <th> Atributo </th>
                                <th> Puntuación (0 &#8230; 100)</th>
                                <th style="text-align: right;"> Ponderación total (%)</th>
                            </tr>
                        </thead>
                        <tbody data-bind="">
                            <tr>
                                <td>
                                    <label class="control-label visible-ie8 visible-ie9" style="display: block;">
                                        Puntaje mínimo necesario
                                    </label>
                                </td>
                                <td>
                                    <input type="number" class="form-control"
                                        data-bind="value: Entity.PlantillaTecnicaSeleccionada().puntaje_minimo, disable: ReadOnly() || $root.BloquearCamposTecnica()"
                                        min="0" max="100" step="10">
                                </td>
                                <!-- ko if: Entity.PlantillaTecnicaSeleccionada().total() == 100 -->
                                <th data-bind="text: Entity.PlantillaTecnicaSeleccionada().total"
                                    style="text-align: right; color:green;">
                                </th>
                                <!-- /ko -->
                                <!-- ko if: Entity.PlantillaTecnicaSeleccionada().total() != 100 -->
                                <th data-bind="text: Entity.PlantillaTecnicaSeleccionada().total"
                                    style="text-align: right; color:red;">
                                </th>
                                <!-- /ko -->
                            </tr>
                        </tbody>
                    </table>

                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th> Atributo </th>
                                <th style="text-align: right;"> Ponderación (%) </th>
                            </tr>
                        </thead>
                        <!-- ko if: !ReadOnly() -->
                        <tbody id="ponderacion" data-bind="foreach: Entity.PlantillaTecnicaSeleccionada().payroll">
                            <tr>
                                <td data-bind="text: atributo" class="col-md-7 vertical-align-middle">
                                </td>
                                <td class="col-md-1 text-center vertical-align-middle">
                                    <input class="form-control ponderacion" type="number" id="ponderacion"
                                        name="ponderacion" min="0" max="100"
                                        data-bind="value: ponderacion, disable: $root.ReadOnly() || $root.BloquearCamposTecnica()">
                                </td>
                            </tr>
                        </tbody>
                        <!-- /ko -->
                        <!-- ko if: ReadOnly() -->
                        <tbody id="ponderacion"
                            data-bind="dataTablesForEach : { data: Entity.PlantillaTecnicaSeleccionada().payroll, options: { paging: false, searching: false, info: false, ordering: false }}">
                            <tr>
                                <td data-bind="text: atributo" class="col-md-6 vertical-align-middle">
                                </td>
                                <td class="col-md-2 text-center vertical-align-middle">
                                    <input class="form-control" type="number"
                                        data-bind="value: ponderacion, disable: $root.ReadOnly() || $root.BloquearCamposTecnica()">
                                </td>
                            </tr>
                        </tbody>
                        <!-- /ko -->
                    </table>
                </div>
            </div>
        </div>
        <!-- /ko -->
    </div>
</div>

<!-- ko if: Entity.PlantillaTecnicaSeleccionada() !== null -->
<div class="row">
    <!-- Nuevos campos para todas las plantillas -->
    <div class="col-md-12">
        <span class="caption-subject bold uppercase d-block mb-3"
            style="text-align: left; display: block; margin-bottom: 15px;">
            MARQUE LOS DOCUMENTOS QUE DEBEN PRESENTAR LOS PROVEEDORES
        </span>
        <div class="form-group col-md-6">
            <span>Lista de sub contratistas</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.ListaProveedores, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.ListaProveedores, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-6" style="padding-left: 30px;">
            <span>Certificado de visita de obra</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.CertificadoVisitaObra, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.CertificadoVisitaObra, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
    </div>


    <!-- ko if: Entity.PlantillaTecnica() == 1 -->
    <div class="col-md-6">

        <div class="form-group col-md-12">
            <span>Póliza de seguro de caución</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.SeguroCaucion, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.SeguroCaucion, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-12">
            <span>Base y condiciones FIRMADO</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.BaseCondicionesFirmado, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.BaseCondicionesFirmado, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-12">
            <span>Condiciones generales FIRMADO</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.CondicionesGenerales, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.CondicionesGenerales, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-12">
            <span>Pliego tecnico FIRMADO</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.PliegoTecnico, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.PliegoTecnico, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-12">
            <span>Documentacion Alta Proveedor</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.LegajoImpositivo, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.LegajoImpositivo, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group col-md-12">
            <span>Diagrama de Gantt/Cronograma de trabajo</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.DiagramaGant, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.DiagramaGant, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-12">
            <span>Acuerdo de confidencialidad FIRMADO</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.AcuerdoConfidencialidad, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.AcuerdoConfidencialidad, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-12">
            <span>Antecedentes y Referencias</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.AntecedentesReferencias, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.AntecedentesReferencias, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-12">
            <span>Reporte accidentes</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.ReporteAccidentes, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.ReporteAccidentes, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-12">
            <span>Envío de muestra</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.EnvioMuestras, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.EnvioMuestras, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>



    </div>
    <!-- /ko -->
    <!-- ko if: Entity.PlantillaTecnica() == 2 -->
    <div class="col-md-12">
        <div class="form-group col-md-6">
            <span>NOM-251-SSA1-2009</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.nom251, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.nom251, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-6">
            <span>Distintivo H</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.distintivo, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.distintivo, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-6">
            <span>Filtros Sanitarios Trimestrales a los empleados</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.filtros_sanitarios, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.filtros_sanitarios, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-6">
            <span>Documentación REPSE</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.repse, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.repse, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-6">
            <span>Póliza de seguro responsabilidad civil</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.poliza, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.poliza, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-6">
            <span>Prima de riesgo 5 millones</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.primariesgo, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.primariesgo, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
    </div>
    <!-- /ko -->

    <!-- ko if: Entity.PlantillaTecnica() == 3 -->
    <div class="col-md-12">
        <div class="form-group col-md-6">
            <span>Referencias comerciales</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.obras_referencias, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.obras_referencias, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-6">
            <span>Organigrama de obra</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.obras_organigrama, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.obras_organigrama, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-6">
            <span>Equipos y herramientas</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.obras_equipos, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.obras_equipos, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-6">
            <span>Cronograma de obra</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.obras_cronograma, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.obras_cronograma, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-6">
            <span>Memoria técnica</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.obras_memoria, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.obras_memoria, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-6">
            <span>Antecedentes de obras similares</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.obras_antecedentes, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.obras_antecedentes, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
    </div>
    <!-- /ko -->
    <!-- ko if: Entity.PlantillaTecnica() == 4 -->
    <div class="col-md-12">
        <div class="form-group col-md-6">
            <span>Ficha Técnica de la tarima</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.tarima_ficha_tecnica, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.tarima_ficha_tecnica, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-6">
            <span>Licencia Ambiental integral (LAI)</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.tarima_licencia, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.tarima_licencia, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-6">
            <span>Cumplimiento NOM-144 SEMARNAT 2017</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.tarima_nom_144, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.tarima_nom_144, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-6">
            <span>Acreditación legal con la procedencia de la madera</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.tarima_acreditacion, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.tarima_acreditacion, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
    </div>
    <!-- /ko -->
    <!-- ko if: Entity.PlantillaTecnica() == 5 -->
    <div class="col-md-12">
        <div class="form-group col-md-6">
            <span>Último balance de la empresa</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.edificio_balance, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.edificio_balance, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-6">
            <span>Ultimas 3 DDJJ de IVA</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.edificio_iva, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.edificio_iva, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-6">
            <span>Constancia de CUIT</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.edificio_cuit, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.edificio_cuit, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-6">
            <span>Brochure de antecedentes de edificios incluyendo obras en curso</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.edificio_brochure, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.edificio_brochure, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-6">
            <span>Organigrama de la empresa (puestos claves)</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.edificio_organigrama, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.edificio_organigrama, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-6">
            <span>Organigrama previsto para la obra</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.edificio_organigrama_obra, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.edificio_organigrama_obra, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-6">
            <span>Listado de subcontratistas por rubro</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.edificio_subcontratistas, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.edificio_subcontratistas, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-6">
            <span>Gestión de H&S (incluir indicadores, procedimientos, detalle de personal, etc.)</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.edificio_gestion, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.edificio_gestion, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-6">
            <span>Listado de máquinas y equipos a utilizar</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.edificio_maquinas, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.edificio_maquinas, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
    </div>
    <!-- /ko -->
    <!-- ko if: Entity.PlantillaTecnica() == 7 -->
    <div class="col-md-12">
        <div class="form-group col-md-6">
            <span>Propuesta Técnica / Procedimientos / Metodologías / Técnicas aplicadas</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.PropuestaTecnica, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.PropuestaTecnica, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>

        <div class="form-group col-md-6">
            <span>Plan de mantenimiento preventivo, correctivo, soporte, evolutivo</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.PlanMantenimientoPreventivo, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.PlanMantenimientoPreventivo, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>

        <div class="form-group col-md-6">
            <span>Diagrama de Gantt / Cronograma de trabajo</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.DiagramaGant, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.DiagramaGant, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>

        <div class="form-group col-md-6">
            <span>Acuerdo de confidencialidad FIRMADO</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.NdaFirmado, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.NdaFirmado, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>

        <div class="form-group col-md-6">
            <span>Inventario de equipos, herramientas, vehículos y/o maquinarias</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.InventarioEquipos, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.InventarioEquipos, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>

        <div class="form-group col-md-6">
            <span>Acreditaciones, Permisos, Autorizaciones</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.AcreditacionesPermisos, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.AcreditacionesPermisos, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>

        <div class="form-group col-md-6">
            <span>Requerimientos tecnológicos de hardware, software y/o conectividad</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.RequerimientosTecnologicos, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.RequerimientosTecnologicos, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>

        <div class="form-group col-md-6">
            <span>Requisitos del personal, calificaciones, CV, certificaciones, experiencia, capacitación, etc</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.RequisitosPersonal, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.RequisitosPersonal, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>

        <div class="form-group col-md-6">
            <span>Organigrama / Equipo de Trabajo / Niveles de escalamiento</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.OrganigramaEquipo, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.OrganigramaEquipo, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>

        <div class="form-group col-md-6">
            <span>Valor agregado</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.ValorAgregado, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.ValorAgregado, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>

        <div class="form-group col-md-6">
            <span>Acuerdos de nivel de servicio</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.AcuerdosNivelServicio, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.AcuerdosNivelServicio, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>

        <div class="form-group col-md-6">
            <span>Requisitos matriz HSEQ según Anexo 2</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.HseqAnexo2, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.HseqAnexo2, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>

        <div class="form-group col-md-6">
            <span>Referencias comerciales / Acreditación experiencia</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.ReferenciasComerciales, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.ReferenciasComerciales, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>

        <div class="form-group col-md-6">
            <span>Forma de pago</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.FormaPago, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.FormaPago, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>

        <div class="form-group col-md-6">
            <span>Evaluación riesgo financiero</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.RiesgoFinanciero, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.RiesgoFinanciero, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
    </div>
    <!-- /ko -->
    <!-- ko if: Entity.PlantillaTecnica() == 8 -->
    <div class="col-md-12">
        <div class="form-group col-md-6">
            <span>Ficha de Especificaciones Técnicas</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.FichaEspecificaciones, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.FichaEspecificaciones, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>

        <div class="form-group col-md-6">
            <span>Hojas de seguridad / MSDS</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.MsdsHojasSeguridad, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.MsdsHojasSeguridad, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>

        <div class="form-group col-md-6">
            <span>Garantía</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.Garantia, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.Garantia, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>

        <div class="form-group col-md-6">
            <span>Envío de muestra</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.EnvioMuestras, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.EnvioMuestras, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>

        <div class="form-group col-md-6">
            <span>Cronograma de entrega / Plazo de entrega</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.CronogramaEntrega, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.CronogramaEntrega, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>

        <div class="form-group col-md-6">
            <span>Carta de representante de la marca y/o distribuidor autorizado</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.CartaRepresentanteMarca, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.CartaRepresentanteMarca, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>

        <div class="form-group col-md-6">
            <span>Soporte Post Venta</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.SoportePostVenta, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.SoportePostVenta, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
    </div>
    <!-- /ko -->

    <!-- ko if: Entity.PlantillaTecnica() == 6 -->
    <div class="col-md-12">
        <div class="form-group col-md-6">
            <span>Entrega de documentación para evaluación y Alta de proveedor</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.EntregaDocEvaluacion, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.EntregaDocEvaluacion, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-6">
            <span>Cumplimiento de requisitos legales y reglamentos aplicables</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.RequisitosLegales, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.RequisitosLegales, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-6">
            <span>Experiencia y referencias comerciales</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.ExperienciaYReferencias, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.ExperienciaYReferencias, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-6">
            <span>Documentación REPSE</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.DocumentacionREPSE, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.DocumentacionREPSE, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-6">
            <span>Alcance</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.Alcance, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.Alcance, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-6">
            <span>Garantías</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.Garantias, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.Garantias, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>



        <div class="form-group col-md-6">
            <span>Forma de pago</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.FormaPago, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.FormaPago, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>



        <div class="form-group col-md-6">
            <span>Tiempo de fabricación e instalación de cocinas</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.TiempoFabricacion, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.TiempoFabricacion, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>

        <div class="form-group col-md-6">
            <span>Ficha técnica (Materiales, especificaciones y características de la propuesta)</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.FichaTecnica, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.FichaTecnica, disable: Entity.IncluyePrecalifTecnica() == 'no' || ReadOnly() || $root.BloquearCamposTecnica()">
                    NO
                </label>
            </div>
        </div>

    </div>
    <!-- /ko -->

</div>
<!-- /ko -->

<div class="row">
    <div class="col-md-12">
        <div id="idUsuarioEvaluaTecnica" class="form-group" data-bind="validationElement: Entity.UsuarioEvaluaTecnica">
            <label class="control-label visible-ie8 visible-ie9" style="display: block;">
                Usuario evaluador de la propuesta Técnica
            </label>
            <div class="selectRequerido">
                <select data-bind="
                        selectedOptions: Entity.UsuarioEvaluaTecnica, 
                        valueAllowUnset: true, 
                        options: Entity.UsuariosEvaluanTecnica, 
                        optionsText: 'text', 
                        optionsValue: 'id', 
                        select2: { placeholder: 'Seleccionar...', allowClear: true, multiple: true }, 
                        disable: IsDisableIncluyePrecalifTecnica() || ReadOnly() || $root.BloquearCamposTecnica()
                        ">
                </select>
            </div>
        </div>
    </div>
</div>





<!-- /ko -->