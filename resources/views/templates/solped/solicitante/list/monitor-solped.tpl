{extends 'solped/solicitante/main.tpl'}

<!-- ESTILOS -->
{block 'styles'}
    <link href="{asset('/global/css/components-rounded.min.css')}" rel="stylesheet" id="style_components" type="text/css" />
    <style>
        #ListaSolpedsEnPreparacion_wrapper div.row,
        #ListaSolpedsEnAnalisis_wrapper div.row,
        #ListaSolpedsDevueltas_wrapper div.row,
        #ListaSolpedsAceptadas_wrapper div.row,
        #ListaSolpedsRechazadas_wrapper div.row,
        #ListaSolpedsCanceladas_wrapper div.row {
            display: none;
        }
        .styled-checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #26c281; /* cambia el color del check */
        }
        td.text-center.vertical-align-center {
            display: flex;
            justify-content: center;   /* centra horizontal */
            align-items: center;       /* alinea vertical */
            gap: 8px;                  /* espacio entre el botón y el checkbox */
        }
    </style>
{/block}

<!-- SCRIPTS PREVIOS A KNOCKOUT -->
{block 'pre-scripts'}
    <script src="{asset('/global/plugins/datatables/datatables.min.js')}" type="text/javascript">
    </script>
    <script src="{asset('/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js')}" type="text/javascript">
    </script>
    <!-- plugin de ordenamiento para fechas DD-MM-YYYY -->
    <script 
    src="https://cdn.datatables.net/plug-ins/1.13.6/sorting/date-eu.js" 
    type="text/javascript">
    </script>
    <!-- DataTables principal -->
    <script 
        src="{asset('/global/plugins/datatables/datatables.min.js')}" 
        type="text/javascript">
    </script>
    <script 
        src="{asset('/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js')}" 
        type="text/javascript">
    </script>
{/block}

<!-- SCRIPTS POSTERIORES A KNOCKOUT -->
{block 'post-scripts'}
{/block}

{block 'title'}
    {$title}
{/block}

<!-- VISTA -->
{block 'solped-list-solicitante'}
    <div class="row">
        <div class="col-md-12 margin-bottom-20">
            <label class="control-label text-center" style="display: block;">
                Filtros
            </label>
            <div style="display: flex; justify-content: center; gap: 15px; align-items: flex-end;">
                <div class="input-group" style="max-width: 14.5vw; width: 100%;">
                    <input type="text" class="form-control"
                        data-bind="value: Filters().searchTerm"
                        placeholder="Nombre, Solicitante o ID de solicitud">
                    <span class="input-group-addon" 
                        data-bind="visible: Filters().isIdSearch(), style: { color: 'green' }">
                        <i class="fa fa-id-card"></i> Búsqueda por ID
                    </span>
                </div>
                
                <div style="min-width: 180px;">
                    <select class="form-control" data-bind="value: Filters().urgencyFilter">
                        <option value="">Todas</option>
                        <option value="Urgencia">Urgencia</option>
                        <option value="Normal">Normal</option>
                        <option value="Regularizacion">Regularizacion</option>
                    </select>
                </div>
            </div>
        </div>

       <!-- ko if: User.Tipo === 4 -->
        <div class="col-md-12">
            <div class="portlet box purple">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-pencil-square-o"></i>
                        <span class="caption-subject bold">En Preparación</span>
                        <span class="caption-helper font-white"
                            data-bind="text: Lists().ListaSolpedsEnPreparacion().length">
                        </span>
                    </div>
                    <div class="tools">
                        <a href="javascript:;" data-original-title="" data-bind="css: { 
                            'collapse': Lists().ListaSolpedsEnPreparacion().length > 0,
                            'expand': Lists().ListaSolpedsEnPreparacion().length == 0 
                        }" title="">
                        </a>
                    </div>
                </div>
                <div class="portlet-body" data-bind="style: { 
                    'display': Lists().ListaSolpedsEnPreparacion().length > 0 ? 'block' : 'none'
                }">
                    <table class="table table-striped table-bordered ListaSolpeds" id="ListaSolpedsEnPreparacion">
                        <thead>
                            <tr>
                                <th> Nº Solicitud </th>
                                <th> Nombre del la Solicitud </th>
                                <th> Área Solicitante </th>
                                <th> Urgencia de Solicitud </th>
                                <th class="text-center"> Acciones </th>
                            </tr>
                        </thead>
                        <tbody
                            data-bind="dataTablesForEach : { data: Lists().ListaSolpedsEnPreparacion, options: { paging: false } }">
                            <tr>
                                <td data-bind="text: Id()" class="vertical-align-middle"></td>
                                <td data-bind="text: Nombre()" class="vertical-align-middle"></td>
                                <td data-bind="text: AreaSolicitante()" class="vertical-align-middle"></td>
                                <td data-bind="text: Urgencia()" class="vertical-align-middle"></td>

                                <td class="text-center vertical-align-middle">
                                    <a href="javascript:void(0);"
                                        data-bind="click: function() { $root.goToEdition(Id(), TipoConcursoPath()) }, css: { disabled: !$root.SolpedActive() }"
                                        class="btn btn-xs purple" title="Editar">
                                        Editar
                                        <i class="fa fa-edit"></i>
                                    </a>

                                    <a data-bind="click: function() { $root.sendSolicitud(Id()) }, css: { disabled: !$root.SolpedActive() }"
                                        class="btn btn-xs purple" title="Enviar Solicitud">
                                        Enviar Solicitud
                                        <i class="fa fa-send"></i>
                                    </a>
                                </td>

                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- /ko -->
        <div class="col-md-12">
            <div class="portlet box blue-steel">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-search"></i>
                        <span class="caption-subject bold">Análisis</span>
                        <span class="caption-helper font-white"
                            data-bind="text: Lists().ListaSolpedsEnAnalisis().length">
                        </span>
                    </div>
                    <div class="tools">
                        <a href="javascript:;" data-original-title="" data-bind="css: { 
                            'collapse': Lists().ListaSolpedsEnAnalisis().length > 0,
                            'expand': Lists().ListaSolpedsEnAnalisis().length == 0 
                        }" title="">
                        </a>
                    </div>
                </div>
                <div class="portlet-body" data-bind="style: { 
                    'display': Lists().ListaSolpedsEnAnalisis().length > 0 ? 'block' : 'none'
                }">
                    <table class="table table-striped table-bordered ListaSolpeds"
                        id="ListaSolpedsEnAnalisis">
                        <thead>
                            <tr>
                                <th> Nº Solicitud </th>
                                <th> Nombre del la Solicitud </th>
                                <th> Área Solicitante </th>
                                <th> Urgencia de Solicitud </th>
                                <th> Estado </th>
                                <th class="text-center"> Acciones </th>
                            </tr>
                        </thead>
                        <tbody
                            data-bind="dataTablesForEach : { data: Lists().ListaSolpedsEnAnalisis, options: { paging: false } }">
                            <tr>
                                <td data-bind="text: Id()" class="vertical-align-middle"></td>
                                <td data-bind="text: Nombre()" class="vertical-align-middle"></td>
                                <td data-bind="text: AreaSolicitante()" class="vertical-align-middle"></td>
                                <td data-bind="text: Urgencia()" class="vertical-align-middle"></td>
                                <td data-bind="text: $root.StateTranslator(Estado())" class="vertical-align-middle"></td>
                                <td class="text-center vertical-align-center">

                                    <a href="javascript:void(0);"
                                        data-bind="click: function() { $root.goToAcceso(Id(), 'en-analisis', User.Tipo) }"
                                        class="btn btn-xs blue-steel" title="Acceder">
                                        Acceder
                                        <i class="fa fa-play"></i>
                                    </a>

                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-12">
            <div class="portlet box yellow-gold">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-undo"></i>
                        <span class="caption-subject bold">Corrección</span>
                        <span class="caption-helper font-white"
                            data-bind="text: Lists().ListaSolpedsDevueltas().length">
                        </span>
                    </div>
                    <div class="tools">
                        <a href="javascript:;" data-original-title="" data-bind="css: { 
                            'collapse': Lists().ListaSolpedsDevueltas().length > 0,
                            'expand': Lists().ListaSolpedsDevueltas().length == 0 
                        }" title="">
                        </a>
                    </div>
                </div>
                <div class="portlet-body" data-bind="style: { 
                    'display': Lists().ListaSolpedsDevueltas().length > 0 ? 'block' : 'none'
                }">
                    <table class="table table-striped table-bordered ListaSolpeds" id="ListaSolpedsDevueltas">
                        <thead>
                            <tr>
                                <th> Nº Solicitud </th>
                                <th> Nombre de la Solicitud </th>
                                <th> Área Solicitante </th>
                                <th> Urgencia de Solicitud</th>
                                <th> Estado </th>
                                <th class="text-center"> Acciones </th>
                            </tr>
                        </thead>
                        <tbody
                            data-bind="dataTablesForEach : { data: Lists().ListaSolpedsDevueltas, options: { paging: false }}">
                            <tr>
                                <td data-bind="text: Id()" class="vertical-align-middle"></td>
                                <td data-bind="text: Nombre()" class="vertical-align-middle"></td>
                                <td data-bind="text: AreaSolicitante()" class="vertical-align-middle"></td>
                               
                                <td data-bind="text: Urgencia()" class="vertical-align-middle"></td>
                                <td data-bind="text: $root.StateTranslator(Estado())" class="vertical-align-middle"></td>

                                <td class="text-center vertical-align-center">

                                    <!-- ko if: User.Tipo === 4 -->
                                    <a href="javascript:void(0);"
                                        data-bind="click: function() { $root.goToEdition(Id(), TipoConcursoPath()) }, css: { disabled: !$root.SolpedActive() }"
                                        class="btn btn-xs yellow-gold" title="Editar">
                                        Editar
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <!-- /ko -->
                                    <a href="javascript:;" data-bind="click: function() { $root.goToAcceso(Id(), 'en-correccion', User.Tipo) }"
                                        class="btn btn-xs yellow-gold" title="Acceder">
                                        Acceder <i class="fa fa-play"></i>
                                    </a>
                                    
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="portlet box green-jungle">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-check-circle"></i>
                        <span class="caption-subject bold">Aceptadas</span>
                        <span class="caption-helper font-white"
                            data-bind="text: Lists().ListaSolpedsAceptadas().length">
                        </span>
                    </div>
                    <div class="tools">
                        <a href="javascript:;" data-original-title="" data-bind="css: { 
                            'collapse': Lists().ListaSolpedsAceptadas().length > 0,
                            'expand': Lists().ListaSolpedsAceptadas().length == 0 
                        }" title="">
                        </a>
                    </div>
                </div>
                <div class="portlet-body" data-bind="style: { 
                    'display': Lists().ListaSolpedsAceptadas().length > 0 ? 'block' : 'none'
                }">
                    <table class="table table-striped table-bordered ListaSolpeds" id="ListaSolpedsAceptadas">
                        <thead>
                            <tr>
                                <th> Nº Solicitud </th>
                                <th> Nombre de la Solicitud </th>
                                <th> Área Solicitante </th>
                                <th> Urgencia de Solicitud </th>
                                <th> Estado </th>
                                <th class="text-center"> Acciones </th>
                            </tr>
                        </thead>
                        <tbody
                            data-bind="dataTablesForEach : { data: Lists().ListaSolpedsAceptadas, options: { paging: false }}">
                            <tr>
                             
                                <td data-bind="text: Id()" class="vertical-align-middle"></td>
                                <td data-bind="text: Nombre()" class="vertical-align-middle"></td>
                                <td data-bind="text: AreaSolicitante()" class="vertical-align-middle"></td>
                                <td data-bind="text: Urgencia()" class="vertical-align-middle"></td>
                                <td data-bind="text: $root.StateTranslator(Estado())" class="vertical-align-middle"></td>
                                <td class="text-center vertical-align-center">
                                    
                                    <a href="javascript:;" data-bind="click: function() { $root.goToAcceso(Id(), 'aceptada', User.Tipo) }"
                                        class="btn btn-xs green-jungle" title="Acceder">
                                        Acceder <i class="fa fa-play"></i>
                                    </a>
                                    <!-- ko if: User.Tipo === 3  && Estado() !== 'licitando'-->

                                    <input class="styled-checkbox" 
                                            type="checkbox" 
                                            data-bind="checked: $root.SelectedSolpeds, value: Id()" />
                                                            
                                    <!-- /ko -->

                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>                          

        <div class="col-md-12">
            <div class="portlet box red-thunderbird">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-times-circle"></i>
                        <span class="caption-subject bold">Rechazadas</span>
                        <span class="caption-helper font-white"
                            data-bind="text: Lists().ListaSolpedsRechazadas().length">
                        </span>
                    </div>
                    <div class="tools">
                        <a href="javascript:;" data-original-title="" data-bind="css: { 
                            'collapse': Lists().ListaSolpedsRechazadas().length > 0,
                            'expand': Lists().ListaSolpedsRechazadas().length == 0 
                        }" title="">
                        </a>
                    </div>
                </div>
                <div class="portlet-body" data-bind="style: { 
                    'display': Lists().ListaSolpedsRechazadas().length > 0 ? 'block' : 'none'
                }">
                    <table class="table table-striped table-bordered ListaSolpeds"
                        id="ListaSolpedsRechazadas">
                        <thead>
                            <tr>
                                <th> Nº Solicitud </th>
                                <th> Nombre de la Solicitud </th>
                                <th> Área Solicitante </th>
                                <th> Urgencia de Solicitud </th>
                                <th> Estado </th>
                                <th class="text-center"> Acciones </th>
                            </tr>
                        </thead>
                        <tbody
                            data-bind="dataTablesForEach : { data: Lists().ListaSolpedsRechazadas, options: { paging: false }}">
                            <tr>
                                <td data-bind="text: Id()" class="vertical-align-middle"></td>
                                <td data-bind="text: Nombre()" class="vertical-align-middle"></td>
                                <td data-bind="text: AreaSolicitante()" class="vertical-align-middle"></td>
                                <td data-bind="text: Urgencia()" class="vertical-align-middle"></td>
                                <td data-bind="text: $root.StateTranslator(Estado())" class="vertical-align-middle"></td>
                                <td class="text-center vertical-align-center">

                                    <a href="javascript:;" data-bind="click: function() { $root.goToAcceso(Id(), 'rechazada', User.Tipo) }"
                                        class="btn btn-xs red-thunderbird" title="Acceder">
                                        Acceder <i class="fa fa-play"></i>
                                    </a>

                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-12">
            <div class="portlet box green-haze">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-check-circle"></i>
                        <span class="caption-subject bold">Solicitudes Adjudicadas</span>
                        <span class="caption-helper font-white" data-bind="text: Lists().ListaSolpedsAdjudicadas().length">
                        </span>
                    </div>
                    <div class="tools">
                        <a href="javascript:;" data-original-title="" data-bind="css: { 
                            'collapse': Lists().ListaSolpedsAdjudicadas().length > 0,
                            'expand': Lists().ListaSolpedsAdjudicadas().length == 0 
                        }" title="">
                        </a>
                    </div>
                </div>
                <div class="portlet-body" data-bind="style: { 
                    'display': Lists().ListaSolpedsAdjudicadas().length > 0 ? 'block' : 'none'
                }">
                    <table class="table table-striped table-bordered ListaSolpeds" id="ListaSolpedsAdjudicadas">
                        <thead>
                            <tr>
                                <th> Nº Solicitud </th>
                                <th> Nombre de la Solicitud </th>
                                <th> Área Solicitante </th>
                                <th> Urgencia de Solicitud </th>
                                <th> Empresa Adjudicada </th>
                                <th class="text-center"> Acciones </th>
                            </tr>
                        </thead>
                        <tbody
                            data-bind="dataTablesForEach : { data: Lists().ListaSolpedsAdjudicadas, options: { paging: false, searching: false }}">
                            <tr>
                                <td data-bind="text: Id()" class="vertical-align-middle"></td>
                                <td data-bind="text: Nombre()" class="vertical-align-middle"></td>
                                <td data-bind="text: AreaSolicitante()" class="vertical-align-middle"></td>
                                <td data-bind="text: Urgencia()" class="vertical-align-middle"></td>
                                <td class="vertical-align-middle">
                                    <div data-bind="if: AdjudicacionDetalles">
                                        <strong data-bind="text: AdjudicacionDetalles.empresaAdjudicada()"></strong>
                                        <!-- ko if: AdjudicacionDetalles.nombreConcurso -->
                                        <br>
                                        <small data-bind="text: 'Licitación: ' + AdjudicacionDetalles.nombreConcurso()"></small>
                                        <!-- /ko -->
                                    </div>
                                </td>
                                <td class="text-center vertical-align-middle">
                                    <a href="javascript:;" data-bind="click: function() { $root.goToAcceso(Id(), 'aceptada', User.Tipo) }"
                                        class="btn btn-xs green-haze" title="Ver Detalles">
                                        Ver Detalles <i class="fa fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-12">
            <div class="portlet box red-haze">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-ban"></i>
                        <span class="caption-subject bold">Solicitudes Finalizadas/Canceladas</span>
                        <span class="caption-helper font-white" data-bind="text: Lists().ListaSolpedsCanceladas().length">
                        </span>
                    </div>
                    <div class="tools">
                        <a href="javascript:;" data-original-title="" data-bind="css: { 
                            'collapse': Lists().ListaSolpedsCanceladas().length > 0,
                            'expand': Lists().ListaSolpedsCanceladas().length == 0 
                        }" title="">
                        </a>
                    </div>
                </div>
                <div class="portlet-body" data-bind="style: { 
                    'display': Lists().ListaSolpedsCanceladas().length > 0 ? 'block' : 'none'
                }">
                    <table class="table table-striped table-bordered ListaSolpeds" id="ListaSolpedsCanceladas">
                        <thead>
                            <tr>
                                <th> Nº Solicitud </th>
                                <th> Nombre de la Solicitud </th>
                                <th> Área Solicitante </th>
                                <th> Urgencia de Solicitud </th>
                                <th> Estado </th>
                                <th class="text-center"> Acciones </th>
                            </tr>
                        </thead>
                        <tbody
                            data-bind="dataTablesForEach : { data: Lists().ListaSolpedsCanceladas, options: { paging: false }}">
                            <tr>
                                <td data-bind="text: Id()" class="vertical-align-middle"></td>
                                <td data-bind="text: Nombre()" class="vertical-align-middle"></td>
                                <td data-bind="text: AreaSolicitante()" class="vertical-align-middle"></td>
                                <td data-bind="text: Urgencia()" class="vertical-align-middle"></td>
                                <td data-bind="text: $root.StateTranslator(Estado())" class="vertical-align-middle"></td>
                                <td class="text-center vertical-align-center">
                                
                                <a href="javascript:;" data-bind="click: function() { $root.goToAcceso(Id(), 'rechazada', User.Tipo) }"
                                        class="btn btn-xs red-haze" title="Acceder">
                                        Acceder <i class="fa fa-play"></i>
                                    </a>
                                </td>

                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- ko if: User.Tipo === 3 -->
        <div class="text-right margin-top-10" data-bind="visible: hasSelected">
            <button class="btn btn-primary"
                    data-bind="click: convertirEnLicitacion, enable: SolpedActive">
                Convertir Solicitudes en Licitación
            </button>
            <button class="btn btn-success"
                    data-bind="click: convertirEnSubasta, enable: SolpedActive">
                Convertir Solicitudes en Subasta
            </button>
        </div>

        <!-- /ko -->

    </div>
{/block}

<!-- KNOCKOUT JS -->
{block 'knockout' append}
    <script type="text/javascript">
        var solpedActiveFlag = {if isSolpedActive()}true{else}false{/if};
        console.log("User", User.Tipo);
        var ListItem = function(data) {
            console.log("datos", data);

            var self = this;

            this.Id = ko.observable(data.Id);
            // this.CantidadOferentes = ko.observable(data.CantidadOferentes);
            // this.CantidadPresentaciones = ko.observable(data.CantidadPresentaciones);
            this.HabilitaEnvioAComprador = ko.observable(data.HabilitaEnvioAComprador);
            this.Estado = ko.observable(data.Estado);
            this.Urgencia = ko.observable(data.Urgencia);

            
            this.Nombre = ko.observable(data.Nombre);
            this.Solicitante = ko.observable(data.Solicitante);
            this.CodigoInterno = ko.observable(data.CodigoInterno);
            this.TipoConcursoPath = ko.observable(data.TipoConcursoPath);
            //this.UsuarioSolicitante = ko.observable(data.UsuarioSolicitante);
            this.AreaSolicitante = ko.observable(data.AreaSolicitante);
            this.AdjudicacionDetalles = data.AdjudicacionDetalles ? {
                adjudicada: ko.observable(data.AdjudicacionDetalles.adjudicada),
                empresaAdjudicada: ko.observable(data.AdjudicacionDetalles.empresaAdjudicada),
                idConcurso: ko.observable(data.AdjudicacionDetalles.idConcurso),
                nombreConcurso: ko.observable(data.AdjudicacionDetalles.nombreConcurso)
            } : null;
            
        }

        var List = function(data) {
            var self = this;

            this.ListaSolpedsEnPreparacion = ko.observableArray([]);
            if (data.ListaSolpedsEnPreparacion.length > 0) {
                data.ListaSolpedsEnPreparacion.forEach(item => {
                    self.ListaSolpedsEnPreparacion.push(new ListItem(item));
                });
            }
            
            this.ListaSolpedsEnAnalisis = ko.observableArray([]);
            if (data.ListaSolpedsEnAnalisis.length > 0) {
                data.ListaSolpedsEnAnalisis.forEach(item => {
                    self.ListaSolpedsEnAnalisis.push(new ListItem(item));
                });
            }
           
            this.ListaSolpedsDevueltas = ko.observableArray([]);
            if (data.ListaSolpedsDevueltas.length > 0) {
                data.ListaSolpedsDevueltas.forEach(item => {
                    self.ListaSolpedsDevueltas.push(new ListItem(item));
                });
            }
            
            this.ListaSolpedsAceptadas = ko.observableArray([]);
            if (data.ListaSolpedsAceptadas.length > 0) {
                data.ListaSolpedsAceptadas.forEach(item => {
                    self.ListaSolpedsAceptadas.push(new ListItem(item));
                });
            }
           
            this.ListaSolpedsRechazadas = ko.observableArray([]);
            if (data.ListaSolpedsRechazadas.length > 0) {
                data.ListaSolpedsRechazadas.forEach(item => {
                    self.ListaSolpedsRechazadas.push(new ListItem(item));
                });
            }

            this.ListaSolpedsAdjudicadas = ko.observableArray([]);
            if (data.ListaSolpedsAdjudicadas.length > 0) {
                data.ListaSolpedsAdjudicadas.forEach(item => {
                    self.ListaSolpedsAdjudicadas.push(new ListItem(item));
                });
            }

            this.ListaSolpedsCanceladas = ko.observableArray([]);
            if (data.ListaSolpedsCanceladas.length > 0) {
                data.ListaSolpedsCanceladas.forEach(item => {
                    self.ListaSolpedsCanceladas.push(new ListItem(item));
                });
            }

        }

        var Filters = function(parent) {
            var self = this;
            
            //Search observable
            this.searchTerm = ko.observable(null);
            
            //Urgency filter observable
            this.urgencyFilter = ko.observable('');

            //Subscribe to search term changes
            self.searchTerm.subscribe((value) => {
                parent.applyFilters();
            });
            
            //Subscribe to urgency filter changes
            self.urgencyFilter.subscribe((value) => {
                parent.applyFilters();
            });

            //Detect if searching by ID (all digits)
            this.isIdSearch = ko.computed(function() {
                var term = self.searchTerm();
                return term && /^\d+$/.test(term);
            });
        };



        

        var SolpedsListadosSolicitante = function(data) {
            var self = this;

            this.Breadcrumbs = ko.observableArray(data.breadcrumbs);
            this.OriginalData = data.list; // Guardar datos originales sin filtrar
            this.Lists = ko.observable(new List(data.list));
            this.UserType = ko.observable(data.userType);
            this.SolpedActive = ko.observable(!!solpedActiveFlag);

            this.guardSolpedActive = function() {
                if (self.SolpedActive()) {
                    return true;
                }
                swal('Atención', 'El módulo de Solped está desactivado para tu empresa.', 'warning');
                return false;
            };
            
            // Función para aplicar filtros en el frontend
            this.applyFilters = function() {
                var searchTerm = self.Filters().searchTerm();
                var urgencyFilter = self.Filters().urgencyFilter();
                
                // Copiar datos originales
                var filtered = JSON.parse(JSON.stringify(self.OriginalData));
                
                // Aplicar filtro de búsqueda
                if (searchTerm && searchTerm.trim() !== '') {
                    const term = searchTerm.trim().toLowerCase();
                    const isIdSearch = /^\d+$/.test(searchTerm);
                    
                    for (let key in filtered) {
                        filtered[key] = filtered[key].filter(item => {
                            if (isIdSearch) {
                                return item.Id == searchTerm;
                            } else {
                                return (item.Nombre && item.Nombre.toLowerCase().indexOf(term) !== -1) ||
                                       (item.Solicitante && item.Solicitante.toLowerCase().indexOf(term) !== -1);
                            }
                        });
                    }
                }
                
                // Aplicar filtro de urgencia
                if (urgencyFilter && urgencyFilter.trim() !== '') {
                    for (let key in filtered) {
                        filtered[key] = filtered[key].filter(item => {
                            return item.Urgencia && item.Urgencia.toLowerCase() === urgencyFilter.toLowerCase();
                        });
                    }
                }
                
                self.Lists(new List(filtered));
            };
            
            this.Filters = ko.observable(new Filters(self));

            this.goToEdition = function(idSolped) {
                if (!self.guardSolpedActive()) {
                    return;
                }
                try {
                    window.location.href = '/solped/edicion/' + idSolped;
                } catch (error) {
                    $.unblockUI();
                    swal('Error', error.message, 'error');
                }
            };

            this.goToAcceso = function(idSolped, etapa, tipoCliente) {

                try {
                    if (tipoCliente === 4) {
                        window.location.href = '/solped/solicitante/' + etapa + '/' + idSolped;
                    }
                    else {
                        window.location.href = '/solped/cliente/' + etapa + '/' + idSolped;
                    }
                    
                } catch (error) {
                    $.unblockUI();
                    swal('Error', error.message, 'error');
                }
            };

            this.StateTranslator = function(state) {
                // Extraer el valor si es un observable
                var stateValue = ko.isObservable(state) ? state() : state;
                
                const states = {
                    'borrador': 'En Preparación',
                    'esperando-revision': 'Esperando Revisión',
                    'esperando-revision-2': 'Esperando Revisión (2da Ronda)',
                    'revisada': 'Revisada',
                    'revisada-2': 'Revisada (2da Ronda)',
                    'aceptada': 'Aceptada',
                    'aprobada': 'Aprobada',
                    'rechazada': 'Rechazada',
                    'devuelta': 'Devuelta para Corrección',
                    'cancelada': 'Cancelada',
                    'licitando': 'En Proceso de Licitación'

                };
                return states[stateValue] || 'Estado Desconocido';
            };

             //  Array con las solicitudes seleccionadas
            this.SelectedSolpeds = ko.observableArray([]);

            // Computed: true si hay seleccionadas
            this.hasSelected = ko.computed(function() {
                return self.SelectedSolpeds().length > 0;
            });
            
            // Acciones
           // Al hacer click en "Convertir en Licitación"
            this.convertirEnLicitacion = function() {
    if (!self.guardSolpedActive()) {
        return;
    }
    const seleccionadas = self.SelectedSolpeds();

    if (seleccionadas.length === 0) {
        swal("Atención", "Debe seleccionar al menos una solicitud", "warning");
        return;
    }

    // Mostrar modal de carga
    swal({
        title: "Creando Licitación",
        text: "Por favor espere mientras se crea la licitación y se envían las notificaciones...",
        icon: "info",
        buttons: false,
        showConfirmButton: false,
        closeOnClickOutside: false,
        closeOnEsc: false,
        allowOutsideClick: false,
        didOpen: function() {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: '/concursos/cliente/from-solpeds',
        type: 'POST',
        data: {
            solpeds: seleccionadas,
            UserToken: User.Token
        },
        success: function(response) {
            console.log("✅ Respuesta del backend:", response);
            if (response.success) {
                swal({
                    title: "¡Éxito!",
                    text: "Licitación creada correctamente. Redirigiendo...",
                    icon: "success",
                    buttons: false,
                    showConfirmButton: false,
                    closeOnClickOutside: false,
                    closeOnEsc: false,
                    allowOutsideClick: false,
                    timer: 2000
                });
                setTimeout(function() {
                    window.location.href = response.redirectUrl;
                }, 2000);
            } else {
                swal("Error", response.message, "error");
            }
        },
        error: function(err) {
            console.error("❌ Error en el request:", err);
            swal("Error", "No se pudo crear la licitación desde las solicitudes", "error");
        }
    });
};






            this.convertirEnSubasta = function() {
                if (!self.guardSolpedActive()) {
                    return;
                }
                const seleccionadas = self.SelectedSolpeds();
                console.log("👉 Solpeds seleccionadas para subasta:", seleccionadas);

                if (seleccionadas.length === 0) {
                    swal("Atención", "Debe seleccionar al menos una solicitud", "warning");
                    return;
                }

                // Mostrar modal de carga
                swal({
                    title: "Creando Subasta",
                    text: "Por favor espere mientras se crea la subasta y se envían las notificaciones...",
                    icon: "info",
                    buttons: false,
                    showConfirmButton: false,
                    didOpen: function() {
                        // Mostrar spinner
                        Swal.showLoading();
                    },
                    closeOnClickOutside: false,
                    closeOnEsc: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false
                });

                $.ajax({
                    url: '/concursos/cliente/auction-from-solpeds',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        solpeds: seleccionadas,
                        UserToken: User.Token
                    }),
                    success: function(response) {
                        console.log("✅ Respuesta del backend:", response);
                        if (response.success) {
                            swal({
                                title: "¡Éxito!",
                                text: "Subasta creada correctamente. Redirigiendo...",
                                icon: "success",
                                buttons: false,
                                showConfirmButton: false,
                                closeOnClickOutside: false,
                                closeOnEsc: false,
                                allowOutsideClick: false,
                                timer: 2000,
                                timerProgressBar: false
                            });
                            setTimeout(function() {
                                window.location.href = response.redirectUrl;
                            }, 2000);
                        } else {
                            swal("Error", response.message, "error");
                        }
                    },
                    error: function(err) {
                        console.error("❌ Error en el request:", err);
                        swal("Error", "No se pudo crear la subasta desde las solicitudes", "error");
                    }
                });
            };



            this.sendSolicitud = function(IdSolped) {
                if (!self.guardSolpedActive()) {
                    return;
                }
                // Extraer el valor si es un observable de Knockout
                var solpedId = ko.isObservable(IdSolped) ? IdSolped() : IdSolped;
                
                console.log('Enviando solicitud con ID:', solpedId);
                
                if (!solpedId || isNaN(solpedId)) {
                    swal('Error', 'ID de solicitud no válido', 'error');
                    return;
                }
                
                swal({
                    title: '¿Desea enviar la solicitud?',
                    text: 'Esta a punto de enviar las notificaciones para esta solicitud.',
                    type: 'info',
                    closeOnClickOutside: false,
                    showCancelButton: true,
                    closeOnConfirm: true,
                    confirmButtonText: 'Aceptar',
                    confirmButtonClass: 'btn btn-success',
                    cancelButtonText: 'Cancelar',
                    cancelButtonClass: 'btn btn-default'
                }, function(result) {
                    swal.close();
                    if (result) {
                        $.blockUI();
                        var url = '/solped/solicitante/send';
                        Services.Post(url, {
                                UserToken: User.Token,
                                IdSolped: solpedId
                            },
                            (response) => {
                                swal.close();
                                $.unblockUI();
                                setTimeout(function() {
                                    if (response.success) {
                                        swal({
                                            title: 'Hecho',
                                            text: response.message,
                                            type: 'success',
                                            closeOnClickOutside: false,
                                            closeOnConfirm: true,
                                            confirmButtonText: 'Aceptar',
                                            confirmButtonClass: 'btn btn-success'
                                        }, function(result) {
                                            location.reload();
                                        });
                                    } else {
                                        swal('Error', response.message, 'error');
                                    }
                                }, 500);
                            },
                            (error) => {
                                swal.close();
                                $.unblockUI();
                                setTimeout(function() {
                                    console.error('Error al enviar solicitud:', error);
                                    var errorMessage = error.message || 'Error desconocido al enviar la solicitud';
                                    if (error.status === 500) {
                                        errorMessage = 'Error interno del servidor. Por favor, verifique los datos e intente nuevamente.';
                                    }
                                    swal('Error', errorMessage, 'error');
                                }, 500);
                            },
                            null,
                            null
                        );
                    }
                });
            }
        };

        

        jQuery(document).ready(function() {
            $.blockUI();
            var data = {};
            Services.Get('/solped/solicitante/monitor/list', {
                    UserToken: User.Token,
                    Entity: JSON.stringify(ko.toJS(data))
                },
                (response) => {
                    if (response.success) {
                        window.E = new SolpedsListadosSolicitante(response.data);
                        AppOptus.Bind(E);
                    }
                    $.unblockUI();
                },
                (error) => {
                    $.unblockUI();
                    swal('Error', error.message, 'error');
                },
                null,
                null
            );
        });

        // Chrome allows you to debug it thanks to this
        {chromeDebugString('dynamicScript')}
    </script>
{/block}


