<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\BaseController;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Models\Concurso;
use App\Models\Participante;
use App\Models\ParticipanteGoDocument;
use Carbon\Carbon;

class MediaController extends BaseController
{
    public function uploadFile(Request $request, Response $response)
    {
        $message = '';
        $status = 200;
        $results = [
            'initialPreview' => [],
            'initialPreviewConfig' => [],
            'initialPreviewAsData' => true
        ];

        try {
            $body = $request->getParsedBody();
            $file = !empty($request->getUploadedFiles()) ? $request->getUploadedFiles()['file'][0] : null;

            if (empty($file)) {
                $status = 422;
                $results['error'] = 'Error al recuperar los archivos.';
            } else {
                $filepath = $body['path']; // viene del TPL
                $originalClientName = $file->getClientFilename();

                // Info del concurso (viene del TPL)
                $concursoId = isset($body['concurso_id']) ? (int)$body['concurso_id'] : null;
                $concursoNombre = isset($body['concurso_nombre']) ? (string)$body['concurso_nombre'] : null;

                // Si no viene en el body, podés intentar inferirlo por el path o buscarlo por oferente, etc.
                // (opcional) fallback:
                if (!$concursoId || !$concursoNombre) {
                    // ejemplo: intentar por path u otras reglas tuyas
                    // $concurso = ...;
                    // $concursoId = $concurso->id ?? $concursoId;
                    // $concursoNombre = $concurso->nombre ?? $concursoNombre;
                }

                // Sanitizar partes
                $originalSan = $this->sanitizeFilename($originalClientName); // mantiene ext
                $ext = pathinfo($originalSan, PATHINFO_EXTENSION);
                $baseOriginal = pathinfo($originalSan, PATHINFO_FILENAME);

                // ConcursoNombre en MAYÚSCULAS sin espacios (o a tu gusto)
                $nombreConcursoPart = $concursoNombre ? strtoupper(preg_replace('/\s+/', '', $concursoNombre)) : null;

                // Armar prefijo: id + nombreconcurso (si existen)
                $prefixParts = [];
                if (!empty($concursoId))         $prefixParts[] = $concursoId;
                if (!empty($nombreConcursoPart)) $prefixParts[] = $nombreConcursoPart;

                // Nombre final: <prefijo>_<original>
                $finalBase = $baseOriginal;
                if (!empty($prefixParts)) {
                    $finalBase = implode('_', $prefixParts) . '_' . $baseOriginal;
                }

                // Reconstruir con extensión
                $finalCandidate = $ext ? "{$finalBase}.{$ext}" : $finalBase;

                // Directorios
                $relative_dir = rtrim($filepath, DIRECTORY_SEPARATOR);
                $absolute_path = rtrim(rootPath() . DIRECTORY_SEPARATOR . $relative_dir, DIRECTORY_SEPARATOR);
                if (!is_dir($absolute_path)) {
                    mkdir($absolute_path, 0777, true);
                }

                // Evitar colisiones
                $finalFilename = $this->uniqueFilename($absolute_path, $this->sanitizeFilename($finalCandidate));

                $relative_file = $relative_dir . DIRECTORY_SEPARATOR . $finalFilename;
                $absolute_file = $absolute_path . DIRECTORY_SEPARATOR . $finalFilename;

                // Mover
                $file->moveTo($absolute_file);

                if ($file->getError()) {
                    $results['error'] = $file->getError();
                    $status = 422;
                } else {
                    $results['initialPreview'][] = $relative_file;
                    $results['initialPreviewConfig'][] = [
                        'key' => \Carbon\Carbon::now()->timestamp,
                        'caption' => $finalFilename,     // muestra el nuevo nombre
                        'size' => $file->getSize(),
                        'downloadUrl' => $relative_file, // descarga con el nuevo nombre
                        'url' => route('media.file.delete'),
                        'extra' => [
                            'path' => $relative_file
                        ]
                    ];
                    $status = 200;
                }
            }

        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
            $status = 500;
        }

        return $this->json($response, $results, $status);
    }



    /**
     * Limpia el nombre de archivo: quita rutas, normaliza caracteres y limita longitud.
     */
    private function sanitizeFilename(string $name, int $maxLength = 150): string
    {
        // Nos quedamos solo con el basename por seguridad
        $name = basename($name);

        // Opcional: normalizar unicode (si tenés ext-intl): 
        // $name = \Normalizer::normalize($name, \Normalizer::FORM_KD);

        // Reemplazar caracteres no permitidos (mantener letras, números, espacio, punto, guion y guion bajo)
        $name = preg_replace('/[^\w\-. ]+/u', '_', $name);

        // Evitar nombres vacíos
        if ($name === '' || $name === '.' || $name === '..') {
            $name = 'archivo';
        }

        // Separar nombre y extensión
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $base = pathinfo($name, PATHINFO_FILENAME);

        // Limitar longitud total preservando extensión
        if (mb_strlen($name) > $maxLength) {
            $maxBase = $maxLength - (mb_strlen($ext) ? (mb_strlen($ext) + 1) : 0);
            $base = mb_substr($base, 0, max(1, $maxBase));
        }

        return $ext ? "{$base}.{$ext}" : $base;
    }

    /**
     * Si existe un archivo con el mismo nombre, agrega sufijo " (1)", " (2)", etc.
     */
    private function uniqueFilename(string $dir, string $filename): string
    {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $base = pathinfo($filename, PATHINFO_FILENAME);

        $candidate = $filename;
        $i = 1;
        while (file_exists($dir . DIRECTORY_SEPARATOR . $candidate)) {
            $suffix = " ({$i})";
            $candidate = $ext ? "{$base}{$suffix}.{$ext}" : "{$base}{$suffix}";
            $i++;
            // Evitar loops raros
            if ($i > 999) {
                $candidate = uniqid($base . '_', true) . ($ext ? ".{$ext}" : '');
                break;
            }
        }
        return $candidate;
    }


    public function deleteFile(Request $request, Response $response)
    {
        $message = '';
        $status = 200;
        $results = [
            'initialPreview' => [],
            'initialPreviewConfig' => [],
            'initialPreviewAsData' => true
        ];

        try {
            $body = $request->getParsedBody();
            $path = rootPath() . DIRECTORY_SEPARATOR . $body['path'];
            @unlink($path);

        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
        }

        return $this->json($response, $results, $status);
    }

    public function rollbackFile(Request $request, Response $response)
    {
        $body = $request->getParsedBody();
        $success = false;
        $message = null;
        $status = 200;
        $results = [];

        try {
            $filepath = rootPath($body['path']);
            if (file_exists($filepath)) {
                @unlink($filepath);

                $success = true;
                $message = 'Archivo "' . basename($body['path']) . '" eliminado con éxito.';
            } else {
                $status = 422;
                $success = false;
                $message = 'El Archivo "' . basename($body['path']) . '" no existe.';
            }


        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
        }

        return $this->json($response, [
            'success' => $success,
            'message' => $message
        ], $status);
    }

    public function downloadFile(Request $request, Response $response)
    {
        $body = json_decode($request->getParsedBody()['Entity'], true);
        $path = $body['Path'];
        $type = $body['Type'];
        $id = $body['Id'];
        $success = false;
        $status = 200;
        $file_path = null;
        $message = "";


        try {
            $file_name = basename($path);
            $user = user();

            switch ($type) {
                case 'oferente':
                    $oferente = Participante::find((int) $id);
                    $file_path =
                        $oferente ?
                        $oferente->file_path . $file_name :
                        null;

                    break;
                case 'concurso':
                    $concurso = Concurso::find((int) $id);
                    if ($concurso) {
                        $file_path = $concurso->file_path . $file_name;
                    } else {
                        $file_path =
                            $user->file_path_customer .
                            $file_name;
                    }
                    break;
                case 'concurso_image':
                    $concurso = Concurso::find((int) $id);
                    $file_path = config('app.images_path') . $file_name;
                    break;
                default:
                    $file_path = $file_name;
                    break;
            }

            $file_path_absolute = rootPath() . filePath('/' . $file_path);


            if ($file_path_absolute && file_exists($file_path_absolute)) {
                $success = true;
            } else {
                $message = 'Archivo no encontrado.';
            }

        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
        }

        return $this->json($response, [
            'success' => $success,
            'message' => $message,
            'data' => [
                'public_path' => filePath('/' . $file_path, true)
            ]
        ], $status);
    }

    public function downloadZip(Request $request, Response $response)
{
    $body = $request->getParsedBody();
    $success = false;
    $message = null;
    $status = 200;

    try {
        $concurso = Concurso::find($body['Entity']['Id']);
        if (!$concurso) {
            throw new \RuntimeException('Concurso no encontrado.');
        }

        // Sanitizador para nombres de archivo y carpetas
        $sanitize = function ($text) {
            $text = (string)$text;
            $t = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
            if ($t === false) { $t = $text; }
            $t = preg_replace('/[^A-Za-z0-9]+/', '_', $t);
            $t = trim(preg_replace('/_+/', '_', $t), '_');
            return $t !== '' ? strtolower($t) : 'concurso';
        };

        // Sanitizador para archivos - preserva extensión
        $sanitizeFile = function ($filename) {
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $base = pathinfo($filename, PATHINFO_FILENAME);

            $base = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $base);
            if ($base === false) { $base = pathinfo($filename, PATHINFO_FILENAME); }
            $base = preg_replace('/[^A-Za-z0-9]+/', '_', $base);
            $base = trim(preg_replace('/_+/', '_', $base), '_');

            return $ext ? strtolower($base . '.' . $ext) : strtolower($base);
        };

        $safeConcursoName = $sanitize($concurso->nombre ?? $concurso->Nombre ?? 'concurso');
        $filename = "{$concurso->id}_{$safeConcursoName}.zip";

        $basepath = rootPath() . filePath(config('app.files_tmp')); // Debe terminar en '/'
        if (!is_dir($basepath)) {
            @mkdir($basepath, 0777, true);
        }

        $filepath = $basepath . $filename;

        $zip = new \ZipArchive();
        if ($zip->open($filepath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('No pudo abrirse el archivo ZIP para escritura.');
        }

        $filesAdded = 0;

        // ====== Archivos del Concurso ======
        $attachments_concurso = $concurso->attachments ?? [];
        foreach ($attachments_concurso as $attachment) {
            switch ($attachment->name) {
                case 'imagen': $folder = 'concurso/imagen/'; break;
                case 'pliego': $folder = 'concurso/pliegos/'; break;
                default:       $folder = 'concurso/otros/';   break;
            }
            $safeInnerName = $sanitizeFile(pathinfo($attachment->filename, PATHINFO_BASENAME));
            $attachment_path = rootPath() . $attachment->path;
            if (is_file($attachment_path)) {
                $zip->addFile($attachment_path, $folder . $safeInnerName);
                $filesAdded++;
            }
        }

        // ====== Archivos de Oferentes ======
        $oferentes = $concurso->oferentes ?? [];
        foreach ($oferentes as $oferente) {
            $companyName = $sanitize($oferente->company->business_name ?? 'oferente');
            $baseOffererFolder = "oferentes/{$companyName}/";

            // Por si hay prefijo de ruta en BD
            $file_path_prefix = filePath('/' . ($oferente->file_path ?? ''));

            // Técnica
            $technical_proposal = $oferente->technical_proposal;
            if (isset($technical_proposal->documents)) {
                $folder = $baseOffererFolder . 'tecnica/';
                foreach ($technical_proposal->documents as $document) {
                    $attachment_path = rootPath() . $file_path_prefix . $document->filename;
                    $safeDocName = $sanitizeFile(pathinfo($document->filename, PATHINFO_BASENAME));
                    if (is_file($attachment_path)) {
                        // Opcional: sólo crear directorio lógico, ZipArchive lo maneja al agregar archivo
                        $zip->addFile($attachment_path, $folder . $safeDocName);
                        $filesAdded++;
                    }
                }
            }

            // Económica
            $economic_proposal = $oferente->economic_proposal;
            if (isset($economic_proposal->documents)) {
                $folder = $baseOffererFolder . 'economica/';
                foreach ($economic_proposal->documents as $document) {
                    $attachment_path = rootPath() . $file_path_prefix . $document->filename;
                    $safeDocName = $sanitizeFile(pathinfo($document->filename, PATHINFO_BASENAME));
                    if (is_file($attachment_path)) {
                        $zip->addFile($attachment_path, $folder . $safeDocName);
                        $filesAdded++;
                    }
                }
            }
        }

        // Cerrar ZIP SIEMPRE
        $zip->close();

        if ($filesAdded > 0 && is_file($filepath)) {
            $success = true;
            $message = 'Archivo generado con éxito.';
            $status = 200;
        } else {
            // Sin contenido: eliminar ZIP vacío para no dejar basura
            if (is_file($filepath)) { @unlink($filepath); }
            $success = false;
            $message = 'No hay archivos para descargar.';
            $status = 400;
        }

    } catch (\Exception $e) {
        $success = false;
        $message = $e->getMessage();
        $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
    }

    // Rutas que verá el front
    return $this->json($response, [
        'success' => $success,
        'message' => $message,
        'data' => [
            'real_path'          => filePath(config('app.files_tmp') . ($success ? $filename : '')),
            'public_path'        => filePath(config('app.files_tmp') . ($success ? $filename : ''), true),
            'suggested_filename' => $success ? $filename : null,
        ]
    ], $status);
}


}