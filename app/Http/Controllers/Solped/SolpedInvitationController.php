<?php

namespace App\Http\Controllers\Solped;

use App\Http\Controllers\BaseController;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Models\Solped;
use App\Services\EmailService;
use Carbon\Carbon;

class SolpedInvitationController extends BaseController
{
    public function send(Request $request, Response $response)
    {
        $success = false;
        $message = '';
        $status = 200;

        try {
            $body = $request->getParsedBody();

            // Buscar la solicitud
            $solped = Solped::find((int)$body['IdSolicitud']);
            if (!$solped) {
                throw new \Exception('Solicitud no encontrada.');
            }

            // Cambiar estado de la solicitud
            $solped->estado = 'pendiente_revision';
            $solped->save();

            // Obtener todos los usuarios de la empresa compradora
            $compradores = $solped->compradores; // relación hasMany User
            $users = $compradores->pluck('email')->toArray();

            if (empty($users)) {
                throw new \Exception('No hay usuarios disponibles para notificar.');
            }

            // Tomamos el nombre de la empresa (asumimos que todos los usuarios son de la misma empresa)
            $companyName = $compradores->first()->customer_company->business_name ?? 'Empresa';

            // Preparar correo
            $title = 'Nueva Solicitud de Pedido para Revisión';
            $subject = $companyName . ' - ' . $title;
            $template = rootPath(config('app.templates_path')) . '/email/new-solped-for-customer.tpl';

            $emailService = new EmailService();
            $html = $this->fetch($template, [
                'title' => $title,
                'ano' => Carbon::now()->format('Y'),
                'solped' => $solped,
                'solicitante' => $solped->solicitante,
                'company_name' => $companyName
            ]);

            // Enviar correo
            $result = $emailService->send($html, $subject, $users, "");

            $success = $result['success'];
            $message = $success ? 'Notificación enviada correctamente.' : 'Error al enviar la notificación.';

        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
        }

        return $this->json($response, [
            'success' => $success,
            'message' => $message
        ], $status);
    }
}
