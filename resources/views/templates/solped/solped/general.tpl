<div class="row">
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group required" data-bind="validationElement: Entity.Nombre">
                    <label id="nombre" class="control-label visible-ie8 visible-ie9" style="display: block;">Nombre de la
                        solicitud</label>
                    <input class="form-control placeholder-no-fix" type="text" name="nombre" autocomplete="off"
                        id="nombre" data-bind="value: Entity.Nombre, disable: ReadOnly()" />
                </div>
            </div>  

            

            <div class="col-md-6">
            <div class="form-group required">
                <label class="control-label visible-ie8 visible-ie9" style="display: block;">Área solicitante</label>
                    <select class="form-control" id="AreaSolicitante"
                    data-bind="options: $root.areasDisponibles,
                                optionsText: 'text',
                                optionsValue: 'id',
                                value: Entity.AreaSolicitante,
                                select2Safe: { placeholder: 'Seleccionar...' },

                                disable: ReadOnly()">
                    </select>
            </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
    <div class="form-group required" >
        <label class="control-label">Tipo de Compra</label>
        <select class="form-control" id="TipoCompra"
            data-bind="options: $root.TipoComprasDisponibles,
                        optionsText: 'text',
                        optionsValue: 'id',
                        value: Entity.TipoCompra,
                        valueAllowUnset: true,
                        select2Safe: { placeholder: 'Seleccionar...' },
                        disable: ReadOnly()">
            </select>

    </div>
</div>

<div class="col-md-6">
    <div class="form-group">
        <label class="control-label">Código Interno</label>
        <input class="form-control" 
            type="text" 
            data-bind="value: Entity.CodigoInterno, disable: ReadOnly()" />
    </div>
</div>


    {* <div class="col-md-12">
        <div class="form-group">
            <label class="control-label visible-ie8 visible-ie9" style="display: block;">Descripción</label>
            <!-- ko if: !ReadOnly() -->                
            <div class="mt-element-ribbon">
                <div class="ribbon ribbon-right ribbon-shadow ribbon-round ribbon-color-success" data-bind="text: (Entity.Descripcion() ? Entity.Descripcion().length : 0) + '/' + Entity.DescriptionLimit()"></div>
                <textarea class="form-control" required data-bind="value: Entity.Descripcion, summernote: {
                    height: 250,
                    disableDragAndDrop: true,
                    shortcuts: false,
                    dialogsInBody: false,
                    dialogsFade: false,
                    limit: Entity.DescriptionLimit()
                }">
                </textarea>
            </div>
            <!-- /ko -->
            <!-- ko if: ReadOnly() -->
            <textarea class="form-control placeholder-no-fix" maxlength="300" rows="3" id="maxlength_textarea" data-bind="value: Entity.Descripcion, disable: ReadOnly()"></textarea>
            <!-- /ko -->
        </div>
    </div> *}

    