<?php

namespace App\Http\Controllers\AD;

use App\Http\Controllers\BaseController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\CustomerCompany;
use App\Models\OffererCompany;
use App\Services\EmailService;

class ADController extends BaseController
{
    private function generateToken() 
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 20);
    }

    private function setUsuario($user) 
    {
        $token = $this->generateToken();

        try {
            $user->update([
                'token'         => $token,
                'validity_date' => Carbon::now()->addMinutes(150)->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return null;
        }
        
        $_SESSION['user_id'] = $user->id;
        $_SESSION['type_id'] = $user->type_id;
        $_SESSION['customer_company_id'] = $user->customer_company_id;
        $_SESSION['pass_change'] = $user->pass_change;
        $_SESSION['token'] = $token;
        $_SESSION['type'] = $user->type->code;
        $_SESSION['permissions'] = $user->permissions->pluck('code')->toArray();

        return [
            'Token' => $token,
            'Id' => $user->id,
            'Nombre' => ucfirst(strtolower($user->first_name)),
            'Apellido' => ucfirst(strtolower($user->last_name)),
            'FullName' => ucfirst(strtolower($user->full_name)),
            'Image' => $user->image,
            'Email' => $user->email,
            'Tipo' => (int) $user->type->id,
            'PassChange' => $user->pass_change,
            'Permissions' => $user->permissions->pluck('code'),
            'isAdmin' => isAdmin(),
            'isCustomer' => isCustomer(),
            'isOfferer' => isOfferer()
        ];
    }

    public function loginLG(Request $request, Response $response)
    {
        $client_id = env('CLIENT_ID_LG');
        $adTenant = env('TENANT_ID_LG');
        $redirect_uri = env('CALLBACK_AD');
        setcookie('AD', 'LG', time() + 3600, '/');

        if (!isset($_GET["code"]) && !isset($_GET["error"])) {
            
            // Generar code_verifier y code_challenge
            $code_verifier = bin2hex(random_bytes(32));
            $code_challenge = rtrim(strtr(base64_encode(hash('sha256', $code_verifier, true)), '+/', '-_'), '=');

            // Guardar el code_verifier en la sesión
            $_SESSION['code_verifier'] = $code_verifier;
            
            // Redirigir al usuario para la autenticación
            $url = "https://login.microsoftonline.com/" . $adTenant . "/oauth2/v2.0/authorize?";
            $url .= "state=" . session_id();
            $url .= "&scope=User.Read";
            $url .= "&response_type=code";
            $url .= "&approval_prompt=auto";
            $url .= "&client_id=" . $client_id;
            $url .= "&redirect_uri=" . urlencode($redirect_uri);
            $url .= "&code_challenge=" . $code_challenge;
            $url .= "&code_challenge_method=S256";

            header("Location: " . $url);
            exit();
        }
    }

    public function loginTLC(Request $request, Response $response)
    {
        $client_id = env('CLIENT_ID_TLC');
        $adTenant = env('TENANT_ID_TLC');
        $redirect_uri = env('CALLBACK_AD');
        setcookie('AD', 'TLC', time() + 3600, '/');

        if (!isset($_GET["code"]) && !isset($_GET["error"])) {
            
            // Generar code_verifier y code_challenge
            $code_verifier = bin2hex(random_bytes(32));
            $code_challenge = rtrim(strtr(base64_encode(hash('sha256', $code_verifier, true)), '+/', '-_'), '=');

            // Guardar el code_verifier en la sesión
            $_SESSION['code_verifier'] = $code_verifier;
            
            // Redirigir al usuario para la autenticación
            $url = "https://login.microsoftonline.com/" . $adTenant . "/oauth2/v2.0/authorize?";
            $url .= "state=" . session_id();
            $url .= "&scope=User.Read";
            $url .= "&response_type=code";
            $url .= "&approval_prompt=auto";
            $url .= "&client_id=" . $client_id;
            $url .= "&redirect_uri=" . urlencode($redirect_uri);
            $url .= "&code_challenge=" . $code_challenge;
            $url .= "&code_challenge_method=S256";

            header("Location: " . $url);
            exit();
        }
    }

    public function callback(Request $request, Response $response)
    {
        // TOMAR VARIABLES DE ENTORNO ENV
        $ad = $_COOKIE['AD'] ?? null;
        if ($ad == 'LG') {
            $clientId     = env('CLIENT_ID_LG');
            $adTenant     = env('TENANT_ID_LG');
            $clientSecret = env('CLIENT_SECRET_LG');
        } else if ($ad == 'TLC') {
            $clientId     = env('CLIENT_ID_TLC');
            $adTenant     = env('TENANT_ID_TLC');
            $clientSecret = env('CLIENT_SECRET_TLC');
        } else {
            echo "Error: AD cookie no válida";
            return;
        }
        
        $redirectUri = env('CALLBACK_AD');
        $token_url = "https://login.microsoftonline.com/" . $adTenant . "/oauth2/v2.0/token";

        // Verificar que se recibieron los parámetros necesarios
        $queryParams = $request->getQueryParams();
        if (!isset($queryParams['code']) || !isset($queryParams['state'])) {
            echo "Error: código de autorización o estado no encontrados";
            return;
        }
        
        $code  = $queryParams['code'];
        $state = $queryParams['state'];
        
        // Verificar el estado de la sesión
        if ($state !== session_id()) {
            // Registrar error en logs
            $this->login_logs("LoginAD", null, 'F', 'AD', 'Estado inválido');
            echo "Error: estado inválido";
            return;
        }

        // Verificar el código de verificación
        if (!isset($_SESSION['code_verifier'])) {
            $this->login_logs("LoginAD", null, 'F', 'AD', 'Código de verificación no encontrado');
            echo "Error: código de verificación no encontrado";
            return;
        }
        $code_verifier = $_SESSION['code_verifier'];

        // Datos para la solicitud del token
        $post_data = [
            "grant_type"    => "authorization_code",
            "client_id"     => $clientId,
            "client_secret" => $clientSecret,
            "code"          => $code,
            "redirect_uri"  => $redirectUri,
            "code_verifier" => $code_verifier
        ];

        // Solicitar el token de acceso usando CURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $token_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $tokenResponse = curl_exec($ch);
        curl_close($ch);

        $tokenJson = json_decode($tokenResponse, true);
        if (isset($tokenJson['access_token'])) {
            $access_token = $tokenJson['access_token'];
        } else {
            $this->login_logs("LoginAD", null, 'F', 'AD', 'Error al obtener el token de acceso');
            echo "Error al obtener el token de acceso: " . json_encode($tokenJson);
            return;
        }

        // Solicitar información del usuario a Microsoft Graph
        $graph_url = "https://graph.microsoft.com/v1.0/me?\$select=mail,givenName,surname,mobilePhone";
        $headers = [
            "Authorization: Bearer " . $access_token
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $graph_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $graphResponse = curl_exec($ch);
        curl_close($ch);

        $userMicrosoft = json_decode($graphResponse, true);
        if (!isset($userMicrosoft['mail'])) {
            $this->login_logs("LoginAD", null, 'F', 'AD', 'Información de usuario incompleta');
            echo "Error: no se recibió información de usuario completa";
            return;
        }
        
        $user_email = $userMicrosoft['mail'];

        // Buscar el usuario en la base de datos
        $user = User::where(function ($query) use ($user_email) {
            $query->where('email', '=', $user_email);
        })->first();

        if ($user) {
            // Usuario encontrado
            $userLogs = $user;
            $user = $this->setUsuario($user, '');
            $this->login_logs("LoginAD", $userLogs, 'S', 'AD', 'Inicio de sesión exitoso');
        } else {
            // Usuario no existe, se crea uno nuevo
            $user = $this->nuevoUsuario($userMicrosoft);
            $userLogs = $user;
            $user = $this->setUsuario($user, '');
            $this->login_logs("LoginAD", $userLogs, 'S', 'AD', 'Inicio de sesión exitoso');
        }

        // Guardar datos en localStorage o cookie y redirigir
        echo "
            <script>
                localStorage.setItem('userdata', '" . json_encode($user) . "');
                document.cookie = 'customer_company_id=" . $_SESSION['customer_company_id'] . "; path=/';
                window.location.href = '/login';
            </script>
        ";
        exit();
    }

    private function nuevoUsuario($userMicrosoft) 
    {
        $user_email = $userMicrosoft['mail'];
        $user_tel = $userMicrosoft['mobilePhone'];
        $user_tel = preg_replace('/\D/', '', $user_tel);
        $user_name = $userMicrosoft['givenName'];
        $user_surname = $userMicrosoft['surname'];
        $user_username = strtolower(substr($user_name, 0, 1) . explode(' ', $user_surname)[0]);
        $user_type = 3;
        // $user_type = strtolower(str_replace(' ', '', iconv('UTF-8', 'ASCII//TRANSLIT', $user_type)));

        // switch ($user_type) {
        //     case 'cliente':
        //         $user_type = 3;
        //         break;
        //     case 'tecnico':
        //         $user_type = 4;
        //         break;
        //     case 'visor':
        //         $user_type = 5;
        //         break;
        //     case 'proveedor':
        //         $user_type = 6;
        //         break;
        //     case 'evaluador':
        //         $user_type = 7;
        //         break;
        //     default:
        //         $user_type = 0;
        //         break;
        // }

        // $user_cuit = $userMicrosoft['companyName'];
        // $user_cuit = preg_replace('/\D/', '', $user_cuit);

        // $company = OffererCompany::where(
        //     function ($query) use ($user_cuit) {
        //         $query
        //             ->where('cuit', '=', $user_cuit);
        //     }
        // )
        // ->get()
        // ->first();
        

        // if ($company == null) {
        //     $company = CustomerCompany::where(
        //         function ($query) use ($user_cuit) {
        //             $query
        //                 ->where('cuit', '=', $user_cuit);
        //         }
        //     )
        //     ->get()
        //     ->first();
        //     $customer_company_id = $company->id;
        //     $offerer_company_id = null;
        // } else {
        //     $offerer_company_id = $company->id;
        //     $customer_company_id = null;
        // }

        $domain_with_at = strstr($user_email, '@');
        $domain = substr($domain_with_at, 1);

        if ($domain == 'losgrobo.com') {
            $customer_company_id = 13;
            $offerer_company_id = null;
        } else if ($domain == 'agrofina.com.ar') {
            $customer_company_id = 14;
            $offerer_company_id = null;
        } else if ($domain == 'telecentro.net.ar') {
            $customer_company_id = 7;
            $offerer_company_id = null;
        } else if ($domain == 'laborcorporativa.com.ar') {
            $customer_company_id = 7;
            $offerer_company_id = null;
        }

        $length = 8;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomPassword = '';
        for ($i = 0; $i < $length; $i++) {
            $randomPassword .= $characters[rand(0, $charactersLength - 1)];
        }
        $md5 = md5($randomPassword);

        $userData = [
            'type_id' => $user_type,
            'status_id' => 1,
            'username' => $user_username,
            'password' => $md5,
            'first_name' => $user_name,
            'last_name' => $user_surname,
            'phone' => $user_tel,
            'cellphone' => $user_tel,
            'area' => 'Compras',
            'rol' => 'Comprador',
            'email' => $user_email,
            'ad' => 'S'
        ];

        if ($offerer_company_id !== null) {
            $userData['offerer_company_id'] = $offerer_company_id;
        }
        
        if ($customer_company_id !== null) {
            $userData['customer_company_id'] = $customer_company_id;
        }
        
        $user = new User($userData);
        $user->save();
        $this->permisionClient($user->id);

        $user = User::where(
            function ($query) use ($user_email) {
                $query
                    ->where('email', '=', $user_email);
            }
        )
        ->get()
        ->first();

        return $user;
    }

    private function permisionClient($user_id)
    {
        $user = User::find($user_id);
        $permisions = [
            [
                "id" => "2",
                "description" => "Concursos",
                "permissions" => [
                    [
                        "id" => 2,
                        "description" => "Subasta",
                        "active" => false
                    ],
                    [
                        "id" => 3,
                        "description" => "Licitación",
                        "active" => true
                    ],
                    [
                        "id" => 4,
                        "description" => "Go",
                        "active" => false
                    ],
                    [
                        "id" => 5,
                        "description" => "Por+Etapas",
                        "active" => true
                    ]
                ],
                "active" => false
            ],
            [
                "id" => "3",
                "description" => "Usuarios",
                "permissions" => [
                    [
                        "id" => 6,
                        "description" => "Edición+de+Usuarios",
                        "active" => false
                    ]
                ],
                "active" => false
            ],
            [
                "id" => "5",
                "description" => "Empresas",
                "permissions" => [
                    [
                        "id" => 15,
                        "description" => "Proveedores",
                        "active" => true
                    ],
                    [
                        "id" => 16,
                        "description" => "Clientes",
                        "active" => false
                    ],
                    [
                        "id" => 18,
                        "description" => "Materiales+del+Clientes",
                        "active" => true
                    ]
                ],
                "active" => false
            ],
            [
                "id" => "7",
                "description" => "Muro+de+Consultas",
                "permissions" => [
                    [
                        "id" => 13,
                        "description" => "Ver+Chat",
                        "active" => true
                    ],
                    [
                        "id" => 14,
                        "description" => "Botones+de+Moderación",
                        "active" => true
                    ]
                ],
                "active" => true
            ]
        ];
        $permission_ids = [];
        foreach ($permisions as $group) {
            foreach ($group["permissions"] as $permission) {
                if ($permission["active"]) {
                    $permission_ids[] = (int) $permission["id"];
                }
            }
        }
        $user->permissions()->sync($permission_ids);
    }

    private function login_logs($username, $user, $estado, $tipo, $detalle)
    {
        // Si el usuario existe se toman sus datos, caso contrario se deja null
        $offerer_company_id = $user ? $user->offerer_company_id : null;
        $customer_company_id = $user ? $user->customer_company_id : null;
        $user_id = $user ? $user->id : null;
        $fecha = Carbon::now()->format('Y-m-d H:i:s');
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'Unknown';

        try {
            $capsule = dependency('db');
            $connection = $capsule->getConnection();
            $pdo = $connection->getPdo();
            $sql = "INSERT INTO login_logs (username, customer_company_id, offerer_company_id, user_id, estado, detalle, tipo, fecha, ip) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $statement = $pdo->prepare($sql);
            $statement->execute([$username, $customer_company_id, $offerer_company_id, $user_id, $estado, $detalle, $tipo, $fecha, $ip]);
        } catch (\Exception $e) {
            // Se registra el error sin interrumpir la ejecución
            error_log($e->getMessage());
        }
    }
}
?>