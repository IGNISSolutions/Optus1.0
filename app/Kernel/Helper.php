<?php

if (!function_exists('config')) {
	function config($string, $optional = null) 
	{
		$search = explode('.', $string);
		$file_name = is_array($search) && isset($search[0]) ? $search[0] : $string;

		if ($file_name) {
			$string = str_replace($search[0] . '.', '', $string);
			$config = \App\Kernel\Kernel::getConfig() ?? require rootPath() . '/config/' . $file_name . '.php';
            return count($search) > 1 ? (isset($config[$string]) ? $config[$string] : $optional) : $config;
		}

		return null;
	}
}

if (!function_exists('app')) {
    /**
     * @return \Slim\App
     */
    function app()
    {
        return \App\Kernel\Kernel::getApp();
    }
}

if (!function_exists('container')) {

    /**
     * Enable access to the DI container by consumers of $app
     *
     * @return \Psr\Container\ContainerInterface
     */
    function container()
    {
        return app()->getContainer();
    }
}

if (!function_exists('dependency')) {

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param $name
     *
     * @return mixed
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    function dependency($name)
    {
        return container()->get($name);
    }
}

if (!function_exists('route')) {

    /**
     * Build the path for a named route including the base path
     *
     * @param       $name
     * @param array $data
     * @param array $queryParams
     *
     * @return string
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    function route($name, $data = [], $queryParams = [], $absolute = false)
    {
        /** @var \Slim\Router $route */
        $router = dependency('router');

        return ($absolute ? env('APP_SITE_URL') : '') . $router->pathFor($name, $data, $queryParams);
    }
}

if (!function_exists('abort_if')) {
    /**
     * @return \Slim\Exception
     */
    function abort_if($request, $response, $condition, $code)
    {
        if ($condition && $request && $response) {
            switch ($code) {
                case 404:
                    throw new \Slim\Exception\NotFoundException($request, $response);
                    break;
                default:
                    throw new \Exception(null, $code);
                    break;
            }
        }

        return false;
    }
}

if (!function_exists('asset')) {

    /**
     * Get asset path
     *
     * @param $file
     *
     * @return string
     */
    function asset($file = null, $absolute = false)
    {
        return $file ? ($absolute ? env('APP_SITE_URL') : '') . config('app.assets_path') . $file : config('app.assets_path');
    }
}

if (!function_exists('filePath')) {

    /**
     * Get files path
     *
     * @param $file
     *
     * @return string
     */
    function filePath($file = null, $absolute = false)
    {
        return $file ? ($absolute ? env('APP_SITE_URL') : '') . config('app.storage_path') . $file : config('app.storage_path');
    }
}

if (!function_exists('csrf')) {

    /**
     * output csrf name and value as input string to use in forms.
     *
     */
    function csrf()
    {
        /** @var \Slim\Csrf\Guard $csrf */
        $csrf = dependency('csrf');
        $nameKey = $csrf->getTokenNameKey();
        $valueKey = $csrf->getTokenValueKey();
        $name = $csrf->getTokenName();
        $value = $csrf->getTokenValue();

        $inputs
            = <<<CSRF_INPUT
            <input type="hidden" name="$nameKey" value="$name">
            <input type="hidden" name="$valueKey" value="$value">
CSRF_INPUT;

        return trim($inputs);
    }
}

if (!function_exists('rootPath')) {

    /**
     * get root path.
     *
     * @param null $path
     *
     * @return string
     */
    function rootPath($path = null)
    {
        $root = __DIR__ . '/../..';

        return realpath($path ? $root . $path : $root);
    }
}

if (!function_exists('publicPath')) {

    /**
     * get public path.
     *
     * @param null $path
     *
     * @return string
     */
    function publicPath($path = null)
    {
        $public = rootPath(config('app.public_path'));

        return realpath($path ? $public . $path : $public);
    }
}

if (!function_exists('chromeDebugString')) {
    function chromeDebugString($fileName)
    {
        return env('APP_ENVIRONMENT', 'development') === 'development' ? '//# sourceURL=' . $fileName . '.js' : '';
    }
}

if (!function_exists('trans')) {

    /**
     * Get the translation for a given key.
     *
     * @param  string $key
     * @param  array  $replace
     * @param  string $locale
     *
     * @return string|array|null
     */
    function trans($key, array $replace = [], $locale = null)
    {
        /** @var \Illuminate\Translation\Translator $translator */
        $translator = dependency(Illuminate\Translation\Translator::class);

        return $translator->get($key, $replace, $locale);
    }
}

if (!function_exists('validator')) {

    /**
     * Create a new Validator instance.
     *
     * See full documentation on https://laravel.com/docs/5.5/validation
     *
     * @param  array $data
     * @param  array $rules
     * @param  array $messages
     * @param  array $customAttributes
     *
     * @return \Illuminate\Validation\Validator
     */
    function validator(array $data, array $rules, array $messages = [], array $customAttributes = [])
    {
        /** @var \Illuminate\Validation\Factory $validator */
        $validator = dependency('validator');

        return $validator->make($data, $rules, $messages, $customAttributes);
    }
}

if (!function_exists('logger')) {
    function logger($type = null)
    {
        switch ($type) {
            case 'cron':
                return dependency('logger_cron');
                break;
            case 'auction':
                return dependency('logger_auction');
                break;
            default:
                return dependency('logger_default');
                break;
        }
    }
}

if (!function_exists('isLogged')) {
	function isLogged()
	{
		return !empty($_SESSION);
	}
}

if (!function_exists('user')) {
	function user()
	{
        if (empty($_SESSION)) {
            return null;
        }        
		return \App\Models\User::with('customer_company')->where('token', $_SESSION['token'])->get()->first();
	}
}

if (!function_exists('isRole')) {
	function isRole($role)
	{
        $type = isset($_SESSION['type']) ? $_SESSION['type'] : null;
        return $type ? (bool) (\App\Models\UserType::TYPES[$type] === $role) : false;

	}
}

if (!function_exists('isAdmin')) {
	function isAdmin()
	{
		return isRole('admin') || isRole('superadmin');
	}
}

if (!function_exists('isSuperAdmin')) {
	function isSuperAdmin()
	{
		return isRole('superadmin');
	}
}

if (!function_exists('isOfferer')) {
	function isOfferer()
	{
		return isRole('offerer');
	}
}

if (!function_exists('isCustomer')) {
	function isCustomer()
	{
		return isRole('customer') || isRole('customer-approve') || isRole('customer-read') || isRole('supervisor') || isRole('evaluator');
	}
}

if (!function_exists('isSolicitante')) {
	function isSolicitante()
	{
		return isRole('customer-approve');
	}
}

if (!function_exists('isSolpedActive')) {
    function isSolpedActive()
    {
        if (isAdmin()) {
            return true;
        }
        $user = user();
        if (!$user || !$user->customer_company) {
            return false;
        }
        return $user->customer_company->solped_active === 'si';
    }
}

if (!function_exists('can')) {
	function can($permission)
	{
        $permissions = isset($_SESSION['permissions']) ? $_SESSION['permissions'] : null;
        return $permissions ? in_array($permission, $permissions) : false;
	}
}

if (!function_exists('cannot')) {
	function cannot($permission)
	{
        $permissions = isset($_SESSION['permissions']) ? $_SESSION['permissions'] : null;
		return $permissions ? !in_array($permission, $permissions) : true;
	}
}

if (!function_exists('setLanguage')) {
	function setLanguage($lang = null)
	{
        $lang = $lang ?? config('app.language');
        setlocale(LC_TIME, config('app.locale') . '.utf8');
        \Carbon\Carbon::setLocale($lang);
        if (!empty($_SESSION)) {
            $_SESSION['lang'] = $lang;
        }
	}
}

if (!function_exists('getStepName')) {

    /**
     * Get step name
     *
     * @param $slug
     *
     * @return string
     */
    function getStepName($step, $is_go = false)
    {
        $step_name = null;
        switch ($step) {
            // Oferente
            case 'invitacion':
                $step_name = 'Invitación';
                break;
            case 'tecnica':
                $step_name = $is_go ? 'Documentación' : 'Presentación P. Tećnica';
                break;
            case 'economica':
                $step_name = $is_go ? 'Cotización' : 'Presentación P. Económica';
                break;
            case 'analisis':
                $step_name = $is_go ? 'Resultado' : 'Análisis';
                break;
            case 'adjudicado':
                $step_name = 'Adjudicado';
                break;
            // Cliente
            case 'en-preparacion':
                $step_name = 'En preparación';
                break;
            case 'convocatoria-oferentes':
                $step_name = 'Convocatoria de Proveedores';
                break;
            case 'analisis-tecnicas':
                $step_name = 'Análisis Técnicas';
                break;
            case 'analisis-ofertas':
                $step_name = 'Análisis de ofertas';
                break;
            case 'liberacion':
                $step_name = 'Estrategia de liberación';
                break;
            case 'evaluacion-reputacion':
                $step_name = 'Evaluación de reputación';
                break;
            case 'informes':
                $step_name = 'Informes';
                break;
        }

        return $step_name;
    }
}