{extends 'concurso/main.tpl'}

<!-- ESTILOS -->
{block 'styles'}
    <link href="{asset('/global/css/components-rounded.min.css')}" rel="stylesheet" id="style_components" type="text/css" />
    <style>
        #ListaConcursosEnPreparacion_wrapper div.row,
        #ListaConcursosConvocatoriaOferentes_wrapper div.row,
        #ListaConcursosPropuestasTecnicas_wrapper div.row,
        #ListaConcursosAnalisisOfertas_wrapper div.row,
        #ListaConcursosEvaluacionReputacion_wrapper div.row,
        #ListaConcursosInformes_wrapper div.row,
        #ListaConcursosInvitaciones_wrapper div.row,
        #ListaConcursosTecnicas_wrapper div.row,
        #ListaConcursosEconomicas_wrapper div.row,
        #ListaConcursosAnalisis_wrapper div.row,
        #ListaConcursosAdjudicados_wrapper div.row,
        #ListaConcursosCancelados_wrapper div.row {
            display: none;
        }
        
        /* Estilo para botón deshabilitado */
        .btn-disabled {
            background-color: #95a5a6 !important;
            border-color: #7f8c8d !important;
            color: #ecf0f1 !important;
            cursor: not-allowed !important;
            opacity: 0.6;
        }
        
        .btn-disabled:hover {
            background-color: #95a5a6 !important;
            border-color: #7f8c8d !important;
            color: #ecf0f1 !important;
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
{block 'concurso-list-customer'}
    <div class="row">
        <div class="col-md-12 margin-bottom-20">
            <label class="control-label text-center" style="display: block;">
                Buscar
            </label>
            <div style="display: flex; justify-content: center;">
                <div class="input-group" style="max-width: 14.5vw; width: 100%;">
                    <input type="text" class="form-control"
                        data-bind="value: Filters().searchTerm"
                        placeholder="Nombre, Solicitante o ID de concurso">
                    <span class="input-group-addon" 
                        data-bind="visible: Filters().isIdSearch(), style: { color: 'green' }">
                        <i class="fa fa-id-card"></i> Búsqueda por ID
                    </span>
                </div>
            </div>
        </div>

        <!-- ko if: $root.UserType() == 'customer' || $root.UserType() == 'supervisor'-->
        <div class="col-md-12">
            <div class="portlet box purple">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-hourglass-1"></i>
                        <span class="caption-subject bold">En preparación</span>
                        <span class="caption-helper font-white"
                            data-bind="text: Lists().ListaConcursosEnPreparacion().length">
                        </span>
                    </div>
                    <div class="tools">
                        <a href="javascript:;" data-original-title="" data-bind="css: { 
                            'collapse': Lists().ListaConcursosEnPreparacion().length > 0,
                            'expand': Lists().ListaConcursosEnPreparacion().length == 0 
                        }" title="">
                        </a>
                    </div>
                </div>
                <div class="portlet-body" data-bind="style: { 
                    'display': Lists().ListaConcursosEnPreparacion().length > 0 ? 'block' : 'none'
                }">
                    <table class="table table-striped table-bordered ListaConcursos" id="ListaConcursosEnPreparacion">
                        <thead>
                            <tr>
                                <th> Nº Concurso </th>
                                <th> Nombre del concurso </th>
                                <th> Área Solicitante </th>
                                <th> Comprador </th>
                                <th> N° de Solicitud </th>
                                <th> Fecha Límite </th>
                                <th> Tipo de concurso </th>
                                <th class="text-center"> Acciones </th>
                            </tr>
                        </thead>
                        <tbody
                            data-bind="dataTablesForEach : { data: Lists().ListaConcursosEnPreparacion, options: { paging: false } }">
                            <tr>
                                <td data-bind="text: Id()" class="vertical-align-middle"></td>
                                <td data-bind="text: Nombre()" class="vertical-align-middle"></td>
                                <td data-bind="text: AreaSolicitante()" class="vertical-align-middle"></td>
                                <td data-bind="text: UsuarioSolicitante()" class="vertical-align-middle"></td>
                                <td data-bind="text: NumSolicitud()" class="vertical-align-middle"></td>
                                <td class="vertical-align-middle"
                                    data-bind="
                                    text: FechaLimite(),
                                    attr: { 'data-order': FechaLimiteOrden }
                                    ">
                                </td>
                                <td data-bind="text: TipoConcurso()" class="vertical-align-middle"></td>

                                <td class="text-center vertical-align-middle">
                                    <a href="javascript:void(0);"
                                        data-bind="click: function() { $root.goToEdition(Id(), TipoConcursoPath()) }"
                                        class="btn btn-xs purple" title="Editar">
                                        Editar
                                        <i class="fa fa-edit"></i>
                                    </a>

                                    <a data-bind="
                                        click: HabilitaEnvioInvitaciones() ? $root.sendInvitations.bind($data, Id()) : function(){}, 
                                        attr: {literal}{ 
                                            'disabled': !HabilitaEnvioInvitaciones(),
                                            'title': HabilitaEnvioInvitaciones() ? 'Enviar invitaciones' : 'Complete todos los campos obligatorios antes de enviar invitaciones'
                                        }{/literal},
                                        css: {literal}{ 
                                            'btn-disabled': !HabilitaEnvioInvitaciones(),
                                            'purple': HabilitaEnvioInvitaciones()
                                        }{/literal}"
                                        class="btn btn-xs" 
                                        style="cursor: pointer;">
                                        Enviar invitaciones
                                        <i class="fa fa-send"></i>
                                    </a>
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
                        <i class="fa fa-envelope"></i>
                        <span class="caption-subject bold">Convocatoria proveedores</span>
                        <span class="caption-helper font-white"
                            data-bind="text: Lists().ListaConcursosConvocatoriaOferentes().length">
                        </span>
                    </div>
                    <div class="tools">
                        <a href="javascript:;" data-original-title="" data-bind="css: { 
                            'collapse': Lists().ListaConcursosConvocatoriaOferentes().length > 0,
                            'expand': Lists().ListaConcursosConvocatoriaOferentes().length == 0 
                        }" title="">
                        </a>
                    </div>
                </div>
                <div class="portlet-body" data-bind="style: { 
                    'display': Lists().ListaConcursosConvocatoriaOferentes().length > 0 ? 'block' : 'none'
                }">
                    <table class="table table-striped table-bordered ListaConcursos"
                        id="ListaConcursosConvocatoriaOferentes">
                        <thead>
                            <tr>
                                <th> Nº Concurso </th>
                                <th> Nombre del concurso </th>
                                <th> Área Solicitante </th>
                                <th> Comprador </th>
                                <th> N° de Solicitud </th>
                                <th> Fecha Límite </th>
                                <th> Tipo de concurso </th>
                                <th> Estado </th>
                                <th class="text-center"> Acciones </th>
                            </tr>
                        </thead>
                        <tbody
                            data-bind="dataTablesForEach : { data: Lists().ListaConcursosConvocatoriaOferentes, options: { paging: false } }">
                            <tr>
                                <td data-bind="text: Id()" class="vertical-align-middle"></td>
                                <td data-bind="text: Nombre()" class="vertical-align-middle"></td>
                                <td data-bind="text: AreaSolicitante()" class="vertical-align-middle"></td>
                                <td data-bind="text: UsuarioSolicitante()" class="vertical-align-middle"></td>
                                <td data-bind="text: NumSolicitud()" class="vertical-align-middle"></td>
                                <td class="vertical-align-middle"
                                    data-bind="
                                    text: FechaLimite(),
                                    attr: { 'data-order': FechaLimiteOrden }
                                    ">
                                </td>
                                <td data-bind="text: TipoConcurso()" class="vertical-align-middle"></td>
                                <td data-bind="text: CantidadPresentaciones() + '/' + CantidadOferentes()"
                                    class="vertical-align-middle"></td>
                                <td class="text-center vertical-align-center">
                                    <a href="javascript:void(0);"
                                        data-bind="click: function() { $root.goToEdition(Id(), TipoConcursoPath()) }"
                                        class="btn btn-xs red-thunderbird" title="Editar">
                                        Editar
                                        <i class="fa fa-edit"></i>
                                    </a>

                                    <a href="javascript:void(0);"
                                        data-bind="click: function() { $root.goToAcceso(Id(), TipoConcursoPath(), 'convocatoria-oferentes') }"
                                        class="btn btn-xs red-thunderbird" title="Acceder">
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
        
        <!-- /ko -->
        <div class="col-md-12">
            <div class="portlet box yellow-gold">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-cogs"></i>
                        <span class="caption-subject bold">Análisis propuestas técnicas</span>
                        <span class="caption-helper font-white"
                            data-bind="text: Lists().ListaConcursosPropuestasTecnicas().length">
                        </span>
                    </div>
                    <div class="tools">
                        <a href="javascript:;" data-original-title="" data-bind="css: { 
                            'collapse': Lists().ListaConcursosPropuestasTecnicas().length > 0,
                            'expand': Lists().ListaConcursosPropuestasTecnicas().length == 0 
                        }" title="">
                        </a>
                    </div>
                </div>
                <div class="portlet-body" data-bind="style: { 
                    'display': Lists().ListaConcursosPropuestasTecnicas().length > 0 ? 'block' : 'none'
                }">
                    <table class="table table-striped table-bordered ListaConcursos" id="ListaConcursosPropuestasTecnicas">
                        <thead>
                            <tr>
                                <th> Nº Concurso </th>
                                <th> Nombre del concurso </th>
                                <th> Área Solicitante </th>
                                <th> Comprador </th>
                                <th> N° de Solicitud </th>
                                <th> Fecha Límite </th>
                                <th> Tipo de concurso </th>
                                <th> Estado </th>
                                <th class="text-center"> Acciones </th>
                            </tr>
                        </thead>
                        <tbody
                            data-bind="dataTablesForEach : { data: Lists().ListaConcursosPropuestasTecnicas, options: { paging: false }}">
                            <tr>
                                <td data-bind="text: Id()" class="vertical-align-middle"></td>
                                <td data-bind="text: Nombre()" class="vertical-align-middle"></td>
                                <td data-bind="text: AreaSolicitante()" class="vertical-align-middle"></td>
                                <td data-bind="text: UsuarioSolicitante()" class="vertical-align-middle"></td>
                                <td data-bind="text: NumSolicitud()" class="vertical-align-middle"></td>
                                <td class="vertical-align-middle"
                                    data-bind="
                                    text: FechaTecnica() + ' ' + HoraTecnica(),
                                    attr: { 'data-order': FechaTecnicaOrden }
                                    ">
                                </td>
                                <td data-bind="text: TipoConcurso()" class="vertical-align-middle"></td>
                                <td data-bind="text: CantidadPresentaciones() + '/' + CantidadOferentes()"
                                    class="vertical-align-middle"></td>

                                <td class="text-center vertical-align-center">

                                    <a href="javascript:;" data-bind="click: function() { $root.goToAcceso(Id(), TipoConcursoPath(), 'analisis-tecnicas') }"
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
            <div class="portlet box yellow-lemon">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-money"></i>
                        <span class="caption-subject bold">Análisis de ofertas</span>
                        <span class="caption-helper font-white"
                            data-bind="text: Lists().ListaConcursosAnalisisOfertas().length">
                        </span>
                    </div>
                    <div class="tools">
                        <a href="javascript:;" data-original-title="" data-bind="css: { 
                            'collapse': Lists().ListaConcursosAnalisisOfertas().length > 0,
                            'expand': Lists().ListaConcursosAnalisisOfertas().length == 0 
                        }" title="">
                        </a>
                    </div>
                </div>
                <div class="portlet-body" data-bind="style: { 
                    'display': Lists().ListaConcursosAnalisisOfertas().length > 0 ? 'block' : 'none'
                }">
                    <table class="table table-striped table-bordered ListaConcursos" id="ListaConcursosAnalisisOfertas">
                        <thead>
                            <tr>
                                <th> Nº Concurso </th>
                                <th> Nombre del concurso </th>
                                <th> Área Solicitante </th>
                                <th> Comprador </th>
                                <th> N° de Solicitud </th>
                                <th> Fecha Límite </th>
                                <th> Tipo de concurso </th>
                                <th> Estado </th>
                                <th> Status </th>
                                <th class="text-center"> Acciones </th>
                            </tr>
                        </thead>
                        <tbody
                            data-bind="dataTablesForEach : { data: Lists().ListaConcursosAnalisisOfertas, options: { paging: false }}">
                            <tr>
                                <td data-bind="text: Id()" class="vertical-align-middle"></td>
                                <td data-bind="text: Nombre()" class="vertical-align-middle"></td>
                                <td data-bind="text: AreaSolicitante()" class="vertical-align-middle"></td>
                                <td data-bind="text: UsuarioSolicitante()" class="vertical-align-middle"></td>
                                <td data-bind="text: NumSolicitud()" class="vertical-align-middle"></td>
                                <td class="vertical-align-middle" style="white-space: nowrap;"
                                    data-bind="
                                    text: Fecha() + ' ' + Hora(),
                                    attr: { 'data-order': FechaEconomicaOrden }
                                    ">
                                </td>
                                <td data-bind="text: TipoConcurso()" class="vertical-align-middle"></td>
                                <td data-bind="text: CantidadPresentaciones() + '/' + CantidadOferentes()"
                                    class="vertical-align-middle"></td>
                                <td data-bind="text: Estado()" class="vertical-align-middle"></td>
                                <td class="text-center vertical-align-center">
                                    
                                    <a href="javascript:;" data-bind="click: function() { $root.goToAcceso(Id(), TipoConcursoPath(), 'analisis-ofertas') }"
                                        class="btn btn-xs yellow-lemon" title="Acceder">
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
            <div class="portlet box green">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-star"></i>
                        <span class="caption-subject bold">Evaluación de reputación</span>
                        <span class="caption-helper font-white"
                            data-bind="text: Lists().ListaConcursosEvaluacionReputacion().length">
                        </span>
                    </div>
                    <div class="tools">
                        <a href="javascript:;" data-original-title="" data-bind="css: { 
                            'collapse': Lists().ListaConcursosEvaluacionReputacion().length > 0,
                            'expand': Lists().ListaConcursosEvaluacionReputacion().length == 0 
                        }" title="">
                        </a>
                    </div>
                </div>
                <div class="portlet-body" data-bind="style: { 
                    'display': Lists().ListaConcursosEvaluacionReputacion().length > 0 ? 'block' : 'none'
                }">
                    <table class="table table-striped table-bordered ListaConcursos"
                        id="ListaConcursosEvaluacionReputacion">
                        <thead>
                            <tr>
                                <th> Nº Concurso </th>
                                <th> Nombre del concurso </th>
                                <th> Área Solicitante </th>
                                <th> Comprador </th>
                                <th> N° de Solicitud </th>
                                <th> Fecha Límite </th>
                                <th> Tipo de concurso </th>
                                <th class="text-center"> Acciones </th>
                            </tr>
                        </thead>
                        <tbody
                            data-bind="dataTablesForEach : { data: Lists().ListaConcursosEvaluacionReputacion, options: { paging: false }}">
                            <tr>
                                <td data-bind="text: Id()" class="vertical-align-middle"></td>
                                <td data-bind="text: Nombre()" class="vertical-align-middle"></td>
                                <td data-bind="text: AreaSolicitante()" class="vertical-align-middle"></td>
                                <td data-bind="text: UsuarioSolicitante()" class="vertical-align-middle"></td>
                                <td data-bind="text: NumSolicitud()" class="vertical-align-middle"></td>
                                <td class="vertical-align-middle"
                                    data-bind="
                                    text: FechaLimite(),
                                    attr: { 'data-order': FechaLimiteOrden }
                                    ">
                                </td>
                                <td data-bind="text: TipoConcurso()" class="vertical-align-middle"></td>
                                <td class="text-center vertical-align-center">

                                    <a href="javascript:;" data-bind="click: function() { $root.goToAcceso(Id(), TipoConcursoPath(), 'evaluacion-reputacion') }"
                                        class="btn btn-xs green" title="Acceder">
                                        Acceder <i class="fa fa-play"></i>
                                    </a>

                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- ko if: $root.UserType() == 'customer' || $root.UserType() == 'supervisor' -->
        <div class="col-md-12">
            <div class="portlet box green-jungle">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-archive"></i>
                        <span class="caption-subject bold">Informe</span>
                        <span class="caption-helper font-white" data-bind="text: Lists().ListaConcursosInformes().length">
                        </span>
                    </div>
                    <div class="tools">
                        <a href="javascript:;" class="expand" data-original-title="" title=""> </a>
                    </div>
                </div>
                <div class="portlet-body" style="display: none;">
                    <table class="table table-striped table-bordered ListaConcursos" id="ListaConcursosInformes">
                        <thead>
                            <tr>
                                <th> Nº Concurso </th>
                                <th> Nombre del concurso </th>
                                <th> Área Solicitante </th>
                                <th> Comprador </th>
                                <th> N° de Solicitud </th>
                                <th> Fecha Límite </th>
                                <th> Tipo de concurso </th>
                                <th class="text-center"> Acciones </th>
                            </tr>
                        </thead>
                        <tbody class="lebels"
                            data-bind="dataTablesForEach : { data: Lists().ListaConcursosInformes, options: { paging: false }}">
                            <tr>
                                <td data-bind="text: Id()" class="vertical-align-middle"></td>
                                <td data-bind="text: Nombre()" class="vertical-align-middle"></td>
                                <td data-bind="text: AreaSolicitante()" class="vertical-align-middle"></td>
                                <td data-bind="text: UsuarioSolicitante()" class="vertical-align-middle"></td>
                                <td data-bind="text: NumSolicitud()" class="vertical-align-middle"></td>
                                <td class="vertical-align-middle"
                                    data-bind="
                                    text: FechaLimite(),
                                    attr: { 'data-order': FechaLimiteOrden }
                                    ">
                                </td>
                                <td data-bind="text: TipoConcurso()" class="vertical-align-middle"></td>
                                <td class="text-center vertical-align-center">
                                    
                                    <a href="javascript:;" data-bind="click: function() { $root.goToAcceso(Id(), TipoConcursoPath(), 'informes') }"
                                        class="btn btn-xs green-jungle" title="Acceder">
                                        Acceder <i class="fa fa-play"></i>
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
            <div class="portlet box red-haze">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-ban"></i>
                        <span class="caption-subject bold">Concursos Cancelados/Vencidos</span>
                        <span class="caption-helper font-white" data-bind="text: Lists().ListaConcursosCancelados().length">
                        </span>
                    </div>
                    <div class="tools">
                        <a href="javascript:;" data-original-title="" data-bind="css: { 
                            'collapse': Lists().ListaConcursosCancelados().length > 0,
                            'expand': Lists().ListaConcursosCancelados().length == 0 
                        }" title="">
                        </a>
                    </div>
                </div>
                <div class="portlet-body" data-bind="style: { 
                    'display': Lists().ListaConcursosCancelados().length > 0 ? 'block' : 'none'
                }">
                    <table class="table table-striped table-bordered ListaConcursos" id="ListaConcursosCancelados">
                        <thead>
                            <tr>
                                <th> Nº Concurso </th>
                                <th> Nombre del concurso </th>
                                <th> Área Solicitante </th>
                                <th> Comprador </th>
                                <th> Fecha Límite </th>
                                <th> N° de Solicitud </th>
                                <th> Tipo de concurso </th>
                                <th> Status </th>
                                <th class="text-center"> Acciones </th>
                            </tr>
                        </thead>
                        <tbody
                            data-bind="dataTablesForEach : { data: Lists().ListaConcursosCancelados, options: { paging: false }}">
                            <tr>
                                <td data-bind="text: Id()" class="vertical-align-middle"></td>
                                <td data-bind="text: Nombre()" class="vertical-align-middle"></td>
                                <td data-bind="text: AreaSolicitante()" class="vertical-align-middle"></td>
                                <td data-bind="text: UsuarioSolicitante()" class="vertical-align-middle"></td>
                                <td data-bind="text: NumSolicitud()" class="vertical-align-middle"></td>
                                <td class="vertical-align-middle" style="white-space: nowrap;"
                                    data-bind="
                                    text: FechaCancelacion() + ' ' + HoraCancelacion(),
                                    attr: { 'data-order': FechaCancelacionOrden }
                                    ">
                                </td>
                                <td data-bind="text: TipoConcurso()" class="vertical-align-middle"></td>
                                <td data-bind="text: Estado()" class="vertical-align-middle"></td>
                                <td class="text-center vertical-align-center">
                                    <!-- <a data-bind="attr: 
                                    {literal}
                                        { href: '/concursos/cliente/' + TipoConcursoPath() + '/analisis-ofertas/' + Id() }
                                    {/literal}" class="btn btn-xs yellow-lemon" title="Editar">
                                        Acceder
                                        <i class="fa fa-play"></i>
                                    </a> -->
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
        var ListItem = function(data) {
            var self = this;

            this.Id = ko.observable(data.Id);
            this.CantidadOferentes = ko.observable(data.CantidadOferentes);
            this.CantidadPresentaciones = ko.observable(data.CantidadPresentaciones);
            this.HabilitaEnvioInvitaciones = ko.observable(data.HabilitaEnvioInvitaciones);
            this.Estado = ko.observable(data.Estado);
            
            //Fechas limite propuesta tecnica 
            this.FechaTecnica = ko.observable(data.FechaTecnica);
            this.FechaTecnicaOrden = ko.observable(data.FechaTecnicaOrden);
            this.HoraTecnica = ko.observable(data.HoraTecnica);

            //Fecha limite licitacion ? creo
            this.FechaLimite = ko.observable(data.FechaLimite);
            this.FechaLimiteOrden = ko.observable(data.FechaLimiteOrden);

            //Fecha limite propuesta economic
            this.Fecha = ko.observable(data.Fecha);
            this.Hora = ko.observable(data.Hora);
            this.FechaEconomicaOrden = ko.observable(data.FechaEconomicaOrden);

            //Fecha cancelacion
            this.FechaCancelacion = ko.observable(data.FechaCancelacion);
            this.HoraCancelacion = ko.observable(data.HoraCancelacion);
            this.FechaCancelacionOrden = ko.observable(data.FechaCancelacionOrden);

            this.Nombre = ko.observable(data.Nombre);
            this.Solicitante = ko.observable(data.Solicitante);
            this.NumSolicitud = ko.observable(data.NumSolicitud);
            this.TipoConcurso = ko.observable(data.TipoConcurso);
            this.TipoConcursoPath = ko.observable(data.TipoConcursoPath);
            this.UsuarioSolicitante = ko.observable(data.UsuarioSolicitante);
            this.AreaSolicitante = ko.observable(data.AreaSolicitante);
            
        }

        var List = function(data) {
            var self = this;

            this.ListaConcursosEnPreparacion = ko.observableArray([]);
            if (data.ListaConcursosEnPreparacion.length > 0) {
                data.ListaConcursosEnPreparacion.forEach(item => {
                    self.ListaConcursosEnPreparacion.push(new ListItem(item));
                });
            }
            this.ListaConcursosConvocatoriaOferentes = ko.observableArray([]);
            if (data.ListaConcursosConvocatoriaOferentes.length > 0) {
                data.ListaConcursosConvocatoriaOferentes.forEach(item => {
                    self.ListaConcursosConvocatoriaOferentes.push(new ListItem(item));
                });
            }
            this.ListaConcursosPropuestasTecnicas = ko.observableArray([]);
            if (data.ListaConcursosPropuestasTecnicas.length > 0) {
                data.ListaConcursosPropuestasTecnicas.forEach(item => {
                    self.ListaConcursosPropuestasTecnicas.push(new ListItem(item));
                });
            }
            this.ListaConcursosAnalisisOfertas = ko.observableArray([]);
            if (data.ListaConcursosAnalisisOfertas.length > 0) {
                data.ListaConcursosAnalisisOfertas.forEach(item => {
                    self.ListaConcursosAnalisisOfertas.push(new ListItem(item));
                });
            }
            this.ListaConcursosEvaluacionReputacion = ko.observableArray([]);
            if (data.ListaConcursosEvaluacionReputacion.length > 0) {
                data.ListaConcursosEvaluacionReputacion.forEach(item => {
                    self.ListaConcursosEvaluacionReputacion.push(new ListItem(item));
                });
            }
            this.ListaConcursosInformes = ko.observableArray([]);
            if (data.ListaConcursosInformes.length > 0) {
                data.ListaConcursosInformes.forEach(item => {
                    self.ListaConcursosInformes.push(new ListItem(item));
                });
            }
            this.ListaConcursosCancelados = ko.observableArray([]);
            if (data.ListaConcursosCancelados.length > 0) {
                data.ListaConcursosCancelados.forEach(item => {
                    self.ListaConcursosCancelados.push(new ListItem(item));
                });
            }
            this.ListaConcursosAdjudicados = ko.observableArray([]);
            if (data.ListaConcursosAdjudicados.length > 0) {
                data.ListaConcursosAdjudicados.forEach(item => {
                    self.ListaConcursosAdjudicados.push(new ListItem(item));
                });
            }


        }

        var Filters = function(parent) {
            var self = this;
            
            //Search observable
            this.searchTerm = ko.observable(null);

            //Subscribe to search term changes
            this.searchTerm = ko.observable(null);
            self.searchTerm.subscribe((value) => {
                parent.filter(self);
            });

            //Detect if searching by ID (all digits)
            this.isIdSearch = ko.computed(function() {
                return /^\d+$/.test(self.searchTerm());
            });
        };

        

        var ConcursosListadosCliente = function(data) {
            var self = this;

            this.Breadcrumbs = ko.observableArray(data.breadcrumbs);
            this.Lists = ko.observable(new List(data.list));
            this.Conectados = ko.observable('0');
            this.UserType = ko.observable(data.userType);
            this.filter = function(filters) {
                if (filters) {
                    $.blockUI()
                    var data = {};
                    Services.Post('/concursos/cliente/list', {
                            UserToken: User.Token,
                            Entity: JSON.stringify(ko.toJS(data)),
                            Filters: JSON.stringify(ko.toJS(filters))
                        },
                        (response) => {
                            if (response.success) {
                                self.Lists(new List(response.data.list))
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
                }
            };
            this.Filters = ko.observable(new Filters(self));

            this.goToEdition = function(idConcurso, tipoConcursoPath) {
                $.blockUI();
                Services.Post('/concursos/guardar-token-acceso', {
                    UserToken: User.Token,
                    id: idConcurso
                },
                (response) => {
                    $.unblockUI();
                    if (response.success) {
                        window.location.href = '/concursos/' + tipoConcursoPath + '/edicion/' + idConcurso;
                    } else {
                        swal('Error', response.message, 'error');
                    }
                },
                (error) => {
                    $.unblockUI();
                    swal('Error', error.message, 'error');
                });
            };

            this.goToAcceso = function(idConcurso, tipoConcursoPath, etapa) {
                $.blockUI();
                Services.Post('/concursos/guardar-token-acceso', {
                    UserToken: User.Token,
                    id: idConcurso
                },
                (response) => {
                    $.unblockUI();
                    if (response.success) {
                        window.location.href = '/concursos/cliente/' + tipoConcursoPath + '/' + etapa + '/' + idConcurso;
                    } else {
                        swal('Error', response.message, 'error');
                    }
                },
                (error) => {
                    $.unblockUI();
                    swal('Error', error.message, 'error');
                });
            };





            // Conectar a la subasta online si esta ha iniciado.
            var query = '?id_cliente=' + User.Id + '&listado=true';

            var path = 'wss://' + location.host + '/wss/';

            var subastaConn = new WebSocket(path + query);

            subastaConn.onopen = function(e) {};
            subastaConn.onerror = function(e) {};
            subastaConn.onmessage = function(e) {
                var result = JSON.parse(e.data);

                if (result.conectadosPorConcurso) {
                    $.blockUI();
                    // Recorremos los concursos con número de conectados disponibles.
                    for (var i = result.conectadosPorConcurso.length - 1; i >= 0; i--) {
                        // Buscamos en la lista de Concursos los correspondientes.
                        self.Lists().ListaConcursosAnalisisOfertas().forEach((item) => {
                            if (item.Id() == result.conectadosPorConcurso[i]['id_concurso']) {
                                // Actualizamos la cantidad de participantes.
                                item.CantidadPresentaciones(result.conectadosPorConcurso[i]['conectados']);
                            }
                        });
                    }
                    $.unblockUI();
                }
            };
            console.log('Socket Fin ->', path);

            this.sendInvitations = function(IdConcurso) {
                swal({
                    title: '¿Desea enviar las invitaciones?',
                    text: 'Esta a punto de enviar las notificaciones para este concurso.',
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
                        var url = '/concursos/invitations/send';
                        Services.Post(url, {
                                UserToken: User.Token,
                                IdConcurso: IdConcurso
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
                                    swal('Error', error.message, 'error');
                                }, 500);
                            },
                            null,
                            null
                        );
                    }
                });
            }
        };

        function confirmAccept(IdConcurso) {
            swal({
                title: '¿Está seguro de que desea aceptar la licitación?',
                type: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, aceptar',
                cancelButtonText: 'No, cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.blockUI();  // Bloquea la UI mientras se procesa la solicitud
                    var data = {
                        UserToken: User.Token
                    };
                    var url = '/configuraciones/estrategia-liberacion/accept/' + concursoId;
                    Services.Post(url, data,  // Paso del ID correctamente
                        (response) => {
                            $.unblockUI();  // Desbloquea la UI después de la respuesta
                            if (response.success) {
                                swal('Hecho', response.message, 'success');
                                location.reload();  // Recargar la página para ver los cambios
                            } else {
                                swal('Error', response.message, 'error');
                            }
                        },
                        (error) => {
                            $.unblockUI();
                            swal('Error', error.message, 'error');
                        }
                    );
                }
            });
        }

        jQuery(document).ready(function() {
            $.blockUI();
            var data = {};
            Services.Get('/concursos/cliente/list', {
                    UserToken: User.Token,
                    Entity: JSON.stringify(ko.toJS(data))
                },
                (response) => {
                    if (response.success) {
                        window.E = new ConcursosListadosCliente(response.data);
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