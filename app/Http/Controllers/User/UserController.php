<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use App\Models\OffererCompany;
use App\Models\CustomerCompany;
use App\Models\Permission;
use App\Services\EmailService;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class UserController extends BaseController
{
    public function serveList(Request $request, Response $response, $params)
    {

        $url = '';
        $type = $params['type'];
        if ($type == 'admin') {
            $url = '/usuarios/list/admin';
        } else if ($type == 'client') {
            $url = '/usuarios/list/client';
        } else if ($type == 'offerer') {
            if (isset($params['id'])) {
                $url = route('empresas.usuariosOferentesList', ['type' => $type, 'id' => $params['id']]);
            } else {
                $url = route('usuarios.list', ['type' => $type]);
            }

        }

        return $this->render($response, 'usuarios/list.tpl', [
            'page' => 'usuarios',
            'accion' => 'listado-usuarios',
            'title' => 'Usuarios Listado',
            'urlList' => $url,
            'type' => $type
        ]);
    }

    public function serveEdit(Request $request, Response $response, $params)
    {
        $secret = getenv('TOKEN_SECRET_KEY');

        $id = (int) $params['id'];
        $user = User::find($id);
        abort_if($request, $response, !$user, true, 404);

        // Recalcular token esperado
        $expectedToken = hash_hmac('sha256', $id . session_id(), $secret);
        $storedToken = $_SESSION['edit_token'][$id] ?? null;

        if (!$storedToken || $storedToken !== $expectedToken) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Acceso no autorizado. Token inválido.'
            ], 403);
        }

        //unset($_SESSION['edit_token'][$id]);

        $data = $user->is_admin
            ? ['type' => 'admin']
            : ($user->is_customer ? ['type' => 'client'] : ['type' => 'offerer']);

        return $this->render($response, 'usuarios/edit.tpl', [
            'page' => 'usuarios',
            'accion' => 'edicion-usuario',
            'id' => $params['id'],
            'title' => 'Edición Usuario',
            'urlBack' => route('usuarios.serveList', $data),
            'type' => $data['type']
        ]);
    }


    public function guardarIdEdicion(Request $request, Response $response)
    {
        $secret = getenv('TOKEN_SECRET_KEY');

        $id = $request->getParsedBody()['id'] ?? null;

        if (!$id || !is_numeric($id)) {
            return $this->json($response, [
                'success' => false,
                'message' => 'ID inválido.'
            ], 400);
        }

        // Generar token HMAC con ID + sesión
        $token = hash_hmac('sha256', $id . session_id(), $secret);

        $_SESSION['edit_token'] = [];

        $_SESSION['edit_token'][$id] = $token;

        return $this->json($response, [
            'success' => true
        ]);
    }


    public function serveDetail(Request $request, Response $response, $params)
    {   
        $secret = getenv('TOKEN_SECRET_KEY');

        $id = (int) $params['id'];
        $storedToken = $_SESSION['detalle_token'][$id] ?? null;
        $expectedToken = hash_hmac('sha256', $id . session_id(), $secret);
    
        if (!$storedToken || $storedToken !== $expectedToken) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Acceso no autorizado. Token inválido.'
            ], 403);
        }
    
        //unset($_SESSION['detalle_token'][$id]); // uso único
        
        $user = null;
        $type = $params['type'] ?? null;

        if (isAdmin()) {
            $user = User::find((int) $params['id']);
        } else if (isCustomer()) {
            $user = user()->getRelatedByRoleSlug()->where('id', (int) $params['id'])->first();
        }

        abort_if($request, $response, !$user, true, 404);

        if (!$type) {
            $type = $user->is_admin ? 'admin' : ($user->is_customer ? 'client' : 'offerer');
        }

        $data = ['type' => $type];

        return $this->render($response, 'usuarios/detail.tpl', [
            'page' => 'usuarios',
            'accion' => 'detalle-usuario',
            'id' => $params['id'],
            'title' => 'Detalle Usuario',
            'urlBack' => route('usuarios.serveList', $data),
            'type' => $type
        ]);
    }

    public function guardarIdDetalle(Request $request, Response $response)
    {
        $secret = getenv('TOKEN_SECRET_KEY');
    
        $id = $request->getParsedBody()['id'] ?? null;
    
        if (!$id || !is_numeric($id)) {
            return $this->json($response, [
                'success' => false,
                'message' => 'ID inválido.'
            ], 400);
        }
    
        $sessionId = session_id();
        $token = hash_hmac('sha256', $id . $sessionId, $secret);

        $_SESSION['detalle_token'] = [];

        $_SESSION['detalle_token'][$id] = $token;
    
        return $this->json($response, [
            'success' => true
        ]);
    }
        

    public function serveCreate(Request $request, Response $response, $params)
    {
        $data = $params['type'] == 'admin' ? ['type' => 'admin'] : ($params['type'] == 'client' ? ['type' => 'client'] : ['type' => 'offerer']);
        return $this->render($response, 'usuarios/edit.tpl', [
            'page' => 'usuarios',
            'accion' => 'nuevo-usuario',
            'id' => 0,
            'title' => 'Nuevo Usuario',
            'urlBack' => route('usuarios.serveList', $data),
            'type' => $params['type']
        ]);
    }

    public function list(Request $request, Response $response, $params)
    {
        // dd($params);
        $type = $params['type'];
        $success = false;
        $message = null;
        $status = 200;
        $list = [];
        $users = null;
        try {
            
            if ($type == 'admin') {
                $users = User::where('type_id', 1)->OrWhere('type_id', 2)->get();
            } else if ($type == 'client') {
                $users = User::whereIn('type_id', [3, 4, 5, 7, 8])->get();
            
                if (user()->is_customer) {
                $companyId = user()->customer_company->id;

                $query = User::where('customer_company_id', $companyId)
                            ->whereIn('type_id', [3, 4, 5, 7, 8]);

                // Excluirse solo si no sos supervisor (8)
                if ((int) user()->type_id !== 8) {
                    $query->where('id', '!=', user()->id);
                }

                $users = $query->get();
            }

            } else if ($type == 'offerer') {
                if (user()->is_admin) {
                    $users = User::where('type_id', 6)->get();
                } else {
                    $users = [];
                    if (isset($params['id'])) {
                        $company = OffererCompany::find($params['id']);
                        foreach ($company->users as $user) {
                            array_push($users, $user);
                        }
                    } else {
                        $companies = user()->customer_company->associated_offerers;
                        foreach ($companies as $company) {
                            foreach ($company->users as $user) {
                                array_push($users, $user);
                            }
                        }
                    }
                }

            }

            foreach ($users as $user) {
                $company =
                    $user->is_offerer ?
                    $user->offerer_company :
                    (
                        $user->is_customer ?
                        $user->customer_company :
                        null
                    );

                array_push($list, [
                    'Id' => $user->id,
                    'Nombre' => $user->first_name,
                    'Apellido' => $user->last_name,
                    'Email' => $user->email,
                    'Estado' => $user->status->code,
                    'EstadoDescripcion' => $user->status->description,
                    'Tipo' => $user->type->code,
                    'TipoDescripcion' => $user->type->description,
                    'EmpresaAsociada' => $company ? $company->business_name : null,
                    'Celular' => $user->cellphone,
                    'Telefono' => $user->phone,
                    'Area' => $user->area, // Añadir área
                    'Rol' => $user->rol   // Añadir rol
                ]);
            }

            $success = true;

            // Breadcrumbs
            $breadcrumbs = [
                ['description' => 'Usuarios', 'url' => null]
            ];

        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
        }

        return $this->json($response, [
            'success' => $success,
            'message' => $message,
            'data' => [
                'list' => $list,
                'breadcrumbs' => $breadcrumbs
            ]
        ], $status);
    }

    public function detail(Request $request, Response $response, $params)
    {
        $success = false;
        $message = null;
        $status = 200;
        $list = [];
        $data = [];

        try {
            $user = null;
            if (isAdmin()) {
                $user = User::find((int) $params['id']);
            } else if (isCustomer()) {
                $user = user()->getRelatedByRoleSlug()->where('id', (int) $params['id'])->first();
            }

            if ($user->is_admin) {
                $data = ['type' => 'admin'];
                $empresa = null;
            }

            if ($user->is_customer) {
                $data = ['type' => 'client'];
                $empresa = $user->customer_company->business_name;
            }

            if ($user->is_offerer) {
                $data = ['type' => 'offerer'];
                $empresa = $user->offerer_company->business_name;
            }

            $list = array_merge($list, [
                'Id' => $user->id,
                'Estado' => $user->status->code,
                'EstadoDescripcion' => $user->status->description,
                'Tipo' => $user->type->description,
                'Empresa' => $empresa,
                'Nombre' => $user->first_name,
                'Apellido' => $user->last_name,
                'FullName' => $user->full_name,
                'Username' => $user->username,
                'Telefono' => $user->phone,
                'Celular' => $user->cellphone,
                'Email' => $user->email,
                'Area' => $user->area, // Añadir área
                'Rol' => $user->rol   // Añadir rol
            ]);

            $success = true;

            // Breadcrumbs
            $breadcrumbs = [
                ['description' => 'Usuarios', 'url' => route('usuarios.serveList', $data)],
                ['description' => 'Detalle', 'url' => null]
            ];

        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
        }

        return $this->json($response, [
            'success' => $success,
            'message' => $message,
            'data' => [
                'list' => $list,
                'breadcrumbs' => $breadcrumbs
            ]
        ], $status);
    }

    public function editOrCreate(Request $request, Response $response, $params)
    {

        $success = false;
        $message = null;
        $status = 200;
        $list = [];
        $data = [];

        try {
            $creation = !isset($params['id']);
            $user = $creation ? null : User::find((int) $params['id']);
            $action_description = $creation ? 'Nuevo' : 'Edición';

            $user_type_list = UserType::getList($params['type']);
            if ($creation) {
                $user_type = UserType::find((int) $user_type_list[0]['id']);
            } else {
                $user_type = $user->type;
            }

            $company_list = [];
            if ($params['type'] == 'admin') {
                $company_list = [];
                $data = ['type' => 'admin'];
            }

            if ($params['type'] == 'client') {
                if (user()->is_admin) {
                    $company_list = CustomerCompany::getList();
                } else if (user()->is_customer) {
                    $company_list = CustomerCompany::where('id', user()->customer_company_id)
                        ->get()
                        ->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'text' => $item->business_name
                            ];
                        })
                        ->toArray();
                }
                $data = ['type' => 'client'];
            }

            if ($params['type'] == 'offerer') {
                $company_list = OffererCompany::getList();
                $data = ['type' => 'offerer'];
            }

            $list = array_merge($list, [
                'Id' => $creation ? null : $user->id,
                'Estados' => UserStatus::getList(),
                'Estado' => $creation ? null : $user->status->id,
                'Tipos' => $user_type_list,
                'Tipo' => $creation ? $user_type_list[0]['id'] : $user->type->id,
                'Empresas' => $company_list,
                'Empresa' =>
                $creation ?
                (count($company_list) > 0 ? $company_list[0]['id'] : null) :
                (
                    $user->is_admin ? null : ($user->is_customer ?
                        $user->customer_company->id :
                        $user->offerer_company->id)
                ),
                'Nombre' => $creation ? null : $user->first_name,
                'Apellido' => $creation ? null : $user->last_name,
                'FullName' => $creation ? null : $user->full_name,
                'Username' => $creation ? null : $user->username,
                'Telefono' => $creation ? null : $user->phone,
                'Celular' => $creation ? null : $user->cellphone,
                'Email' => $creation ? null : $user->email,
                'Area' => $creation ? null : $user->area,
                'Rol' => $creation ? null : $user->rol
            ]);

            $success = true;

            // Breadcrumbs
            $breadcrumbs = [
                ['description' => 'Usuarios', 'url' => route('usuarios.serveList', $data)],
                ['description' => $action_description, 'url' => null]
            ];

        } catch (\Exception $e) {
            dd($e);
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
        }

        return $this->json($response, [
            'success' => $success,
            'message' => $message,
            'data' => [
                'list' => $list,
                'breadcrumbs' => $breadcrumbs
            ]
        ], $status);
    }

    public function store(Request $request, Response $response, $params)
    {
        $success = false;
        $message = null;
        $status = 200;
        $list = [];
        $redirect_url = null;

        try {
            $capsule = dependency('db');
            $connection = $capsule->getConnection();
            $connection->beginTransaction();

            $body = json_decode($request->getParsedBody()['Data']);
            $creation = !isset($params['id']);
            $fields = [];
            $type = null;

            // =========================
            // Normalizaciones de entrada
            // =========================
            $inputEmail = trim($body->Email ?? '');
            $inputUsername = null;
            if (isset($body->Username) && !empty(trim($body->Username))) {
                $inputUsername = preg_replace('/\s*/', '', strtolower($body->Username));
            }

            // =========================
            // GUARDS de unicidad a nivel código (solo contra ACTIVOS)
            // =========================

            // Email: activo duplicado -> bloquear
            if ($creation) {
                $activeEmailExists = User::where('email', $inputEmail)
                    ->whereNull('deleted_at')
                    ->exists();

                if ($activeEmailExists) {
                    $connection->rollBack();
                    return $this->json($response, [
                        'success' => false,
                        'message' => 'Ya existe un usuario activo con ese email.',
                        'data' => ['redirect' => null]
                    ], 422);
                }
            } else {
                // edición: permitir el mismo email propio, bloquear si es de otro activo
                $activeEmailExists = User::where('email', $inputEmail)
                    ->whereNull('deleted_at')
                    ->where('id', '!=', (int) $params['id'])
                    ->exists();

                if ($activeEmailExists) {
                    $connection->rollBack();
                    return $this->json($response, [
                        'success' => false,
                        'message' => 'Ya existe otro usuario activo con ese email.',
                        'data' => ['redirect' => null]
                    ], 422);
                }
            }

            // Username (si viene): activo duplicado -> bloquear
            if ($inputUsername) {
                if ($creation) {
                    $activeUserExists = User::where('username', $inputUsername)
                        ->whereNull('deleted_at')
                        ->exists();

                    if ($activeUserExists) {
                        $connection->rollBack();
                        return $this->json($response, [
                            'success' => false,
                            'message' => 'El nombre de usuario ya está en uso por una cuenta activa.',
                            'data' => ['redirect' => null]
                        ], 422);
                    }
                } else {
                    $activeUserExists = User::where('username', $inputUsername)
                        ->whereNull('deleted_at')
                        ->where('id', '!=', (int) $params['id'])
                        ->exists();

                    if ($activeUserExists) {
                        $connection->rollBack();
                        return $this->json($response, [
                            'success' => false,
                            'message' => 'El nombre de usuario ya está en uso por otra cuenta activa.',
                            'data' => ['redirect' => null]
                        ], 422);
                    }
                }
            }
            // =========================
            // Fin GUARDS
            // =========================

            // Relationships
            $user_status = UserStatus::find((int) $body->Estado);
            $user_type = UserType::find((int) $body->Tipo);

            if ($user_type->code === 'admin' || $user_type->code === 'superadmin') {
                $type = 'admin';
            }
            if ($user_type->code === 'customer' || $user_type->code === 'customer-approve' || $user_type->code === 'customer-read' || $user_type->code === 'supervisor') {
                $type = 'client';
            }
            if ($user_type->code === 'offerer') {
                $type = 'offerer';
            }

            $company = null;
            if ($user_type->is_offerer) {
                $company = OffererCompany::where('id', (int) $body->Empresa)->get()->first();
                $fields = array_merge($fields, [
                    'offerer_company_id' => $company ? $company->id : null,
                ]);
            } else if ($user_type->is_customer) {
                $company = CustomerCompany::where('id', (int) $body->Empresa)->get()->first();
                $fields = array_merge($fields, [
                    'customer_company_id' => $company ? $company->id : null,
                ]);
            }

            $fields = array_merge($fields, [
                'status_id' => $user_status ? $user_status->id : null,
                'type_id' => $user_type ? $user_type->id : null,
                'first_name' => $body->Nombre,
                'last_name' => $body->Apellido,
                'phone' => $body->Telefono,
                'cellphone' => $body->Celular,
                'email' => $inputEmail,
                'area' => $body->Area,
                'rol' => $body->Rol
            ]);

            if ($inputUsername) {
                $fields['username'] = $inputUsername;
            }

            // =========================
            // Generación de contraseña (igual que tu lógica actual)
            // =========================
            $length = 8;
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomPassword = '';
            for ($i = 0; $i < $length; $i++) {
                $randomPassword .= $characters[rand(0, $charactersLength - 1)];
            }

            // Evitar null en el seed del hash
            $seedForMd5 = $inputUsername ?: $inputEmail;
            $username_md5 = md5($seedForMd5);
            $part1 = substr($username_md5, 0, strlen($username_md5) / 2);
            $part2 = substr($username_md5, strlen($username_md5) / 2);
            $passwordHash = hash("sha256", $part2 . $randomPassword . $part1);

            $fields['password'] = $passwordHash;
            $fields['password_confirmation'] = $passwordHash;

            // Validación (ya ignora Id y chequea solo contra activos por whereNull('deleted_at'))
            $validation = $this->validate($body, $fields, $creation);
            if ($validation->fails()) {
                $success = false;
                $status = 422;
                $message = $validation->errors()->first();
            } else {
                if ($creation) {
                    // Crear nuevo usuario (si hubiera soft-deleted con ese email/username NO bloquea)
                    $user = new User($fields);
                    $user->save();

                    if ($user->is_admin) {
                        $this->permisionAdmin($user->id);
                    } else if ($user->type_id === 5) {
                        $this->permisionVisualizer($user->id);
                    } else if ($user->type_id === 8) {
                        $this->permisionSupervisor($user->id);
                    } else if ($user->type_id === 7) {
                        $this->permisionTech($user->id);
                    } else if ($user->is_customer) {
                        $this->permisionClient($user->id);
                    } else {
                        $this->permisionOfferer($user->id);
                    }

                } else {
                    // Edición
                    $user = User::find((int) $params['id']);
                    if ($user) {
                        $tipoAnterior = $user->type_id;
                        $user->update($fields);

                        // ✅ Si el tipo cambió, reasignar permisos
                        if ($tipoAnterior !== $user_type->id) {
                            if (method_exists($user, 'permissions')) {
                                $user->permissions()->detach();
                            }

                            if ($user->is_admin) {
                                $this->permisionAdmin($user->id);
                            } else if ($user->type_id === 5) {
                                $this->permisionVisualizer($user->id);
                            } else if ($user->type_id === 8) {
                                $this->permisionSupervisor($user->id);
                            } else if ($user->type_id === 7) {
                                $this->permisionTech($user->id);
                            } else if ($user->is_customer) {
                                $this->permisionClient($user->id);
                            } else {
                                $this->permisionOfferer($user->id);
                            }
                        }
                    }
                }

                $connection->commit();

                $redirect_url = route('usuarios.serveList', ['type' => $type ?? 'client']);
                $success = true;
                $message = 'Usuario guardado con éxito.';

                if ($creation) {
                    $emailService = new EmailService();
                    $subject = 'Nuevo usuario Optus';
                    $alias = 'Optus';
                    $template = rootPath(config('app.templates_path')) . '/email/new-user.tpl';
                    $url = 'portal.optus.com.ar/login';

                    $html = $this->fetch($template, [
                        'title' => $subject,
                        'ano' => Carbon::now()->format('Y'),
                        'user' => $user,
                        'password' => $randomPassword,
                        'url' => $url
                    ]);

                    $emailService->send($html, $subject, [$user->email], $alias);
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
            'data' => [
                'redirect' => $redirect_url
            ]
        ], $status);
    }


    public function delete(Request $request, Response $response, $params)
    {
        $success = false;
        $message = null;
        $status = 200;
        $list = [];
        $redirect_url = null;
        $data = [];

        try {

            $user = User::find((int) $params['id']);
            $data = $user->is_admin ? ['type' => 'admin'] : ($user->is_customer ? $data = ['type' => 'client'] : $data = ['type' => 'offerer']);

            if ($user) {
                $user->delete();
                $success = true;
                $message = 'Usuario eliminado con éxito.';
                $redirect_url = route('usuarios.serveList', $data);
            } else {
                $message = 'No se ha podido eliminar el usuario.';
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
                'redirect' => $redirect_url
            ]
        ], $status);
    }

    private function validate($body, $fields, $creation)
    {
        $conditional_rules = [];
        $common_rules = [
            'type_id' => 'required|exists:user_types,id',
            'status_id' => 'required|exists:user_status,id',
            'offerer_company_id' => 'nullable|exists:offerer_companies,id',
            'customer_company_id' => 'nullable|exists:customer_companies,id',
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'phone' => 'nullable|numeric',
            'cellphone' => 'nullable|numeric',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')
                    ->ignore($body->Id, 'id')
                    ->whereNull('deleted_at')
            ],
            //'email' => 'required|email|unique:users,email,' . $body->Id . ',id', // modificar aqui la validacion del mail para usuarios eliminados
            'area' => 'nullable|string|max:45',
            'rol' => 'nullable|string|max:45',
        ];
        
        if ($creation) {
            $conditional_rules = array_merge($conditional_rules, [
                'username' => 'required|string|max:50|unique:users,username',
                'password' => 'required|string|confirmed|min:8'
            ]);
        } else {
            $conditional_rules = array_merge($conditional_rules, [
                'username' => 'nullable|string|max:50|unique:users,username,' . $body->Id . ',id',
                'password' => 'nullable|string|confirmed|min:8'
            ]);
        }

        return validator(
            $data = $fields,
            $rules = array_merge($common_rules, $conditional_rules)
        );
    }

    private function permisionAdmin($user_id)
    {
        $user = User::find($user_id);
        $permisions = [
            [
                "id" => "1",
                "description" => "Dashboard",
                "permissions" => [
                    [
                        "id" => 1,
                        "description" => "Ver+Dashboard",
                        "active" => true
                    ]
                ],
                "active" => true
            ],
            [
                "id" => "2",
                "description" => "Concursos",
                "permissions" => [
                    [
                        "id" => 2,
                        "description" => "Subasta",
                        "active" => true
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
                        "active" => true
                    ]
                ],
                "active" => true
            ],
            [
                "id" => "4",
                "description" => "Configuraciones",
                "permissions" => [
                    [
                        "id" => 7,
                        "description" => "Catálogo+de+Rubros",
                        "active" => true
                    ],
                    [
                        "id" => 8,
                        "description" => "Unidades+de+Medida",
                        "active" => true
                    ],
                    [
                        "id" => 17,
                        "description" => "Categorías+de+Catálogo",
                        "active" => true
                    ],
                    [
                        "id" => 22,
                        "description" => "Tipo+de+Cambio",
                        "active" => true
                    ]
                ],
                "active" => true
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
                        "active" => true
                    ],
                    [
                        "id" => 18,
                        "description" => "Materiales+del+Clientes",
                        "active" => true
                    ]
                ],
                "active" => true
            ],
            [
                "id" => "6",
                "description" => "Tarifas",
                "permissions" => [
                    [
                        "id" => 9,
                        "description" => "Gestión+de+Tarifas",
                        "active" => true
                    ],
                    [
                        "id" => 10,
                        "description" => "Reportes+Concursos",
                        "active" => true
                    ],
                    [
                        "id" => 11,
                        "description" => "Reportes+Cobros",
                        "active" => true
                    ],
                    [
                        "id" => 12,
                        "description" => "Reporte+Tarifas+Históricas",
                        "active" => true
                    ]
                ],
                "active" => true
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

    private function permisionClient($user_id)
    {
        $user = User::find($user_id);
        $permisions = [
            [
                "id" => "1",
                "description" => "Dashboard",
                "permissions" => [
                    [
                        "id" => 1,
                        "description" => "Ver+Dashboard",
                        "active" => true
                    ]
                ],
                "active" => true
            ],
            [
                "id" => "2",
                "description" => "Concursos",
                "permissions" => [
                    [
                        "id" => 2,
                        "description" => "Subasta",
                        "active" => true
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
                    ],
		    [
                        "id" => 19,
                        "description" => "Edición+de+Usuarios+Proveedores",
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
                        "active" => false
                    ],
                    [
                        "id" => 16,
                        "description" => "Clientes",
                        "active" => false
                    ],
                    [
                        "id" => 18,
                        "description" => "Materiales+del+Clientes",
                        "active" => false
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
            ],
           
            
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

    private function permisionOfferer($user_id)
    {
        $user = User::find($user_id);
        $permisions = [
            [
                "id" => "1",
                "description" => "Dashboard",
                "permissions" => [
                    [
                        "id" => 1,
                        "description" => "Ver+Dashboard",
                        "active" => true
                    ]
                ],
                "active" => true
            ],
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
                        "active" => false
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
                        "active" => false
                    ]
                ],
                "active" => false
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

    private function permisionVisualizer($user_id)
    {
        $user = User::find($user_id);
        $permisions = [
            [
                "id" => "1",
                "description" => "Dashboard",
                "permissions" => [
                    [
                        "id" => 1,
                        "description" => "Ver+Dashboard",
                        "active" => true
                    ]
                ],
                "active" => true
            ],
            [
                "id" => "2",
                "description" => "Concursos",
                "permissions" => [
                    [
                        "id" => 2,
                        "description" => "Subasta",
                        "active" => true
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
                "id" => "4",
                "description" => "Configuraciones",
                "permissions" => [
                    [
                        "id" => 7,
                        "description" => "Catálogo+de+Rubros",
                        "active" => false
                    ],
                    [
                        "id" => 8,
                        "description" => "Unidades+de+Medida",
                        "active" => false
                    ],
                    [
                        "id" => 17,
                        "description" => "Categorías+de+Catálogo",
                        "active" => false
                    ],
                    [
                        "id" => 22,
                        "description" => "Tipo+de+Cambio",
                        "active" => false
                    ],
                    [
                        "id" => 23,
                        "description" => "Estrategia+Liberacion",
                        "active" => false
                    ],
                    [
                        "id" => 24,
                        "description" => "Mailer",
                        "active" => false
                    ]
                ],
                "active" => true
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
                        "active" => false
                    ]
                ],
                "active" => true
            ],
            [
                "id" => 8,
                "description" => "Reporte",
                "permissions" => [
                    [
                        "id" => 21,
                        "description" => "Reportes",
                        "active" => true
                    ]
                ]
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

    private function permisionSupervisor($user_id)
    {
        $user = User::find($user_id);
        $permisions = [
            [
                "id" => "1",
                "description" => "Dashboard",
                "permissions" => [
                    [
                        "id" => 1,
                        "description" => "Ver+Dashboard",
                        "active" => true
                    ]
                ],
                "active" => true
            ],
            [
                "id" => "2",
                "description" => "Concursos",
                "permissions" => [
                    [
                        "id" => 2,
                        "description" => "Subasta",
                        "active" => true
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
                        "active" => true
                    ],
		    [
                        "id" => 19,
                        "description" => "Edición+de+Usuarios+Proveedores",
                        "active" => true
                    ]

                ],
                "active" => true
            ],
            [
                "id" => "4",
                "description" => "Configuraciones",
                "permissions" => [
                    [
                        "id" => 7,
                        "description" => "Catálogo+de+Rubros",
                        "active" => true
                    ],
                    [
                        "id" => 8,
                        "description" => "Unidades+de+Medida",
                        "active" => false
                    ],
                    [
                        "id" => 17,
                        "description" => "Categorías+de+Catálogo",
                        "active" => true
                    ],
                    [
                        "id" => 22,
                        "description" => "Tipo+de+Cambio",
                        "active" => false
                    ],
                    [
                        "id" => 23,
                        "description" => "Estrategia+Liberacion",
                        "active" => false
                    ],
                    [
                        "id" => 24,
                        "description" => "Mailer",
                        "active" => false
                    ]
                ],
                "active" => true
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
                "active" => true
            ],
            [
                "id" => "6",
                "description" => "Tarifas",
                "permissions" => [
                    [
                        "id" => 9,
                        "description" => "Gestión+de+Tarifas",
                        "active" => false
                    ],
                    [
                        "id" => 10,
                        "description" => "Reportes+Concursos",
                        "active" => false
                    ],
                    [
                        "id" => 11,
                        "description" => "Reportes+Cobros",
                        "active" => false
                    ],
                    [
                        "id" => 12,
                        "description" => "Reporte+Tarifas+Históricas",
                        "active" => false
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
            ],
            [
                "id" => 8,
                "description" => "Reporte",
                "permissions" => [
                    [
                        "id" => 21,
                        "description" => "Reportes",
                        "active" => true
                    ]
                ]
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

	    //Tech hace referencia al perfil EValuador, por que completo es Evaluador tecnico

    private function permisionTech($user_id)
    {
        $user = User::find($user_id);
        $permisions = [
            [
                "id" => "1",
                "description" => "Dashboard",
                "permissions" => [
                    [
                        "id" => 1,
                        "description" => "Ver+Dashboard",
                        "active" => true
                    ]
                ],
                "active" => true
            ],
            [
                "id" => "2",
                "description" => "Concursos",
                "permissions" => [
                    [
                        "id" => 2,
                        "description" => "Subasta",
                        "active" => true
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
                "id" => "4",
                "description" => "Configuraciones",
                "permissions" => [
                    [
                        "id" => 7,
                        "description" => "Catálogo+de+Rubros",
                        "active" => false
                    ],
                    [
                        "id" => 8,
                        "description" => "Unidades+de+Medida",
                        "active" => false
                    ],
                    [
                        "id" => 17,
                        "description" => "Categorías+de+Catálogo",
                        "active" => false
                    ],
                    [
                        "id" => 22,
                        "description" => "Tipo+de+Cambio",
                        "active" => false
                    ],
                    [
                        "id" => 23,
                        "description" => "Estrategia+Liberacion",
                        "active" => false
                    ],
                    [
                        "id" => 24,
                        "description" => "Mailer",
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
                        "active" => false
                    ],
                    [
                        "id" => 16,
                        "description" => "Clientes",
                        "active" => false
                    ],
                    [
                        "id" => 18,
                        "description" => "Materiales+del+Clientes",
                        "active" => false
                    ]
                ],
                "active" => false
            ],
            [
                "id" => "6",
                "description" => "Tarifas",
                "permissions" => [
                    [
                        "id" => 9,
                        "description" => "Gestión+de+Tarifas",
                        "active" => false
                    ],
                    [
                        "id" => 10,
                        "description" => "Reportes+Concursos",
                        "active" => false
                    ],
                    [
                        "id" => 11,
                        "description" => "Reportes+Cobros",
                        "active" => false
                    ],
                    [
                        "id" => 12,
                        "description" => "Reporte+Tarifas+Históricas",
                        "active" => false
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
            ],
            [
                "id" => 8,
                "description" => "Reporte",
                "permissions" => [
                    [
                        "id" => 21,
                        "description" => "Reportes",
                        "active" => false
                    ]
                ],
                "active" => false
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
}