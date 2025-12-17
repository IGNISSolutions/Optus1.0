<!-- BEGIN SIDEBAR -->
<div class="page-sidebar-wrapper">
    <!-- BEGIN SIDEBAR -->
    <div class="page-sidebar navbar-collapse collapse">
        <!-- BEGIN SIDEBAR MENU -->
        <ul class="page-sidebar-menu  page-header-fixed " data-keep-expanded="false" data-auto-scroll="true"
            data-slide-speed="200" style="padding-top: 20px">
            <!-- BEGIN SIDEBAR TOGGLER BUTTON -->
            <li class="sidebar-toggler-wrapper hide">
                <div class="sidebar-toggler">
                    <span></span>
                </div>
            </li>

            <li class="nav-item{if $page eq 'dashboard'} active open{/if}">
                <a href="/dashboard" class="nav-link nav-toggle">
                    <i class="icon-bar-chart"></i>
                    <span class="title">Dashboard</span>
                    <span class="selected"></span>
                </a>
            </li>


            {if isOfferer()}
                <li class="nav-item{if $page eq 'perfil'} active open{/if}">
                    <a href="/empresas/oferentes/perfil/edicion" class="nav-link nav-toggle">
                        <i class="icon-settings"></i>
                        <span class="title">Perfil Empresa</span>
                        <span class="selected"></span>
                    </a>
                </li>
            {/if}

            {if can('concursos-edit-online') or
                can('concursos-edit-sobrecerrado') or
                can('concursos-edit-go') or
                can('concursos-list')}

            <li class="nav-item{if $page eq 'concursos'} active open{/if}">
                <a href="#" class="nav-link nav-toggle">
                    <i class="icon-diamond"></i>
                    <span class="title">Concursos</span>
                    <span class="arrow{if $page eq 'concursos'} open{/if}"></span>
                    {if $page eq 'concursos'}
                        <span class="selected"></span>
                    {/if}
                </a>
                <ul class="sub-menu">
                    {if can('concursos-edit-online')}
                        <li class="nav-item{if $accion eq 'listado-online' or $accion eq 'nuevo-online'} active open{/if}">
                            <a href="/concursos/cliente/online" class="nav-link ">
                                <span class="title">Subasta</span>
                            </a>
                        </li>
                    {/if}
                    {if can('concursos-edit-sobrecerrado')}
                        <li
                            class="nav-item{if $accion eq 'listado-sobrecerrado' or $accion eq 'nuevo-sobrecerrado'} active open{/if}">
                            <a href="/concursos/cliente/sobrecerrado"
                                class="nav-link ">
                                <span class="title">Licitación </span>
                            </a>
                        </li>
                    {/if}
                    {if can('concursos-edit-go')}
                        <li class="nav-item{if $accion eq 'listado-go' or $accion eq 'nuevo-go'} active open{/if}">
                            <a href="/concursos/cliente/go" class="nav-link ">
                                <span class="title">Go </span>
                            </a>
                        </li>
                    {/if}
                    {if can('concursos-list')}
                        {if isOfferer() || isAdmin()}
                            <li class="nav-item{if $accion eq 'listado-oferente'} active open{/if}">
                                <a href="/concursos/oferente" class="nav-link ">
                                    <span class="title">
                                        {if isAdmin()}
                                            Monitor Proveedor
                                        {else}
                                            Monitor
                                        {/if}
                                    </span>
                                </a>
                            </li>
                        {/if}
                        {if isCustomer() || isAdmin()}
                            <li class="nav-item{if $accion eq 'listado-cliente'} active open{/if}">
                                <a href="/concursos/cliente" class="nav-link ">
                                    <span class="title">
                                        {if isAdmin()}
                                            Monitor Cliente
                                        {else}
                                            Monitor
                                        {/if}
                                    </span>
                                </a>
                            </li>
                        {/if}
                    {/if}
                </ul>
            </li>

            {/if}

            {if isAdmin() || isCustomer() }

                {if can('usuarios-clients-edit') or can('usuarios-offerer-edit') or can('usuarios-admin-edit')}
                    <li class="nav-item{if $page eq 'usuarios'} active open{/if}">
                        <a href="#" class="nav-link nav-toggle">
                            <i class="icon-users"></i>
                            <span class="title">Usuarios</span>
                            <span class="arrow{if $page eq 'usuarios'} open{/if}"></span>
                            {if $page eq 'usuarios'}
                                <span class="selected"></span>
                            {/if}
                        </a>
                        <ul class="sub-menu">
                            {if can('usuarios-admin-edit')}
                                <li class="nav-item{if $page eq 'usuarios'} active open{/if}">
                                    <a href="/usuarios/tipo/admin" class="nav-link nav-toggle">
                                        <i class="icon-user"></i>
                                        <span class="title">Usuarios Administradores</span>
                                        {if $page eq 'usuarios'}
                                            <span class="selected"></span>
                                        {/if}
                                    </a>
                                </li>
                            {/if}
                            {if can('usuarios-clients-edit')}
                                <li class="nav-item{if $page eq 'usuarios'} active open{/if}">
                                    <a href="/usuarios/tipo/client" class="nav-link nav-toggle">
                                        <i class="icon-user"></i>
                                        <span class="title">Usuarios</span>
                                        {if $page eq 'usuarios'}
                                            <span class="selected"></span>
                                        {/if}
                                    </a>
                                </li>
                            {/if}
                            {if can('usuarios-offerer-edit')}
                                <li class="nav-item{if $page eq 'usuarios'} active open{/if}">
                                    <a href="/usuarios/tipo/offerer" class="nav-link nav-toggle">
                                        <i class="icon-user"></i>
                                        <span class="title">Usuarios Proveedores</span>
                                        {if $page eq 'usuarios'}
                                            <span class="selected"></span>
                                        {/if}
                                    </a>
                                </li>
                            {/if}
                        </ul>
                    </li>
                {/if}

            {/if}

            {if isAdmin() || isCustomer() }

                {if can('configurations-areas') or can('configurations-measurements') or can('configurations-tipocambio') or can('configurations-catalogs')}
                    <!-- or can('configurations-estrategia-liberacion') -->
                    <li class="nav-item{if $page eq 'configuraciones'} active open{/if}">
                        <a href="/configuraciones" class="nav-link nav-toggle">
                            <i class="icon-settings"></i>
                            <span class="title">Configuraciones</span>
                            <span class="arrow{if $page eq 'configuraciones'} open{/if}"></span>
                            {if $page eq 'configuraciones'}
                                <span class="selected"></span>
                            {/if}
                        </a>
                        <ul class="sub-menu">
                            {if can('configurations-tipocambio')}
                                <li class="nav-item{if $accion eq 'listado-tipocambio'} active open{/if}">
                                    <a href="/configuraciones/tipocambio" class="nav-link ">
                                        <span class="title">Tipos de cambio</span>
                                    </a>
                                </li>
                            {/if}
                            {if can('configurations-areas')}
                                <li class="nav-item{if $accion eq 'listado-rubros'} active open{/if}">
                                    <a href="/configuraciones/rubros" class="nav-link ">
                                        <span class="title">Catálogo de rubros</span>
                                    </a>
                                </li>
                            {/if}
            
                            {if can('configurations-measurements')}
                                <li class="nav-item{if $accion eq 'listado-unidades'} active open{/if}">
                                    <a href="/configuraciones/unidades" class="nav-link ">
                                        <span class="title">Unidades de Medida</span>
                                    </a>
                                </li>
                            {/if}

                            {if can('configurations-catalogs')}
                            <li class="nav-item{if $accion eq 'listado-catalogos'} active open{/if}">
                                <a href="/configuraciones/catalogos" class="nav-link ">
                                    <span class="title">Categorías de Materiales</span>
                                </a>
                            </li>
                            {/if}

                            <!-- {if can('configurations-estrategia-liberacion')}
                                <li class="nav-item{if $accion eq 'listado-estrategia-liberacion'} active open{/if}">
                                    <a href="/configuraciones/estrategia-liberacion" class="nav-link ">
                                        <span class="title">Estrategia de liberación</span>
                                    </a>
                                </li>
                            {/if} -->
                        </ul>
                    </li>
                {/if}
            {/if}

            {if isAdmin() || isCustomer()}

                {if can('companies-offerers') or can('companies-customers')}
                    <li class="nav-item{if $page eq 'empresas'} active open{/if}">
                        <a href="/configuraciones" class="nav-link nav-toggle">
                            <i class="fa fa-building-o"></i>
                            <span class="title">Empresas</span>
                            <span class="arrow{if $page eq 'empresas'} open{/if}"></span>
                            {if $page eq 'empresas'}
                                <span class="selected"></span>
                            {/if}
                        </a>
                        <ul class="sub-menu">
                            {if can('companies-offerers')}
                                <li
                                    class="nav-item{if $accion eq 'listado-oferentes' or $accion eq 'nuevo-oferentes'} active open{/if}">
                                    <a href="/empresas/offerer" class="nav-link ">
                                        <span class="title">Proveedores</span>
                                    </a>
                                </li>
                            {/if}
                            {if can('companies-customers')}
                                <li
                                    class="nav-item{if $accion eq 'listado-clientes' or $accion eq 'nuevo-clientes'} active open{/if}">
                                    <a href="/empresas/client" class="nav-link ">
                                        <span class="title">Clientes</span>
                                    </a>
                                </li>
                            {/if}

                        </ul>
                    </li>
                {/if}
                {if can('companies-materials')}
                    <li class="nav-item{if $page eq 'materiales'} active open{/if}">
                        <a href="/materiales" class="nav-link nav-toggle">
                            <i class="fa fa-cubes" aria-hidden="true"></i>
                            <span class="title">Materiales</span>
                            {if $page eq 'materiales'}
                                <span class="selected"></span>
                            {/if}
                        </a>
                    </li>
                {/if}
                {if can('reports')}
                    <li class="nav-item{if $page eq 'reportes'} active open{/if}">
                        <a href="#" class="nav-link nav-toggle">
                            <i class="fa fa-files-o" aria-hidden="true"></i>
                            <span class="title">Reportes</span>
                            <span class="arrow{if $page eq 'reportes'} open{/if}"></span>
                            {if $page eq 'reportes'}
                                <span class="selected"></span>
                            {/if}
                        </a>
                        <ul class="sub-menu">
                            <li class="nav-item{if $accion eq 'lista-concursos-adjudicados'} active open{/if}">
                                <a href="/reportes/adjudicados" class="nav-link ">
                                    <span class="title">Concursos Adjudicados</span>
                                </a>
                            </li>
                            <li class="nav-item{if $accion eq 'lista-concursos-evaluados'} active open{/if}">
                                <a href="/reportes/evaluados" class="nav-link ">
                                    <span class="title">Concursos Evaluados</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                {/if}
            {/if}

            <li class="nav-item{if $page eq 'tutoriales'} active open{/if}">
                <a href="/tutoriales" class="nav-link nav-toggle">
                    <i class="icon-book-open"></i>
                    <span class="title">Tutoriales</span>
                    {if $page eq 'tutoriales'}
                        <span class="selected"></span>
                    {/if}
                </a>
            </li>

            {if isAdmin()}
                {if can('rates-edit') or
                            can('rates-report-concursos') or
                            can('rates-report-payment') or
                            can('rates-report-history')}

                <li class="nav-item{if $page eq 'tarifas'} active open{/if}">
                    <a href="/configuraciones" class="nav-link nav-toggle">
                        <i class="fa fa-usd"></i>
                        <span class="title">Tarifas</span>
                        <span class="arrow"></span>
                        {if $page eq 'tarifas'}
                            <span class="selected"></span>
                        {/if}
                    </a>
                    <ul class="sub-menu">
                        {if can('rates-edit')}
                            <li class="nav-item{if $accion eq 'listado-empresa-oferente'} active open{/if}">
                                <a href="javascript:;" class="nav-link ">
                                    <span class="title">Gestión de tarifas</span>
                                </a>
                            </li>
                        {/if}
                        {if can('rates-report-concursos')}
                            <li class="nav-item{if $accion eq 'listado-reportes-concursos'} active open{/if}">
                                <a href="javascript:;" class="nav-link ">
                                    <span class="title">Reportes concursos</span>
                                </a>
                            </li>
                        {/if}
                        {if can('rates-report-payment')}
                            <li class="nav-item{if $accion eq 'listado-reportes-cobros'} active open{/if}">
                                <a href="javascript:;" class="nav-link ">
                                    <span class="title">Reportes cobros</span>
                                </a>
                            </li>
                        {/if}
                        {if can('rates-report-history')}
                            <li class="nav-item{if $accion eq 'listado-reportes-tarifas'} active open{/if}">
                                <a href="javascript:;" class="nav-link ">
                                    <span class="title">Reportes tarifas históricas</span>
                                </a>
                            </li>
                        {/if}
                    </ul>
                </li>

            {/if}

            {/if}
            {if isCustomer() || isOfferer()}
                <li class="nav-item{if $page eq 'consultas'} active open{/if}">
                    <a href="/consultas/nuevo" class="nav-link nav-toggle">
                        <i class="icon-info"></i>
                        <span class="title">Mesa de ayuda</span>
                        {if $page eq 'consultas'}
                            <span class="selected"></span>
                        {/if}
                    </a>
                </li>
            {/if}
        </ul>
        <!-- END SIDEBAR MENU -->
    </div>
    <!-- END SIDEBAR -->
</div>
<!-- END SIDEBAR -->