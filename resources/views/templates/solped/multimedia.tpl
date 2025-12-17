<div class="row">
    <div class="" data-bind="css: ReadOnly() ? 'col-sm-12' : 'col-sm-6'">
        <div class="tabbable-custom nav-justified">
            <ul class="nav nav-tabs nav-justified">
                <li class="active">
                    <a href="#tab_1" data-toggle="tab">Lugar de Prestación del Servicio o Entrega del Producto</a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab_1">
                    <div class="form-group">
                        <label class="control-label visible-ie8 visible-ie9" style="display: block;">País</label>
                        <select
                            data-bind="value: Entity.Pais, valueAllowUnset: true, options: Entity.Countries, optionsText: 'text', optionsValue: 'code', select2Safe: { placeholder: 'Seleccionar...' }, disable: ReadOnly()">
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="control-label visible-ie8 visible-ie9" style="display: block;">Estado/Provincia</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="provincia" 
                            name="provincia"
                            data-bind="value: Entity.Provincia, disable: ReadOnly()">
                    </div>
                    <div class="form-group">
                        <label class="control-label visible-ie8 visible-ie9" style="display: block;">Ciudad</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="localidad" 
                            name="localidad"  
                            data-bind="value: Entity.Localidad, disable: ReadOnly()">
                    </div>
                    <div class="form-group">
                        <label class="control-label visible-ie8 visible-ie9" style="display: block;">Dirección</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="direccion" 
                            name="direccion" 
                            data-bind="value: Entity.Direccion, disable: ReadOnly()">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label visible-ie8 visible-ie9" style="display: block;">Código Postal</label>
                                <input type="text" class="form-control placeholder-no-fix" name="cp" id="cp" data-bind="value: Entity.Cp, disable: ReadOnly()" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label class="control-label visible-ie8 visible-ie9" style="display: block;">Google Map</label>
            <div id="map-canvas-1" style="width: 100%; height: 406px; background: #ccc;" data-bind="disable: ReadOnly()"></div>
        </div>
    </div>
</div>