{extends 'tipocambio/main.tpl'}

<!-- ESTILOS -->
{block 'styles'}
    <link href="{asset('/global/plugins/datatables/datatables.min.css')}" rel="stylesheet" type="text/css" />
    <link href="{asset('/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css')}" rel="stylesheet" type="text/css" />
    <link href="{asset('/global/css/components-rounded.min.css')}" rel="stylesheet" id="style_components" type="text/css" />
    <link href="{asset('/global/plugins/jquery-ui/jquery-ui.min.css')}" rel="stylesheet" type="text/css" />
{/block}

<!-- SCRIPTS PREVIOS A KNOCKOUT -->
{block 'pre-scripts'}
    <script src="{asset('/global/plugins/datatables/datatables.min.js')}" type="text/javascript"></script>
    <script src="{asset('/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js')}" type="text/javascript"></script>
{/block}

<!-- SCRIPTS POSTERIORES A KNOCKOUT -->
{block 'post-scripts'}
{/block}

<!-- VISTA -->
{block 'tipocambio-list'}
    <div class="row">
        <div class="col-md-12">
            <div class="portlet light bordered">
                <div class="portlet-body">
                    <div class="table-toolbar">
                        <div class="row">
                            <div class="col-md-6">
                            </div>
                            <div class="col-md-6">
                            </div>
                        </div>
                    </div>
                    <table class="table table-striped table-bordered" id="listaUnidades">
                        <thead>
                            <tr>
                                <th colspan="2" class="text-left header-col" style="background-color: #659BE0; color: #ffffff;">Conversión a 1 dólar</th>
                            </tr>
                            <tr>
                                <th class="text-left header-col" style="background-color: #32C5D2; color: #ffffff;">Moneda</th>
                                <th class="text-left header-col" style="background-color: #32C5D2; color: #ffffff;">Cambio</th>
                            </tr>   
                        </thead>
                        <tbody data-bind="foreach: ListaUnidades">
                            <tr>
                                <td class="text-left" data-bind="text: Moneda"></td>
                                <td class="text-left" data-bind="text: Cambio"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
{/block}

<!-- KNOCKOUT JS -->
{block 'knockout' append}
<script type="text/javascript">
var Unidad = function (data) {
    var self = this;

    this.Idtipocambio = ko.observable(data.Idtipocambio);
    //this.Dolar = ko.observable(data.Dolar);
    this.Moneda = ko.observable(data.Moneda); 
    this.Cambio = ko.observable(data.Cambio);
    
}

var ConfiguracionesTipoCambioListado = function (data) {
    var self = this;

    this.Breadcrumbs = ko.observableArray(data.breadcrumbs);
    this.ListaUnidades = ko.observableArray(ko.utils.arrayMap(data.list, function(item) {
        return new Unidad(item);
    }));
    
};

jQuery(document).ready(function () {
    $.blockUI();
    var data = {
        UserToken: User.Token
    };
    var url = '/configuraciones/tipocambio/list';
    Services.Get(url, data, 
        (response) => {
            if (response.success) {
                window.E = new ConfiguracionesTipoCambioListado(response.data);
                AppOptus.Bind(E);
            }
            $.unblockUI();
        }, 
        (error) => {
            $.unblockUI();
            swal('Error', error.message, 'error');
        }
    );
});

// Chrome allows you to debug it thanks to this
{chromeDebugString('dynamicScript')}
</script>
{/block}
