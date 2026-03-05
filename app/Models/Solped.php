<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use App\Models\Go;
use App\Models\User;
use App\Models\Model;
use App\Models\Alcance;
use App\Models\Measurement;
use App\Models\Producto;
use App\Models\Mensaje;
use App\Models\Participante;
use App\Models\TipoOperacion;
use App\Models\TipoConvocatoria;
use App\Models\Invitation;
use App\Models\Sheet;
use App\Services\DocumentService;
use App\Models\OffererCompany;
use App\Models\Concurso;

class Solped extends Model 
{
    use SoftDeletes;

    protected $table = 'solpeds';

    protected $dates  = [
        'fecha_alta',
        'fecha_resolucion',
        'fecha_entrega',
        'fecha_envio_a_comprador',
        'fecha_devolucion',
        'fecha_aceptacion',
        'fecha_first_revision',
        'fecha_rechazo',
        'fecha_inicio_licitacion',
        'updated_at',
        'deleted_at'
    ];

    protected $fillable = [
        'id',
        'id_solicitante',
        'codigo_interno',
        'area_sol',
        'nombre',
        'descripcion',
        'pais',
        'provincia',
        'localidad',
        'direccion',
        'cp',
        'latitud',
        'longitud',
        'etapa_actual',
        'estado_actual',
        'clasificacion',
        'reject_comment',
        'return_comment',
        'id_comprador_sugerido',
        'id_comprador_decision',
        'id_comprador_first_revision',
        'tipo_compra',
        'fecha_envio_a_comprador',
        'fecha_first_revision',
        'fecha_rechazo',
        'fecha_devolucion',
        'fecha_resolucion',
        'fecha_entrega',
        'cancel_motive',
    ];

    protected $appends = [
        'compradores_etapa_preparacion',
        'comprador_reviso_solped',
        'tipo_compra_nombre',
        'file_path'
    ];

    const TYPE_DESCRIPTION = [
        '1' => 'Normal',
        '2' => 'Urgencia',
        '3' => 'Regularizacion'
    ];

    // const $estados_posibles = [
    //     'borrador',
    //     'esperando-revision',
    //     'esperando-revision-2',
    //     'revisada',
    //     'revisada-2',
    //     'rechazada',
    //     'devuelta',
    //     'aceptada',
    //     'en-licitacion',
    //     'finalizada'
    // ]

    // const $etapas_posibles = [
    //     'en-preparacion',
    //     'en-analisis',
    //     'acepatada',
    //     'rechazada',
    //     'devuelta',
    //     'cancelada',
    //     'finalizada'
    // ]

    

    public function productos()
    {
        return $this->hasMany(SolpedItems::class, 'id_solped', 'id')
            ->with('unidad_medida')
            ->where('eliminado', '!=', '1');
    }

    public function solicitante()
    {
        return $this->belongsTo(User::class, 'id_solicitante');
    }

    public function compradores()
    {
        return $this->hasMany(User::class, 'customer_company_id', 'customer_company_id');
    }

    public function comprador_sugerido()
    {
        return $this->belongsTo(User::class, 'id_comprador_sugerido');
    }

    public function comprador_decision()
    {
        return $this->belongsTo(User::class, 'id_comprador_decision');
    }

    public function comprador_first_revision()
    {
        return $this->belongsTo(User::class, 'id_comprador_first_revision');
    }

    public function cliente()
    {
        return $this->belongsTo(User::class, 'id_solicitante');
    }

    public function documents()
    {
        return $this->hasMany(SolpedDocument::class, 'solped_id', 'id');
    }

    public function getTipocompraNombreAttribute()
    {
        return $this::TYPE_DESCRIPTION[$this->attributes['tipo_compra']];
    }

     public function getFilePathAttribute()
    {
        // Retornar ruta base usando el solicitante (cliente)
        if ($this->cliente && $this->fecha_alta) {
            $year = $this->fecha_alta instanceof \Carbon\Carbon 
                ? $this->fecha_alta->format('Y') 
                : substr($this->fecha_alta, 0, 4);
            return 'solpeds/' . $this->cliente->customer_company->cuit . '/' . $year . '/';
        }
        return 'solpeds/';
    }

    public function getSolpedsEnPreparacionAttribute()
    {
        return $this->etapa_actual === 'en-preparacion' && $this->estado_actual === 'borrador';
    }


    public function getSolpedsEnAnalisisAttribute()
    {
        return $this->etapa_actual === 'en-analisis' || 
               in_array($this->estado_actual, ['esperando-revision', 'revisada', 'esperando-revision-2', 'revisada-2']);
    }


    


}