{extends 'estrategialiberacion/main.tpl'}

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
{block 'estrategialiberacion-list'}
    <div class="row">
        <div class="col-md-12">
            <div class="portlet light bordered">
                <div class="portlet-body">
                    <!-- Botón Editar Política -->
                    <div class="table-toolbar">
                        <div class="row">
                            <div class="col-md-6">
                                <button type="button" class="btn btn-primary" onclick="window.location.href='/configuraciones/estrategia-liberacion/edit.tpl'">Crear Política</button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tabla -->
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th class="text-left header-col" colspan="6" style="background-color: #e9e8e8; color: #444343;">Política de Estrategia ($)</th>
                            </tr>
                            <tr>
                                <th class="text-center header-col" style="background-color: #93d3d9; color: #ffffff;">Nìvel 0</th>
                                <th class="text-center header-col" style="background-color: #7fc9d0; color: #ffffff;">Nivel 1</th>
                                <th class="text-center header-col" style="background-color: #63c7d0; color: #ffffff;">Nivel 2</th>
                                <th class="text-center header-col" style="background-color: #40c9d6; color: #ffffff;">Nivel 3</th>
                                <th class="text-center header-col" style="background-color: #02c5d7; color: #ffffff;">Nivel 4</th>
                                <th class="text-center header-col" style="background-color: #ffffff; color: #444343;">Accion</th>
                            </tr>   
                            </tr>   
                        </thead>
                        <tbody data-bind="foreach: ListaNiveles">
                            <tr>
                                <td class="text-center" data-bind="text: Nivel0"></td>
                                <td class="text-center" data-bind="text: Nivel1"></td>
                                <td class="text-center" data-bind="text: Nivel2"></td>
                                <td class="text-center" data-bind="text: Nivel3"></td>
                                <td class="text-center" data-bind="text: Nivel4"></td>
                                <td style="text-align: center;">
                                    <a data-bind="attr:"
                                        class="btn btn-xs red" title="Editar">
                                        Editar
                                        <i class="fa fa-pencil"></i>
                                    </a>

                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
{/block}

<!-- KNOCKOUT JS -->
{block 'knockout' append}
<script type="text/javascript">

var Politica = function (data) {
    var self = this;

    this.Idestrategia = ko.observable(data.Idestrategia);
    //this.Dolar = ko.observable(data.Dolar);
    this.Habilitado = ko.observable(data.Habilitado); 
    this.Nivel0 = ko.observable(data.Nivel0);
    this.Nivel1 = ko.observable(data.Nivel1);
    this.Nivel2 = ko.observable(data.Nivel2);
    this.Nivel3 = ko.observable(data.Nivel3);
    this.Nivel4 = ko.observable(data.Nivel4);
    
}

var ConfiguracionesEstrategias = function (data) {
    var self = this;

    this.Breadcrumbs = ko.observableArray(data.breadcrumbs);
    this.ListaNiveles = ko.observableArray(ko.utils.arrayMap(data.list, function(item) {
        return new Politica(item);
    }));
};

jQuery(document).ready(function () {
    $.blockUI();
    var data = {
        UserToken: User.Token
    };
    var url = '/configuraciones/estrategia-liberacion/list';
    Services.Get(url, data, 
        (response) => {
            if (response.success) {
                window.E = new ConfiguracionesEstrategias(response.data);
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
