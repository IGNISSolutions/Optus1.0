<div class="row">
    <!-- ko if: IsOnline() -->
    <div class="col-md-12">
        <div class="form-group required" data-bind="validationElement: Entity.Moneda">
            <label class="control-label visible-ie8 visible-ie9" style="display: block;">Moneda de las
                cotizaciones</label>
            <div class="selectRequerido">
                <select
                    data-bind="value: Entity.Moneda, valueAllowUnset: true, options: Entity.Monedas, optionsText: 'text', optionsValue: 'id', select2: { placeholder: 'Seleccionar...' }, disable: ReadOnly()">
                </select>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group required">
            <label class="control-label visible-ie8 visible-ie9" style="display: block;">Tipo de subasta</label>
            <select
                data-bind="value: Entity.TipoValorOfertar, valueAllowUnset: true, options: Entity.TiposValoresOfertar, optionsText: 'text', optionsValue: 'id', select2: { placeholder: 'Seleccionar...' }, disable: ReadOnly()">
            </select>
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label class="control-label visible-ie8 visible-ie9" style="display: block;">¿Chat durante el
                concurso?</label>
            SI <input type="radio" value="si" data-bind="checked: Entity.Chat, disable: ReadOnly()">
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            NO <input type="radio" value="no" data-bind="checked: Entity.Chat, disable: ReadOnly()">
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label class="control-label visible-ie8 visible-ie9" style="display: block;">¿Visualizar cantidad de
                oferentes que están participando?</label>
            SI <input type="radio" value="si"
                data-bind="checked: Entity.VerNumOferentesParticipan, disable: ReadOnly()">
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            NO <input type="radio" value="no"
                data-bind="checked: Entity.VerNumOferentesParticipan, disable: ReadOnly()">
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label class="control-label visible-ie8 visible-ie9" style="display: block;">¿Visualizar la oferta que va
                ganando y sus respectivos cambios?</label>
            SI <input type="radio" value="si" data-bind="checked: Entity.VerOfertaGanadora, disable: ReadOnly()">
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            NO <input type="radio" value="no" data-bind="checked: Entity.VerOfertaGanadora, disable: ReadOnly()">
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label class="control-label visible-ie8 visible-ie9" style="display: block;">¿Los oferentes puedan ver en
                que puesto está su oferta?</label>
            SI <input type="radio" value="si" data-bind="checked: Entity.VerRanking, disable: ReadOnly()">
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            NO <input type="radio" value="no" data-bind="checked: Entity.VerRanking, disable: ReadOnly()">
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label class="control-label visible-ie8 visible-ie9" style="display: block;">¿Los oferentes pueden ver el
                tiempo restante para finalizar el concurso?</label>
            SI <input type="radio" value="si" data-bind="checked: Entity.VerTiempoRestante, disable: ReadOnly()">
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            NO <input type="radio" value="no" data-bind="checked: Entity.VerTiempoRestante, disable: ReadOnly()">
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label class="control-label visible-ie8 visible-ie9" style="display: block;">¿Permitir que los oferentes
                puedan anular su última oferta hasta 1 minuto después de efectuada?</label>
            SI <input type="radio" value="si" data-bind="checked: Entity.PermitirAnularOferta, disable: ReadOnly()">
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            NO <input type="radio" value="no" data-bind="checked: Entity.PermitirAnularOferta, disable: ReadOnly()">
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label class="control-label visible-ie8 visible-ie9" style="display: block;">¿Vista ciega durante la
                subasta?</label>
            SI <input type="radio" value="si" data-bind="checked: Entity.SubastaVistaCiega, disable: ReadOnly()">
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            NO <input type="radio" value="no" data-bind="checked: Entity.SubastaVistaCiega, disable: ReadOnly()">
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label class="control-label visible-ie8 visible-ie9" style="display: block;">Solo aceptar ofertas mejores a
                las ya realizadas</label>
            SI <input type="radio" value="si" data-bind="checked: Entity.SoloOfertasMejores, disable: ReadOnly()">
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            NO <input type="radio" value="no" data-bind="checked: Entity.SoloOfertasMejores, disable: ReadOnly()">
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group required">
            <label class="control-label visible-ie8 visible-ie9" style="display: block;">Límite inferior para ofertas
                precio unitario</label>
            <input class="form-control placeholder-no-fix" type="number" min="0.01" step="0.01" name=""
                id="precio_minimo" data-bind="value: Entity.PrecioMinimo, disable: ReadOnly()" />
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group required">
            <label class="control-label visible-ie8 visible-ie9" style="display: block;">Unidad mínima para mejorar
                oferta</label>
            <input class="form-control placeholder-no-fix" type="number" min="0.01" step="0.01" name=""
                id="unidad_minima" data-bind="value: Entity.UnidadMinima, disable: ReadOnly()" />
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group required">
            <label class="control-label visible-ie8 visible-ie9" style="display: block;">Límite superior para ofertas
                precio unitario</label>
            <input class="form-control placeholder-no-fix" type="number" min="0.01" step="0.01" name=""
                id="precio_maximo"
                data-bind="value: Entity.PrecioMaximo, disable: (ReadOnly() || Entity.PrecioMinimo() < 1)" />
        </div>
    </div>
    <!-- /ko -->

    <!-- ko if: IsSobrecerrado() || IsGo() -->
    <div class="col-md-6">
        <div class="form-group required" data-bind="validationElement: Entity.Moneda">
            <label class="control-label visible-ie8 visible-ie9" style="display: block;">Moneda de las
                cotizaciones</label>
            <div class="selectRequerido">
                <select
                    data-bind="value: Entity.Moneda, valueAllowUnset: true, options: Entity.Monedas, optionsText: 'text', optionsValue: 'id', select2: { placeholder: 'Seleccionar...' }, disable: ReadOnly()">
                </select>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <span>Usuario fiscalizador autoriza ver ofertas</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.concurso_fiscalizado, disable: ReadOnly()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.concurso_fiscalizado, disable: ReadOnly()">
                    NO
                </label>
            </div>
        </div>

        <div id="idSuper" class="form-group">
            <label class="control-label visible-ie8 visible-ie9" style="display: block;">Seleccione Fiscalizador</label>
            <div class="selectRequerido">
                <select
                    data-bind="value: Entity.UsuarioSupervisor, valueAllowUnset: true, options: Entity.UsuariosSupervisores, optionsText: 'text', optionsValue: 'id', select2: { placeholder: 'Seleccionar...' }, disable: IsDisableSuper() || ReadOnly()">
                </select>
            </div>
        </div>
    </div>
    <!-- ko if: IsSobrecerrado() -->
    <div class="col-md-6">
        <div class="form-group col-md-12">
            <span>Apertura sobres con oferentes</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si" data-bind="checked: Entity.Aperturasobre, disable: ReadOnly()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no" data-bind="checked: Entity.Aperturasobre, disable: ReadOnly()">
                    NO
                </label>
            </div>
        </div>

        <div class="form-group col-md-12">
            <span>¿Habilitar proceso de adjudicación anticipado?</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.FinalizarSiOferentesCompletaronEconomicas, disable: ReadOnly() || IsGo()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.FinalizarSiOferentesCompletaronEconomicas, disable: ReadOnly() || IsGo()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-12">
            <span>Planilla estructura de costos</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.EstructuraCostos, disable: ReadOnly() || IsGo()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.EstructuraCostos, disable: ReadOnly() || IsGo()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-12">
            <span>Análisis de Precio Unitario (APU)</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si" data-bind="checked: Entity.Apu, disable: ReadOnly() || IsGo()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no" data-bind="checked: Entity.Apu, disable: ReadOnly() || IsGo()">
                    NO
                </label>
            </div>
        </div>
        <div class="form-group col-md-12">
            <span>Permitir anticipo</span>
            <div class="pull-right">
                <label class="radio-inline">
                    <input type="radio" value="si"
                        data-bind="checked: Entity.CondicionPago, disable: ReadOnly() || IsGo()">
                    SI
                </label>
                <label class="radio-inline">
                    <input type="radio" value="no"
                        data-bind="checked: Entity.CondicionPago, disable: ReadOnly() || IsGo()">
                    NO
                </label>
            </div>
        </div>
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

    </div>
    <!-- /ko -->
    <!-- /ko -->
</div>