<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Model;
use App\Models\Alcance;
use App\Models\OffererCompany;
use App\Models\CustomerCompany;
use App\Models\UserType;
use App\Models\UserStatus;
use App\Models\Permission;

class User extends Model
{
    use SoftDeletes;

    public $timestamps = true;

    protected $table = 'users';

    protected $dates = [
        'validity_date',
        'created_at',
        'updated_at',
        'pass_date'
    ];

    protected $fillable = [
        'id',
        'type_id',
        'status_id',
        'offerer_company_id',
        'customer_company_id',
        'username',
        'password',
        'first_name',
        'last_name',
        'phone',
        'cellphone',
        'email',
        'ad',
        'token',
        'validity_date',
        'pass_change',
        'area',
        'rol',
        'pass_date',
        'two_factor_code',
        'requires_ip_verification'
    ];

    protected $appends = [
        'full_name',
        'is_admin',
        'is_super_admin',
        'is_customer',
        'is_offerer',
        'file_path_customer',
        'file_path_offerer',
        'image'
    ];

    public function status()
    {
        return $this->belongsTo(UserStatus::class, 'status_id', 'id');
    }

    public function concursos_invitado()
    {
        return $this->belongsToMany(Concurso::class, 'concursos_x_oferentes', 'id_offerer', 'id_concurso', 'offerer_company_id')->whereNull('concursos.deleted_at');
    }

    public function concursos_invitado_with_trashed()
    {
        return $this->belongsToMany(Concurso::class, 'concursos_x_oferentes', 'id_offerer', 'id_concurso', 'offerer_company_id')->withTrashed();
    }

    public function type()
    {
        return $this->hasOne(UserType::class, 'id', 'type_id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'users_permissions');
    }

    public function concursos_creados()
    {
        return $this->hasMany(Concurso::class, 'id_cliente', 'id');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_usuario', 'id')
            ->where('eliminado', '!=', '1');
    }

    public function getIsSuperAdminAttribute()
    {
        return $this->type->is_superadmin;
    }

    public function getIsAdminAttribute()
    {
        return $this->type->is_admin || $this->type->is_superadmin;
    }

    public function getIsCustomerAttribute()
    {
        return $this->type->is_customer;
    }

    public function getIsOffererAttribute()
    {
        return $this->type->is_offerer;
    }

    public function concursos_evalua()
    {
        return $this->hasMany(Concurso::class, 'ficha_tecnica_usuario_evalua', 'id');
    }

    public function concursos_califica()
    {
        return $this->hasMany(Concurso::class, 'usuario_califica_reputacion', 'id');
    }

    public function offerer_company()
    {
        return $this->belongsTo(OffererCompany::class, 'offerer_company_id', 'id');
    }

    public function customer_company()
    {
        return $this->belongsTo(CustomerCompany::class, 'customer_company_id', 'id');
    }

    public function getImageAttribute()
    {
        return filePath(config('app.images_user_path') . 'default.jpg');
    }

    public function can($permission)
    {
        $permissions = $this->permissions->pluck('code')->toArray();
        return $permissions ? in_array($permission, $permissions) : false;
    }

    public function cannot($permission)
    {
        $permissions = $this->permissions->pluck('code')->toArray();
        return $permissions ? !in_array($permission, $permissions) : true;
    }

    public function getFullNameAttribute()
    {
        return $this->attributes['first_name'] . ' ' . $this->attributes['last_name'];
    }

    public function getFilePathCustomerAttribute()
    {
        if (!$this->is_customer) {
            return null;
        }

        return
            config('app.files_path') .
            $this->customer_company->cuit .
            DIRECTORY_SEPARATOR;
    }

    public function getFilePathOffererAttribute()
    {
        if (!$this->is_offerer) {
            return null;
        }

        return
            config('app.files_path') .
            $this->offerer_company->cuit .
            DIRECTORY_SEPARATOR;
    }

    public function getRelatedByRoleSlug($role_slug = null)
    {
        $results = collect();

        if (isSuperAdmin()) {
            $results = self::all();
        } elseif ($this->is_offerer) {
            $results = $this->offerer_company->associated_customers->pluck('users')->flatten()->unique('id');
        } elseif (isAdmin() || $this->is_customer) {
            $results = $results
                ->merge(
                    $this->customer_company->associated_offerers->pluck('users')->flatten()
                )->merge(
                    $this->customer_company->users
                )
                ->unique('id');
        } else {
            $results = self::all();
        }

        if ($role_slug) {
            $results = $results->filter(function ($item) use ($role_slug) {
                return $item->type->code === $role_slug;
            });
        }

        return $results->filter(function ($item) {
            return $item->id !== $this->id;
        });
    }

    public function getEvaluadoresTecnicaList()
    {
        $result = [];

        $users = collect();
        $users = $users->push(user());
        $users = $users
            ->merge(
                $this->getRelatedByRoleSlug(UserType::TYPES['customer-approve'])
            )->merge(
                $this->getRelatedByRoleSlug(UserType::TYPES['customer'])
            )->merge(
                $this->getRelatedByRoleSlug(UserType::TYPES['evaluator'])
            );

        foreach ($users as $user) {
            $result[] = [
                'id' => $user->id,
                'text' => $user->full_name
            ];
        }

        return $result;
    }

    public function getEvaluadoresTecnicalSelected($ids)
    {
        $result = [];
        $selected = $this->whereIn('id', explode(',', $ids))->get();

        foreach ($selected as $user) {
            $result[] = $user->id;
        }

        return $result;
    }

    public function getSupervisoresList()
    {
        $result = [];
        $users = collect();
        $users = $users
            ->merge(
                $this->getRelatedByRoleSlug(UserType::TYPES['supervisor'])
            );
        foreach ($users as $user) {
            $result[] = [
                'id' => $user->id,
                'text' => $user->full_name
            ];
        }
        return $result;
    }

    public function getCompradoresByCompanyList()
    {
        $result = [];
        $query = self::query()->where('type_id', 3); // 3 = compradores

        // Caso: cliente / admin / superadmin -> mismos compradores de su company
        if ($this->is_customer || isAdmin() || isSuperAdmin()) {
            if ($this->customer_company_id) {
                $query->where('customer_company_id', $this->customer_company_id);
            } else {
                return $result; // sin company, no hay qué listar
            }
        }
        // Caso: oferente -> compradores de las compañías cliente asociadas a su empresa oferente
        elseif ($this->is_offerer && $this->offerer_company) {
            $customerIds = $this->offerer_company->associated_customers
                ? $this->offerer_company->associated_customers->pluck('id')->all()
                : [];

            if (!empty($customerIds)) {
                $query->whereIn('customer_company_id', $customerIds);
            } else {
                return $result;
            }
        }
        // Otros tipos de usuario: no listamos
        else {
            return $result;
        }

        // SoftDeletes ya filtra borrados por defecto
        $users = $query->orderBy('first_name')->orderBy('last_name')->get();

        foreach ($users as $user) {
            $result[] = [
                'id'   => $user->id,
                'text' => $user->full_name,
            ];
        }

        return $result;
    }
}