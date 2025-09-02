<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use App\Models\Mailer;

class EmailService
{
    protected $mail = null;
    // CID y bandera para insertar el logo de Telecentro en el footer (SMTP y Graph)
    protected $telecentroCid = 'telecentro_footer_logo';
    protected $telecentroEmbedded = false;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);
        $this->mail->isSMTP();
        $this->mail->SMTPDebug = 0;
        $this->mail->Debugoutput = 'html';
        $this->mail->SMTPAuth = true;

        $customer_company_id = isset($_SESSION['customer_company_id']) ? $_SESSION['customer_company_id'] : null;

        
        if ($customer_company_id === null) {
            $this->mail->Host = env('MAIL_HOST');
            $this->mail->Port = env('MAIL_PORT');
            $this->mail->SMTPSecure = env('MAIL_SMTP');
            $this->mail->Username = env('MAIL_USERNAME');
            $this->mail->Password = env('MAIL_PASSWORD');
            $logo = 'images/logo.jpg';

            $this->mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            $this->mail->ClearAddresses();
            $this->mail->setFrom(env('MAIL_USERNAME'), env('MAIL_ALIAS'));

            $this->mail->Priority = '3';
            $this->mail->Timeout = 600;
            $this->mail->IsHTML(true);
            $this->mail->CharSet = 'UTF-8';
            $this->mail->SMTPKeepAlive = true;

            $this->mail->AddEmbeddedImage(publicPath(asset('/global/img/logo-small.png')), 'front', $logo);
        } else {
            $lst = Mailer::where(
                function ($query) use ($customer_company_id) {
                    $query->where('customer_company_id', '=', $customer_company_id);
                }
            )
                ->get()
                ->first();

            if ($lst) {
                $this->mail->Host = $lst['host'];
                $this->mail->Port = $lst['port'];
                $this->mail->SMTPSecure = $lst['smtp'];
                $this->mail->Username = $lst['user'];
                $this->mail->Password = $lst['pass'];
                $logo = $lst['logo'];

                $this->mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ]
                ];

                $this->mail->ClearAddresses();
                
                // Verificar si el campo userAlt es null
                if (is_null($lst['userAlt'])) {
                    $this->mail->setFrom($lst['user'], $lst['alias']);
                } else {
                    $this->mail->setFrom($lst['userAlt'], $lst['alias']);
                }

                $this->mail->Priority = '3';
                $this->mail->Timeout = 600;
                $this->mail->IsHTML(true);
                $this->mail->CharSet = 'UTF-8';
                $this->mail->SMTPKeepAlive = true;
                $this->mail->AddEmbeddedImage(publicPath(asset($lst['logosmall'])), 'front');
            } else {
                $this->mail->Host = env('MAIL_HOST');
                $this->mail->Port = env('MAIL_PORT');
                $this->mail->SMTPSecure = env('MAIL_SMTP');
                $this->mail->Username = env('MAIL_USERNAME');
                $this->mail->Password = env('MAIL_PASSWORD');
                $logo = 'images/logo.jpg';

                $this->mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ]
                ];

                $this->mail->ClearAddresses();
                $this->mail->setFrom(env('MAIL_USERNAME'), env('MAIL_ALIAS'));

                $this->mail->Priority = '3';
                $this->mail->Timeout = 600;
                $this->mail->IsHTML(true);
                $this->mail->CharSet = 'UTF-8';
                $this->mail->SMTPKeepAlive = true;

                $this->mail->AddEmbeddedImage(publicPath(asset('/global/img/logo-small.png')), 'front', $logo);
            }
        }
    }

    public function send($message, $subject, $email_to, $alias, $withCC = false)
    {
        $customer_company_id = isset($_SESSION['customer_company_id']) ? $_SESSION['customer_company_id'] : null;

        $result = [
            'success' => false,
            'message' => ''
        ];

        if ($customer_company_id == 7) {
            $result = $this->sendMailTelecentro($message, $subject, $email_to, $alias);
            return $result;
        }
        
        // Set Recipient
        $this->mail->ClearAddresses();
        foreach ($email_to as $email) {
            $this->mail->addAddress($email);
        }
        if ($withCC) {
            $this->mail->addCC(env('MAIL_CC'));
        }
        // Set Subject
        $this->mail->Subject = $subject;
        // Set Message
        $this->mail->msgHTML($message);
        $this->mail->AltBody = strip_tags($message);
        try {
            if ($this->mail->send()) {
                $result['message'] = 'Mensaje enviado con éxito.';
                $result['success'] = true;
            } else {
                $result['message'] = $this->mail->ErrorInfo;
                $result['success'] = true;
            }
        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = $e->getMessage();
        }

        return $result;
    }

    public function sendMultiple($emails)
    {
        $results = [];
        foreach ($emails as $emailData) {
            // Verifica que el emailData contenga los datos necesarios
            if (!isset($emailData['message'], $emailData['subject'], $emailData['email_to'], $emailData['alias'])) {
                $results[] = [
                    'success' => false,
                    'message' => 'Datos incompletos para el correo.'
                ];
                continue;
            }

            // Envía cada correo individualmente
            $result = $this->send(
                $emailData['message'],
                $emailData['subject'],
                $emailData['email_to'],
                $emailData['alias'],
                isset($emailData['withCC']) ? $emailData['withCC'] : false
            );
            $results[] = $result;
        }
        return $results;
    }

    private function guessMimeType(string $path): string
    {
        if (function_exists('mime_content_type')) {
            $t = @mime_content_type($path);
            if ($t) return $t;
        }
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $map = ['png'=>'image/png','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','gif'=>'image/gif','svg'=>'image/svg+xml'];
        return $map[$ext] ?? 'application/octet-stream';
    }

    private function resolvePublicPath(string $rel): ?string
    {
        try {
            if (function_exists('publicPath') && function_exists('asset')) {
                $abs = publicPath(asset($rel));
            } else {
                $abs = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'public'.str_replace('/', DIRECTORY_SEPARATOR, $rel);
            }
            return file_exists($abs) ? $abs : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function getFrontLogoPath(): ?string
    {
        $customer_company_id = $_SESSION['customer_company_id'] ?? null;

        // 1) Intentá con logosmall de Mailer
        if ($customer_company_id) {
            $lst = Mailer::where('customer_company_id', $customer_company_id)->first();
            if ($lst && !empty($lst['logosmall'])) {
                $p = $this->resolvePublicPath($lst['logosmall']);
                if ($p) return $p;
            }
        }
        // 2) Fallbacks
        foreach (['/global/img/logo-small.png','/assets/global/img/logo-small.png'] as $rel) {
            $p = $this->resolvePublicPath($rel);
            if ($p) return $p;
        }
        return null;
    }
    private function sendMailTelecentro($message, $subject, $email_to, $alias)
    {
        // CONFIGURAR LAS VARIABLES CON LOS DATOS DE TU APLICACIÓN DE AZURE
        $tenantId     = env('TENANT_MAILER_TLC');
        $clientId     = env('CLIENT_MAILER_TLC');
        $clientSecret = env('SECRET_MAILER_TLC');

        // 1. OBTENER EL TOKEN DE ACCESO
        $tokenUrl = "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token";
        $tokenData = [
            "client_id"     => $clientId,
            "client_secret" => $clientSecret,
            "scope"         => "https://graph.microsoft.com/.default",
            "grant_type"    => "client_credentials"
        ];
        $ch = curl_init($tokenUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($tokenData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            return ['success' => false, 'message' => 'Error al obtener token: '.curl_error($ch)];
        }
        curl_close($ch);

        $responseData = json_decode($response, true);
        if (!isset($responseData['access_token'])) {
            return ['success' => false, 'message' => "Error obteniendo token. Respuesta: $response"];
        }
        $accessToken = $responseData['access_token'];

        // 2. PREPARAR DESTINATARIOS
        $toRecipients = [];
        foreach ($email_to as $recipient) {
            $toRecipients[] = [
                "emailAddress" => [
                    "address" => $recipient
                ]
            ];
        }

        // 3. ENVIAR CORREO CON LA API DE MICROSOFT GRAPH
        // Adjuntar logo de Telecentro como imagen inline (si existe) y agregar footer HTML
        $attachments = [];
        $telecentroPath = $this->getTelecentroImagePath();
        // (A) Adjuntar el logo principal para <img src="cid:front">
        if ($frontPath = $this->getFrontLogoPath()) {
            $attachments[] = [
                '@odata.type' => '#microsoft.graph.fileAttachment',
                'name'        => basename($frontPath),
                'contentType' => $this->guessMimeType($frontPath),
                'contentBytes'=> base64_encode(file_get_contents($frontPath)),
                'isInline'    => true,
                'contentId'   => 'front',
            ];
        }

        // (B) Footer de Telecentro: agrega el HTML y adjunta el PNG inline
        if ($telecentroPath = $this->getTelecentroImagePath()) {
            $message .= $this->getTelecentroFooterHtml($this->telecentroCid);
            $attachments[] = [
                '@odata.type' => '#microsoft.graph.fileAttachment',
                'name'        => 'telecentro.png',
                'contentType' => $this->guessMimeType($telecentroPath),
                'contentBytes'=> base64_encode(file_get_contents($telecentroPath)),
                'isInline'    => true,
                'contentId'   => $this->telecentroCid,
            ];
        }

        $senderEmail = "licitaciones@telecentro.net.ar";
        $mailUrl     = "https://graph.microsoft.com/v1.0/users/{$senderEmail}/sendMail";

        $emailBody = [
            "message" => [
                "subject"      => $subject,
                "body"         => ["contentType" => "HTML", "content" => $message],
                "toRecipients" => $toRecipients,
            ],
            "saveToSentItems" => true,
        ];

        if (!empty($attachments)) {
            $emailBody['message']['attachments'] = $attachments;
        }

        $ch = curl_init($mailUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer {$accessToken}",
                "Content-Type: application/json"
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS     => json_encode($emailBody),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch)) {
            curl_close($ch);
            return ['success' => false, 'message' => 'Error enviando correo: '.curl_error($ch)];
        }
        curl_close($ch);

        if ($httpCode === 202) {
            return ['success' => true, 'message' => 'Correo enviado exitosamente.'];
        } else {
            return ['success' => false, 'message' => "Error enviando correo. HTTP $httpCode"];
        }
    }

    // ---- Helpers para footer con imagen Telecentro ----
    private function embedTelecentroLogoIfAvailable(): bool
    {
        if ($this->telecentroEmbedded) {
            return true;
        }
        $path = $this->getTelecentroImagePath();
        if (!$path || !file_exists($path)) {
            return false;
        }
        if ($this->mail->AddEmbeddedImage($path, $this->telecentroCid, 'telecentro.png')) {
            $this->telecentroEmbedded = true;
            return true;
        }
        return false;
    }

    private function getTelecentroFooterHtml(string $cid): string
    {
        $html  = '<div style="margin-top:16px;padding-top:12px;border-top:1px solid #e6e6e6;text-align:center">';
        $html .= '<img src="cid:' . htmlspecialchars($cid, ENT_QUOTES, 'UTF-8') . '" alt="Telecentro" style="height:36px;" />';
        $html .= '</div>';
        return $html;
    }

    private function getTelecentroImagePath(): ?string
    {
        $candidates = [
            '/assets/global/img/Logos/TLC/telecentro.png',
            '/assets/telecentro.png',
            '/global/img/telecentro.png',
            '/img/telecentro.png',
            '/telecentro.png',
        ];
        foreach ($candidates as $rel) {
            try {
                if (function_exists('publicPath') && function_exists('asset')) {
                    $abs = publicPath(asset($rel));
                } else {
                    $abs = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . str_replace('/', DIRECTORY_SEPARATOR, $rel);
                }
                if (is_string($abs) && file_exists($abs)) {
                    return $abs;
                }
            } catch (\Throwable $e) {
                // continuar intentando
            }
        }
        return null;
    }
}
