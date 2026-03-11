<!--[if lt IE 9]>
<script src="{asset('/global/plugins/respond.min.js')}"></script>
<script src="{asset('/global/plugins/excanvas.min.js')}"></script>
<script src="{asset('/global/plugins/ie8.fix.min.js')}"></script>
<![endif]-->

<script>
    var HOST = "{env('APP_SITE_URL')}"
    var LANG = '{$LANG}';
</script>

<!-- BEGIN CORE PLUGINS -->
<script src="{asset('/global/plugins/jquery.min.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/bootstrap/js/bootstrap.min.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/js.cookie.min.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/bootstrap-sweetalert/sweetalert.min.js')}" type="text/javascript"></script>
<script src="{asset('/pages/scripts/ui-sweetalert.min.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/jquery.blockui.min.js')}" type="text/javascript"></script>
<script src="{asset('/pages/scripts/ui-blockui.min.js')}" type="text/javascript"></script>
<!-- END CORE PLUGINS -->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="{asset('/global/plugins/moment.min.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/bootstrap-daterangepicker/daterangepicker.min.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/morris/morris.min.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/morris/raphael-min.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/counterup/jquery.waypoints.min.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/counterup/jquery.counterup.min.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/amcharts/amcharts/amcharts.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/amcharts/amcharts/serial.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/amcharts/amcharts/pie.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/amcharts/amcharts/radar.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/amcharts/amcharts/themes/light.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/amcharts/amcharts/themes/patterns.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/amcharts/amcharts/themes/chalk.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/amcharts/ammap/ammap.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/amcharts/ammap/maps/js/worldLow.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/amcharts/amstockcharts/amstock.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/fullcalendar/fullcalendar.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/fullcalendar/lang/es.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/horizontal-timeline/horizontal-timeline.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/flot/jquery.flot.min.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/flot/jquery.flot.resize.min.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/flot/jquery.flot.categories.min.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/jquery-easypiechart/jquery.easypiechart.min.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/jquery.sparkline.min.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/jqvmap/jqvmap/jquery.vmap.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/jqvmap/jqvmap/maps/jquery.vmap.russia.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/jqvmap/jqvmap/maps/jquery.vmap.world.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/jqvmap/jqvmap/maps/jquery.vmap.europe.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/jqvmap/jqvmap/maps/jquery.vmap.germany.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/jqvmap/jqvmap/maps/jquery.vmap.usa.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/jqvmap/jqvmap/data/jquery.vmap.sampledata.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/bootstrap-tagsinput/bootstrap-tagsinput.min.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.es.js')}" type="text/javascript"></script>

<!-- END PAGE LEVEL PLUGINS -->
<!-- BEGIN THEME GLOBAL SCRIPTS -->
<script src="{asset('/global/scripts/app.min.js')}" type="text/javascript"></script>
<script src="{asset('/global/scripts/knockout.js')}" type="text/javascript"></script>
<script src="{asset('/global/scripts/knockout.validation/knockout.validation.js')}" type="text/javascript"></script>'
<script src="{asset('/global/scripts/knockout.validation/localization/es-ES.js')}" type="text/javascript"></script>'
<script src="{asset('/global/scripts/knockout.validation/localization/en-US.js')}" type="text/javascript"></script>'
<script src="{asset('/global/scripts/knockout.currency.js')}" type="text/javascript"></script>

<script src="{asset('/js/services.js')}" type="text/javascript"></script>
<script src="{asset('/js/app.js')}" type="text/javascript"></script>
<script src="{asset('/js/common.js')}" type="text/javascript"></script>

<!-- END THEME GLOBAL SCRIPTS -->
<!-- BEGIN THEME LAYOUT SCRIPTS -->
<script src="{asset('/layouts/layout/scripts/layout.min.js')}" type="text/javascript"></script>
<script src="{asset('/layouts/layout/scripts/demo.min.js')}" type="text/javascript"></script>
<script src="{asset('/layouts/global/scripts/quick-sidebar.min.js')}" type="text/javascript"></script>
<script src="{asset('/layouts/global/scripts/quick-nav.min.js')}" type="text/javascript"></script>
<!-- END THEME LAYOUT SCRIPTS -->
<!-- BEGIN PAGE LEVEL SCRIPTS -->
<script src="{asset('/pages/scripts/components-bootstrap-select.min.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/bootstrap-select/js/bootstrap-select.min.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/select2/js/select2.full.min.js')}" type="text/javascript"></script>
<script src="{asset('/global/plugins/select2/js/i18n/es.js')}"></script>

<!-- BEGIN DYNAMIC PRE-SCRIPTS -->
{block name='pre-scripts'}{/block}
{foreach $pre_scripts_child as $tpl_script}
    {$tpl_script}
{/foreach}
<!-- END DYNAMIC PRE-SCRIPTS -->

<!-- BEGIN DYNAMIC KNOCKOUT -->
{block name='knockout'}
<script>
var params = window.location.pathname.split('/').slice(1);
</script>
{/block}
<!-- END DYNAMIC KNOCKOUT -->

<!-- BEGIN KNOCKOUT COMPONENTS -->
{include file='concurso/detail/partials/chat.tpl'}
<!-- END KNOCKOUT COMPONENTS -->

<!-- BEGIN DYNAMIC POST-SCRIPTS -->
{block name='post-scripts'}{/block}
{foreach $post_scripts_child as $tpl_script}
    {$tpl_script}
{/foreach}
<!-- END DYNAMIC POST-SCRIPTS -->

<!-- END PAGE LEVEL SCRIPTS -->