<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use Slim\Http\Request;
use Slim\Http\Response;
use Carbon\Carbon;
use App\Models\User;
use App\Services\EmailService;

class AuthController extends BaseController
{
    public function serveLogin(Request $request, Response $response)
    {
        return $this->render($response, 'login.tpl');
    }
    
    public function login(Request $request, Response $response)
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $limiteIntentosLogin = 5;
        $ventanaDeTiempo = Carbon::now()->subMinutes(15);

        // 1) Control de intentos fallidos por IP
        $intentosFallidos = dependency('db')->table('login_logs')
            ->where('ip', $ip)
            ->where('estado', 'F')
            ->where('fecha', '>=', $ventanaDeTiempo)
            ->count();

        if ($intentosFallidos >= $limiteIntentosLogin) {
            $this->login_logs($request->getParsedBody()['UserName'], null, 'F', 'Optus', 'Bloqueado por exceso de intentos');
            return $this->json($response, [
                'success' => false,
                'message' => 'Demasiados intentos fallidos. Intenta nuevamente más tarde.',
                'data'    => ['user' => null]
            ], 429);
        }

        // 2) Recuperar credenciales del payload
        $body     = $request->getParsedBody();
        $username = $body['UserName']   ?? '';
        $password = $body['Password']   ?? '';

        // 3) Buscar el usuario en BD
        $userModel = User::where(function ($q) use ($username) {
            $q->where('username', $username)
            ->orWhere('email', $username);
        })->first();

        $success = false;
        $message = 'Usuario o Contraseña incorrectos.';
        $status  = 422;
        $userData = null;

        if ($userModel) {
            // 4) Lógica de IP dinámica (2FA)
            $ipsActuales   = dependency('db')->table('users_login_ips')
                ->where('user_id', $userModel->id)
                ->orderBy('created_at', 'asc')
                ->get();
            $ipsRegistradas = $ipsActuales->pluck('ip_address')->toArray();

            if (! in_array($ip, $ipsRegistradas)) {
                if ($ipsActuales->count() >= 10) {
                    $userModel->update(['requires_ip_verification' => 'S']);
                } else {
                    $this->guardarIpUsuario($userModel->id, $ip);
                    $userModel->update(['requires_ip_verification' => 'N']);
                }
            } else {
                $userModel->update(['requires_ip_verification' => 'N']);
            }

            // 5) Verificar contraseña
            $usernameMd5 = md5($userModel->username);
            $part1       = substr($usernameMd5, 0, 16);
            $part2       = substr($usernameMd5, 16);
            $hash        = hash('sha256', $part2 . $password . $part1);

            if ($userModel->password === $hash) {
                // 6) Iniciar sesión: cargar token, SESSION, preparar $userData
                $userData = $this->setUsuario($userModel, $password);
                $success  = true;
                $message  = 'Usuario validado.';
                $status   = 200;
                $this->login_logs($username, $userModel, 'S', 'Optus', 'Inicio de sesión exitoso');
            } else {
                $this->login_logs($username, $userModel, 'F', 'Optus', 'Contraseña incorrecta');
            }
        } else {
            $this->login_logs($username, null, 'F', 'Optus', 'Usuario incorrecto');
        }

        // 7) Sólo si el login fue exitoso, firmamos y enviamos la cookie
        if ($success && $userModel) {
            $payload = $userModel->customer_company_id;
            $secret  = $_ENV['TOKEN_SECRET_KEY'];
            $hmac    = hash_hmac('sha256', $payload, $secret);
            $cookieValue = "{$payload}.{$hmac}";

            // Flags: Secure sólo en HTTPS real, HttpOnly para no exponerla a JS
            setcookie(
                'customer_company',
                $cookieValue,
                time() + 7 * 24 * 60 * 60,
                '/',
                null,
                true,   // Secure: sólo HTTPS
                true    // HttpOnly
            );
        }

        // 8) Devolver JSON limpio
        return $this->json($response, [
            'success' => $success,
            'message' => $message,
            'data'    => ['user' => $userData]
        ], $status);
    }

    
    public function logout(Request $request, Response $response)
    {
        $success = false;
        $message = '';
        $status = 422;

        try {
            $user = user();

            if ($user) {
                $user->update([
                    'token'         => null,
                    'validity_date' => null
                ]);
            }
            
            unset($_SESSION);
            session_destroy();

            $success = true;
            $message = 'Usuario deslogueado.';
            $status = 200;
        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
            $status = 500;
        }

        return $this->json($response, [
            'success'   => $success,
            'message'   => $message
        ], $status);
    }

    private function setUsuario($user, $password) 
    {
        $token = $this->generateToken();

        try {
            $user->update([
                'token'         => $token,
                'validity_date' => Carbon::now()->addMinutes(60)->format('Y-m-d H:i:s')
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
        $_SESSION['requires_ip_verification'] = $user->requires_ip_verification;
       

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
            'RequiresIpVerification' =>  $user->requires_ip_verification,
            'Permissions' => $user->permissions->pluck('code'),
            'isAdmin' => isAdmin(),
            'isCustomer' => isCustomer(),
            'isOfferer' => isOfferer(),
            
        ];
    }

    public function generateToken() 
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 20);
    }

    public function resourcesDatatable(Request $request, Response $response)
    {
        return $this->json($response, $this->container->get('lang')['datatables'], 200);
    }

    public function sendRecover(Request $request, Response $response)
    {
        $success = false;
        $message = '';
        $status = 200;
        $result = [];
        
        try {
            $capsule = dependency('db');
            $connection = $capsule->getConnection();
            $connection->beginTransaction();
            $emailService = new EmailService();
            

            $body = $request->getParsedBody();
            $username = $body['email'];
            $user = User::where('email', '=', $username)->first();
            
            if (!$user) {
                $status = 422;
                $message = 'Por favor revise sus credenciales';
            }else{
                $token = $this->generateToken();
                $user->update([
                    'token'         => $token,
                    'validity_date' => Carbon::now()->addMinutes(30)->format('Y-m-d H:i:s')
                ]);
                $title = 'Recuperación de contraseña';
                $subject = $user->first_name.' '.$user->last_name;
                $template = rootPath(config('app.templates_path')) . '/email/reset-password.tpl';
                $url = env('APP_SITE_URL').'/'.'send/'.$user->id;
                

                $html = $this->fetch($template, [
                    'title'         => $title,
                    'ano'           => Carbon::now()->format('Y'),
                    'user'          => $user,
                    'url'           => $url
                ]);

                $result = $emailService->send($html, $subject, [$user->email], $user->full_name);

                if (!$result['success']) {
                    $status = 422;
                    $success = false;
                    $message = 'Por favor revise sus credenciales';
                }else{
                    $connection->commit();
                    $success = true;
                    $message = 'Se ha enviado el correo electrónico con éxito.';
                }
            }
        } catch (\Exception $e) {
            $connection->rollBack();
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
        }

        return $this->json($response, [
            'success'   => $success,
            'message'   => $message,
        ], $status);
    }

    public function serverReset(Request $request, Response $response)
    {
        $route = $request->getAttribute('route');
        $usuario = User::find($route->getArgument('usuario'));
        $token_valid = false;
        $fecha_actual = Carbon::now();
        if($usuario->validity_date){
            $token_valid = $fecha_actual->lessThan($usuario->validity_date);     
        }        
        return $this->render($response, 'reset-password.tpl', array('token_valid' => $token_valid, 'usuario' => $usuario->id));
    }

    public function serverResetChangePassword(Request $request, Response $response)
    {
        $id = $_SESSION['user_id'];
        $usuario = User::find($id);
        $token_valid = false;
        $fecha_actual = Carbon::now();
        if($usuario->validity_date){
            $token_valid = $fecha_actual->lessThan($usuario->validity_date);     
        }

        if(!$token_valid){
            return $response->withStatus(404)->write('Not Found: Usuario not found');
        }
        else{
            unset($_SESSION);
            return $this->render($response, 'reset-password.tpl', array('token_valid' => $token_valid, 'usuario' => $usuario->id));
        }
    }


    public function updatePassword(Request $request, Response $response)
    {
        $success = false;
        $message = '';
        $status = 200;

        try {
            $capsule = dependency('db');
            $connection = $capsule->getConnection();
            $connection->beginTransaction();

            $body = $request->getParsedBody();
            $user_id = $body['usuario'];
            $new_password = $body['password'];

            // Obtener el usuario
            $user = User::find($user_id);

            if (!$user) {
                $status = 422;
                $message = 'El usuario no existe';
            } else {
                // Obtener el MD5 del nombre de usuario
                $username_md5 = md5($user->username);
                // Dividir el MD5 en dos partes
                $part1 = substr($username_md5, 0, strlen($username_md5) / 2);
                $part2 = substr($username_md5, strlen($username_md5) / 2);

                // Generar el hash de la nueva contraseña
                $hashedPassword = hash("sha256", $part2 . $new_password . $part1);

                // Actualizar la contraseña en la base de datos
                $user->update([
                    'password' => $hashedPassword,
                    'token' => $this->generateToken(),
                    'validity_date' => Carbon::now()->addMinutes(60)->format('Y-m-d H:i:s'),
                    'pass_change' => 'N',
                    'pass_date' => Carbon::now()->format('Y-m-d H:i:s'),
                ]);

                $connection->commit();
                $success = true;
                $message = 'Se ha modificado la contraseña con éxito.';
                session_destroy();
            }
        } catch (\Exception $e) {
            $connection->rollBack();
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
        }

        return $this->json($response, [
            'success'   => $success,
            'message'   => $message,
        ], $status);
    }

    public function sendResetCode(Request $request, Response $response)
    { 
        $success = false;
        $message = '';
        $status = 200;
        
        try {
            $capsule = dependency('db');
            $connection = $capsule->getConnection();
            $connection->beginTransaction();
            $emailService = new EmailService();

            $body = $request->getParsedBody();
            $username = $body['email'];
            $user = User::where('email', '=', $username)->first();
            


            if (!$user) {
                $status = 422;
                $message = 'Usuario no encontrado. Por favor, revise sus credenciales.';
            } else {
                
                $twoFactorCode = $this->generateTwoFactorCode();

                $user->update([
                    'two_factor_code' => $twoFactorCode,
                    'validity_date'   => Carbon::now()->addMinutes(10)->format('Y-m-d H:i:s')
                ]);

                $title = 'Código de verificación';
                $subject = 'Verificación de dos factores para ' . $user->first_name . ' ' . $user->last_name;
                $template = rootPath(config('app.templates_path')) . '/email/verification-code.tpl';
                //$url = env('APP_SITE_URL').'/verify-code';


                
                $html = $this->fetch($template, [
                    'title'         => $title,
                    'ano'           => Carbon::now()->format('Y'),
                    'user'          => $user,
                    'twoFactorCode' => $twoFactorCode
                ]);

                $result = $emailService->send($html, $subject, [$user->email], $user->full_name);

                if (!$result['success']) {
                    $status = 422;
                    $message = 'Error al enviar el código de verificación.';
                } else {
                    $connection->commit();
                    $success = true;
                    $message = 'El código de verificación ha sido enviado con éxito.';
                }
            }
        } catch (\Exception $e) {
            $connection->rollBack();
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
        }

        return $this->json($response, [
            'success' => $success,
            'message' => $message,
        ], $status);
        
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

    private function guardarIpUsuario($user_id, $ip)
    {
        $capsule = dependency('db');
        $connection = $capsule->getConnection();

        $ipExistente = $connection->table('users_login_ips')
            ->where('user_id', $user_id)
            ->where('ip_address', $ip)
            ->first();

        if ($ipExistente) {
            return;
        }

        $now = Carbon::now();

        $connection->table('users_login_ips')->insert([
            'user_id'    => $user_id,
            'ip_address' => $ip,
            'created_at' => $now->format('Y-m-d H:i:s'),
            'expires_at' => $now->copy()->addMonths(2)->format('Y-m-d H:i:s')
        ]);
    }

 
    public function verifyCode(Request $request, Response $response)
    {
        $user_id = $_SESSION['user_id'] ?? null;
        $input_code = $request->getQueryParams()['verification_code'] ?? null;

        if (!$user_id || !$input_code) {
            return $response->withStatus(400)->write('Faltan datos');
        }

        $usuario = User::find($user_id);

        if (!$usuario) {
            return $response->withStatus(404)->write('Usuario no encontrado');
        }

        if ($usuario->two_factor_code === $input_code) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

            $ipsActuales = dependency('db')->table('users_login_ips')
                ->where('user_id', $usuario->id)
                ->orderBy('created_at', 'asc')
                ->get();

            $ipsRegistradas = $ipsActuales->pluck('ip_address')->toArray();

            if (!in_array($ip, $ipsRegistradas)) {
                if ($ipsActuales->count() >= 10) {
                    dependency('db')->table('users_login_ips')
                        ->where('user_id', $usuario->id)
                        ->delete();
                }

                $this->guardarIpUsuario($usuario->id, $ip);
            }

            $usuario->update([
                'two_factor_code' => null,
                'requires_ip_verification' => 'N',
            ]);

                // Decide a dónde irá luego del popup
            $next = $usuario->pass_change === 'S'
                ? '/change-password'
                : '/login';

            // Redirige de vuelta al formulario con éxito y la URL de “next”
            return $response->withRedirect(
                '/verify-code?success=1&next=' . urlencode($next)
            );
        }

        return $response->withRedirect('/verify-code?error=1');
    }

    public function generateTwoFactorCode()
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public function serverTwoFA(Request $request, Response $response)
    {
        $id       = $_SESSION['user_id'];
        $usuario  = User::find($id);
        $token_valid = false;
        $fecha_actual = Carbon::now();

        if ($usuario->validity_date) {
            $token_valid = $fecha_actual->lessThan($usuario->validity_date);
        }

        if (! $token_valid) {
            return $response->withStatus(404)->write('Not Found: Usuario not found');
        }

        // limpia la sesión original
        unset($_SESSION);

        $params  = $request->getQueryParams();
        $error   = isset($params['error'])   && $params['error']   == 1;
        $success = isset($params['success']) && $params['success'] == 1;
        // si no viene “next”, por defecto lo mandamos a /login
        $next    = $params['next'] ?? '/login';

        return $this->render($response, 'verify-code.tpl', [
            'token_valid'     => $token_valid,
            'usuario'         => $usuario->id,
            'two_factor_code' => $usuario->two_factor_code,
            'error'           => $error,
            'success'         => $success,
            'next'            => $next,
        ]);
    }


    public function serverTwoFAAdvice(Request $request, Response $response)
    {
        $id = $_SESSION['user_id'];
            $usuario = User::find($id);
            $token_valid = false;
            $fecha_actual = Carbon::now();
            if($usuario->validity_date){
                $token_valid = $fecha_actual->lessThan($usuario->validity_date);     
            }

            if(!$token_valid){
                return $response->withStatus(404)->write('Not Found: Usuario not found');
            }
            else{
                unset($_SESSION);
                return $this->render($response, 'verify-code-advice.tpl', array('token_valid' => $token_valid, 'usuario' => $usuario->id));
            }
    }

}