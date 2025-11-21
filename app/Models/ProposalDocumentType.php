<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Model;

class ProposalDocumentType extends Model
{
    use SoftDeletes;

    protected $table = 'proposal_document_types';

    protected $fillable = [
        'id',
        'code',
        'description'
    ];

    protected $appends = [
        'is_technical',
        'is_economic'
    ];

    public function getIsTechnicalAttribute()
    {
        return in_array($this->attributes['code'], [
            'technical',
            'gantt',
            'surety',
            'condition',
            'general',
            'technicalSigned',
            'confidentiality',
            'impositive',
            'reference',
            'accidents',
            'sample',
            'nom',
            'distintivo',
            'filters',
            'repse',
            'responsability',
            'risk',
            'obras_referencias',
            'obras_organigrama',
            'obras_equipos',
            'obras_cronograma',
            'obras_memoria',
            'obras_antecedentes',
            'tarima_ficha_tecnica',
            'tarima_licencia',
            'tarima_nom_144',
            'tarima_acreditacion',
            'list_prov',
            'cert_visita',
            'edificio_balance',
            'edificio_iva',
            'edificio_cuit',
            'edificio_brochure',
            'edificio_organigrama',
            'edificio_organigrama_obra',
            'edificio_subcontratistas',
            'edificio_gestion',
            'edificio_maquinas',
            'entrega_doc_evaluacion',
            'requisitos_legales',
            'experiencia_y_referencias',
            'repse_two',
            'alcance_two',
            'garantias',
            'forma_pago',
            'tiempo_fabricacion',
            'ficha_tecnica',
            // ===== NUEVOS CAMPOS PLANTILLA 7 =====
            'propuesta_tecnica',
            'plan_mantenimiento_preventivo',
            'nda_firmado',
            'inventario_equipos',
            'acreditaciones_permisos',
            'requerimientos_tecnologicos',
            'requisitos_personal',
            'organigrama_equipo',
            'valor_agregado',
            'acuerdos_nivel_servicio',
            'hseq_anexo2',
            'referencias_comerciales',
            'riesgo_financiero',
            // ===== NUEVOS CAMPOS PLANTILLA 8 =====
            'ficha_especificaciones',
            'msds_hojas_seguridad',
            'garantia_tecnica',
            'cronograma_entrega',
            'carta_representante_marca',
            'soporte_post_venta',        
            'lugar_forma_entrega',
            // ===== NUEVOS CAMPOS PLANTILLA 1 =====
            'listado-equipos-herramientas',
            'equipo-humano-competencias',
            'balances-estados-resultados',
            'estatuto-contrato-social',
            'actas-designacion-autoridades',




        ]);
    }

    public function getIsEconomicAttribute()
    {
        return in_array($this->attributes['code'], [
            'economic',
            'costs',
            'analisisApu'
        ]);
    }
}
