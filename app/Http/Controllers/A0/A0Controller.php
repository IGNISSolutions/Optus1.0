<?php

namespace App\Http\Controllers\A0;

use App\Http\Controllers\BaseController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Carbon\Carbon;
use App\Models\User;


class A0Controller extends BaseController
{
    /* ===========================
     * Helpers base (idénticos a AD)
     * =========================== */

    private function generateToken()
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 20);
    }

    /**
     * Setea sesión + token + respuesta "userdata" (igual a ADController->setUsuario)
     */
    private function setUsuario($user)
    {
        $token = $this->generateToken();

        try {
            $user->update([
                'token' => $token,
                'validity_date' => Carbon::now()->addMinutes(150)->format('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            return null;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
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
            'isOfferer' => isOfferer(),
        ];
    }

    /**
     * Permisos por defecto para clientes (idéntico criterio al ADController->permisionClient)
     */
    private function permisionClient($user_id)
    {
        $user = User::find($user_id);

        // Ajustá este payload si tus permisos difieren
        $permisions = [
            [
                "id" => "2",
                "description" => "Concursos",
                "permissions" => [
                    ["id" => 2, "description" => "Subasta", "active" => true],
                    ["id" => 3, "description" => "Licitación", "active" => true],
                    ["id" => 5, "description" => "Archivos", "active" => true],
                ],
                "active" => true
            ]
        ];

        $permission_ids = [];
        foreach ($permisions as $group) {
            foreach ($group["permissions"] as $permission) {
                if (!empty($permission["active"])) {
                    $permission_ids[] = (int) $permission["id"];
                }
            }
        }
        $user->permissions()->sync($permission_ids);
    }

    /**
     * Auditoría de login (idéntico espíritu al ADController->login_logs)
     */
    private function login_logs($accion, $userOrNull, $estado, $tipo, $detalle = '')
    {
        try {
            $capsule = dependency('db');
            $pdo = $capsule->getConnection()->getPdo();
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
            $fecha = Carbon::now()->format('Y-m-d H:i:s');

            $username = $userOrNull->username ?? null;
            $customer_company_id = $userOrNull->customer_company_id ?? null;
            $offerer_company_id = $userOrNull->offerer_company_id ?? null;
            $user_id = $userOrNull->id ?? null;

            $sql = "INSERT INTO login_logs (accion, username, customer_company_id, offerer_company_id, user_id, estado, detalle, tipo, fecha, ip) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $statement = $pdo->prepare($sql);
            $statement->execute([
                $accion,
                $username,
                $customer_company_id,
                $offerer_company_id,
                $user_id,
                $estado,        // 'S'/'F'
                $detalle,
                $tipo,          // 'A0'
                $fecha,
                $ip
            ]);
        } catch (\Exception $e) {
            error_log("login_logs error: " . $e->getMessage());
        }
    }

    /**
     * Alta automática de usuario desde el payload de Auth0 (similar a ADController->nuevoUsuario)
     */
    private function nuevoUsuario(array $auth0User)
    {
        $user_email = $auth0User['email'] ?? null;
        $user_tel = $auth0User['phone_number'] ?? '';
        $user_tel = preg_replace('/\D/', '', (string) $user_tel);

        $user_name = $auth0User['given_name'] ?? ($auth0User['name'] ?? '');
        $user_surname = $auth0User['family_name'] ?? (explode(' ', $auth0User['name'] ?? '')[1] ?? '');
        
        // Generar username basado en el email (parte antes del @) + número aleatorio
        $emailParts = explode('@', $user_email);
        $baseUsername = strtolower(preg_replace('/[^a-z0-9]/', '', strtolower($emailParts[0] ?? 'user')));
        
        // Si el username está vacío o es muy corto, usar fallback
        if (strlen($baseUsername) < 3) {
            $baseUsername = 'user';
        }
        
        // Agregar número aleatorio para evitar duplicados
        $user_username = $baseUsername . rand(100, 999);

        $length = 8;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomPassword = '';
        for ($i = 0; $i < $length; $i++) {
            $randomPassword .= $characters[rand(0, $charactersLength - 1)];
        }
        $md5 = md5($randomPassword);

        // Determinar customer_company_id según el cliente de Auth0
        $customerCompanyId = null;
        if (isset($_SESSION['A0_CLIENT']) && $_SESSION['A0_CLIENT'] === 'SCR') {
            $customerCompanyId = 22; // Sancor
        }

        $userData = [
            'type_id' => 3,
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
            'customer_company_id' => $customerCompanyId,
        ];

        $user = new User($userData);
        $user->save();

        $this->permisionClient($user->id);

        // Retornar el user cargado desde DB (evita issues de relaciones perezosas)
        return User::where(function ($q) use ($user_email) {
            $q->where('email', '=', $user_email);
        })->first();
    }

    /* ===========================
     *       Auth0: LOGIN
     * =========================== */

    /**
     * Inicia el flujo Authorization Code + PKCE contra Auth0
     */
    public function loginSCR(Request $request, Response $response)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Marcamos origen del login (Sancor Seguros)
        $_SESSION['A0_CLIENT'] = 'SCR';
        setcookie('A0', 'SCR', time() + 3600, '/');

        $domain = env('AUTH0_DOMAIN');
        $client_id = env('AUTH0_CLIENT_ID');
        $redirect_uri = env('CALLBACK_A0');
        $audience = env('AUTH0_AUDIENCE'); // opcional

        if (!$domain || !$client_id || !$redirect_uri) {
            echo "Error de configuración Auth0 (revisá variables de entorno).";
            return $response;
        }

        // PKCE
        $code_verifier = bin2hex(random_bytes(32));
        $code_challenge = rtrim(strtr(base64_encode(hash('sha256', $code_verifier, true)), '+/', '-_'), '=');
        $_SESSION['code_verifier'] = $code_verifier;

        // Cookie para auditar "A0"
        //setcookie('A0', 'SANCOR', time() + 3600, '/');

        $params = [
            'response_type' => 'code',
            'client_id' => $client_id,
            'redirect_uri' => $redirect_uri,
            'scope' => 'openid profile email',
            'code_challenge' => $code_challenge,
            'code_challenge_method' => 'S256',
            'prompt' => 'login', // Forzar siempre la pantalla de login (no usar SSO automático)
        ];
        if (!empty($audience)) {
            $params['audience'] = $audience;
        }

        $url = "https://{$domain}/authorize?" . http_build_query($params);

        // Redirección usando Slim Response
        return $response
            ->withHeader('Location', $url)
            ->withStatus(302);
    }

    /**
     * Callback de Auth0: intercambio de code por tokens, obtención de perfil y login local.
     */
    public function callback(Request $request, Response $response)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $domain = env('AUTH0_DOMAIN');
        $clientId = env('AUTH0_CLIENT_ID');
        $clientSecret = env('AUTH0_CLIENT_SECRET');
        $redirectUri = env('CALLBACK_A0');

        if (!$domain || !$clientId || !$clientSecret || !$redirectUri) {
            $this->login_logs("LoginA0", null, 'F', 'A0', 'Configuración Auth0 incompleta');
            echo "Error de configuración Auth0 (revisá variables de entorno).";
            return $response;
        }

        $queryParams = $request->getQueryParams();
        if (isset($queryParams['error'])) {
            $this->login_logs("LoginA0", null, 'F', 'A0', $queryParams['error_description'] ?? $queryParams['error']);
            echo "Error de autenticación: " . htmlspecialchars($queryParams['error_description'] ?? $queryParams['error']);
            return $response;
        }

        $code = $queryParams['code'] ?? null;
        if (!$code) {
            $this->login_logs("LoginA0", null, 'F', 'A0', 'Code no recibido');
            echo "Error: no se recibió código de autorización.";
            return $response;
        }

        if (empty($_SESSION['code_verifier'])) {
            $this->login_logs("LoginA0", null, 'F', 'A0', 'code_verifier no encontrado');
            echo "Error: code_verifier no encontrado.";
            return $response;
        }
        $code_verifier = $_SESSION['code_verifier'];

        // Intercambio por tokens
        $token_url = "https://{$domain}/oauth/token";
        $post_data = [
            'grant_type' => 'authorization_code',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'code_verifier' => $code_verifier,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $token_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $tokenResponse = curl_exec($ch);
        if ($tokenResponse === false) {
            $err = curl_error($ch);
            curl_close($ch);
            $this->login_logs("LoginA0", null, 'F', 'A0', 'cURL token error: ' . $err);
            echo "Error al solicitar token a Auth0.";
            return $response;
        }
        curl_close($ch);

        $tokenJson = json_decode($tokenResponse, true);
        if (!isset($tokenJson['access_token'])) {
            $this->login_logs("LoginA0", null, 'F', 'A0', 'access_token no recibido');
            echo "Error: no se recibió access_token de Auth0.";
            return $response;
        }

        $access_token = $tokenJson['access_token'];

        // Traer perfil /userinfo
        $userinfo_url = "https://{$domain}/userinfo";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $userinfo_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $access_token]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $userResponse = curl_exec($ch);
        if ($userResponse === false) {
            $err = curl_error($ch);
            curl_close($ch);
            $this->login_logs("LoginA0", null, 'F', 'A0', 'cURL userinfo error: ' . $err);
            echo "Error al obtener perfil de Auth0.";
            return $response;
        }
        curl_close($ch);

        $auth0User = json_decode($userResponse, true);

        // Campos clave
        $email = $auth0User['email'] ?? null;
        $name = $auth0User['name'] ?? null;

        if (!$email || !$name) {
            $this->login_logs("LoginA0", null, 'F', 'A0', 'Perfil incompleto (email/name faltantes)');
            echo "Error: perfil incompleto desde Auth0 (email/name requeridos).";
            return $response;
        }

        // Buscar usuario local
        $user = User::where(function ($q) use ($email) {
            $q->where('email', '=', $email);
        })->first();

        if ($user) {
            $userLogs = $user;
            $user = $this->setUsuario($user);
            $this->login_logs("LoginA0", $userLogs, 'S', 'A0', 'Inicio de sesión exitoso');
        } else {
            $user = $this->nuevoUsuario($auth0User);
            $userLogs = $user;
            $user = $this->setUsuario($user);
            $this->login_logs("LoginA0", $userLogs, 'S', 'A0', 'Usuario creado y login exitoso');
        }

        if (!$user) {
            $this->login_logs("LoginA0", null, 'F', 'A0', 'Fallo setUsuario');
            echo "Error interno al establecer la sesión de usuario.";
            return $response;
        }

        // Guardar datos en localStorage y cookie, luego redirigir al home (igual que AD)
        echo "
            <script>
                localStorage.setItem('userdata', '" . json_encode($user) . "');
                localStorage.setItem('auth_provider', 'A0');
                document.cookie = 'customer_company_id=" . ($_SESSION['customer_company_id'] ?? '') . "; path=/';
                window.location.href = '/';
            </script>
        ";
        exit();
        
    }

    /**
     * Logout de Auth0: cierra la sesión local y redirige al logout de Auth0 para cerrar SSO
     */
    public function logoutA0(Request $request, Response $response)
    {
        // Limpiar sesión local
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Limpiar token del usuario en BD si existe
        $user = user();
        if ($user) {
            $user->update([
                'token' => null,
                'validity_date' => null
            ]);
        }

        // Destruir sesión
        unset($_SESSION);
        session_destroy();

        // Configuración de Auth0
        $domain = env('AUTH0_DOMAIN');
        $clientId = env('AUTH0_CLIENT_ID');
        $returnTo = env('APP_URL') . '/login'; // URL de retorno después del logout

        if (!$domain || !$clientId) {
            // Si no hay configuración, redirigir al login local
            return $response
                ->withHeader('Location', '/login')
                ->withStatus(302);
        }

        // Construir URL de logout de Auth0
        // https://auth0.com/docs/api/authentication#logout
        $logoutUrl = "https://{$domain}/v2/logout?" . http_build_query([
            'client_id' => $clientId,
            'returnTo' => $returnTo
        ]);

        // Redirigir al logout de Auth0
        return $response
            ->withHeader('Location', $logoutUrl)
            ->withStatus(302);
    }
}
