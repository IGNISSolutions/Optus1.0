
<div class="row">
    <div class="col-md-6">
        <div class="form-group required" data-bind="validationElement: Entity.FechaLimite">
            <label class="control-label visible-ie8 visible-ie9" style="display: block;">Fecha límite aceptar invitación</label>
            <div class="input-group date form_datetime bs-datetime">
                <input class="form-control" size="16" type="text" data-bind="dateTimePicker: Entity.FechaLimite, dateTimePickerOptions: {
                        format: 'dd-mm-yyyy hh:ii',
                        momentFormat: 'DD-MM-YYYY HH:mm',
                        startDate: Entity.FechaLimite(),
                        value: Entity.FechaLimite(),
                        todayBtn: true,
                        
                    },">
                <span class="input-group-addon">
                    <button class="btn default date-set" type="button">
                        <i class="fa fa-calendar"></i>
                    </button>
                    <button class="btn default" type="button" data-toggle="tooltip" title="Fecha limite para que los proveedores acepten la invitación al concurso">
                        <i class="fa fa-info-circle" aria-hidden="true"></i>
                    </button>
                </span>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group required" data-bind="validationElement: Entity.FinalizacionConsultas">
            <label class="control-label visible-ie8 visible-ie9" style="display: block;">Fecha cierre muro de
                consultas</label>
            <div class="input-group date form_datetime bs-datetime">
                <input class="form-control" size="16" type="text" data-bind="dateTimePicker: Entity.FinalizacionConsultas, dateTimePickerOptions: {
                    format: 'dd-mm-yyyy hh:ii',
                    momentFormat: 'DD-MM-YYYY HH:mm',
                    startDate: Entity.FinalizacionConsultas(),
                    value: Entity.FinalizacionConsultas(),
                    todayBtn: true
                },
                ">
                <span class="input-group-addon">
                    <button class="btn default date-set" type="button">
                        <i class="fa fa-calendar"></i>
                    </button>
                    <button class="btn default" type="button" data-toggle="tooltip" title="La fecha de Muro de Consultas debe ser al menos 48 horas mayor a la fecha limite para aceptar la invitación">
                        <i class="fa fa-info-circle" aria-hidden="true"></i>
                    </button>
                </span>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div 
            id=idFechaLimiteTecnica
            class="form-group" 
            data-bind="validationElement: Entity.FechaLimiteTecnica">
            <label class="control-label visible-ie8 visible-ie9" style="display: block;">Fecha límite oferta técnica</label>
            <div class="input-group date form_datetime bs-datetime">
                <input 
                    class="form-control" 
                    size="16" 
                    type="text" 
                    data-bind="dateTimePicker: Entity.FechaLimiteTecnica, dateTimePickerOptions: {
                        format: 'dd-mm-yyyy hh:ii',
                        momentFormat: 'DD-MM-YYYY HH:mm',
                        startDate: Entity.FechaLimiteTecnica(),
                        value: Entity.FechaLimiteTecnica(),
                        todayBtn: true
                    },
                    disable: IsDisableIncluyePrecalifTecnica() || ReadOnly()"
                    >
                <span class="input-group-addon">
                    <button class="btn default date-set" type="button">
                        <i class="fa fa-calendar"></i>
                    </button>
                    <button class="btn default" type="button" data-toggle="tooltip" title="La fecha de Presentación Técnica debe ser al menos 48 horas mayor a la fecha limite para aceptar la invitación">
                        <i class="fa fa-info-circle" aria-hidden="true"></i>
                    </button>
                </span>
            </div>
        </div>
    </div>

    <!-- ko if: IsOnline() -->
        <div class="col-md-6">
            <div class="form-group required" data-bind="validationElement: Entity.InicioSubasta">
                <label class="control-label visible-ie8 visible-ie9" style="display: block;">Día de inicio</label>
                <div class="input-group date form_datetime bs-datetime">
                    <input 
                        class="form-control" 
                        size="16" 
                        type="text" 
                        data-bind="dateTimePicker: Entity.InicioSubasta, dateTimePickerOptions: {
                            format: 'dd-mm-yyyy hh:ii',
                            momentFormat: 'DD-MM-YYYY HH:mm',
                            startDate: Entity.InicioSubasta(),
                            value: Entity.InicioSubasta(),
                            todayBtn: true
                        },
                        ">
                    <span class="input-group-addon">
                        <button class="btn default date-set" type="button">
                            <i class="fa fa-calendar"></i>
                        </button>
                        <button class="btn default" type="button" data-toggle="tooltip" title="La fecha de Día de inicio debe ser al menos 24 horas mayor a la del Muro de Consultas">
                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                        </button>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group required" data-bind="validationElement: Entity.Duracion">
                <label class="control-label visible-ie8 visible-ie9" style="display: block;">Duración</label>
                <input class="form-control placeholder-no-fix" type="text" name="" id="" data-bind="inputmask: { 
                    value: Entity.Duracion , 
                    mask: '999 \\m. s \\s.', 
                    clearIncomplete: false,
                    placeholder: 'X', 
                    autoUnmask: true,
                    allowPlus: false,
                    allowMinus: false
                }, 
                ,
                value: Entity.Duracion()" />
            </div>
        </div>

        <div class="col-md-8">
            <div class="form-group required" data-bind="validationElement: Entity.TiempoAdicional">
                <label class="control-label visible-ie8 visible-ie9" style="display: block;">Tiempo de adicional para cerrar el concurso luego de última oferta (en segundos)</label>
                <input class="form-control placeholder-no-fix"type="number" min="0" max="120" name="" id="tiempo_adicional" data-bind="value: Entity.TiempoAdicional, " />
            </div>
        </div>
    <!-- /ko -->

    <!-- ko if: IsSobrecerrado() || IsGo() -->
        <div class="col-md-6">
            <div class="form-group required" data-bind="validationElement: Entity.FechaLimiteEconomicas">
                <label class="control-label visible-ie8 visible-ie9" style="display: block;">Fecha límite para presentar propuestas económicas</label>
                <div class="input-group date form_datetime bs-datetime">
                    <input class="form-control" type="text"
                    data-bind="
                        dateTimePicker: Entity.FechaLimiteEconomicas,
                        dateTimePickerOptions: {
                        format: 'dd-mm-yyyy hh:ii',
                        momentFormat: 'DD-MM-YYYY HH:mm',
                        startDate: Entity.FinalizacionConsultas()
                            ? moment(Entity.FinalizacionConsultas(), 'DD-MM-YYYY HH:mm').add(1, 'day').toDate()
                            : null,
                        value: Entity.FechaLimiteEconomicas(),
                        todayBtn: true
                        },
                        disable: !Entity.isRoundEditable(1)
                    ">

                    <span class="input-group-addon">
                        <button class="btn default date-set" type="button">
                            <i class="fa fa-calendar"></i>
                        </button>
                        <button class="btn default" type="button" data-toggle="tooltip" title="La fecha de Presentación Económica debe ser al menos 24 horas mayor a la fecha de cierre del muro de consultas">
                        <i class="fa fa-info-circle" aria-hidden="true"></i>
                    </button>
                    </span>
                </div>
            </div>
        </div>

        <!-- ko if: action() === 'edit' -->

            <!-- SEGUNDA RONDA -->
            <!-- ko if: Entity.isRoundVisible(2) -->
                <div class="col-md-6">
                    <div class="form-group required" data-bind="validationElement: Entity.segunda_ronda_fecha">
                        <label class="control-label">Fecha límite Segunda ronda</label>
                        <div class="input-group date form_datetime bs-datetime">
                            <input 
                                class="form-control" 
                                size="16" 
                                type="text"
                                data-bind="
                                    dateTimePicker: Entity.segunda_ronda_fecha, 
                                    dateTimePickerOptions: {
                                        format: 'dd-mm-yyyy hh:ii',
                                        momentFormat: 'DD-MM-YYYY HH:mm',
                                        startDate: Entity.segunda_ronda_fecha(),
                                        value: Entity.segunda_ronda_fecha(),
                                        todayBtn: true
                                    },
                                    disable: !Entity.isRoundEditable(2)
                                ">
                            <span class="input-group-addon">
                                <button class="btn default date-set" type="button">
                                    <i class="fa fa-calendar"></i>
                                </button>
                                <button class="btn default" type="button" data-toggle="tooltip" title="Fecha limite para la Segunda Ronda">
                                    <i class="fa fa-info-circle"></i>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
            <!-- /ko -->

            <!-- TERCERA RONDA -->
            <!-- ko if: Entity.isRoundVisible(3) -->
                <div class="col-md-6">
                    <div class="form-group required" data-bind="validationElement: Entity.tercera_ronda_fecha_limite">
                        <label class="control-label">Fecha límite Tercera ronda</label>
                        <div class="input-group date form_datetime bs-datetime">
                            <input 
                                class="form-control" 
                                size="16" 
                                type="text"
                                data-bind="
                                    dateTimePicker: Entity.tercera_ronda_fecha_limite, 
                                    dateTimePickerOptions: {
                                        format: 'dd-mm-yyyy hh:ii',
                                        momentFormat: 'DD-MM-YYYY HH:mm',
                                        startDate: Entity.tercera_ronda_fecha_limite(),
                                        value: Entity.tercera_ronda_fecha_limite(),
                                        todayBtn: true
                                    },
                                    disable: !Entity.isRoundEditable(3)
                                ">
                            <span class="input-group-addon">
                                <button class="btn default date-set" type="button">
                                    <i class="fa fa-calendar"></i>
                                </button>
                                <button class="btn default" type="button" data-toggle="tooltip" title="Fecha limite para la Tercera Ronda">
                                    <i class="fa fa-info-circle"></i>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
            <!-- /ko -->

            <!-- CUARTA RONDA -->
            <!-- ko if: Entity.isRoundVisible(4) -->
                <div class="col-md-6">
                    <div class="form-group required" data-bind="validationElement: Entity.cuarta_ronda_fecha_limite">
                        <label class="control-label">Fecha límite Cuarta ronda</label>
                        <div class="input-group date form_datetime bs-datetime">
                            <input 
                                class="form-control" 
                                size="16" 
                                type="text"
                                data-bind="
                                    dateTimePicker: Entity.cuarta_ronda_fecha_limite, 
                                    dateTimePickerOptions: {
                                        format: 'dd-mm-yyyy hh:ii',
                                        momentFormat: 'DD-MM-YYYY HH:mm',
                                        startDate: Entity.cuarta_ronda_fecha_limite(),
                                        value: Entity.cuarta_ronda_fecha_limite(),
                                        todayBtn: true
                                    },
                                    disable: !Entity.isRoundEditable(4)
                                ">
                            <span class="input-group-addon">
                                <button class="btn default date-set" type="button">
                                    <i class="fa fa-calendar"></i>
                                </button>
                                <button class="btn default" type="button" data-toggle="tooltip" title="Fecha limite para la Cuarta Ronda">
                                    <i class="fa fa-info-circle"></i>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
            <!-- /ko -->

            <!-- QUINTA RONDA -->
            <!-- ko if: Entity.isRoundVisible(5) -->
                <div class="col-md-6">
                    <div class="form-group required" data-bind="validationElement: Entity.quita_ronda_fecha_limite">
                        <label class="control-label">Fecha límite Quinta ronda</label>
                        <div class="input-group date form_datetime bs-datetime">
                            <input 
                                class="form-control" 
                                size="16" 
                                type="text"
                                data-bind="
                                    dateTimePicker: Entity.quita_ronda_fecha_limite, 
                                    dateTimePickerOptions: {
                                        format: 'dd-mm-yyyy hh:ii',
                                        momentFormat: 'DD-MM-YYYY HH:mm',
                                        startDate: Entity.quita_ronda_fecha_limite(),
                                        value: Entity.quita_ronda_fecha_limite(),
                                        todayBtn: true
                                    },
                                    disable: !Entity.isRoundEditable(5)
                                ">
                            <span class="input-group-addon">
                                <button class="btn default date-set" type="button">
                                    <i class="fa fa-calendar"></i>
                                </button>
                                <button class="btn default" type="button" data-toggle="tooltip" title="Fecha limite para la Quinta Ronda">
                                    <i class="fa fa-info-circle"></i>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
            <!-- /ko -->
        <!-- /ko -->
    <!-- /ko -->
</div>