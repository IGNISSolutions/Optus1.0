{extends 'solped/solicitante/main.tpl'}

{block 'styles'}
    <link href="{asset('/global/plugins/datatables/datatables.min.css')}" rel="stylesheet" type="text/css" />
    <link href="{asset('/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css')}" rel="stylesheet" type="text/css" />
    <link href="{asset('/global/css/components-rounded.min.css')}" rel="stylesheet" id="style_components" type="text/css" />
{/block}

{block 'pre-scripts'}
    <script src="{asset('/global/plugins/datatables/datatables.min.js')}" type="text/javascript"></script>
    <script src="{asset('/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js')}" type="text/javascript"></script>
{/block}

{block 'title'} {$title} {/block}

{block 'solped-created-list'}
<div class="row">
  <div class="col-md-12">
    <div class="portlet light bordered">
      <div class="portlet-title">
        <div class="btn-group">
          <a href="/solped/nuevo" class="btn sbold green">
            Agregar Nueva Solicitud De Pedido
            <i class="fa fa-plus"></i>
          </a>
        </div>
      </div>

      <div class="portlet-body">
        <table class="table table-striped table-bordered" id="listaSolped">
          <thead>
            <tr>
              <th>Nº Solicitud</th>
              <th>Nombre</th>
              <th>Creado por</th>
              <th class="text-center">Acciones</th>
            </tr>
          </thead>

          <tbody data-bind="dataTablesForEach: { data: ListaSolpeds, options: { paging: true } }">
            <tr>
              <td class="vertical-align-middle" data-bind="text: Id"></td>
              <td class="vertical-align-middle" data-bind="text: Nombre"></td>
              <td class="vertical-align-middle" data-bind="text: CreadoPor"></td>
              <td class="text-center vertical-align-middle">
                <a href="javascript:void(0);"
                   class="btn btn-xs green"
                   title="Editar"
                   data-bind="click: function(){ $root.EditarSolped(Id) }">
                   Editar <i class="fa fa-pencil"></i>
                </a>

                <a href="javascript:void(0);"
                   class="btn btn-xs btn-danger"
                   title="Eliminar"
                   data-bind="click: function(){ $root.Eliminar(Id) }">
                   Eliminar <i class="fa fa-trash-o"></i>
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

{block 'knockout' append}
<script>
var SolpedListado = function (data) {
  var self = this;

  // DATA
  console.log("datos", data)
  this.ListaSolpeds  = ko.observableArray(data.list || []);
  this.Breadcrumbs   = ko.observableArray(data.breadcrumbs || []);

  // Acciones
  this.EditarSolped = function(id) {
    // Si querés token, implementá un endpoint similar a concursos.
    window.location.href = '/solped/edicion/' + id;
  };

  this.Eliminar = function(id) {
    swal({
      title: 'Eliminar Solicitud',
      text: '¿Confirmás eliminar la solicitud #' + id + '?',
      type: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Eliminar',
      confirmButtonClass: 'btn btn-danger',
      cancelButtonText: 'Cancelar',
      cancelButtonClass: 'btn btn-default',
      closeOnConfirm: false
    }, function (ok) {
      if (!ok) return;
      $.blockUI();
      Services.Post('/solped/delete/' + id, { UserToken: User.Token },
        function (resp) {
          $.unblockUI();
          if (resp && resp.success) {
            swal({
              title: 'Hecho',
              text: resp.message || 'Solicitud eliminada.',
              type: 'success'
            }, function(){ location.reload(); });
          } else {
            swal('Error', (resp && resp.message) || 'No se pudo eliminar.', 'error');
          }
        },
        function (err) {
          $.unblockUI();
          swal('Error', err.message || 'Fallo al eliminar.', 'error');
        }
      );
    });
  };
};

jQuery(document).ready(function () {
  $.blockUI();

  // Endpoint JSON (ver más abajo backend)
  var url = '/solped/list';
  Services.Get(url, { UserToken: User.Token },
    function (response) {
      if (response && response.success) {
        window.E = new SolpedListado(response.data);
        AppOptus.Bind(E);
      } else {
        swal('Error', (response && response.message) || 'No se pudo cargar el listado', 'error');
      }
      $.unblockUI();
    },
    function (error) {
      $.unblockUI();
      swal('Error', error.message || 'Fallo la carga', 'error');
    }
  );

  {chromeDebugString('dynamicScript')}
});
</script>
{/block}
