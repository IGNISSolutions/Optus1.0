<?php

namespace App\Http\Controllers\Customer;
use App\Http\Controllers\BaseController;
use App\Models\InvitationStatus;
use App\Models\ProposalStatus;
use App\Models\Solped;
use App\Models\SolpedDocument;
use App\Models\UserType;
use Carbon\Traits\ToStringFormat;
use PhpMyAdmin\Console;
use Slim\Http\Request;
use Slim\Http\Response;
use Carbon\Carbon;
use App\Models\Pais;
use App\Models\Category;
use App\Models\Measurement;
use App\Models\Catalogo;
use App\Models\Alcance;
use App\Models\Concurso;
use App\Models\Participante;
use App\Models\Producto;
use App\Models\Provincia;
use App\Models\TipoOperacion;
use App\Models\Sheet;
use App\Models\SheetType;
use App\Models\ConvocatoriaTipo;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\PlantillaTecnica;
use App\Models\PlantillaTecnicaTipo;
use App\Models\PolicyAmount;
use App\Models\Step;
use App\Services\EmailService;
use \Exception as Exception;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as DB;
use App\Models\OffererCompany;
use App\Models\Evaluacion;
use DateTimeZone;
use DateTime;
use stdClass;

class SolpedController extends BaseController {

    private function ensureSolpedActive(Request $request, Response $response)
    {
        if (isAdmin()) {
            return;
        }
        abort_if($request, $response, !isSolpedActive(), 404);
    }

   public function serveDetail(Request $request, Response $response, $params)
    {
        try {
            $id = (int) $params['id'];
            $user = user();
            
            if (isAdmin()) {
                $solped = Solped::find($id);
            } else {
                $solped = $user->customer_company->getAllSolpedsByCompany()->find($id);
            }

            if (!$solped) {
                return $this->json($response, [
                    'success' => false,
                    'message' => 'Solicitud no encontrada'
                ], 404);
            }

            // **REGISTRAR PRIMERA VISITA DEL COMPRADOR TAMBIÉN AQUÍ**
            // Solo registrar si el usuario es un comprador (type_id = 3) y es la primera vez que accede
            if ($user->type_id == 3 && $solped->id_comprador_first_revision === null) {
                $solped->id_comprador_first_revision = $user->id;
                $solped->fecha_first_revision = \Carbon\Carbon::now();
                
                // Cambiar estado a 'revisada' si está en 'esperando-revision'
                if ($solped->estado_actual === 'esperando-revision') {
                    $solped->estado_actual = 'revisada';
                }
                
                $solped->save();
                
                error_log("Primera visita registrada en serveDetail: comprador_id={$user->id}, solped_id={$solped->id}, fecha=" . \Carbon\Carbon::now()->format('d-m-Y H:i:s') . ", nuevo_estado={$solped->estado_actual}");
            }

            return $this->render($response, 'solped/customer/detail.tpl', [
                'page' => 'solped',
                'accion' => 'poretapascliente', 
                'etapa' => $params['etapa'],
                'idSolped' => $params['id'],
                'title' => 'Solped Titulo'
            ]);
            
        } catch (\Exception $e) {
            return $this->json($response, [
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function detail(Request $request, Response $response, $params) {
        // Eliminar o comentar temporalmente todo el logging para debug
        
        $logFile = __DIR__ . '/debug_detail_cust.txt';
        $fp = fopen($logFile, 'a');
        fwrite($fp, "\n=== detail INICIO === " . date('d-m-Y H:i:s') . " ===\n");
        // ... resto del logging
        
        
        date_default_timezone_set(user()->customer_company->timeZone);
        $success = false;
        $message = null;
        $status = 200;
        $list = [];

        try {
            $user = user();

            if (isAdmin()) {
                $solped = Solped::find($params['id']);
            } else {
                $solped = $user->customer_company->getAllSolpedsByCompany()->find($params['id']);
            }

            if (!$solped) {
                throw new Exception("Solped no encontrado con id {$params['id']}");
            }

            // Registrar primera visita del comprador
            if ($user->type_id == 3 && $solped->id_comprador_first_revision === null) {
                $solped->id_comprador_first_revision = $user->id;
                $solped->fecha_first_revision = \Carbon\Carbon::now();

                if ($solped->estado_actual === 'esperando-revision' ) {
                    $solped->estado_actual = 'revisada';
                }
                
                
                $solped->save();
            }

            if ($user->type_id == 3 && $solped->estado_actual === 'esperando-revision-2') {
                $solped->estado_actual = 'revisada-2';
                $solped->save();
            }

            // Refetch fechas por query directa (defensa por si el ORM no hidrata)
            $rawRow = null;
            try {
                $rawRow = DB::table('solpeds')->where('id', $solped->id)->first();
                if (!$solped->fecha_resolucion && $rawRow && !empty($rawRow->fecha_resolucion)) {
                    $solped->fecha_resolucion = Carbon::parse($rawRow->fecha_resolucion);
                }
                if (!$solped->fecha_entrega && $rawRow && !empty($rawRow->fecha_entrega)) {
                    $solped->fecha_entrega = Carbon::parse($rawRow->fecha_entrega);
                }
            } catch (\Throwable $e) {
                // Si falla, continuamos con los valores cargados
            }

            // Common data
            $common_data = [
                'IdSolicitud'     => $solped->id,
                'Nombre'          => $solped->nombre,
                'IdSolicitante'   => $solped->solicitante->id,
                'Solicitante'     => $solped->solicitante->full_name,
                'TipoCompraId'    => $solped->tipo_compra ? (int)$solped->tipo_compra : null,
                'CodigoInterno'   => $solped->codigo_interno ? $solped->codigo_interno : 0,
                'Descripcion'     => $solped->descripcion,
                'AreaSolicitante' => $solped->area_sol,
                'CompradorSugerido'=> $solped->comprador_sugerido && $solped->comprador_sugerido->full_name ? $solped->comprador_sugerido->full_name : null,
                'FechaCreacion'   => $solped->fecha_alta ? $solped->fecha_alta->format('d-m-Y H:i:s') : null,
                'FechaEnvioComprador' => $solped->fecha_envio_a_comprador ? $solped->fecha_envio_a_comprador->format('d-m-Y H:i:s') : null,
                'FechaResolucion'  => $solped->fecha_resolucion ? $solped->fecha_resolucion->format('d-m-Y H:i') : null,
                'FechaEntrega'    => $solped->fecha_entrega ? $solped->fecha_entrega->format('d-m-Y H:i') : null,
                'Eliminado'       => $solped->deleted_at ? true : false,
                'UsuarioReject'   => $solped->usuario_rechazo ? $solped->usuario_rechazo : null,
                'UsuarioAccept'   => $solped->id_comprador_decision ? $solped->id_comprador_decision : null,
                'FechaRechazo'    => $solped->fecha_rechazo ? $solped->fecha_rechazo->format('d-m-Y H:i:s') : null,
                'FechaAceptacion' => $solped->fecha_aceptacion ? $solped->fecha_aceptacion->format('d-m-Y H:i:s') : null,
                'RejectComment'   => $solped->reject_comment ? $solped->reject_comment : null,
                'ReturnComment'   => $solped->return_comment ? $solped->return_comment : null,
                'Etapa'           => $solped->etapa_actual,
                'EstadoActual'    => $solped->estado_actual,
                'CompradorDecision' => $solped->comprador_decision ? $solped->comprador_decision->full_name : null,
                'CompradorFirstRevision' => $solped->comprador_first_revision ? $solped->comprador_first_revision->full_name : null,
                'FechaFirstRevision' => $solped->fecha_first_revision ? $solped->fecha_first_revision->format('d-m-Y H:i:s') : null,
                'FechaDevolucion' => $solped->fecha_devolucion ? $solped->fecha_devolucion->format('d-m-Y H:i:s') : null,
                'CompradorDecisionFecha' => $solped->comprador_decision && $solped->fecha_rechazo ? 
                    $solped->comprador_decision->full_name . ' - ' . $solped->fecha_rechazo->format('d-m-Y H:i:s') : null,
                'CompradorDevolucionFecha' => $solped->comprador_decision && $solped->fecha_devolucion ? 
                    $solped->comprador_decision->full_name . ' - ' . $solped->fecha_devolucion->format('d-m-Y H:i:s') : null,
                'CancelMotive'   => $solped->cancel_motive ?: null,
                'FechaCancelacion' => $solped->updated_at ? $solped->updated_at->format('d-m-Y H:i:s') : null,
            ];

            // Debug: registrar fechas clave
            fwrite($fp, "FECHAS => Resolucion: " . ($common_data['FechaResolucion'] ?? 'null') . ", Entrega: " . ($common_data['FechaEntrega'] ?? 'null') . ", Creacion: " . ($common_data['FechaCreacion'] ?? 'null') . "\n");
                // Debug: registrar atributos fecha
                fwrite($fp, "FECHA CANCELACION: " . ($common_data['FechaCancelacion'] ?? 'null') . "\n");
                fwrite($fp, "FECHA ACEPTACION: " . ($common_data['FechaAceptacion'] ?? 'null') . "\n");
                fwrite($fp, "FECHA RECHAZO: " . ($common_data['FechaRechazo'] ?? 'null') . "\n");
                fwrite($fp, "FECHA DEVOLUCION: " . ($common_data['FechaDevolucion'] ?? 'null') . "\n");
                fwrite($fp, "FECHA FIRST REVISION: " . ($common_data['FechaFirstRevision'] ?? 'null') . "\n");
            // Debug: atributos crudos desde DB
            $attrs = $solped->getAttributes();
            fwrite($fp, "RAW fecha_resolucion attr: " . (isset($attrs['fecha_resolucion']) ? $attrs['fecha_resolucion'] : 'null') . "\n");
            fwrite($fp, "RAW fecha_entrega attr: " . (isset($attrs['fecha_entrega']) ? $attrs['fecha_entrega'] : 'null') . "\n");
                // Debug: fila directa con query builder
                try {
                    $rawRow = DB::table('solpeds')->where('id', $solped->id)->first();
                    fwrite($fp, "DB ROW: " . json_encode($rawRow) . "\n");
                } catch (\Throwable $e) {
                    fwrite($fp, "DB ROW ERROR: " . $e->getMessage() . "\n");
                }

            // Productos
            $productos = [];
            if ($solped->productos) {
                foreach ($solped->productos as $producto) {
                    $productos[] = [
                        'Id'                => $producto->id,
                        'Nombre'            => $producto->nombre ?? '',
                        'Descripcion'       => $producto->descripcion ?? '',
                        'Cantidad'          => (float)($producto->cantidad ?? 0),
                        'OfertaMinima'      => (float)($producto->oferta_minima ?? 0),
                        'UnidadMedidaId'    => $producto->unidad_medida && $producto->unidad_medida->id ? (int)$producto->unidad_medida->id : null,
                        'UnidadMedidaNombre'=> $producto->unidad_medida && $producto->unidad_medida->name ? $producto->unidad_medida->name : '',
                        'TargetCost'        => (float)($producto->targetcost ?? 0),
                    ];
                }
            }

            // FilePath y documentos (misma lógica que en Concurso)
            // Usar la estructura CUIT/AÑO a través del método file_path del modelo
            $file_path = '/storage/img/' . $solped->file_path;            
            $documents = [];
            $sheetTypes = SheetType::all()->values();
            $docIndex = 0;
            foreach ($solped->documents as $doc) {
                $sheetType = isset($sheetTypes[$docIndex]) ? $sheetTypes[$docIndex] : null;
                $documents[] = [
                    'nombre' => $sheetType ? $sheetType->description : $doc->filename,
                    'type_name' => $sheetType ? $sheetType->description : null,
                    'imagen' => $doc->filename,
                    'url' => $file_path . $doc->filename
                ];
                $docIndex++;
            }

            $list = array_merge($common_data, [
                'Productos'        => $productos,
                'FilePath'         => $documents,
                'Documents'        => $documents,
                'FilePathComplete' => $documents ? $file_path . $documents[0]['imagen'] : null,
                'file_path'        => $solped->file_path,  // Agregar la ruta relativa para la vista

            ]);

            // Exponer datos mínimos de adjudicación para vista del solicitante
            try {
                $concurso = \App\Models\Concurso::whereRaw("FIND_IN_SET(?, created_from_solped)", [$solped->id])->first();
                if ($concurso) {
                    $oferenteAdjudicado = $concurso->oferentes
                        ->where('etapa_actual', \App\Models\Participante::ETAPAS['adjudicacion-aceptada'])
                        ->first();

                    $proveedor = $oferenteAdjudicado && $oferenteAdjudicado->company
                        ? $oferenteAdjudicado->company->business_name
                        : null;

                    $fechaHora = null;
                    if ($oferenteAdjudicado && isset($oferenteAdjudicado->updated_at) && $oferenteAdjudicado->updated_at) {
                        $fechaHora = $oferenteAdjudicado->updated_at instanceof \Carbon\Carbon
                            ? $oferenteAdjudicado->updated_at->format('d-m-Y H:i')
                            : (new \Carbon\Carbon($oferenteAdjudicado->updated_at))->format('d-m-Y H:i');
                    } elseif ($oferenteAdjudicado && !empty($oferenteAdjudicado->acepta_adjudicacion_fecha)) {
                        try { $fechaHora = (new \Carbon\Carbon($oferenteAdjudicado->acepta_adjudicacion_fecha))->format('d-m-Y'); } catch (\Throwable $e) { $fechaHora = $oferenteAdjudicado->acepta_adjudicacion_fecha; }
                    }

                    $comprador = $concurso->cliente ? $concurso->cliente->full_name : null;

                    $list['AdjudicacionProveedor'] = $proveedor;
                    $list['AdjudicacionFechaHora'] = $fechaHora;
                    $list['CompradorConcurso'] = $comprador;
                }
            } catch (\Throwable $e) {
                // Silenciar errores para no romper la respuesta
            }

            // Debug: listar claves finales
            fwrite($fp, "LIST KEYS: " . implode(',', array_keys($list)) . "\n");

            $success = true;

            $breadcrums = [
                ['description' => 'Solped', 'url' => route('solped.serveList')],
                ['description' => 'Detalle']
            ];

        } catch (Exception $e) {
            $success = false;
            $message = $e->getMessage();
            $status = 500;
        }

        // Asegurar que no hay output antes del JSON
        if (ob_get_level()) {
            ob_clean();
        }

        return $this->json($response, [
            'success' => $success,
            'message' => $message,
            'data' => [
                'list' => $list,
                'breadcrumbs' => isset($breadcrums) ? $breadcrums : []
            ]
        ], $status);
    }

    public function reject(Request $request, Response $response, $params) {
        $success = false;
        $message = null;
        $status = 200;
        $result = [];
        $redirect_url = null;

        if (!isSolpedActive() && !isAdmin()) {
            return $this->json($response, [
                'success' => false,
                'message' => 'El módulo de Solped está desactivado para tu empresa.'
            ], 403);
        }

        // Archivo de log
        $logFile = __DIR__ . '/debug_reject_cust.txt';
        $fp = fopen($logFile, 'a');
        fwrite($fp, "\n=== reject INICIO === " . date('d-m-Y H:i:s') . " ===\n");

        try {
            $body = json_decode($request->getParsedBody()['Entity']);
            $reason = $request->getParsedBody()['Reason'] ?? null;
            
            fwrite($fp, "Body recibido: " . print_r($body, true) . "\n");
            fwrite($fp, "Reason recibido: " . $reason . "\n");

            if (!$reason || trim($reason) === '') {
                throw new \Exception("El motivo de rechazo es obligatorio");
            }

            $capsule = dependency('db');
            $connection = $capsule->getConnection();
            $connection->beginTransaction();

            $user = user();
            fwrite($fp, "Usuario: ID={$user->id}, type_id={$user->type_id}\n");

            // Obtener la solped
            if (isAdmin()) {
                $solped = Solped::find($body->IdSolicitud);
            } else {
                $solped = $user->customer_company->getAllSolpedsByCompany()->find($body->IdSolicitud);
            }

            if (!$solped) {
                throw new \Exception("Solicitud no encontrada con ID: {$body->IdSolicitud}");
            }

            fwrite($fp, "Solped encontrada: ID={$solped->id}, estado_actual={$solped->estado_actual}\n");

            // Verificar que se pueda rechazar
            if ($solped->estado_actual !== 'esperando-revision' && $solped->estado_actual !== 'revisada' && $solped->estado_actual !== 'esperando-revision-2' && $solped->estado_actual !== 'revisada-2') {
                throw new \Exception("La solicitud no se puede rechazar en su estado actual: {$solped->estado_actual}");
            }

            $fechaRechazo = Carbon::now();
            
            // Actualizar la solped
            $solped->update([
                'etapa_actual' => 'rechazada',
                'estado_actual' => 'rechazada',
                'reject_comment' => trim($reason),
                'fecha_rechazo' => $fechaRechazo,
                'id_comprador_decision' => $user->id
            ]);

            fwrite($fp, "Solped actualizada con estado rechazada\n");

            // Enviar email al solicitante notificando rechazo
            $emailService = new EmailService();
            $template = rootPath(config('app.templates_path')) . '/email/solped-rejected.tpl';
            $subject = 'Solicitud #' . $solped->id . ' rechazada';
            $html = $this->fetch($template, [
                'title' => $subject,
                'ano' => Carbon::now()->format('Y'),
                'solped' => $solped,
                'reason' => trim($reason),
                'user' => $user
            ]);

            $successMail = $emailService->send($html, $subject, [$solped->solicitante->email], $solped->solicitante->full_name);
            fwrite($fp, "MAIL rechazo: " . json_encode($successMail) . "\n");

            $success = true;
            $message = 'Solicitud rechazada correctamente.';
            $redirect_url = '/solped/cliente/monitor';

            $connection->commit();
            fwrite($fp, "Transacción confirmada\n");

        } catch (\Exception $e) {
            if (isset($connection)) {
                $connection->rollback();
            }
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
            
            fwrite($fp, "ERROR: {$message}\n");
        }

        fwrite($fp, "=== reject FIN ===\n\n");
        fclose($fp);

        return $this->json($response, [
            'success' => $success,
            'message' => $message,
            'data' => [
                'redirect' => $redirect_url
            ]
        ], $status);
    }

    public function sendBack(Request $request, Response $response, $params) {
        $success = false;
        $message = null;
        $status = 200;
        $result = [];
        $redirect_url = null;

        if (!isSolpedActive() && !isAdmin()) {
            return $this->json($response, [
                'success' => false,
                'message' => 'El módulo de Solped está desactivado para tu empresa.'
            ], 403);
        }

        // Archivo de log
        $logFile = __DIR__ . '/debug_sendback_cust.txt';
        $fp = fopen($logFile, 'a');
        fwrite($fp, "\n=== sendBack INICIO === " . date('d-m-Y H:i:s') . " ===\n");

        try {
            $body = json_decode($request->getParsedBody()['Entity']);
            $reason = $request->getParsedBody()['Reason'] ?? null;
            
            fwrite($fp, "Body recibido: " . print_r($body, true) . "\n");
            fwrite($fp, "Reason recibido: " . $reason . "\n");

            if (!$reason || trim($reason) === '') {
                throw new \Exception("El motivo de devolución es obligatorio");
            }

            $capsule = dependency('db');
            $connection = $capsule->getConnection();
            $connection->beginTransaction();

            $user = user();
            fwrite($fp, "Usuario: ID={$user->id}, type_id={$user->type_id}\n");

            // Obtener la solped
            if (isAdmin()) {
                $solped = Solped::find($body->IdSolicitud);
            } else {
                $solped = $user->customer_company->getAllSolpedsByCompany()->find($body->IdSolicitud);
            }

            if (!$solped) {
                throw new \Exception("Solicitud no encontrada con ID: {$body->IdSolicitud}");
            }

            fwrite($fp, "Solped encontrada: ID={$solped->id}, estado_actual={$solped->estado_actual}\n");

            // Verificar que se pueda devolver
            if ($solped->estado_actual !== 'esperando-revision' && $solped->estado_actual !== 'revisada') {
                throw new \Exception("La solicitud no se puede devolver en su estado actual: {$solped->estado_actual}");
            }

            $fechaDevolucion = Carbon::now();
            
            // Actualizar la solped
            $solped->update([
                'etapa_actual' => 'devuelta',
                'estado_actual' => 'devuelta',
                'return_comment' => trim($reason),
                'fecha_devolucion' => $fechaDevolucion,
                'id_comprador_decision' => $user->id,
            ]);

            fwrite($fp, "Solped actualizada con estado devuelta\n");

            // Enviar email al solicitante notificando devolución para modificación
            $emailService = new EmailService();
            $template = rootPath(config('app.templates_path')) . '/email/solped-returned.tpl';
            $subject = 'Solicitud #' . $solped->id . ' devuelta para modificación';
            $html = $this->fetch($template, [
                'title' => $subject,
                'ano' => Carbon::now()->format('Y'),
                'solped' => $solped,
                'reason' => trim($reason),
                'user' => $user
            ]);

            $successMail = $emailService->send($html, $subject, [$solped->solicitante->email], $solped->solicitante->full_name);
            fwrite($fp, "MAIL devolucion: " . json_encode($successMail) . "\n");

            $success = true;
            $message = 'Solicitud devuelta correctamente.';
            $redirect_url = '/solped/cliente/monitor';

            $connection->commit();
            fwrite($fp, "Transacción confirmada\n");

        } catch (\Exception $e) {
            if (isset($connection)) {
                $connection->rollback();
            }
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
            
            fwrite($fp, "ERROR: {$message}\n");
        }

        fwrite($fp, "=== sendBack FIN ===\n\n");
        fclose($fp);

        return $this->json($response, [
            'success' => $success,
            'message' => $message,
            'data' => [
                'redirect' => $redirect_url
            ]
        ], $status);
    }

    public function approve(Request $request, Response $response, $params) {
        $success = false;
        $message = null;
        $status = 200;
        $result = [];
        $redirect_url = null;

        if (!isSolpedActive() && !isAdmin()) {
            return $this->json($response, [
                'success' => false,
                'message' => 'El módulo de Solped está desactivado para tu empresa.'
            ], 403);
        }

        // Archivo de log
        $logFile = __DIR__ . '/debug_approve_cust.txt';
        $fp = fopen($logFile, 'a');
        fwrite($fp, "\n=== approve INICIO === " . date('d-m-Y H:i:s') . " ===\n");

        try {
            $body = json_decode($request->getParsedBody()['Entity']);
            
            fwrite($fp, "Body recibido: " . print_r($body, true) . "\n");

            $capsule = dependency('db');
            $connection = $capsule->getConnection();
            $connection->beginTransaction();

            $user = user();
            fwrite($fp, "Usuario: ID={$user->id}, type_id={$user->type_id}\n");

            // Verificar que el usuario sea un comprador
            if ($user->type_id != 3) {
                throw new \Exception("Solo los compradores pueden aprobar solicitudes");
            }

            // Obtener la solped
            if (isAdmin()) {
                $solped = Solped::find($body->IdSolicitud);
            } else {
                $solped = $user->customer_company->getAllSolpedsByCompany()->find($body->IdSolicitud);
            }

            if (!$solped) {
                throw new \Exception("Solicitud no encontrada con ID: {$body->IdSolicitud}");
            }

            fwrite($fp, "Solped encontrada: ID={$solped->id}, estado_actual={$solped->estado_actual}\n");

            // Verificar que se pueda aprobar
            $estadosAprobables = ['esperando-revision', 'revisada', 'esperando-revision-2', 'revisada-2'];
            if (!in_array($solped->estado_actual, $estadosAprobables)) {
                throw new \Exception("La solicitud no se puede aprobar en su estado actual: {$solped->estado_actual}");
            }

            $fechaAceptacion = Carbon::now();
            
            // Actualizar la solped con todos los campos necesarios
            $solped->etapa_actual = 'aceptada';
            $solped->estado_actual = 'aceptada';
            $solped->fecha_aceptacion = $fechaAceptacion;
            $solped->id_comprador_decision = $user->id;
            
            // Limpiar comentarios de rechazo/devolución previos si existen
            $solped->reject_comment = null;
            $solped->return_comment = null;
            $solped->fecha_rechazo = null;
            $solped->fecha_devolucion = null;
            
            $solped->save();

            fwrite($fp, "Solped actualizada con estado aprobada\n");
            fwrite($fp, "Fecha aceptación: " . $fechaAceptacion->format('d-m-Y H:i:s') . "\n");
            fwrite($fp, "Comprador decisión: {$user->id} ({$user->full_name})\n");

            // Enviar email al solicitante notificando aprobación
            $emailService = new EmailService();
            $template = rootPath(config('app.templates_path')) . '/email/solped-approved.tpl';
            $subject = 'Solicitud #' . $solped->id . ' aprobada';
            $html = $this->fetch($template, [
                'title' => $subject,
                'ano' => Carbon::now()->format('Y'),
                'solped' => $solped,
                'user' => $user
            ]);

            $successMail = $emailService->send($html, $subject, [$solped->solicitante->email], $solped->solicitante->full_name);
            fwrite($fp, "MAIL aprobacion: " . json_encode($successMail) . "\n");

            $success = true;
            $message = 'Solicitud aprobada correctamente.';
            $redirect_url = '/solped/cliente/monitor';

            $connection->commit();
            fwrite($fp, "Transacción confirmada exitosamente\n");

        } catch (\Exception $e) {
            if (isset($connection)) {
                $connection->rollback();
                fwrite($fp, "Transacción revertida debido a error\n");
            }
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? ($e->getCode() ?: 500) : 500);
            
            fwrite($fp, "ERROR: {$message}\n");
            fwrite($fp, "Stack trace: " . $e->getTraceAsString() . "\n");
        }

        fwrite($fp, "=== approve FIN ===\n\n");
        fclose($fp);

        return $this->json($response, [
            'success' => $success,
            'message' => $message,
            'data' => [
                'redirect' => $redirect_url
            ]
        ], $status);
    }
}