{capture 'post_scripts_child'}
    <script src="{asset('/global/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js')}" type="text/javascript"></script>
    <script src="{asset('/pages/scripts/components-bootstrap-maxlength.js')}" type="text/javascript"></script>
    <script src="{asset('/global/plugins/bootbox/bootbox.min.js')}" type="text/javascript"></script>
{/capture}
{$post_scripts_child[] = $smarty.capture.post_scripts_child scope="global"}

<div class="row">
    <div class="col-sm-12">
        <div class="m-heading-1 border-default m-bordered text-left">
            <h4 class="block bold" style="margin-top: 0; padding-top: 0;">Resultados rondas de ofertas</h4>
            <ul data-bind="foreach: RondasOfertas()" class="nav nav-pills nav-justified">
                <li data-bind="css: { active: active ? 'active' : '' }">
                    <a data-toggle="pill" data-bind="text:title, attr: { href: '#'+ref }"></a>
                </li>
            </ul>
            <div class="tab-content" data-bind="foreach: RondasOfertas()">
                <div class="tab-pane fade"
                    data-bind="attr: { id: ref }, css: { in: active ? 'in' : '', active: active ? 'active' : '' }">

                    {* Comparativas de ofertas *}
                    <div class="m-heading-1 border-default m-bordered text-left"
                        style="display: flex; justify-content: space-between; flex-direction: column;">
                        {include file='concurso/detail/customer/partials/economica/resumen-adjudicacion/comparativa-ofertas.tpl'}
                    </div>
                
                    
                    <div class="m-heading-1 border-default m-bordered text-left"
                        style="display: flex; justify-content: space-between; flex-direction: column;">
                        {include file='concurso/detail/customer/partials/economica/resumen-adjudicacion/mejor-integral.tpl'}
                    </div>
                    
                    <div class="m-heading-1 border-default m-bordered text-left"
                        style="display: flex; justify-content: space-between; flex-direction: column;">
                        {include file='concurso/detail/customer/partials/economica/resumen-adjudicacion/mejor-individual.tpl'}
                    </div>
                    
                    <!-- ko if: active -->
                    <div class="m-heading-1 border-default m-bordered text-left table-responsive"
                        style="display: flex; justify-content: space-between; flex-direction: column;">
                        {include file='concurso/detail/customer/partials/economica/resumen-adjudicacion/mejor-manual.tpl'}
                    </div>
                    <div class="m-heading-1 border-default m-bordered text-left"
                        style="display: flex; justify-content: space-between; flex-direction: column;">
                        {include file='concurso/detail/customer/partials/economica/resumen-adjudicacion/resumen.tpl'}
                    </div>
                    <!-- /ko -->
                    <!-- Bloque de Informes -->
                    <div class="m-heading-1 border-default m-bordered text-left"
                        style="display:flex; flex-direction:column; gap:8px;">
                        <h4 class="block bold" style="margin:0;">Informes</h4>

                        <p class="text-muted" style="margin:0;">
                            El informe se generará con la información obtenida hasta el momento de su descarga.
                        </p>

                        <div>
                            <a class="btn btn-success"
                            data-bind="click: $root.downloadReport"
                            download>
                                Descargar <i class="fa fa-download"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>