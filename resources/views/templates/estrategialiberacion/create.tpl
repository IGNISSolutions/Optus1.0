{extends 'estrategialiberacion/main.tpl'}

<!-- ESTILOS -->
{block 'styles'}
    <link href="{asset('/global/plugins/jquery-ui/jquery-ui.min.css')}" rel="stylesheet" type="text/css" />
    
    
{/block}

<!-- SCRIPTS PREVIOS A KNOCKOUT -->
{block 'pre-scripts'}
    <script>
        var IdUsuario = {$id};
    </script>
    <script src="{asset('/global/plugins/jquery-ui/jquery-ui.min.js')}" type="text/javascript"></script>
    <script src="{asset('/global/plugins/jquery.pulsate.min.js')}" type="text/javascript"></script>
{/block}

<!-- SCRIPTS POSTERIORES A KNOCKOUT -->
{block 'post-scripts'}
{/block}

<!-- VISTA -->
{block 'estrategialiberacion-create'}

        <div class="row">
            <div class="col-md-12 ">
                <div class="portlet light bg-inverse">
                    <div class="portlet-title">
                        <div class="caption font-red-sunglo">
                            <span class="caption-subject bold uppercase">
                                Politica
                            </span>
                        </div>
                        <div class="tools">
                            <a href="javascript:;" class="collapse" data-original-title="Retraer/Expandir"
                                title="Retraer/Expandir"> </a>
                        </div>
                    </div>

                    <div class="portlet-body form">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group required" >
                                    <label class="control-label visible-ie8 visible-ie9" style="display: block;">
                                        Nivel 0
                                    </label>
                                    <input class="form-control placeholder-no-fix" type="number" name="nivel0"
                                         />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group required" >
                                    <label class="control-label visible-ie8 visible-ie9" style="display: block;">
                                        Nivel 1
                                    </label>
                                    <input class="form-control placeholder-no-fix" type="number" name="nivel1"
                                        />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group required" >
                                    <label class="control-label visible-ie8 visible-ie9" style="display: block;">
                                        Nivel 2
                                    </label>
                                    <input class="form-control placeholder-no-fix" type="number" name="nivel2"
                                         />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group required" data-bind="validationElement: Entity.Nombre">
                                    <label class="control-label visible-ie8 visible-ie9" style="display: block;">
                                        Nivel 3
                                    </label>
                                    <input class="form-control placeholder-no-fix" type="number" name="nivel3"
                                         />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group required" data-bind="validationElement: Entity.Nombre">
                                    <label class="control-label visible-ie8 visible-ie9" style="display: block;">
                                        Nivel 4
                                    </label>
                                    <input class="form-control placeholder-no-fix" type="number" name="nivel4"
                                         />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-md-12 text-right">
                <a href="{$urlBack}" type="button" class="btn default">
                    Volver a Politica
                </a>
                <button type="button" class="btn btn-primary" data-bind="click: Save, disable: IsdisableSave">
                    Guardar Datos
                </button>
            </div>
        </div>
    {/block}

    <!-- KNOCKOUT JS -->
    {block 'knockout' append}
        <script type="text/javascript">
            ko.validation.locale('es-ES');
            ko.validation.init({
                insertMessages: false,
                messagesOnModified: false,
                decorateElement: true,
                errorElementClass: 'wrong-field'
            }, false);

            

            jQuery(document).ready(function() {
                $.blockUI();                
            });

            // Chrome allows you to debug it thanks to this
            {chromeDebugString('dynamicScript')}
        </script>
{/block}