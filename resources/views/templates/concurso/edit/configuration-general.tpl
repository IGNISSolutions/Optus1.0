<div class="row">
    <div class="col-md-12">
        <div class="form-group required" data-bind="validationElement: Entity.OferentesAInvitar">
            <label for="oferentes_a_invitar" class="control-label">
                Oferentes a Invitar
            </label>
            <label> (Los Proveedores Invitados <strong>NO</strong> podrán ser excluidos de la Licitación) </label>
            <div class="selectRequerido">
                <select id="oferentes_a_invitar"
                    data-bind="selectedOptions: 
                        Entity.OferentesAInvitar, 
                        options: Entity.OferentesAInvitarList, 
                        valueAllowUnset: true, 
                        optionsText: 'text', 
                        optionsValue: 'id', 
                        select2: { placeholder: 'Seleccionar...', allowClear: true, multiple: true }, disable: BloquearInvitacionOferentes()">
                </select>
            </div>
            <!-- ko if: !BloquearInvitacionOferentes() -->
            <div style="text-align:right;padding:10px;">
                <a class="btn btn-primary" data-bind='click: addAll'>
                    Añadir todos
                </a>
                <a class="btn btn-primary" data-bind='click: removeAll'>
                    Limpiar
                </a>
                <a class="btn btn-primary" data-toggle="modal" data-target="#modal-filtros-oferente">
                    Búsqueda Avanzada
                </a>
            </div>
            <!-- /ko -->
        </div>

        <div class="modal fade" id="modal-filtros-oferente" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content c-square">
                    <div class="modal-header" style="height:50px;">
                        <h4 class="modal-title bold">Seleccione filtros</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <div id="accordion">
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="filters_areas" class="control-label">
                                            Categorías/Rubros
                                        </label>
                                        <select class="form-control" multiple id="filters_areas"
                                            data-bind="
                                                        selectedOptions: Filters().Areas, 
                                                        valueAllowUnset: true, 
                                                        select2: { placeholder: 'Seleccionar...', allowClear: true, multiple: true, matcher:matchStart },disable: ReadOnly()">
                                            <!-- ko foreach: Filters().AreasList -->
                                            <optgroup data-bind="attr: { label: text }, foreach: areas">
                                                <option data-bind="value: id, text: text"></option>
                                            </optgroup>
                                            <!-- /ko -->
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="filters_provinces" class="control-label">
                                            Países/Provincias
                                        </label>
                                        <select class="form-control" multiple id="filters_provinces" data-bind="
                                                        selectedOptions: Filters().Provinces, 
                                                        valueAllowUnset: true,
                                                        select2: { placeholder: 'Seleccionar...', allowClear: true, multiple: true, matcher:matchStart }, 
                                                        disable: ReadOnly()">
                                            <!-- ko foreach: Filters().ProvincesList -->
                                            <optgroup data-bind="attr: { label: text }, foreach: provincias">
                                                <option data-bind="value: id, text: text"></option>
                                            </optgroup>
                                            <!-- /ko -->
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="filters_cities" class="control-label">
                                            Ciudades
                                        </label>
                                        <select id="filters_cities"
                                            data-bind="selectedOptions: 
                                                        Filters().Cities, 
                                                        options: Filters().CitiesList, 
                                                        valueAllowUnset: true, 
                                                        optionsText: 'text', 
                                                        optionsValue: 'id', 
                                                        select2: { placeholder: 'Seleccionar...', allowClear: true, multiple: true }, disable: ReadOnly() || Filters().Provinces().length == 0">
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline dark sbold"
                                data-bind="click: filter.bind($data, false)">
                            Aplicar filtros
                        </button>

                        <button type="button" class="btn btn-outline dark sbold"
                                data-bind="click: clearFilters">
                            Limpiar filtros
                        </button>

                        <!-- NUEVO: agrega todos los resultados filtrados al select principal -->
                        <button type="button" class="btn btn-success"
                                data-bind="click: addFilteredToInvite,
                                            enable: Entity.OferentesAInvitarList().length > 0,
                                            visible: !BloquearInvitacionOferentes()">
                            Agregar oferentes
                        </button>

                        <button type="button" class="btn btn-outline dark sbold" data-dismiss="modal">
                            Cerrar
                        </button>
                        </div>


                    <!-- tabla resultado-->
                    <div style="position: relative; height: 200px; overflow: auto; display: block;" class="portlet-body"
                        data-bind="style: { 
                        'display': Entity.OferentesAInvitarList().length > 0 ? 'block' : 'none'
                    }">
                        <table class="table table-striped table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>
                                        Proveedores a Invitar
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- ko foreach: Entity.OferentesAInvitarList -->
                                <tr>
                                    <td data-bind="text: text"></td>
                                </tr>
                                <!-- /ko -->
                            </tbody>
                        </table>
                    </div>
                    <!-- tabla resultado -->
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>
                Aceptar pliegos y términos y condiciones
            </label>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <label class="radio-inline">
                <input name="AceptacionTerminos" type="radio" value="si"
                    data-bind="checked: Entity.AceptacionTerminos, disable: ReadOnly()"> SI
            </label>
            <label class="radio-inline">
                <input name="AceptacionTerminos" type="radio" value="no"
                    data-bind="checked: Entity.AceptacionTerminos, disable: ReadOnly()"> NO
            </label>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>
                Permitir técnico ver ofertas
            </label>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <label class="radio-inline">
                <input name="TecnicoOfertas" type="radio" value="si"
                    data-bind="checked: Entity.TecnicoOfertas, disable: ReadOnly()">SI
            </label>
            <label class="radio-inline">
                <input name="TecnicoOfertas" type="radio" value="no"
                    data-bind="checked: Entity.TecnicoOfertas, disable: ReadOnly()">NO
            </label>            
        </div>
    </div>

    <div class="col-md-12">
        <div class="form-group">
            <label class="control-label visible-ie8 visible-ie9" style="display: block;">Usuario para calificar
                desempeño y contestar muro de consultas</label>
            <select
                data-bind="selectedOptions: Entity.UsuarioCalificaReputacion, 
                valueAllowUnset: true, 
                options: Entity.UsuariosCalificanReputacion, 
                optionsText: 'text', 
                optionsValue: 'id', select2: { placeholder: 'Seleccionar...', allowClear: true, multiple: true }, disable: ReadOnly()">
            </select>
        </div>
    </div>
</div>