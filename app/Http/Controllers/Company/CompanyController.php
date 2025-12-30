<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\BaseController;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Models\OffererCompany;
use App\Models\CustomerCompany;
use App\Models\OffererCompanyStatus;
use App\Models\CustomerCompanyStatus;
use App\Models\RateSystem;
use App\Models\Pais;
use App\Models\Provincia;
use App\Models\Ciudad;
use App\Models\Area;
use App\Models\Alcance;
use DateTimeZone;
use DateTime;


class CompanyController extends BaseController
{
    public function serveList(Request $request, Response $response, $params)
    {
        // dd($params);
        $title =
            $params['role'] === 'client' ?
            'Listado de Clientes' :
            'Listado de Proveedores';

        return $this->render($response, 'empresas/list.tpl', [
            'page' => 'empresas',
            'accion' => 'listado-' . $params['role'],
            'tipo' => $params['role'],
            'title' => $title,
            'userType' => user()->type->code
        ]);
    }

    public function serveEdit(Request $request, Response $response, $params)
    {
        $secret = getenv('TOKEN_SECRET_KEY');

        $id = (int) $params['id'];
        $sessionId = session_id();
        $expectedToken = hash_hmac('sha256', $id . $sessionId, $secret);
        $storedToken = $_SESSION['edit_company_token'][$id] ?? null;

        if (!$storedToken || $storedToken !== $expectedToken) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Acceso no autorizado. Token inválido.'
            ], 403);
        }

        // Consumir el token (uso único)
        //unset($_SESSION['edit_company_token'][$id]);

        switch ($params['role']) {
            case 'client':
                $company = CustomerCompany::find($id);
                $title = 'Edición Cliente';
                break;
            default:
                $company = OffererCompany::find($id);
                $title = 'Edición Proveedor';
                break;
        }

        abort_if($request, $response, !$company, true, 404);

        return $this->render($response, 'empresas/edit.tpl', [
            'page' => 'empresas',
            'accion' => 'edicion-' . $params['role'],
            'tipo' => $params['role'],
            'id' => $id,
            'title' => $title,
            'userType' => user()->type->code
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

        $sessionId = session_id();
        $token = hash_hmac('sha256', $id . $sessionId, $secret);

        $_SESSION['edit_company_token'] = [];

        $_SESSION['edit_company_token'][$id] = $token;

        return $this->json($response, [
            'success' => true
        ]);
    }


    public function serveProfileEdit(Request $request, Response $response, $params)
    {
        $user = null;
        if ($params['role'] === 'oferentes')
            $user = user();

        abort_if($request, $response, !$user, true, 404);

        $title = 'Edición Perfil Provedor';

        return $this->render($response, 'empresas/profile.tpl', [
            'page' => 'empresas',
            'accion' => 'edicion-' . $params['role'],
            'tipo' => $params['role'],
            'id' => $user->offerer_company->id,
            'title' => $title
        ]);
    }

    public function serveDetail(Request $request, Response $response, $params)
    {
        $secret = getenv('TOKEN_SECRET_KEY');
        $id = (int) $params['id'];

        switch ($params['role']) {
            case 'client':
                $company = CustomerCompany::find($id);
                $title = 'Detalle Cliente';
                break;
            default:
                $company = OffererCompany::find($id);
                $title = 'Detalle Proveedor';
                break;
        }

        abort_if($request, $response, !$company, true, 404);

        $expectedToken = hash_hmac('sha256', $id . session_id(), $secret);
        $storedToken = $_SESSION['detalle_token'][$id] ?? null;

        if (!$storedToken || $storedToken !== $expectedToken) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Acceso no autorizado. Token inválido.'
            ], 403);
        }

        //unset($_SESSION['detalle_token'][$id]);

        return $this->render($response, 'empresas/detail.tpl', [
            'page' => 'empresas',
            'accion' => 'detalle-' . $params['role'],
            'tipo' => $params['role'],
            'id' => $params['id'],
            'title' => $title
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

        $token = hash_hmac('sha256', $id . session_id(), $secret);

        $_SESSION['detalle_token'] = [];

        $_SESSION['detalle_token'][$id] = $token;

        return $this->json($response, [
            'success' => true
        ]);
    }


    public function serveCreate(Request $request, Response $response, $params)
    {
        $title =
            $params['role'] === 'client' ?
            'Nuevo Cliente' :
            'Nuevo Proveedor';

        return $this->render($response, 'empresas/edit.tpl', [
            'page' => 'empresas',
            'accion' => 'nuevo-' . $params['role'],
            'tipo' => $params['role'],
            'id' => 0,
            'title' => $title,
            'userType' => user()->type->code
        ]);
    }

    public function getOffererByCuit(Request $request, Response $response, $params)
    {
        $cuit = preg_replace('/\D/', '', $params['cuit'] ?? '');

        if (strlen($cuit) < 2) {
            return $response->withJson([
                'success' => false,
                'message' => 'CUIT inválido'
            ]);
        }

        $offerer = OffererCompany::where('cuit', $cuit)->first();

        if (!$offerer) {
            return $response->withJson([
                'success' => false,
                'message' => 'No existe proveedor con ese CUIT'
            ]);
        }

        // ID de la empresa cliente asociada al usuario logueado
        $user = user();
        //cuando ya existe la relación:

        $already = $user->customer_company
            ->associated_offerers()
            ->where('offerer_companies.id', $offerer->id)
            ->exists();

        if ($already) {
            return $response->withJson([
                'success' => false,
                'message' => 'Ya estás asociado a este proveedor.',
                'data'    => ['already_associated' => true]
            ]);
        }


        return $response->withJson([
            'success' => true,
            'data' => [
                'id'            => $offerer->id,
                'business_name' => $offerer->business_name,
                'cuit'          => $offerer->cuit,
                'email' => $offerer->email,
                'nombre' => $offerer->first_name,
                'apellido' => $offerer->last_name,
            ]
        ]);
    }


    public function parseList($companies, $role)
    {
        $results = [];
        $user = user();

        foreach ($companies as $company) {
            $results[] = [
                'Id' => $company->id,
                'RazonSocial' => strtoupper($company->business_name),
                'Cuit' => $company->cuit,
                'Estado' => $company->status->code,
                'EstadoDescripcion' => $company->status->description,
                'IsAssociated' =>
                $role === 'offerer' && !isAdmin() ?
                $user->customer_company->associated_offerers->where('id', $company->id)->count() > 0 :
                false
            ];
        }

        return $results;
    }

    public function setFilters()
    {
        $filters = new \StdClass();
        // Filtro exclusivo para no-administradores
        $filters->associated_list =
            isAdmin() ?
            [] :
            [
                [
                    'id' => 1,
                    'text' => 'Asociados',
                    'default' => false
                ],
                [
                    'id' => 2,
                    'text' => 'No Asociados',
                    'default' => false
                ],
                [
                    'id' => 3,
                    'text' => 'Todos',
                    'default' => true
                ]
            ];
        // Filtros comunes
        $filters->areas_list = Area::getList();
        $filters->countries_list = Pais::getList();
        $filters->provinces_list = [];
        $filters->cities_list = [];
        // Filtro exclusivo para administradores
        $filters->customers_list = isAdmin() ? CustomerCompany::getList() : [];

        return $filters;
    }

    public function filter(Request $request, Response $response, $params)
    {
        $success = false;
        $message = null;
        $status = 200;
        $results = [];

        try {
            $user = user();
            $filters = json_decode($request->getParsedBody()['Filters']);

            $query = [];

            $companies = OffererCompany::query();

            // CUIT
            if ($filters->Cuit) {
                $companies = $companies->where('cuit', 'like', '%' . $filters->Cuit . '%');
            }

            // Filtros exclusivos de administrador
            if (isAdmin()) {
                // Clientes Asociados
                if ($filters->Customers) {
                    $companies = $companies->whereHas('associated_customers', function ($query) use ($filters) {
                        $query->whereIn('customer_id', $filters->Customers);
                    });
                }
                // Filtros para no-administradores
            } else {
                // Oferentes Asociados / No Asociados / Todos
                switch ($filters->Associated) {
                    case 1:
                        $companies = $companies->whereHas('associated_customers', function ($query) use ($user) {
                            $query->where('customer_id', $user->customer_company->id);
                        });
                        break;
                    case 2:
                        $companies = $companies->whereDoesntHave('associated_customers', function ($query) use ($user) {
                            $query->where('customer_id', $user->customer_company->id);
                        });
                        break;
                }
            }

            // Rubros
            if ($filters->Areas) {
                $companies = $companies->whereHas('areas', function ($query) use ($filters) {
                    $query->whereIn('area_id', $filters->Areas);
                });
            }

            // Obtengo todas las ciudades específicamente elegidas
            $cities = collect();
            if ($filters->Cities) {
                $cities = $cities->merge(
                    Ciudad::whereIn('id', $filters->Cities)
                        ->get()
                );
                if ($cities->count() > 0) {
                    $companies = $companies->whereHas('alcances', function ($query) use ($cities) {
                        $query->whereIn('id_ciudad', $cities->pluck('id')->toArray());
                    });
                }
            }

            // Obtengo todas las ciudades de provincias que no tengan ciudades específicamente elegidas
            $provinces = collect();
            if ($filters->Provinces) {
                $provinces = $provinces->merge(
                    Provincia::whereIn('id', $filters->Provinces)
                        ->whereDoesntHave('ciudades', function ($query) use ($filters) {
                            $query->whereIn('ciudades.id', $filters->Cities);
                        })
                        ->get()
                );
                if ($provinces->count() > 0) {
                    $companies = $companies->whereHas('alcances', function ($query) use ($provinces) {
                        $query->whereIn('id_provincia', $provinces->pluck('id')->toArray());
                    });
                }
            }

            // Obtengo todas las ciudades de países que no tengan provincias específicamente elegidas
            $countries = collect();
            if ($filters->Countries) {
                $countries = $countries->merge(
                    Pais::whereIn('id', $filters->Countries)
                        ->whereDoesntHave('provincias', function ($query) use ($filters) {
                            $query->whereIn('provincias.id', $filters->Provinces);
                        })
                        ->get()
                );
                if ($countries->count() > 0) {
                    $companies = $companies->whereHas('alcances', function ($query) use ($countries) {
                        $query->whereIn('id_pais', $countries->pluck('id')->toArray());
                    });
                }
            }

            $results = $this->parseList($companies->get(), 'oferentes');

            $success = true;

        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
        }

        return $this->json($response, [
            'success' => $success,
            'message' => $message,
            'data' => [
                'results' => $results,
            ]
        ], $status);
    }

    public function list(Request $request, Response $response, $params)
    {
        $success = false;
        $message = null;
        $status = 200;
        $breadcrumbs = [];
        $results = [];
        $filters = $this->setFilters();

        try {
            $user = user();
            $role = $params['role'];

            switch ($role) {
                case 'client':
                    $type_description = 'Clientes';
                    $companies = CustomerCompany::all();
                    $associated = null;
                    break;
                default:
                    $type_description = 'Proveedores';
                    $companies = isAdmin() ? OffererCompany::all() : $user->customer_company->associated_offerers;
                    $associated = isAdmin() ? null : $user->customer_company->associated_offerers->count();
                    break;
            }

            $results = [
                'TotalAsociados' => $associated,
                'TotalOptus' => OffererCompany::all()->count(),
                'List' => []
            ];

            $results['List'] = $this->parseList($companies, $role);

            $success = true;

            // Breadcrumbs
            $breadcrumbs = [
                ['description' => 'Empresas', 'url' => null],
                ['description' => $type_description, 'url' => null]
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
                'results' => $results,
                'breadcrumbs' => $breadcrumbs,
                'filters' => $filters
            ]
        ], $status);
    }

    public function detail(Request $request, Response $response, $params)
    {
        $success = false;
        $message = null;
        $status = 200;
        $breadcrumbs = [];
        $list = [];

        try {
            $role = $params['role'];

            switch ($role) {
                case 'client':
                    $type_description = 'Clientes';
                    $company = CustomerCompany::find($params['id']);
                    break;
                default:
                    $type_description = 'Proveedores';
                    $company = OffererCompany::find($params['id']);
                    break;
            }

            $common = [
                'Tipo' => $role,
                'Id' => $company->id,
                'Estado' => $company->status->code,
                'EstadoDescripcion' => $company->status->description,
                'RazonSocial' => strtoupper($company->business_name),
                'Cuit' => $company->cuit,
                'Pais' => $company->country,
                'Provincia' => $company->province,
                'Ciudad' => $company->city,
                'Direccion' => $company->address,
                'CodigoPostal' => $company->postal_code,
                'Latitud' => $company->latitude,
                'Longitud' => $company->longitude,
                'Nombre' => $company->first_name,
                'Apellido' => $company->last_name,
                'Telefono' => $company->phone,
                'Celular' => $company->cellphone,
                'Email' => $company->email,
                'SitioWeb' => $company->website,
                'Observaciones' => $company->comments,
                'Paises' => $company->country,
                'Provincias' => $company->province,
                'Ciudades   ' => $company->city,
            ];

            if ($role == 'offerer') {
                $provinces = collect();
                $cities = collect();
                $selected_countries = collect();
                $selected_provinces = collect();
                $selected_cities = collect();

                $selected_countries = $company->alcances()->whereHas('country')->get()->pluck('country');

                foreach ($company->alcances()->whereHas('province')->get()->pluck('province') as $province) {
                    $country = $province->pais;
                    $provinces = $provinces->push($country->provincias)->flatten();
                    $selected_provinces = $selected_provinces->push($province)->flatten();
                    $selected_countries = $selected_countries->push($country)->flatten();
                    $cities = $cities->push($province->ciudades)->flatten();
                }

                foreach ($company->alcances()->whereHas('city')->get()->pluck('city') as $city) {
                    $province = $city->provincia;
                    $country = $province->pais;
                    $cities = $cities->push($province->ciudades)->flatten();
                    $selected_cities = $selected_cities->push($city)->flatten();
                    $provinces = $provinces->push($country->provincias)->flatten();
                    $selected_provinces = $selected_provinces->push($province)->flatten();
                    $selected_countries = $selected_countries->push($country)->flatten();
                }

                $list = array_merge($common, [
                    'Rubros' =>
                    $company->areas ?
                    $company->areas->pluck('name')->toArray() :
                    [],
                    'Paises' =>
                    $selected_countries ?
                    $selected_countries->unique('id')->pluck('nombre')->toArray() :
                    [],
                    'Provincias' =>
                    $selected_provinces ?
                    $selected_provinces->unique('id')->pluck('nombre')->toArray() :
                    [],
                    'Ciudades' =>
                    $selected_cities ?
                    $selected_cities->unique('id')->pluck('nombre')->toArray() :
                    [],
                    'Clientes' =>
                    $company->associated_customers ?
                    $company->associated_customers->pluck('business_name')->toArray() :
                    [],
                    'CodigoProveedor' => $company->supplier_code,
                    'FoundationYear' => $company->foundationyear,
                    'NumberOfEmployees' => $company->numberofemployees,
                    'AnnualIncome' => $company->annualincome,
                    'FacebookAccount' => $company->facebookaccount,
                    'TwitterAccount' => $company->twitteraccount,
                    'LinkedinAccount' => $company->linkedinaccount,
                    'CompanyDescription' => $company->companydescription,
                    'CompanyClassification' => $company->companyclassification,
                    'EconomicSector' => $company->economicsector,
                    'CompanyLogo' => $company->logo,
                    'filename' => $company->logo,
                    'LogoPath' => filePath(config('app.images_cliente_path')),
                    'Certifications' => $company->certifications
                ]);
            } elseif ($role == 'client') {
                $list = array_merge($common, [
                    'SistemaTarifario' => $company->rate_system->description
                ]);
            }

            $success = true;

            // Breadcrumbs
            $breadcrumbs = [
                ['description' => 'Empresas', 'url' => null],
                ['description' => $type_description, 'url' => route('empresas.serveList', ['role' => $role])],
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
        $breadcrumbs = [];
        $list = [];

        try {
            $role = $params['role'];
            $creation = $params['action'] === 'nuevo';

            $action_description = $creation ? 'Creación' : 'Edición';

            switch ($role) {
                case 'client':
                    $type_description = 'Clientes';
                    $company = $creation ? null : CustomerCompany::find($params['id']);
                    break;
                default:
                    $type_description = 'Proveedores';
                    $company = $creation ? null : OffererCompany::find($params['id']);
                    break;
            }

            // Refrescar la empresa para obtener las relaciones actualizadas
            if (!$creation && $company) {
                $company->refresh();
                // Limpiar la caché de relaciones para asegurar datos frescos (solo oferentes usan alcances)
                if ($role === 'offerer') {
                    $company->load('alcances');
                }
            }

            $common = [
                'Tipo' => $role,
                'Id' => $creation ? null : $company->id,
                'Estados' => $role === 'clientes' ? CustomerCompanyStatus::getList() : OffererCompanyStatus::getList(),
                'Estado' => $creation ? null : $company->status->id,
                'RazonSocial' => $creation ? null : strtoupper($company->business_name),
                'Cuit' => $creation ? null : $company->cuit,
                'Pais' => $creation ? null : $company->country,
                'Provincia' => $creation ? null : $company->province,
                'Localidad' => $creation ? null : $company->city,
                'Direccion' => $creation ? null : $company->address,
                'Cp' => $creation ? null : $company->postal_code,
                'Latitud' => $creation ? null : $company->latitude,
                'Longitud' => $creation ? null : $company->longitude,
                'Nombre' => $creation ? null : $company->first_name,
                'Apellido' => $creation ? null : $company->last_name,
                'Telefono' => $creation ? null : $company->phone,
                'Celular' => $creation ? null : $company->cellphone,
                'Email' => $creation ? null : $company->email,
                'SitioWeb' => $creation ? null : $company->website,
                'Observaciones' => $creation ? null : $company->comments,
                'timeZone' => $creation ? null : $company->timeZone
            ];

            if ($role == 'offerer') {
                $provinces = collect();
                $cities = collect();
                $selected_countries = collect();
                $selected_provinces = collect();
                $selected_cities = collect();

                if (!$creation) {
                    $selected_countries = $company->alcances()->whereHas('country')->get()->pluck('country');

                    foreach ($company->alcances()->whereHas('province')->get()->pluck('province') as $province) {
                        $country = $province->pais;
                        $selected_provinces = $selected_provinces->push($province)->flatten();
                        $selected_countries = $selected_countries->push($country)->flatten();
                    }

                    foreach ($company->alcances()->whereHas('city')->get()->pluck('city') as $city) {
                        $province = $city->provincia;
                        $country = $province->pais;
                        $selected_cities = $selected_cities->push($city)->flatten();
                        $selected_provinces = $selected_provinces->push($province)->flatten();
                        $selected_countries = $selected_countries->push($country)->flatten();
                    }
                }

                $list = array_merge($common, [
                    'Rubros' => Area::getList(),
                    'RubrosSelected' =>
                    $creation ?
                    [] :
                    (
                        $company->areas ?
                        $company->areas->pluck('id')->map(
                            function ($id) {
                                return (string) $id;
                            }
                        ) :
                        []
                    ),
                    'Paises' => Pais::getList(),
                    'PaisesSelected' =>
                    $creation ?
                    [] :
                    (
                        $selected_countries ?
                        $selected_countries->unique('id')->values()->map(function ($item) {
                            return (string) $item->id;
                        }) :
                        []
                    ),
                    'ProvinciasSelected' =>
                    $creation ?
                    [] :
                    (
                        $selected_provinces ?
                        $selected_provinces->unique('id')->values()->map(function ($item) {
                            return (string) $item->id;
                        }) :
                        []
                    ),
                    'CiudadesSelected' =>
                    $creation ?
                    [] :
                    (
                        $selected_cities ?
                        $selected_cities->unique('id')->values()->map(function ($item) {
                            return (string) $item->id;
                        }) :
                        []
                    ),
                    'ClientesAsociados' => CustomerCompany::getList(),
                    'ClienteAsociado' =>
                    $creation ?
                    [] :
                    (
                        $company->associated_customers ?
                        $company->associated_customers->pluck('id')->values()->map(function ($id) {
                            return (string) $id;
                        }) :
                        []
                    ),
                    'CodigoProveedor' => $creation ? null : $company->supplier_code
                ]);
            } elseif ($role == 'client') {
                $list = array_merge($common, [
                    'Tarifarios' => RateSystem::getList(),
                    'Tarifario' => $creation ? null : $company->rate_system->id,
                    'timeZones' => $this->getTimeZones(),
                ]);
            }

            $success = true;

            // Breadcrumbs
            $breadcrumbs = [
                ['description' => 'Empresas', 'url' => null],
                ['description' => $type_description, 'url' => route('empresas.serveList', ['role' => $role])],
                ['description' => $action_description, 'url' => null]
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

    public function editProfile(Request $request, Response $response, $params)
    {
        $success = false;
        $message = null;
        $status = 200;
        $breadcrumbs = [];
        $list = [];

        try {
            $role = $params['role'];
            $creation = false;

            $action_description = 'Edición perfil';

            if ($role === "oferentes") {
                $type_description = 'Proveedores';
                $company = OffererCompany::find($params['id']);
            }

            $common = [
                'Tipo' => $role,
                'Id' => $creation ? null : $company->id,
                'Estados' => $role === 'clientes' ? CustomerCompanyStatus::getList() : OffererCompanyStatus::getList(),
                'Estado' => $creation ? null : $company->status->id,
                'RazonSocial' => $creation ? null : strtoupper($company->business_name),
                'Cuit' => $creation ? null : $company->cuit,
                'Pais' => $creation ? null : $company->country,
                'Provincia' => $creation ? null : $company->province,
                'Localidad' => $creation ? null : $company->city,
                'Direccion' => $creation ? null : $company->address,
                'Cp' => $creation ? null : $company->postal_code,
                'Latitud' => $creation ? null : $company->latitude,
                'Longitud' => $creation ? null : $company->longitude,
                'Nombre' => $creation ? null : $company->first_name,
                'Apellido' => $creation ? null : $company->last_name,
                'Telefono' => $creation ? null : $company->phone,
                'Celular' => $creation ? null : $company->cellphone,
                'Email' => $creation ? null : $company->email,
                'SitioWeb' => $creation ? null : $company->website,
                'Observaciones' => $creation ? null : $company->comments
            ];

            $provinces = collect();
            $cities = collect();
            $selected_countries = collect();
            $selected_provinces = collect();
            $selected_cities = collect();

            if (!$creation) {
                $selected_countries = $company->alcances()->whereHas('country')->get()->pluck('country');

                foreach ($company->alcances()->whereHas('province')->get()->pluck('province') as $province) {
                    $country = $province->pais;
                    $selected_provinces = $selected_provinces->push($province)->flatten();
                    $selected_countries = $selected_countries->push($country)->flatten();
                }

                foreach ($company->alcances()->whereHas('city')->get()->pluck('city') as $city) {
                    $province = $city->provincia;
                    $country = $province->pais;
                    $selected_cities = $selected_cities->push($city)->flatten();
                    $selected_provinces = $selected_provinces->push($province)->flatten();
                    $selected_countries = $selected_countries->push($country)->flatten();
                }
            }

            $list = array_merge($common, [
                'Rubros' => Area::getList(),
                'RubrosSelected' =>
                $creation ?
                [] :
                (
                    $company->areas ?
                    $company->areas->pluck('id')->map(
                        function ($id) {
                            return (string) $id;
                        }
                    ) :
                    []
                ),
                'Paises' => Pais::getList(),
                'PaisesSelected' =>
                $creation ?
                [] :
                (
                    $selected_countries ?
                    $selected_countries->unique('id')->values()->map(function ($item) {
                        return (string) $item->id;
                    }) :
                    []
                ),
                'ProvinciasSelected' =>
                $creation ?
                [] :
                (
                    $selected_provinces ?
                    $selected_provinces->unique('id')->values()->map(function ($item) {
                        return (string) $item->id;
                    }) :
                    []
                ),
                'CiudadesSelected' =>
                $creation ?
                [] :
                (
                    $selected_cities ?
                    $selected_cities->unique('id')->values()->map(function ($item) {
                        return (string) $item->id;
                    }) :
                    []
                ),
                'ClientesAsociados' => CustomerCompany::getList(),
                'ClienteAsociado' =>
                $creation ?
                [] :
                (
                    $company->associated_customers ?
                    $company->associated_customers->pluck('id')->values()->map(function ($id) {
                        return (string) $id;
                    }) :
                    []
                ),
                'CodigoProveedor' => $creation ? null : $company->supplier_code,
                'FoundationYear' => $creation ? null : $company->foundationyear,
                'OptionsNumberOfEmployees' => OffererCompany::getListOptionsNumberOfEmployees(),
                'NumberOfEmployees' => $creation ? null : $company->numberofemployees,
                'OptionsAnnualIncome' => OffererCompany::getListOptionsAnnualIncome(),
                'AnnualIncome' => $creation ? null : $company->annualincome,
                'FacebookAccount' => $creation ? null : $company->facebookaccount,
                'TwitterAccount' => $creation ? null : $company->twitteraccount,
                'LinkedinAccount' => $creation ? null : $company->linkedinaccount,
                'CompanyDescription' => $creation ? null : $company->companydescription,
                'OptionsClassification' => OffererCompany::getListOptionsClassification(),
                'CompanyClassification' => $creation ? null : $company->companyclassification,
                'OptionsEconomicSector' => OffererCompany::getListOptionsEconomicsector(),
                'EconomicSector' => $creation ? null : $company->economicsector,
                'CompanyLogo' => $creation ? null : $company->logo,
                'filename' => $creation ? null : $company->logo,
                'LogoPath' => filePath(config('app.images_cliente_path')),
                'Certifications' => $creation ? null : $company->certifications
            ]);

            $success = true;

            // Breadcrumbs
            $breadcrumbs = [
                ['description' => 'Empresas', 'url' => null],
                ['description' => $action_description, 'url' => null]
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

    public function store(Request $request, Response $response, $params)
    {
        $success = false;
        $message = null;
        $status = 200;
        $redirect_url = null;
        try {
            $capsule = dependency('db');
            $connection = $capsule->getConnection();
            $connection->beginTransaction();

            $body = json_decode($request->getParsedBody()['Data']);
            $creation = !isset($params['id']);
            $role = $params['role'];

            // Check if it's a new business creation
            if ($creation) {
                $currentUserId = user()->id;
                $cuit = $body->Cuit;

                // Check if a business with the same CUIT already exists for this user
                if ($this->checkExistingBusiness($cuit, $currentUserId, $role)) {
                    $connection->rollBack();
                    return $this->json($response, [
                        'success' => false,
                        'message' => 'Ya existe una empresa con este CUIT asociada a su cuenta.',
                        'data' => [
                            'redirect' => null
                        ]
                    ], 422);
                }
            }

            $fields = [
                'business_name' => strtoupper($body->RazonSocial),
                'cuit' => $body->Cuit,
                'country' => $body->Pais,
                'province' => $body->Provincia,
                'city' => $body->Localidad,
                'address' => $body->Direccion,
                'postal_code' => $body->Cp,
                'latitude' => $body->Latitud,
                'longitude' => $body->Longitud,
                'first_name' => $body->Nombre,
                'last_name' => $body->Apellido,
                'phone' => $body->Telefono,
                'cellphone' => $body->Celular,
                'email' => $body->Email,
                'website' => $body->SitioWeb,
                'comments' => $body->Observaciones
            ];

            if ($creation) {
                $fields = array_merge($fields, [
                    'creator_id' => user()->id
                ]);
            }

            if ($role === 'client') {
                $result = $this->storeCompanyClient($params, $body, $fields, $role, $creation);
            }

            if ($role === 'oferentes' || $role === 'offerer') {
                $result = $this->storeCompanyOfferer($params, $body, $fields, $role, $creation);
            }

            if ($result['success']) {
                $connection->commit();
                $redirect_url = route('empresas.serveList', ['role' => $role]);
                $success = true;
                $message = $result['message'];
            } else {
                $connection->rollBack();
                $redirect_url = null;
                $success = false;
                $message = $result['message'];
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

    public function toggleAssociation(Request $request, Response $response, $params)
    {
        $role = $params['role'];
        $success = false;
        $message = null;
        $status = 200;
        $list = [];
        $total = 0;
        $redirect_url = route('empresas.serveList', ['role' => $role]);
        $is_associated = false;


        try {
            $capsule = dependency('db');
            $connection = $capsule->getConnection();
            $connection->beginTransaction();
            $body = json_decode($request->getParsedBody()['Data']);
            $user = user();

            $customer_company = $user->customer_company;
            $offerer_company = OffererCompany::find((int) $body->Id);

            if ($customer_company && $offerer_company) {
                $associated_offerers = $customer_company->associated_offerers;
                if ($associated_offerers->where('id', $offerer_company->id)->count() > 0) {
                    $offerer_ids = $associated_offerers->where('id', '!=', $offerer_company->id)->pluck('id');
                } else {
                    $offerer_ids = $associated_offerers->pluck('id');
                    $offerer_ids[] = $offerer_company->id;
                    $is_associated = true;
                }
                $customer_company->associated_offerers()->sync($offerer_ids);

                $user->refresh();
                $total = $user->customer_company->associated_offerers->count();
                $connection->commit();
                $success = true;
                $message =
                    'Empresa ' .
                    $offerer_company->business_name .
                    ($is_associated ? ' asociada' : ' desasociada') .
                    ' con éxito.';
            } else {
                $message = 'No se ha podido asociar la empresa.';
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
                'redirect' => $redirect_url,
                'total' => $total,
                'is_associated' => $is_associated
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

        try {
            $role = $params['role'];
            $company =
                $role === 'clientes' ?
                CustomerCompany::find((int) $params['id']) :
                OffererCompany::find((int) $params['id']);

            $company->delete();

            $success = true;
            $message = 'Empresa eliminada con éxito.';

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

    private function validate($fields, $role)
    {
        $conditional_rules = [];
        $common_rules = [
            'business_name' => 'required|max:150',
            'cuit' => 'required',
            'country' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'latitude' => [
                'nullable',
                'regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/',
                'max:255'
            ],
            'longitude' => [
                'nullable',
                'regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/',
                'max:255'
            ],
            'postal_code' => 'max:20',
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'phone' => 'nullable|numeric',
            'cellphone' => 'nullable|numeric',
            'email' => 'required|email',
            'website' => 'nullable|string|max:200',
            'comments' => 'nullable|string|max:255'
        ];

        if ($role === 'clientes') {

            $conditional_rules = [
                'status_id' => 'required|exists:customer_company_status,id',
                'rate_system_id' => 'required|exists:rate_systems,id'
            ];

        } elseif ($role === 'oferentes') {

            $conditional_rules = [
                // 'status_id' => 'required|exists:offerer_company_status,id',
                'supplier_code' => 'nullable|string|max:50',
            ];

        }

        return validator(
            $data = $fields,
            $rules = array_merge($common_rules, $conditional_rules)
        );
    }

    private function getTimeZones()
    {
        $timeZones = DateTimeZone::listIdentifiers(DateTimeZone::AMERICA);
        $result = [];

        foreach ($timeZones as $zone) {
            $result[] = [
                'id' => $zone,
                'text' => $zone . ' ' . $this->toGmtOffset($zone)
            ];
        }

        return $result;

    }

    private function toGmtOffset($timezone)
    {
        $userTimeZone = new DateTimeZone($timezone);
        $offset = $userTimeZone->getOffset(new DateTime("now", new DateTimeZone('GMT'))); // Offset in seconds
        $seconds = abs($offset);
        $sign = $offset > 0 ? '+' : '-';
        $hours = floor($seconds / 3600);
        $mins = floor($seconds / 60 % 60);
        $secs = floor($seconds % 60);
        return sprintf("(GMT$sign%02d:%02d)", $hours, $mins, $secs);
    }

    public function searchCuit(Request $request, Response $response, $params)
    {
        $success = false;
        $message = null;
        $status = 200;
        $data = null;
        $redirect_url = null;

        try {
            $role = $params['role'];
            $company = OffererCompany::where('cuit', $params['cuit'])->get();
            if ($company->count() === 1) {
                $data = $company;
                $success = true;
                $message = 'Empresa existente';
            } else {
                $data = null;
                $success = false;
                $message = 'Empresa no existe';
            }

        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
        }

        return $this->json($response, [
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    private function storeCompanyClient($params, $body, $fields, $role, $creation)
    {
        $result = [
            'success' => false,
            'message' => null,
            'status' => 200
        ];
        $company_status = CustomerCompanyStatus::find((int) $body->Estado);
        $rate_system = RateSystem::find((int) $body->Tarifario);
        $fields = array_merge($fields, [
            'status_id' => $company_status ? $company_status->id : 1,
            'rate_system_id' => $rate_system ? $rate_system->id : null,
            'timeZone' => $body->TimeZone
        ]);

        $validator = $this->validate($fields, $role);

        if ($validator->fails()) {
            $result['success'] = false;
            $result['status'] = 422;
            $result['message'] = $validator->errors()->first();
        } else {
            try {
                if ($creation) {
                    $fields['creator_id'] = user()->id;
                    $company = new CustomerCompany($fields);
                    $company->save();
                    $result['success'] = true;
                    $result['status'] = 200;
                    $result['message'] = 'Cliente creado con éxito.';
                } else {
                    $company = CustomerCompany::find((int) $params['id']);
                    $company->update($fields);
                    $result['success'] = true;
                    $result['status'] = 200;
                    $result['message'] = 'Cliente editado con éxito.';
                }

            } catch (\Exception $e) {
                $result['success'] = false;
                $result['status'] = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
                $result['message'] = $e->getMessage();
            } finally {
                return $result;
            }

        }
        return $result;
    }

    private function storeCompanyOfferer($params, $body, $fields, $role, $creation)
    {
        $result = [
            'success' => false,
            'message' => null,
            'status' => 200
        ];

        // Asegurar que los arrays de alcance siempre existan
        if (!isset($body->PaisesSelected) || !is_array($body->PaisesSelected)) {
            $body->PaisesSelected = [];
        }
        if (!isset($body->ProvinciasSelected) || !is_array($body->ProvinciasSelected)) {
            $body->ProvinciasSelected = [];
        }
        if (!isset($body->CiudadesSelected) || !is_array($body->CiudadesSelected)) {
            $body->CiudadesSelected = [];
        }
        if (!isset($body->RubrosSelected) || !is_array($body->RubrosSelected)) {
            $body->RubrosSelected = [];
        }

        $company_status = OffererCompanyStatus::find((int) $body->Estado);

        $fields = array_merge($fields, [
            'status_id' => $company_status ? $company_status->id : 1,
            'supplier_code' => $body->CodigoProveedor
        ]);

        if (isset($body->FoundationYear))
            $fields = array_merge($fields, ['foundationyear' => ($body->FoundationYear === '' ? null : $body->FoundationYear)]);

        if (isset($body->NumberOfEmployees))
            $fields = array_merge($fields, ['numberofemployees' => ($body->NumberOfEmployees === '' ? null : $body->NumberOfEmployees)]);

        if (isset($body->AnnualIncome))
            $fields = array_merge($fields, ['annualincome' => ($body->AnnualIncome === '' ? null : $body->AnnualIncome)]);

        if (isset($body->EconomicSector))
            $fields = array_merge($fields, ['economicsector' => ($body->EconomicSector === '' ? null : $body->EconomicSector)]);

        if (isset($body->CompanyClassification))
            $fields = array_merge($fields, ['companyclassification' => ($body->CompanyClassification === '' ? null : $body->CompanyClassification)]);

        if (isset($body->FacebookAccount))
            $fields = array_merge($fields, ['facebookaccount' => $body->FacebookAccount]);

        if (isset($body->TwitterAccount))
            $fields = array_merge($fields, ['twitteraccount' => $body->TwitterAccount]);

        if (isset($body->LinkedinAccount))
            $fields = array_merge($fields, ['linkedinaccount' => $body->LinkedinAccount]);

        if (isset($body->CompanyDescription))
            $fields = array_merge($fields, ['companydescription' => $body->CompanyDescription]);

        $fields = array_merge($fields, ['companylogo' => (isset($body->Logo->filename) ? $body->Logo->filename : null)]);

        if (isset($body->Certifications))
            $fields = array_merge($fields, ['certifications' => $body->Certifications]);

        $validator = $this->validate($fields, $role);

        if ($validator->fails()) {
            $result['success'] = false;
            $result['status'] = 422;
            $result['message'] = $validator->errors()->first();
        } else {
            try {
                if ($creation) {
                    $company = new OffererCompany($fields);
                    $company->save();
                    $company->refresh();

                    if (isAdmin()) {
                        $clientesAsociados = $body->ClienteAsociado;
                        $company->associated_customers()->sync($clientesAsociados);
                    }

                    if (isCustomer()) {
                        $user = user();
                        $customer_company = $user->customer_company;
                        $company->associated_customers()->sync([$customer_company->id]);
                    }
                    $result['success'] = true;
                    $result['status'] = 200;
                    $result['message'] = 'Proveedor creado con éxito.';
                } else {
                    $company = OffererCompany::find((int) $params['id']);
                    $company->update($fields);
                    $company->refresh();
                    if (isAdmin()) {
                        $clientesAsociados = $body->ClienteAsociado;
                        $company->associated_customers()->sync($clientesAsociados);
                    }
                    $result['success'] = true;
                    $result['status'] = 200;
                    $result['message'] = 'Proveedor editado con éxito.';
                }
                // Rubros
                if (count($body->RubrosSelected) > 0) {
                    $areas = Area::whereIn('id', $body->RubrosSelected)->get();
                    $company->areas()->sync($areas->pluck('id')->toArray());
                } else {
                    // Si no hay rubros seleccionados, limpiar la relación
                    $company->areas()->sync([]);
                }

                // Alcances - Eliminar todos los alcances existentes
                $company->alcances()->delete();

                // Asegurar que los arrays existan y sean arrays
                $ciudadesSelected = isset($body->CiudadesSelected) && is_array($body->CiudadesSelected) ? $body->CiudadesSelected : [];
                $provinciasSelected = isset($body->ProvinciasSelected) && is_array($body->ProvinciasSelected) ? $body->ProvinciasSelected : [];
                $paisesSelected = isset($body->PaisesSelected) && is_array($body->PaisesSelected) ? $body->PaisesSelected : [];

                foreach ($ciudadesSelected as $id) {
                    $alcance = new Alcance([
                        'id_empresa_oferente' => $company->id,
                        'id_ciudad' => $id
                    ]);
                    $alcance->save();
                }

                foreach ($provinciasSelected as $id) {
                    $province = Provincia::where('id', $id)->whereHas('ciudades', function ($query) use ($ciudadesSelected) {
                        $query->whereIn('id', $ciudadesSelected);
                    })->get()->first();
                    if ($province) {
                        continue;
                    }
                    $alcance = new Alcance([
                        'id_empresa_oferente' => $company->id,
                        'id_provincia' => $id
                    ]);
                    $alcance->save();
                }

                foreach ($paisesSelected as $id) {
                    $country = Pais::where('id', $id)->whereHas('provincias', function ($query) use ($provinciasSelected) {
                        $query->whereIn('id', $provinciasSelected);
                    })->get()->first();
                    if ($country) {
                        continue;
                    }
                    $alcance = new Alcance([
                        'id_empresa_oferente' => $company->id,
                        'id_pais' => $id
                    ]);
                    $alcance->save();
                }

                // Refrescar la empresa para actualizar las relaciones
                $company->refresh();

            } catch (\Exception $e) {
                $result['success'] = false;
                $result['status'] = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
                $result['message'] = $e->getMessage();
            } finally {
                return $result;
            }
        }


        return $result;
    }

    private function checkExistingBusiness($cuit, $creatorId, $role)
    {
        $model = $role === 'client' ? CustomerCompany::class : OffererCompany::class;
        return $model::where('cuit', $cuit)
            ->where('creator_id', $creatorId)
            ->exists();
    }
}