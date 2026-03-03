<div class="row">
  <div class="col-md-12">
    <div class="form-group" data-bind="validationElement: Entity.CompradorSugeridoSelected">
      <label for="compradores_sugeridos_list" class="control-label">
        <strong>Comprador Sugerido</strong>
      </label>
      <label> (Comprador que le gustaría o sugiere que se encargue de la revisión de la solicitud) </label>

      <div class="selectRequerido">
       <select id="compradores_sugeridos_list"
          data-bind="
            options: Entity.CompradoresSugeridos,
            optionsText: 'text',
            optionsValue: 'id',
            value: Entity.CompradorSugeridoSelected,
            valueAllowUnset: true,
            select2Safe: { placeholder: 'Seleccionar...', allowClear: true, width: '100%' }
          ">
          <option value=""></option>
        </select>
      </div>
      <div style="text-align:right;padding:10px;">
        <a class="btn btn-primary" data-bind='click: Entity.removeAll'>Limpiar</a>
        <a class="btn btn-primary" data-toggle="modal" data-target="#modal-filtros-oferente">Búsqueda Avanzada</a>
      </div>
    </div>

  </div>
</div>

<div class="row">
  <div class="col-md-6">
    <div class="form-group" data-bind="validationElement: Entity.FechaResolucion">
      <label for="fecha_resolucion" class="control-label">
        <strong>Fecha de Resolución <span style="color: red;">*</span></strong>
      </label>
      <label> (Fecha esperada de resolución de la solicitud) </label>

      <div class="input-group date form_datetime bs-datetime">
        <input 
          class="form-control" 
          size="16" 
          type="text" 
          data-bind="dateTimePicker: Entity.FechaResolucion, dateTimePickerOptions: {
            format: 'dd-mm-yyyy hh:ii',
            momentFormat: 'DD-MM-YYYY HH:mm',
            startDate: Entity.FechaResolucion(),
            value: Entity.FechaResolucion(),
            todayBtn: true,
            minView: 0,
            minuteStep: 5,
            initialDate: new Date(new Date().setHours(0, 0, 0, 0))
          }">
        <span class="input-group-addon">
          <button class="btn default date-set" type="button">
            <i class="fa fa-calendar"></i>
          </button>
          <button class="btn default" type="button" data-toggle="tooltip" title="Fecha estimada de resolución">
            <i class="fa fa-info-circle" aria-hidden="true"></i>
          </button>
        </span>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group" data-bind="validationElement: Entity.FechaEntrega">
      <label for="fecha_entrega" class="control-label">
        <strong>Fecha de Entrega <span style="color: red;">*</span></strong>
      </label>
      <label> (Fecha esperada de entrega de los productos o servicios) </label>

      <div class="input-group date form_datetime bs-datetime">
        <input 
          class="form-control" 
          size="16" 
          type="text" 
          data-bind="dateTimePicker: Entity.FechaEntrega, dateTimePickerOptions: {
            format: 'dd-mm-yyyy hh:ii',
            momentFormat: 'DD-MM-YYYY HH:mm',
            startDate: Entity.FechaEntrega(),
            value: Entity.FechaEntrega(),
            todayBtn: true,
            minView: 0,
            minuteStep: 5,
            initialDate: new Date(new Date().setHours(0, 0, 0, 0))
          }">
        <span class="input-group-addon">
          <button class="btn default date-set" type="button">
            <i class="fa fa-calendar"></i>
          </button>
          <button class="btn default" type="button" data-toggle="tooltip" title="Fecha estimada de entrega">
            <i class="fa fa-info-circle" aria-hidden="true"></i>
          </button>
        </span>
      </div>
    </div>
  </div>
</div>
