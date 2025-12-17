<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Model;
use App\Models\User;
use App\Models\RateSystem;
use App\Models\CustomerCompanyStatus;
use App\Models\Concurso;

class CustomerCompany extends Model
{
    use SoftDeletes;

    protected $table = 'customer_companies';

    public $timestamps = true;

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $fillable = [
        'id',
        'status_id',
        'creator_id',
        'rate_system_id',
        'business_name',
        'cuit',
        'postal_code',
        'country',
        'province',
        'city',
        'address',
        'latitude',
        'longitude',
        'first_name',
        'last_name',
        'phone',
        'cellphone',
        'email',
        'website',
        'comments',
        'timeZone'
    ];

    protected $appends = [
        'full_name'
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'customer_company_id', 'id');
    }

    public function created_by()
    {
        return $this->hasOne(User::class, 'id', 'creator_id');
    }

    public function associated_offerers()
    {
        return $this->belongsToMany(OffererCompany::class, 'offerers_customers', 'customer_id', 'offerer_id');
    }

    public function status()
    {
        return $this->hasOne(CustomerCompanyStatus::class, 'id', 'status_id');
    }

    public function rate_system()
    {
        return $this->hasOne(RateSystem::class, 'id', 'rate_system_id');
    }

    public function getFullNameAttribute()
    {
        return $this->attributes['first_name'] . ' ' . $this->attributes['last_name'];
    }

    public static function getList()
    {
        $result = [];

        foreach (self::all() as $company) {
            $result[] = [
                'id' => (string) $company->id,
                'text' => $company->business_name,
                'is_offerer' => false,
                'is_customer' => true
            ];
        }

        return $result;
    }

    public function getAllConcursosByCompany()
    {
        // dd($this);
        return $this->hasManyThrough(Concurso::class, User::class, 'customer_company_id', 'id_cliente', 'id', 'id');
    }

    public function getAllConcursosCanceled()
    {
        return $this->hasManyThrough(Concurso::class, User::class, 'customer_company_id', 'id_cliente', 'id', 'id')->whereNotNull('concursos.deleted_at');
    }

        public function getAllSolpedsByCompany()
    {
        return $this->hasManyThrough(Solped::class, User::class, 'customer_company_id', 'id_solicitante', 'id', 'id');
    }
}