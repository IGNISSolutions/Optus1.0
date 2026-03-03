<?php

namespace App\Middleware;

use Slim\Http\Request;
use Slim\Http\Response;
use Carbon\Carbon;
use App\Models\User;

class AuthMiddleware
{
    protected $permissions = [];

    public function __construct($allowed_permissions = [])
    {
        if (count($allowed_permissions) > 0) {
            $this->permissions = $allowed_permissions;
        }
    }

    /**
     * Example middleware invokable class
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(Request $request, Response $response, $next)
    {
        $route = $request->getAttribute('route');
        if ($route && !in_array($route->getName(), ['login', 'login.send', 'login.lg', 'login.tlc', 'login.callback', 'sendRecover', 'serverReset', 'updatePassword', 'sendResetCode', 'serverTwoFA', 'serverTwoFAAdvice', 'login.scr', 'a0.callback', 'a0.p    ong'])) {
            // Validate Token
            if (!$this->checkToken($request)) {
                if ($request->isXhr()) {
                    return $response
                        ->withStatus(403)
                        ->withHeader('Content-Type', 'text/plain; charset=utf-8')
                        ->write('Se ha cumplido su tiempo de sesion. Por favor ingrese nuevamente');
                    
                } else {
                    return $response->withRedirect(route('login'));
                }
            }

            // Validate permissions
            foreach ($this->permissions as $permission) {
                abort_if($request, $response, cannot($permission), 404);
            }
        }

        // Do request
        $response = $next($request, $response);

        return $response;
    }

    protected function updateToken() 
    {
        $user = user();
        try {
            $user->update([
                'validity_date' => Carbon::now()->addMinutes(20)->format('Y-m-d H:i:s')
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function checkToken($request) 
    {
        $user = user();
        if ($user && $user->validity_date) {
            if ($request->isXhr()) {
                if ($request->isGet()) {
                    $body = $request->getQueryParams();
                } else {
                    $body = $request->getParsedBody();
                }
                $token = $body && isset($body['UserToken']) ? $body['UserToken'] : null;
            } else {
                $token = $user->token;
            }
            if ($token && User::where('token', $token)->get()->first()) {
                if (Carbon::now() < $user->validity_date) {
                    if ($this->updateToken()) {
                        return true;
                    }
                }
            }
        }

        $this->forceLogout();

        return false;
    }

    protected function forceLogout()
    {
        unset($_SESSION);
        session_destroy();
    }
}