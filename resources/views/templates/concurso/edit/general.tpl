<div class="row">
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group required" data-bind="validationElement: Entity.Nombre">
                    <label id="nombre" class="control-label visible-ie8 visible-ie9" style="display: block;">Nombre del
                        concurso</label>
                    <input class="form-control placeholder-no-fix" type="text" name="nombre" autocomplete="off"
                        id="nombre" data-bind="value: Entity.Nombre, disable: ReadOnly()" />
                </div>
            </div>  

            <!-- ko if: IsSobrecerrado() || IsOnline() -->
            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label visible-ie8 visible-ie9" style="display: block;">N° de solicitud de
                        compra</label>
                        <input
                        id="SolicitudCompra"
                        class="form-control placeholder-no-fix"
                        type="text"
                        maxlength="11"
                        autocomplete="off"
                        data-bind="value: Entity.SolicitudCompra, disable: ReadOnly()"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                      />                      
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label visible-ie8 visible-ie9" style="display: block;">Orden de compra</label>
                    <input class="form-control placeholder-no-fix" type="text" maxlength="11" autocomplete="off"
                        placeholder="" name="" id="OrdenCompra"
                        data-bind="value: Entity.OrdenCompra, disable: ReadOnly()" />
                </div>
            </div>
            <!-- /ko -->
            <!-- ko if: IsSobrecerrado() || IsOnline() || IsGo() -->
            <div class="col-md-6">
            <div class="form-group required">
                <label class="control-label visible-ie8 visible-ie9" style="display: block;">Área solicitante</label>
                <select class="form-control" id="AreaUsr" data-bind="options: $root.areasDisponibles, optionsText: 'text', optionsValue: 'text', value: Entity.AreaUsr, disable: ReadOnly()">
                </select>
            </div>
            </div>
            <!-- /ko -->
        </div>
    </div>

    <div class="col-md-6">
        <!-- ko if: IsSobrecerrado() || IsOnline() -->
        <div class="row" style="margin: 0; padding: 0;">
            <!-- ko if: IsSobrecerrado() -->
            <div class="col-md-12">
                <div class="form-group required" data-bind="validationElement: Entity.TipoLicitacion">
                    <label class="control-label visible-ie8 visible-ie9" style="display: block;">Tipo de licitación</label>
                    <div class="selectRequerido">
                        <select data-bind="value: Entity.TipoLicitacion,
                        valueAllowUnset: true,
                        options: Entity.TiposLicitacion,
                        optionsText: 'text',
                        optionsValue: 'id',
                        select2: { placeholder: 'Seleccionar...' },
                        disable: ReadOnly()">
                        </select>
                    </div>
                </div>
            </div>
            <!-- /ko -->
            <div class="col-md-12">
                <div class="form-group" data-bind="validationElement: Entity.ImagePath">
                    <label class="control-label visible-ie8 visible-ie9" style="display: block;">Imagen / Icono</label>
                    <!-- ko if: !ReadOnly() -->
                    <input id="input-700" data-bind="fileinput: Entity.Portrait, fileinputOptions: {
                        uploadUrl: '/media/file/upload',
                        initialCaption: Entity.Portrait().filename() ? Entity.Portrait().filename() : [],
                        uploadExtraData: {
                            UserToken: User.Token,
                            path: Entity.ImagePath(),
                        },
                        initialPreview: Entity.Portrait().filename() ? [Entity.ImagePath() + Entity.Portrait().filename()] : [],
                        allowedFileExtensions: ['jpg', 'jpeg', 'png']
                    }" name="file[]" type="file">
                    <!-- /ko -->

                    <!-- ko if: Entity.Portrait().filename() -->
                    <div class="text-center">
                        <img class="img-thumbnail img-responsive" style="max-height: 250px;"
                            data-bind="attr:{literal}{src: Entity.ImagePath() + Entity.Portrait().filename()}{/literal}">
                    </div>
                    <!-- /ko -->
                </div>
            </div>
        </div>
        <!-- /ko -->
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label class="control-label visible-ie8 visible-ie9" style="display: block;">Reseña (máximo 300
                caracteres)</label>
            <textarea class="form-control placeholder-no-fix" maxlength="300" rows="3" id="maxlength_textarea"
                data-bind="value: Entity.Resena, disable: ReadOnly()"></textarea>
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

    <!-- ko if: IsGo() -->
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label visible-ie8 visible-ie9" style="display: block;">Tipo de Carga</label>
                    <select
                        data-bind="value: Entity.GoLoadType, valueAllowUnset: true, options: Entity.GoLoadTypes, optionsText: 'text', optionsValue: 'id', select2: { placeholder: 'Seleccionar...' }, disable: ReadOnly()">
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label visible-ie8 visible-ie9" style="display: block;">Peso neto carga
                        (kg)</label>
                    <input class="form-control placeholder-no-fix" type="number" name="Peso" id="Peso"
                        data-bind="value: Entity.Peso, disable: ReadOnly()" />
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label visible-ie8 visible-ie9" style="display: block;">Ancho (m)</label>
                    <input class="form-control placeholder-no-fix" type="number" step="0.01" name="Ancho" id="Ancho"
                        data-bind="value: Entity.Ancho, disable: ReadOnly()" />
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label visible-ie8 visible-ie9" style="display: block;">Largo (m)</label>
                    <input class="form-control placeholder-no-fix" type="number" step="0.01" name="Largo" id="Largo"
                        data-bind="value: Entity.Largo, disable: ReadOnly()" />
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label visible-ie8 visible-ie9" style="display: block;">Alto (m)</label>
                    <input class="form-control placeholder-no-fix" type="number" step="0.01" name="Alto" id="Alto"
                        data-bind="value: Entity.Alto, disable: ReadOnly()" />
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label visible-ie8 visible-ie9" style="display: block;">Cantidad de
                        Unidades/Bultos</label>
                    <input class="form-control placeholder-no-fix" type="number" name="UnidadesBultos"
                        id="UnidadesBultos" data-bind="value: Entity.UnidadesBultos, disable: ReadOnly()" />
                </div>
            </div>
        </div>
    </div>
    <!-- /ko -->
</div>