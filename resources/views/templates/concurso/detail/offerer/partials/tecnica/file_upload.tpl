<div class="row m-heading-1 border-default m-bordered text-left">
    <ul data-bind="foreach: PropuestasTecnicas()" class="nav nav-pills nav-justified">
        <li data-bind="css: { active: active() ? 'active' : '' }">
            <a data-toggle="pill" data-bind="text:title(), attr: { href: '#'+refRound() }"></a>
        </li>
    </ul>
    <div class="tab-content" data-bind="foreach: PropuestasTecnicas()">
        <div class="tab-pane fade"
            data-bind="attr: { id: refRound() }, css: { in: active() ? 'in' : '', active: active() ? 'active' : '' }">
            <!-- ko if: active() -->

            <!-- ko if: !$root.EnableTechnical() -->
            <!-- ko ifnot: presented() -->
            <div class="m-heading-1 border-default m-bordered text-center"
                style="display: flex; justify-content: center; flex-direction: column;">
                <span>
                    <i class="fa fa-clock-o fa-2x" aria-hidden="true"
                        title="El concurso ha llegado a su fecha límite para presentar su propuesta técnica"
                        style="color:rgba(236, 11, 11, 0.973)">
                        El concurso ha llegado a su fecha límite para presentar su propuesta técnica
                    </i>
                </span>
            </div>
            <!-- /ko -->

            <!-- ko if: presented() -->
            <div class="note note-info">
                <h4 class="block">
                    Comentarios Proveedor
                </h4>
                <p data-bind="text: comentario()"></p>
            </div>
            <table class="table table-striped table-bordered">
                <tbody data-bind="foreach: documents()">
                    <tr>
                        <!---agregra el nombre que tiene en base de datos de los nuevos cosos para el coso (copiar lista sub contra<tista)-->
                        <!-- ko if: 
                        (
                            name() !== 'Lista de sub contratistas' && 
                            name() !== 'Certificado de visita de obra' && 

                            name() !== 'Entrega de documentación para evaluación y Alta de proveedor' &&
                            name() !== 'Cumplimiento de requisitos legales y reglamentos aplicables' &&
                            name() !== 'Experiencia y referencias comerciales' &&
                            name() !== 'Documentacion REPSE' &&
                            name() !== 'Alcance' &&
                            name() !== 'Garantías' &&
                            name() !== 'Forma de pago' &&
                            name() !== 'Tiempo de fabricación e instalación de cocinas' &&
                            name() !== 'Ficha técnica. (Materiales, especificaciones y características de la propuesta)' &&


                            name() !== 'Certificado de visita de obra' &&

                            name() !== 'Cronograma de trabajo' && 
                            name() !== 'Seguro de Caución' && 
                            name() !== 'Bases y condiciones Firmado' && 
                            name() !== 'Condiciones Generales Firmado' && 
                            name() !== 'Pliego Técnico Firmado' && 
                            name() !== 'Acuerdo de Confidencialidad Firmado' && 
                            name() !== 'Legajo Impositivo' && 
                            name() !== 'Referencias comerciales' && 
                            name() !== 'Reporte Accidentes' &&
                            name() !== 'Envío de muestras' &&
                            name() !== 'NOM-251-SSA1-2009'&&
                            name() !== 'Distintivo H'&&
                            name() !== 'Filtros Sanitarios Trimestrales a los empleados'&&
                            name() !== 'Documentación REPSE'&&
                            name() !== 'Póliza de seguro responsabilidad civil'&&
                            name() !== 'Prima de riesgo 5 millones' &&
                            name() !== 'Referencias Comerciales' &&
                            name() !== 'Organigrama de obra' &&
                            name() !== 'Listado de Equipos y herramientas' &&
                            name() !== 'Cronograma de obra' &&
                            name() !== 'Memoria técnica' &&
                            name() !== 'Antecedentes de obras similares' &&
                            name() !== 'Ficha Técnica de la tarima' &&
                            name() !== 'Licencia Ambiental integral (LAI)' &&
                            name() !== 'Cumplimiento NOM-144 SEMARNAT 2017' &&
                            name() !== 'Acreditación legal con la procedencia de la madera' &&
                            name() !== 'Último balance de la empresa' &&
                            name() !== 'Ultimas 3 DDJJ de IVA' &&
                            name() !== 'Constancia de CUIT' &&
                            name() !== 'Brochure de antecedentes de edificios incluyendo obras en curso' &&
                            name() !== 'Organigrama de la empresa (puestos claves)' &&
                            name() !== 'Organigrama previsto para la obra' &&
                            name() !== 'Listado de subcontratistas por rubro' &&
                            name() !== 'Gestión de H&S (incluir indicadores, procedimientos, detalle de personal, etc.)' &&
                            name() !== 'Listado de máquinas y equipos a utilizar' &&
                            name() !== 'TÉCNICA - Propuesta Técnica / Procedimientos / Metodologías / Técnicas aplicadas' &&
                            name() !== 'TÉCNICA - Plan de mantenimiento preventivo, correctivo, soporte, evolutivo' &&
                            name() !== 'TÉCNICA - Inventario de equipos, herramientas, vehículos y/o maquinarias' &&
                            name() !== 'TÉCNICA - Acreditaciones, Permisos, Autorizaciones' &&
                            name() !== 'TÉCNICA - Requerimientos tecnológicos de hardware, software y/o conectividad' &&
                            name() !== 'TÉCNICA - Requisitos del personal, calificaciones, CV, certificaciones, experiencia, capacitación, etc' &&
                            name() !== 'TÉCNICA - Organigrama / Equipo de Trabajo / Niveles de escalamiento' &&
                            name() !== 'TÉCNICA - Valor agregado' &&
                            name() !== 'TÉCNICA - Acuerdos de nivel de servicio' &&
                            name() !== 'HSEQ - Requisitos matriz HSEQ según Anexo 2' &&
                            name() !== 'ECONÓMICA - Referencias comerciales / Acreditación experiencia' &&
                            
                            name() !== 'ECONÓMICA - Evaluación riesgo financiero' &&
                            name() !== 'TÉCNICA - Ficha de Especificaciones Técnicas' &&
                            name() !== 'TÉCNICA - Hojas de seguridad / MSDS' &&
                            name() !== 'TÉCNICA - Garantía' &&
                            name() !== 'TÉCNICA - Envío de muestra' &&
                            name() !== 'TÉCNICA - Cronograma de entrega / Plazo de entrega' &&
                            name() !== 'TÉCNICA - Carta de representante de la marca y/o distribuidor autorizado' &&
                            name() !== 'TÉCNICA - Soporte Post Venta' &&
                            name() !== 'TÉCNICA - Lugar y forma de entrega' &&
                            name() !== 'TÉCNICA - Acuerdo de confidencialidad FIRMADO' &&
                            name() !== 'Listado de equipos y herramientas' &&
                            name() !== 'Equipo humano y competencias' &&
                            name() !== 'Balances y estados de resultados' &&
                            name() !== 'Estatuto o contrato social' &&
                            name() !== 'Actas de designación de autoridades'
                            




                        ) || 

                        (name() === 'Cronograma de trabajo' && $root.DiagramaGant() === 'si') ||                  

                        (name() === 'Lista de sub contratistas' && $root.ListaProveedores() === 'si') ||
                        (name() === 'Certificado de visita de obra' && $root.CertificadoVisitaObra() === 'si') ||
                        (name() === 'TÉCNICA - Acuerdo de confidencialidad FIRMADO' && $root.NdaFirmado() === 'si') ||
                        (name() === 'Entrega de documentación para evaluación y Alta de proveedor' && $root.EntregaDocEvaluacion() === 'si') ||
                        (name() === 'Cumplimiento de requisitos legales y reglamentos aplicables' && $root.RequisitosLegales() === 'si') ||
                        (name() === 'Experiencia y referencias comerciales' && $root.ExperienciaYReferencias() === 'si') ||
                        (name() === 'Documentacion REPSE' && $root.DocumentacionREPSE() === 'si') ||
                        (name() === 'Alcance' && $root.Alcance() === 'si') ||
                        (name() === 'Forma de pago' && $root.FormaPago() === 'si') ||
                        (name() === 'Tiempo de fabricación e instalación de cocinas' && $root.TiempoFabricacion() === 'si') ||
                        (name() === 'Ficha técnica. (Materiales, especificaciones y características de la propuesta)' && $root.FichaTecnica() === 'si') ||
                        (name() === 'Garantías' && $root.Garantias() === 'si') ||


                        (name() === 'Seguro de Caución' && $root.SeguroCaucion() === 'si') ||
                        (name() === 'Bases y condiciones Firmado' && $root.BaseCondiciones() === 'si') ||
                        (name() === 'Condiciones Generales Firmado' && $root.CondicionesGenerales() === 'si') ||
                        (name() === 'Pliego Técnico Firmado' && $root.PliegoTecnico() === 'si') ||
                        (name() === 'Acuerdo de Confidencialidad Firmado' && $root.Confidencialidad() === 'si') ||
                        (name() === 'Legajo Impositivo' && $root.LegajoImpositivo() === 'si') ||
                        (name() === 'Referencias comerciales' && $root.Antecedentes() === 'si') ||
                        (name() === 'Reporte Accidentes' && $root.ReporteAccidentes() === 'si') ||
                        (name() === 'Envío de muestras' && $root.EnvioMuestra() === 'si') ||
                        (name() === 'NOM-251-SSA1-2009' && $root.nom251() === 'si') ||
                        (name() === 'Distintivo H' && $root.distintivo() === 'si') ||
                        (name() === 'Filtros Sanitarios Trimestrales a los empleados' && $root.filtros_sanitarios() === 'si') ||
                        (name() === 'Documentación REPSE' && $root.repse() === 'si') ||
                        (name() === 'Póliza de seguro responsabilidad civil' && $root.poliza() === 'si') ||
                        (name() === 'Prima de riesgo 5 millones' && $root.primariesgo() === 'si') || 
                        (name() === 'Referencias Comerciales' && $root.obras_referencias() === 'si') ||
                        (name() === 'Organigrama de obra' && $root.obras_organigrama() === 'si') ||
                        (name() === 'Equipos y herramientas' && $root.obras_equipos() === 'si') ||
                        (name() === 'Cronograma de obra' && $root.obras_cronograma() === 'si') ||
                        (name() === 'Memoria técnica' && $root.obras_memoria() === 'si') ||
                        (name() === 'Antecedentes de obras similares' && $root.obras_antecedentes() === 'si') ||
                        (name() === 'Ficha Técnica de la tarima' && $root.tarima_ficha_tecnica() === 'si') ||
                        (name() === 'Licencia Ambiental integral (LAI)' && $root.tarima_licencia() === 'si') ||
                        (name() === 'Cumplimiento NOM-144 SEMARNAT 2017' && $root.tarima_nom_144() === 'si') ||
                        (name() === 'Acreditación legal con la procedencia de la madera' && $root.tarima_acreditacion() === 'si') ||
                        (name() === 'Último balance de la empresa' && $root.edificio_balance() === 'si') ||
                        (name() === 'Ultimas 3 DDJJ de IVA' && $root.edificio_iva() === 'si') ||
                        (name() === 'Constancia de CUIT' && $root.edificio_cuit() === 'si') ||
                        (name() === 'Brochure de antecedentes de edificios incluyendo obras en curso' && $root.edificio_brochure() === 'si') ||
                        (name() === 'Organigrama de la empresa (puestos claves)' && $root.edificio_organigrama() === 'si') ||
                        (name() === 'Organigrama previsto para la obra' && $root.edificio_organigrama_obra() === 'si') ||
                        (name() === 'Listado de subcontratistas por rubro' && $root.edificio_subcontratistas() === 'si') ||
                        (name() === 'Gestión de H&S (incluir indicadores, procedimientos, detalle de personal, etc.)' && $root.edificio_gestion() === 'si') ||
                        (name() === 'Listado de máquinas y equipos a utilizar' && $root.edificio_maquinas() === 'si') ||
                        (name() === 'TÉCNICA - Propuesta Técnica / Procedimientos / Metodologías / Técnicas aplicadas' && $root.PropuestaTecnica() === 'si') ||
                        (name() === 'TÉCNICA - Plan de mantenimiento preventivo, correctivo, soporte, evolutivo' && $root.PlanMantenimientoPreventivo() === 'si') ||
                        (name() === 'TÉCNICA - Inventario de equipos, herramientas, vehículos y/o maquinarias' && $root.InventarioEquipos() === 'si') ||
                        (name() === 'TÉCNICA - Acreditaciones, Permisos, Autorizaciones' && $root.AcreditacionesPermisos() === 'si') ||
                        (name() === 'TÉCNICA - Requerimientos tecnológicos de hardware, software y/o conectividad' && $root.RequerimientosTecnologicos() === 'si') ||
                        (name() === 'TÉCNICA - Requisitos del personal, calificaciones, CV, certificaciones, experiencia, capacitación, etc' && $root.RequisitosPersonal() === 'si') ||
                        (name() === 'TÉCNICA - Organigrama / Equipo de Trabajo / Niveles de escalamiento' && $root.OrganigramaEquipo() === 'si') ||
                        (name() === 'TÉCNICA - Valor agregado' && $root.ValorAgregado() === 'si') ||
                        (name() === 'TÉCNICA - Acuerdos de nivel de servicio' && $root.AcuerdosNivelServicio() === 'si') ||

                        (name() === 'HSEQ - Requisitos matriz HSEQ según Anexo 2' && $root.HseqAnexo2() === 'si') ||

                        (name() === 'ECONÓMICA - Referencias comerciales / Acreditación experiencia' && $root.ReferenciasComerciales() === 'si') ||
                        
                        (name() === 'ECONÓMICA - Evaluación riesgo financiero' && $root.RiesgoFinanciero() === 'si') ||

                        (name() === 'TÉCNICA - Ficha de Especificaciones Técnicas' && $root.FichaEspecificaciones() === 'si') ||
                        (name() === 'TÉCNICA - Hojas de seguridad / MSDS' && $root.MsdsHojasSeguridad() === 'si') ||
                        (name() === 'TÉCNICA - Garantía' && $root.Garantia() === 'si') ||
                        (name() === 'TÉCNICA - Envío de muestra' && $root.EnvioMuestra() === 'si') ||
                        (name() === 'TÉCNICA - Cronograma de entrega / Plazo de entrega' && $root.CronogramaEntrega() === 'si') ||
                        (name() === 'TÉCNICA - Carta de representante de la marca y/o distribuidor autorizado' && $root.CartaRepresentanteMarca() === 'si') ||
                        (name() === 'TÉCNICA - Soporte Post Venta' && $root.SoportePostVenta() === 'si') ||
                        (name() === 'TÉCNICA - Lugar y forma de entrega' && $root.LugarFormaEntrega() === 'si') ||

                        (name() === 'Listado de equipos y herramientas' && $root.ListadoEquiposHerramientas() === 'si') ||
                        (name() === 'Equipo humano y competencias' && $root.EquipoHumanoCompetencias() === 'si') ||
                        (name() === 'Balances y estados de resultados' && $root.BalancesEstadosResultados() === 'si') ||
                        (name() === 'Estatuto o contrato social' && $root.EstatutoContratoSocial() === 'si') ||
                        (name() === 'Actas de designación de autoridades' && $root.ActasDesignacionAutoridades() === 'si')





                        -->
                        <td class="col-md-3 text-center vertical-align-middle" data-bind="text: name"></td>
                        <td class="col-md-6 text-center vertical-align-middle">
                            <a data-bind="click: $root.downloadFile.bind($data, filename(), 'oferente', $root.OferenteId())"
                                download class="btn btn-xl green" title="Descargar">
                                Descargar
                                <i class="fa fa-download"></i>
                            </a>
                        </td>
                        <!-- /ko -->
                    </tr>
                </tbody>
            </table>
            <!-- ko if: declinated() -->
            <div class="note note-danger">
                <h4 class="text-center"
                    data-bind="text: 'Ud ha declinado su participación del concurso el dia '+ fechaDeclinacion() + ', dejando el siguiente comentario: ' + comentarioDeclinacion()">
                </h4>
            </div>
            <!-- /ko -->
            <!-- /ko -->
            <!-- /ko -->
            <!-- ko if: $root.EnableTechnical() -->
            <table class="table table-striped table-bordered">
                <tbody>
                    <tr>
                        <td class="col-md-10 vertical-align-middle" colspan="3">
                            <div class="form-group">
                                <label class="control-label visible-ie8 visible-ie9" style="display: block;">
                                    Máximo 5000 caracteres
                                </label>
                                <textarea class="form-control placeholder-no-fix" maxlength="5000" rows="3"
                                    style="resize: none;" id="maxlength_textarea" data-bind="value: comentario">
                                        </textarea>
                            </div>
                        </td>
                    </tr>
                </tbody>
                <tbody data-bind="foreach: documents()">
                    <tr>
                        <!-- ko if: 
                        (

                            name() !== 'Lista de sub contratistas' && 
                            name() !== 'Certificado de visita de obra' && 

                            name() !== 'Entrega de documentación para evaluación y Alta de proveedor' &&
                            name() !== 'Cumplimiento de requisitos legales y reglamentos aplicables' &&
                            name() !== 'Experiencia y referencias comerciales' &&
                            name() !== 'Documentacion REPSE' &&
                            name() !== 'Alcance' &&
                            name() !== 'Garantías' &&
                            name() !== 'Forma de pago' &&
                            name() !== 'Tiempo de fabricación e instalación de cocinas' &&
                            name() !== 'Ficha técnica. (Materiales, especificaciones y características de la propuesta)' &&


                            name() !== 'Cronograma de trabajo' && 
                            name() !== 'Seguro de Caución' && 
                            name() !== 'Bases y condiciones Firmado' && 
                            name() !== 'Condiciones Generales Firmado' && 
                            name() !== 'Pliego Técnico Firmado' && 
                            name() !== 'Acuerdo de Confidencialidad Firmado' && 
                            name() !== 'Legajo Impositivo' && 
                            name() !== 'Referencias comerciales' && 
                            name() !== 'Reporte Accidentes' &&
                            name() !== 'Envío de muestras' &&
                            name() !== 'NOM-251-SSA1-2009'&&
                            name() !== 'Distintivo H'&&
                            name() !== 'Filtros Sanitarios Trimestrales a los empleados'&&
                            name() !== 'Documentación REPSE'&&
                            name() !== 'Póliza de seguro responsabilidad civil'&&
                            name() !== 'Prima de riesgo 5 millones' &&
                            name() !== 'Referencias Comerciales' &&
                            name() !== 'Organigrama de obra' &&
                            name() !== 'Equipos y herramientas' &&
                            name() !== 'Cronograma de obra' &&
                            name() !== 'Memoria técnica' &&
                            name() !== 'Antecedentes de obras similares' &&
                            name() !== 'Ficha Técnica de la tarima' &&
                            name() !== 'Licencia Ambiental integral (LAI)' &&
                            name() !== 'Cumplimiento NOM-144 SEMARNAT 2017' &&
                            name() !== 'Acreditación legal con la procedencia de la madera' &&
                            name() !== 'Último balance de la empresa' &&
                            name() !== 'Ultimas 3 DDJJ de IVA' &&
                            name() !== 'Constancia de CUIT' &&
                            name() !== 'Brochure de antecedentes de edificios incluyendo obras en curso' &&
                            name() !== 'Organigrama de la empresa (puestos claves)' &&
                            name() !== 'Organigrama previsto para la obra' &&
                            name() !== 'Listado de subcontratistas por rubro' &&
                            name() !== 'Gestión de H&S (incluir indicadores, procedimientos, detalle de personal, etc.)' &&
                            name() !== 'Listado de máquinas y equipos a utilizar' &&
                            name() !== 'TÉCNICA - Propuesta Técnica / Procedimientos / Metodologías / Técnicas aplicadas' &&
                            name() !== 'TÉCNICA - Plan de mantenimiento preventivo, correctivo, soporte, evolutivo' &&
                            name() !== 'TÉCNICA - Inventario de equipos, herramientas, vehículos y/o maquinarias' &&
                            name() !== 'TÉCNICA - Acreditaciones, Permisos, Autorizaciones' &&
                            name() !== 'TÉCNICA - Requerimientos tecnológicos de hardware, software y/o conectividad' &&
                            name() !== 'TÉCNICA - Requisitos del personal, calificaciones, CV, certificaciones, experiencia, capacitación, etc' &&
                            name() !== 'TÉCNICA - Organigrama / Equipo de Trabajo / Niveles de escalamiento' &&
                            name() !== 'TÉCNICA - Valor agregado' &&
                            name() !== 'TÉCNICA - Acuerdos de nivel de servicio' &&
                            name() !== 'HSEQ - Requisitos matriz HSEQ según Anexo 2' &&
                            name() !== 'ECONÓMICA - Referencias comerciales / Acreditación experiencia' &&
                            
                            name() !== 'ECONÓMICA - Evaluación riesgo financiero' &&
                            name() !== 'TÉCNICA - Ficha de Especificaciones Técnicas' &&
                            name() !== 'TÉCNICA - Hojas de seguridad / MSDS' &&
                            name() !== 'TÉCNICA - Garantía' &&
                            name() !== 'TÉCNICA - Envío de muestra' &&
                            name() !== 'TÉCNICA - Cronograma de entrega / Plazo de entrega' &&
                            name() !== 'TÉCNICA - Carta de representante de la marca y/o distribuidor autorizado' &&
                            name() !== 'TÉCNICA - Soporte Post Venta' &&
                            name() !== 'TÉCNICA - Lugar y forma de entrega' &&

                            name() !== 'Listado de equipos y herramientas' &&
                            name() !== 'Equipo humano y competencias' &&
                            name() !== 'Balances y estados de resultados' &&
                            name() !== 'Estatuto o contrato social' &&
                            name() !== 'Actas de designación de autoridades' &&
                            name() !== 'TÉCNICA - Acuerdo de confidencialidad FIRMADO'


                        ) || 



                        (name() === 'Lista de sub contratistas' && $root.ListaProveedores() === 'si') ||
                        (name() === 'Certificado de visita de obra' && $root.CertificadoVisitaObra() === 'si') ||

                        (name() === 'Entrega de documentación para evaluación y Alta de proveedor' && $root.EntregaDocEvaluacion() === 'si') ||
                        (name() === 'Cumplimiento de requisitos legales y reglamentos aplicables' && $root.RequisitosLegales() === 'si') ||
                        (name() === 'Experiencia y referencias comerciales' && $root.ExperienciaYReferencias() === 'si') ||
                        (name() === 'Documentacion REPSE' && $root.DocumentacionREPSE() === 'si') ||
                        (name() === 'Alcance' && $root.Alcance() === 'si') ||
                        (name() === 'Forma de pago' && $root.FormaPago() === 'si') ||
                        (name() === 'Tiempo de fabricación e instalación de cocinas' && $root.TiempoFabricacion() === 'si') ||
                        (name() === 'Ficha técnica. (Materiales, especificaciones y características de la propuesta)' && $root.FichaTecnica() === 'si') ||
                        (name() === 'Garantías' && $root.Garantias() === 'si') ||




                        (name() === 'Cronograma de trabajo' && $root.DiagramaGant() === 'si') || 

                        (name() === 'Seguro de Caución' && $root.SeguroCaucion() === 'si') ||

                        (name() === 'Listado de equipos y herramientas' && $root.ListadoEquiposHerramientas() === 'si') ||
                        (name() === 'Equipo humano y competencias' && $root.EquipoHumanoCompetencias() === 'si') ||
                        (name() === 'Balances y estados de resultados' && $root.BalancesEstadosResultados() === 'si') ||
                        (name() === 'Estatuto o contrato social' && $root.EstatutoContratoSocial() === 'si') ||
                        (name() === 'Actas de designación de autoridades' && $root.ActasDesignacionAutoridades() === 'si') ||

                        (name() === 'Bases y condiciones Firmado' && $root.BaseCondiciones() === 'si') ||
                        (name() === 'Condiciones Generales Firmado' && $root.CondicionesGenerales() === 'si') ||
                        (name() === 'Pliego Técnico Firmado' && $root.PliegoTecnico() === 'si') ||
                        (name() === 'Acuerdo de Confidencialidad Firmado' && $root.Confidencialidad() === 'si') ||
                        (name() === 'Legajo Impositivo' && $root.LegajoImpositivo() === 'si') ||
                        (name() === 'Referencias comerciales' && $root.Antecedentes() === 'si') ||
                        (name() === 'Reporte Accidentes' && $root.ReporteAccidentes() === 'si') ||
                        (name() === 'Envío de muestras' && $root.EnvioMuestra() === 'si') ||
                        (name() === 'NOM-251-SSA1-2009' && $root.nom251() === 'si') ||
                        (name() === 'Distintivo H' && $root.distintivo() === 'si') ||
                        (name() === 'Filtros Sanitarios Trimestrales a los empleados' && $root.filtros_sanitarios() === 'si') ||
                        (name() === 'Documentación REPSE' && $root.repse() === 'si') ||
                        (name() === 'Póliza de seguro responsabilidad civil' && $root.poliza() === 'si') ||
                        (name() === 'Prima de riesgo 5 millones' && $root.primariesgo() === 'si') || 
                        (name() === 'Referencias Comerciales' && $root.obras_referencias() === 'si') ||
                        (name() === 'Organigrama de obra' && $root.obras_organigrama() === 'si') ||
                        (name() === 'Equipos y herramientas' && $root.obras_equipos() === 'si') ||
                        (name() === 'Cronograma de obra' && $root.obras_cronograma() === 'si') ||
                        (name() === 'Memoria técnica' && $root.obras_memoria() === 'si') ||
                        (name() === 'Antecedentes de obras similares' && $root.obras_antecedentes() === 'si') ||
                        (name() === 'Ficha Técnica de la tarima' && $root.tarima_ficha_tecnica() === 'si') ||
                        (name() === 'Licencia Ambiental integral (LAI)' && $root.tarima_licencia() === 'si') ||
                        (name() === 'Cumplimiento NOM-144 SEMARNAT 2017' && $root.tarima_nom_144() === 'si') ||
                        (name() === 'Acreditación legal con la procedencia de la madera' && $root.tarima_acreditacion() === 'si') || 
                        (name() === 'Último balance de la empresa' && $root.edificio_balance() === 'si') ||
                        (name() === 'Ultimas 3 DDJJ de IVA' && $root.edificio_iva() === 'si') ||
                        (name() === 'Constancia de CUIT' && $root.edificio_cuit() === 'si') ||
                        (name() === 'Brochure de antecedentes de edificios incluyendo obras en curso' && $root.edificio_brochure() === 'si') ||
                        (name() === 'Organigrama de la empresa (puestos claves)' && $root.edificio_organigrama() === 'si') ||
                        (name() === 'Organigrama previsto para la obra' && $root.edificio_organigrama_obra() === 'si') ||
                        (name() === 'Listado de subcontratistas por rubro' && $root.edificio_subcontratistas() === 'si') ||
                        (name() === 'Gestión de H&S (incluir indicadores, procedimientos, detalle de personal, etc.)' && $root.edificio_gestion() === 'si') ||
                        (name() === 'Listado de máquinas y equipos a utilizar' && $root.edificio_maquinas() === 'si') ||
                        (name() === 'TÉCNICA - Propuesta Técnica / Procedimientos / Metodologías / Técnicas aplicadas' && $root.PropuestaTecnica() === 'si') ||
                        (name() === 'TÉCNICA - Plan de mantenimiento preventivo, correctivo, soporte, evolutivo' && $root.PlanMantenimientoPreventivo() === 'si') ||
                        (name() === 'TÉCNICA - Inventario de equipos, herramientas, vehículos y/o maquinarias' && $root.InventarioEquipos() === 'si') ||
                        (name() === 'TÉCNICA - Acreditaciones, Permisos, Autorizaciones' && $root.AcreditacionesPermisos() === 'si') ||
                        (name() === 'TÉCNICA - Requerimientos tecnológicos de hardware, software y/o conectividad' && $root.RequerimientosTecnologicos() === 'si') ||
                        (name() === 'TÉCNICA - Requisitos del personal, calificaciones, CV, certificaciones, experiencia, capacitación, etc' && $root.RequisitosPersonal() === 'si') ||
                        (name() === 'TÉCNICA - Organigrama / Equipo de Trabajo / Niveles de escalamiento' && $root.OrganigramaEquipo() === 'si') ||
                        (name() === 'TÉCNICA - Valor agregado' && $root.ValorAgregado() === 'si') ||
                        (name() === 'TÉCNICA - Acuerdos de nivel de servicio' && $root.AcuerdosNivelServicio() === 'si') ||

                        (name() === 'HSEQ - Requisitos matriz HSEQ según Anexo 2' && $root.HseqAnexo2() === 'si') ||

                        (name() === 'ECONÓMICA - Referencias comerciales / Acreditación experiencia' && $root.ReferenciasComerciales() === 'si') ||
                        (name() === 'ECONÓMICA - Evaluación riesgo financiero' && $root.RiesgoFinanciero() === 'si') ||

                        (name() === 'TÉCNICA - Ficha de Especificaciones Técnicas' && $root.FichaEspecificaciones() === 'si') ||
                        (name() === 'TÉCNICA - Hojas de seguridad / MSDS' && $root.MsdsHojasSeguridad() === 'si') ||
                        (name() === 'TÉCNICA - Garantía' && $root.Garantia() === 'si') ||
                        (name() === 'TÉCNICA - Envío de muestra' && $root.EnvioMuestra() === 'si') ||
                        (name() === 'TÉCNICA - Cronograma de entrega / Plazo de entrega' && $root.CronogramaEntrega() === 'si') ||
                        (name() === 'TÉCNICA - Carta de representante de la marca y/o distribuidor autorizado' && $root.CartaRepresentanteMarca() === 'si') ||
                        (name() === 'TÉCNICA - Soporte Post Venta' && $root.SoportePostVenta() === 'si') ||
                        (name() === 'TÉCNICA - Lugar y forma de entrega' && $root.LugarFormaEntrega() === 'si') ||
                        (name() === 'TÉCNICA - Acuerdo de confidencialidad FIRMADO' && $root.NdaFirmado() === 'si')
                        -->
                        <td class="col-md-3 text-center vertical-align-middle" data-bind="text: name"></td>
                        <!-- ko if: $root.EnableTechnical() -->
                        <td class="col-md-6 text-center vertical-align-middle">
                            <input id="input-700" data-bind="fileinput: $data, fileinputOptions: {
                                        uploadUrl: '/media/file/upload',
                                        initialCaption: filename() ? filename() : [],
                                        uploadExtraData: {
                                            UserToken: User.Token,
                                            path: $root.FilePathOferente(),
                                            concurso_id: $root.IdConcurso(),
                                            concurso_nombre: $root.Nombre() 
                                        },
                                        initialPreview: filename() ? [$root.FilePathOferente() + filename()] : [],
                                        allowedFileExtensions: ['jpg', 'jpeg', 'png', 'pdf', 'zip', 'rar', 'doc', 'docx', 'xls', 'xlsx', 'dwg']

                                    }" name="file[]" type="file">
                        </td>
                        <!-- /ko -->
                        <td class="col-md-6 text-center vertical-align-middle">
                            <!-- ko if: filename() -->
                            <a data-bind="click: $root.downloadFile.bind($data, filename(), 'oferente', $root.OferenteId())"
                                download class="btn btn-xl green" title="Descargar">
                                Descargar
                                <i class="fa fa-download"></i>
                            </a>
                            <!-- /ko -->
                            <!-- ko if: !filename() -->
                            <span class="label label-danger">Sin archivo</span>
                            <!-- /ko -->
                        </td>
                        <!-- /ko -->
                    </tr>
                </tbody>
            </table>

            <!-- ko ifnot: declinated() -->
            <div class="row">
                <div class="col-sm-12">
                    <table class="table table-striped table-bordered" id="ListaConcursosGo">
                        <tbody>
                            <tr>
                                <td colspan="3" class="col-md-2 text-center vertical-align-middle">
                                    <!-- ko ifnot: presented() -->
                                    <button type="button" class="btn btn-lg green" title="Enviar propuesta técnica"
                                        data-bind="click: $root.TechnicalSend.bind($data, false)">
                                        Enviar propuesta técnica
                                        <i class="fa fa-send"></i>
                                    </button>
                                    <!-- /ko -->
                                    <button type="button" class="btn btn-lg default" title="Guardar documentación"
                                        data-bind="click: $root.TechnicalSend.bind($data, true)">
                                        Guardar documentación
                                        <i class="fa fa-save"></i>
                                    </button>
                                    <button type="button" class="btn btn-lg red" title="Declinar Participación"
                                        data-bind="click: $root.RejectParticipation.bind($data, 'rechazar')">
                                        Declinar Participación
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- /ko -->
            <!-- /ko -->
            <!-- ko if: $root.Rechazado() && !$root.HasTecnicaAprobada() -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="alert alert-danger">
                        Usted no pasó la calificación técnica, ¡gracias por participar!
                        <p data-bind="text:'Comentarios del comprador: '+ evaluacion().comentario"></p>
                    </div>
                </div>
            </div>
            <!-- /ko -->
            <!-- ko if: presented() && !declinated() &&
                    !$root.HasTecnicaAprobada() && 
                    !$root.Rechazado() 
                -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="alert alert-success text-center">
                        Evaluación técnica pendiente de revisión.
                    </div>
                </div>
            </div>
            <!-- /ko -->
            <!-- /ko -->

            <!-- ko ifnot: active() -->
            <div class="note note-info">
                <h4 class="block">
                    Comentarios Proveedor
                </h4>
                <p data-bind="text: comentario()"></p>
            </div>
            <table class="table table-striped table-bordered">
                <tbody data-bind="foreach: documents()">
                    <tr>
                        <!-- ko if: 
                        (        

                            name() !== 'Lista de sub contratistas' && 
                            name() !== 'Certificado de visita de obra' && 

                            name() !== 'Entrega de documentación para evaluación y Alta de proveedor' &&
                            name() !== 'Cumplimiento de requisitos legales y reglamentos aplicables' &&
                            name() !== 'Experiencia y referencias comerciales' &&
                            name() !== 'Documentacion REPSE' &&
                            name() !== 'Alcance' &&
                            name() !== 'Garantías' &&
                            name() !== 'Forma de pago' &&
                            name() !== 'Tiempo de fabricación e instalación de cocinas' &&
                            name() !== 'Ficha técnica. (Materiales, especificaciones y características de la propuesta)' &&

                            name() !== 'Cronograma de trabajo' && 
                            name() !== 'Seguro de Caución' && 
                            name() !== 'Bases y condiciones Firmado' && 
                            name() !== 'Condiciones Generales Firmado' && 
                            name() !== 'Pliego Técnico Firmado' && 
                            name() !== 'Acuerdo de Confidencialidad Firmado' && 
                            name() !== 'Legajo Impositivo' && 
                            name() !== 'Referencias comerciales' && 
                            name() !== 'Reporte Accidentes' &&
                            name() !== 'Envío de muestras' &&
                            name() !== 'NOM-251-SSA1-2009'&&
                            name() !== 'Distintivo H'&&
                            name() !== 'Filtros Sanitarios Trimestrales a los empleados'&&
                            name() !== 'Documentación REPSE'&&
                            name() !== 'Póliza de seguro responsabilidad civil'&&
                            name() !== 'Prima de riesgo 5 millones' &&
                            name() !== 'Referencias Comerciales' &&
                            name() !== 'Organigrama de obra' &&
                            name() !== 'Equipos y herramientas' &&
                            name() !== 'Cronograma de obra' &&
                            name() !== 'Memoria técnica' &&
                            name() !== 'Antecedentes de obras similares' &&
                            name() !== 'Ficha Técnica de la tarima' &&
                            name() !== 'Licencia Ambiental integral (LAI)' &&
                            name() !== 'Cumplimiento NOM-144 SEMARNAT 2017' &&
                            name() !== 'Acreditación legal con la procedencia de la madera' &&
                            name() !== 'Último balance de la empresa' &&
                            name() !== 'Ultimas 3 DDJJ de IVA' &&
                            name() !== 'Constancia de CUIT' &&
                            name() !== 'Brochure de antecedentes de edificios incluyendo obras en curso' &&
                            name() !== 'Organigrama de la empresa (puestos claves)' &&
                            name() !== 'Organigrama previsto para la obra' &&
                            name() !== 'Listado de subcontratistas por rubro' &&
                            name() !== 'Gestión de H&S (incluir indicadores, procedimientos, detalle de personal, etc.)' &&
                            name() !== 'Listado de máquinas y equipos a utilizar' &&
                            name() !== 'TÉCNICA - Propuesta Técnica / Procedimientos / Metodologías / Técnicas aplicadas' &&
                            name() !== 'TÉCNICA - Plan de mantenimiento preventivo, correctivo, soporte, evolutivo' &&
                            name() !== 'TÉCNICA - Inventario de equipos, herramientas, vehículos y/o maquinarias' &&
                            name() !== 'TÉCNICA - Acreditaciones, Permisos, Autorizaciones' &&
                            name() !== 'TÉCNICA - Requerimientos tecnológicos de hardware, software y/o conectividad' &&
                            name() !== 'TÉCNICA - Acuerdo de confidencialidad FIRMADO' &&
                            name() !== 'TÉCNICA - Requisitos del personal, calificaciones, CV, certificaciones, experiencia, capacitación, etc' &&
                            name() !== 'TÉCNICA - Organigrama / Equipo de Trabajo / Niveles de escalamiento' &&
                            name() !== 'TÉCNICA - Valor agregado' &&
                            name() !== 'TÉCNICA - Acuerdos de nivel de servicio' &&
                            name() !== 'HSEQ - Requisitos matriz HSEQ según Anexo 2' &&
                            name() !== 'ECONÓMICA - Referencias comerciales / Acreditación experiencia' &&
                            
                            name() !== 'ECONÓMICA - Evaluación riesgo financiero' &&
                            name() !== 'TÉCNICA - Ficha de Especificaciones Técnicas' &&
                            name() !== 'TÉCNICA - Hojas de seguridad / MSDS' &&
                            name() !== 'TÉCNICA - Garantía' &&
                            name() !== 'TÉCNICA - Envío de muestra' &&
                            name() !== 'TÉCNICA - Cronograma de entrega / Plazo de entrega' &&
                            name() !== 'TÉCNICA - Carta de representante de la marca y/o distribuidor autorizado' &&
                            name() !== 'TÉCNICA - Soporte Post Venta' &&
                            name() !== 'TÉCNICA - Lugar y forma de entrega' &&

                            name() !== 'Listado de equipos y herramientas' &&
                            name() !== 'Equipo humano y competencias' &&
                            name() !== 'Balances y estados de resultados' &&
                            name() !== 'Estatuto o contrato social' &&
                            name() !== 'Actas de designación de autoridades'


                        ) || 

                        (name() === 'Cronograma de trabajo' && $root.DiagramaGant() === 'si') || 

                        (name() === 'Lista de sub contratistas' && $root.ListaProveedores() === 'si') ||
                        (name() === 'Certificado de visita de obra' && $root.CertificadoVisitaObra() === 'si') ||


                        (name() === 'Entrega de documentación para evaluación y Alta de proveedor' && $root.EntregaDocEvaluacion() === 'si') ||
                        (name() === 'Cumplimiento de requisitos legales y reglamentos aplicables' && $root.RequisitosLegales() === 'si') ||
                        (name() === 'Experiencia y referencias comerciales' && $root.ExperienciaYReferencias() === 'si') ||
                        (name() === 'Documentacion REPSE' && $root.DocumentacionREPSE() === 'si') ||
                        (name() === 'Alcance' && $root.Alcance() === 'si') ||
                        (name() === 'Forma de pago' && $root.FormaPago() === 'si') ||
                        (name() === 'Tiempo de fabricación e instalación de cocinas' && $root.TiempoFabricacion() === 'si') ||
                        (name() === 'Ficha técnica. (Materiales, especificaciones y características de la propuesta)' && $root.FichaTecnica() === 'si') ||
                        (name() === 'Garantías' && $root.Garantias() === 'si') ||



                        (name() === 'Seguro de Caución' && $root.SeguroCaucion() === 'si') ||
                        (name() === 'Bases y condiciones Firmado' && $root.BaseCondiciones() === 'si') ||
                        (name() === 'Condiciones Generales Firmado' && $root.CondicionesGenerales() === 'si') ||
                        (name() === 'Pliego Técnico Firmado' && $root.PliegoTecnico() === 'si') ||
                        (name() === 'Acuerdo de Confidencialidad Firmado' && $root.Confidencialidad() === 'si') ||
                        (name() === 'Legajo Impositivo' && $root.LegajoImpositivo() === 'si') ||
                        (name() === 'Referencias comerciales' && $root.Antecedentes() === 'si') ||
                        (name() === 'Reporte Accidentes' && $root.ReporteAccidentes() === 'si') ||
                        (name() === 'Envío de muestras' && $root.EnvioMuestra() === 'si') ||
                        (name() === 'NOM-251-SSA1-2009' && $root.nom251() === 'si') ||
                        (name() === 'Distintivo H' && $root.distintivo() === 'si') ||
                        (name() === 'Filtros Sanitarios Trimestrales a los empleados' && $root.filtros_sanitarios() === 'si') ||
                        (name() === 'Documentación REPSE' && $root.repse() === 'si') ||
                        (name() === 'Póliza de seguro responsabilidad civil' && $root.poliza() === 'si') ||
                        (name() === 'Prima de riesgo 5 millones' && $root.primariesgo() === 'si') || 
                        (name() === 'Referencias Comerciales' && $root.obras_referencias() === 'si') ||
                        (name() === 'Organigrama de obra' && $root.obras_organigrama() === 'si') ||
                        (name() === 'Equipos y herramientas' && $root.obras_equipos() === 'si') ||
                        (name() === 'Cronograma de obra' && $root.obras_cronograma() === 'si') ||
                        (name() === 'Memoria técnica' && $root.obras_memoria() === 'si') ||
                        (name() === 'Antecedentes de obras similares' && $root.obras_antecedentes() === 'si') ||
                        (name() === 'Ficha Técnica de la tarima' && $root.tarima_ficha_tecnica() === 'si') ||
                        (name() === 'Licencia Ambiental integral (LAI)' && $root.tarima_licencia() === 'si') ||
                        (name() === 'Cumplimiento NOM-144 SEMARNAT 2017' && $root.tarima_nom_144() === 'si') ||
                        (name() === 'Acreditación legal con la procedencia de la madera' && $root.tarima_acreditacion() === 'si') ||
                        (name() === 'Último balance de la empresa' && $root.edificio_balance() === 'si') ||
                        (name() === 'Ultimas 3 DDJJ de IVA' && $root.edificio_iva() === 'si') ||
                        (name() === 'Constancia de CUIT' && $root.edificio_cuit() === 'si') ||
                        (name() === 'Brochure de antecedentes de edificios incluyendo obras en curso' && $root.edificio_brochure() === 'si') ||
                        (name() === 'Organigrama de la empresa (puestos claves)' && $root.edificio_organigrama() === 'si') ||
                        (name() === 'Organigrama previsto para la obra' && $root.edificio_organigrama_obra() === 'si') ||
                        (name() === 'Listado de subcontratistas por rubro' && $root.edificio_subcontratistas() === 'si') ||
                        (name() === 'Gestión de H&S (incluir indicadores, procedimientos, detalle de personal, etc.)' && $root.edificio_gestion() === 'si') ||
                        (name() === 'Listado de máquinas y equipos a utilizar' && $root.edificio_maquinas() === 'si') ||
                        (name() === 'TÉCNICA - Propuesta Técnica / Procedimientos / Metodologías / Técnicas aplicadas' && $root.PropuestaTecnica() === 'si') ||
                        (name() === 'TÉCNICA - Plan de mantenimiento preventivo, correctivo, soporte, evolutivo' && $root.PlanMantenimientoPreventivo() === 'si') ||
                        (name() === 'TÉCNICA - Inventario de equipos, herramientas, vehículos y/o maquinarias' && $root.InventarioEquipos() === 'si') ||
                        (name() === 'TÉCNICA - Acreditaciones, Permisos, Autorizaciones' && $root.AcreditacionesPermisos() === 'si') ||
                        (name() === 'TÉCNICA - Requerimientos tecnológicos de hardware, software y/o conectividad' && $root.RequerimientosTecnologicos() === 'si') ||
                        (name() === 'TÉCNICA - Requisitos del personal, calificaciones, CV, certificaciones, experiencia, capacitación, etc' && $root.RequisitosPersonal() === 'si') ||
                        (name() === 'TÉCNICA - Organigrama / Equipo de Trabajo / Niveles de escalamiento' && $root.OrganigramaEquipo() === 'si') ||
                        (name() === 'TÉCNICA - Valor agregado' && $root.ValorAgregado() === 'si') ||
                        (name() === 'TÉCNICA - Acuerdos de nivel de servicio' && $root.AcuerdosNivelServicio() === 'si') ||

                        (name() === 'HSEQ - Requisitos matriz HSEQ según Anexo 2' && $root.HseqAnexo2() === 'si') ||

                        (name() === 'ECONÓMICA - Referencias comerciales / Acreditación experiencia' && $root.ReferenciasComerciales() === 'si') ||
                        
                        (name() === 'ECONÓMICA - Evaluación riesgo financiero' && $root.RiesgoFinanciero() === 'si') ||

                        (name() === 'TÉCNICA - Ficha de Especificaciones Técnicas' && $root.FichaEspecificaciones() === 'si') ||
                        (name() === 'TÉCNICA - Hojas de seguridad / MSDS' && $root.MsdsHojasSeguridad() === 'si') ||
                        (name() === 'TÉCNICA - Garantía' && $root.Garantia() === 'si') ||
                        (name() === 'TÉCNICA - Envío de muestra' && $root.EnvioMuestra() === 'si') ||
                        (name() === 'TÉCNICA - Cronograma de entrega / Plazo de entrega' && $root.CronogramaEntrega() === 'si') ||
                        (name() === 'TÉCNICA - Carta de representante de la marca y/o distribuidor autorizado' && $root.CartaRepresentanteMarca() === 'si') ||
                        (name() === 'TÉCNICA - Soporte Post Venta' && $root.SoportePostVenta() === 'si') ||
                        (name() === 'TÉCNICA - Lugar y forma de entrega' && $root.LugarFormaEntrega() === 'si') ||
                        (name() === 'TÉCNICA - Acuerdo de confidencialidad FIRMADO' && $root.NdaFirmado() === 'si') ||
                        (name() === 'Listado de equipos y herramientas' && $root.ListadoEquiposHerramientas() === 'si') ||
                        (name() === 'Equipo humano y competencias' && $root.EquipoHumanoCompetencias() === 'si') ||
                        (name() === 'Balances y estados de resultados' && $root.BalancesEstadosResultados() === 'si') ||
                        (name() === 'Estatuto o contrato social' && $root.EstatutoContratoSocial() === 'si') ||
                        (name() === 'Actas de designación de autoridades' && $root.ActasDesignacionAutoridades() === 'si') 


                        -->
                        <td class="col-md-3 text-center vertical-align-middle" data-bind="text: name"></td>
                        <td class="col-md-6 text-center vertical-align-middle">
                            <a data-bind="click: $root.downloadFile.bind($data, filename(), 'oferente', $root.OferenteId())"
                                download class="btn btn-xl green" title="Descargar">
                                Descargar
                                <i class="fa fa-download"></i>
                            </a>
                        </td>
                        <!-- /ko -->
                    </tr>
                </tbody>
            </table>
            <div class="note note-info">
                <h4 class="block">
                    Comentarios Comprador
                </h4>
                <p data-bind="text: comentarioNuevaRonda()"></p>
            </div>
            <!-- /ko -->
        </div>
    </div>
</div>