<?php

namespace App\Models;

use App\Models\Model;
use App\Models\OffererCompany;
use App\Models\Concurso;
use App\Models\Evaluacion;
use App\Models\ParticipanteGoDocument;
use App\Models\Payment;
use App\Models\DocumentType;
use App\Models\Proposal;
use App\Models\Invitation;
use Carbon\Carbon;

class Participante extends Model
{
    protected $primaryKey = 'id';

    protected $table = 'concursos_x_oferentes';

    protected $casts = [
        'rechazado' => 'boolean'
    ];

    protected $dates = [
        'fecha_declination',
        'fecha_primera_ronda_tecnica',
        'fecha_segunda_ronda_tecnica',
        'fecha_tercera_ronda_tecnica',
        'fecha_cuarta_ronda_tecnica',
        'fecha_quinta_ronda_tecnica',
    ];

    protected $fillable = [
        'analisis_tecnica_valores',
        'adjudicacion',
        'id_offerer',
        'id_concurso',
        'fecha_carga_economica',
        'id_evaluacion',
        'acepta_adjudicacion',
        'acepta_adjudicacion_fecha',
        'etapa_actual',
        'rechazado',
        'id_conductor',
        'id_vehiculo',
        'id_trailer',
        'reasonDeclination',
        'fecha_declination',
        'ronda_tecnica',
        'fecha_primera_ronda_tecnica',
        'fecha_segunda_ronda_tecnica',
        'fecha_tercera_ronda_tecnica',
        'fecha_cuarta_ronda_tecnica',
        'fecha_quinta_ronda_tecnica',
    ];

    protected $appends = [
        'tipo_adjudicacion',
        'status_invitacion_text',
        'parsed_go_documents',
        'adjudicacion_status',
        'file_path',
        'technical_proposal',
        'technical_proposals',
        'economic_proposal',
        'economic_proposals',
        'parsed_technical_proposal',
        'parsed_economic_proposal',
        // IS se refiere a si actualmente está en dicha etapa
        'is_invitacion_pendiente',
        'is_invitacion_rechazada',
        'is_tecnica_pendiente',
        'is_tecnica_presentada',
        'is_tecnica_declinada',
        'is_tecnica_rechazada',
        'is_economica_pendiente',
        'is_economica_pendiente_segunda_ronda',
        'is_economica_presentada',
        'is_economica_revisada',
        'is_adjudicacion_pendiente',
        'is_adjudicacion_aceptada',
        'is_adjudicacion_rechazada',
        'is_concurso_rechazado',
        // HAS se refiere a si en su haber tiene determinada etapa concluida
        'has_invitacion_aceptada',
        'has_invitacion_vencida',
        'has_economica_presentada',
        'has_economica_revisada',
        'has_economica_vencida',
        'has_tecnica_presentada',
        'has_tecnica_rechazada',
        'has_tecnica_aprobada',
        'has_tecnica_vencida',
        'show_technical',
        'show_economic',
        'enable_technical',
        'enable_economic',
        'is_chat_enabled',
        'send_edit_notification' //determinar si se le envia una notificacion al proveedor cuando el concurso es editado por el comprador, esta condicion debe cumplir con: invitacion no rechazada, tecnica no rechazada, economica presentada sin revisar
    ];

    const ETAPAS = [
        'seleccionado' => 'seleccionado',
        'invitacion-pendiente' => 'invitacion-pendiente',
        'invitacion-rechazada' => 'invitacion-rechazada',
        'tecnica-pendiente' => 'tecnica-pendiente',
        'tecnica-pendiente-2' => 'tecnica-pendiente-2',
        'tecnica-pendiente-3' => 'tecnica-pendiente-3',
        'tecnica-pendiente-4' => 'tecnica-pendiente-4',
        'tecnica-pendiente-5' => 'tecnica-pendiente-5',
        'tecnica-presentada' => 'tecnica-presentada',
        'tecnica-presentada-2' => 'tecnica-presentada-2',
        'tecnica-presentada-3' => 'tecnica-presentada-3',
        'tecnica-presentada-4' => 'tecnica-presentada-4',
        'tecnica-presentada-5' => 'tecnica-presentada-5',
        'tecnica-declinada' => 'tecnica-declinada',
        'tecnica-declinada-2' => 'tecnica-declinada-2',
        'tecnica-declinada-3' => 'tecnica-declinada-3',
        'tecnica-declinada-4' => 'tecnica-declinada-4',
        'tecnica-declinada-5' => 'tecnica-declinada-5',
        'economica-pendiente' => 'economica-pendiente',
        'economica-pendiente-2' => 'economica-pendiente-2',
        'economica-pendiente-3' => 'economica-pendiente-3',
        'economica-pendiente-4' => 'economica-pendiente-4',
        'economica-pendiente-5' => 'economica-pendiente-5',
        'economica-2da-pendiente' => 'economica-2da-pendiente',
        'economica-presentada' => 'economica-presentada',
        'economica-revisada' => 'economica-revisada',
        'economica-declinada' => 'economica-declinada',
        'adjudicacion-pendiente' => 'adjudicacion-pendiente',
        'adjudicacion-aceptada' => 'adjudicacion-aceptada',
        'adjudicacion-rechazada' => 'adjudicacion-rechazada',
        'estrategia-aceptada' => 'estrategia-aceptada',
        'estrategia-rechazada' => 'estrategia-rechazada',
    ];

    const ETAPA_INVITACION = [
        'seleccionado' => 'seleccionado',
        'invitacion-pendiente' => 'invitacion-pendiente',
        'invitacion-rechazada' => 'invitacion-rechazada'
    ];

    const ETAPA_TECNICA_PENDIENTE = [
        'tecnica-pendiente' => 'tecnica-pendiente',
        'tecnica-pendiente-2' => 'tecnica-pendiente-2',
        'tecnica-pendiente-3' => 'tecnica-pendiente-3',
        'tecnica-pendiente-4' => 'tecnica-pendiente-4',
        'tecnica-pendiente-5' => 'tecnica-pendiente-5'
    ];

    const ETAPA_TECNICA_PRESENTADA = [
        'tecnica-presentada' => 'tecnica-presentada',
        'tecnica-presentada-2' => 'tecnica-presentada-2',
        'tecnica-presentada-3' => 'tecnica-presentada-3',
        'tecnica-presentada-4' => 'tecnica-presentada-4',
        'tecnica-presentada-5' => 'tecnica-presentada-5'
    ];
    const ETAPA_TECNICA_DECLINADA = [
        'tecnica-declinada' => 'tecnica-declinada',
        'tecnica-declinada-2' => 'tecnica-declinada-2',
        'tecnica-declinada-3' => 'tecnica-declinada-3',
        'tecnica-declinada-4' => 'tecnica-declinada-4',
        'tecnica-declinada-5' => 'tecnica-declinada-5',
    ];
    const ETAPA_TECNICA_RECHAZADA = [
        'tecnica-rechazada' => 'tecnica-rechazada',
        'tecnica-rechazada-2' => 'tecnica-rechazada-2',
        'tecnica-rechazada-3' => 'tecnica-rechazada-3',
        'tecnica-rechazada-4' => 'tecnica-rechazada-4',
        'tecnica-rechazada-5' => 'tecnica-rechazada-5'
    ];

    const ETAPAS_ECONOMICAS = [
        'economica-pendiente' => 'economica-pendiente',
        'economica-pendiente-2' => 'economica-pendiente-2',
        'economica-pendiente-3' => 'economica-pendiente-3',
        'economica-pendiente-4' => 'economica-pendiente-4',
        'economica-pendiente-5' => 'economica-pendiente-5',
        'economica-presentada' => 'economica-presentada',
        'economica-revisada' => 'economica-revisada',
        'economica-declinada' => 'economica-declinada'
    ];

    const ETAPAS_RECHAZADAS = [
        'invitacion-rechazada' => 'invitacion-rechazada',
        'tecnica-declinada' => 'tecnica-declinada',
        'tecnica-declinada-2' => 'tecnica-declinada-2',
        'tecnica-declinada-3' => 'tecnica-declinada-3',
        'tecnica-declinada-4' => 'tecnica-declinada-4',
        'tecnica-declinada-5' => 'tecnica-declinada-5',
        'tecnica-rechazada' => 'tecnica-rechazada',
        'tecnica-rechazada-2' => 'tecnica-rechazada-2',
        'tecnica-rechazada-3' => 'tecnica-rechazada-3',
        'tecnica-rechazada-4' => 'tecnica-rechazada-4',
        'tecnica-rechazada-5' => 'tecnica-rechazada-5',
        'economica-declinada' => 'economica-declinada'
    ];

    const ETAPA_ADJUDICACION = [
        'adjudicacion-pendiente' => 'adjudicacion-pendiente',
        'adjudicacion-aceptada' => 'adjudicacion-aceptada',
        'adjudicacion-rechazada' => 'adjudicacion-rechazada'
    ];

    const ETAPA_LIBERACION = [
        'estrategia-aceptada' => 'estrategia-aceptada',
        'estrategia-rechazada' => 'estrategia-rechazada',
    ];

    const RONDAS = [
        1 => '1ª ronda',
        2 => '2ª ronda',
        3 => '3ª ronda',
        4 => '4ª ronda',
        5 => '5ª ronda',
    ];



    const ETAPAS_NOMBRES = [
        'invitacion-pendiente' => 'Invitación Pendiente',
        'invitacion-rechazada' => 'Invitación Rechazada',
        'tecnica-pendiente' => 'Técnica Pendiente',
        'tecnica-presentada' => 'Técnica Presentada',
        'tecnica-declinada' => 'Técnica Declinada',
        'tecnica-rechazada' => 'Técnica Rechazada',
        'economica-pendiente' => 'Económica Pendiente',
        'economica-pendiente-2' => 'Económica Pendiente 2ª Ronda',
        'economica-pendiente-3' => 'Económica Pendiente 3ª Ronda',
        'economica-pendiente-4' => 'Económica Pendiente 4ª Ronda',
        'economica-pendiente-5' => 'Económica Pendiente 5ª Ronda',
        'economica-presentada' => 'Económica Presentada',
        'economica-revisada' => 'Económica Revisada',
        'economica-declinada' => 'Económica Declinada',
        'adjudicacion-pendiente' => 'Adjudicación Pendiente',
        'adjudicacion-aceptada' => 'Adjudicación Aceptada',
        'adjudicacion-rechazada' => 'Adjudicación Rechazada',
        'estrategia-aceptada' => 'estrategia-aceptada',
        'estrategia-rechazada' => 'estrategia-rechazada',
    ];

    const ADJUDICACION_NOMBRES = [
        'adjudicacion-pendiente' => 'Pendiente',
        'adjudicacion-aceptada' => 'Aceptada',
        'adjudicacion-rechazada' => 'Rechazada'
    ];

    const LIBERACION_NOMBRES = [
        'estrategia-aceptada' => 'estrategia-aceptada',
        'estrategia-rechazada' => 'estrategia-rechazada',
    ];

    const PLAZOS_PAGO = [
        [
            'id' => 1,
            'text' => '1 día'
        ],
        [
            'id' => 7,
            'text' => '7 días'
        ],
        [
            'id' => 30,
            'text' => '30 días'
        ],
        [
            'id' => 45,
            'text' => '45 días'
        ],
        [
            'id' => 60,
            'text' => '60 días'
        ],
        [
            'id' => 90,
            'text' => '90 días'
        ],
        [
            'id' => 120,
            'text' => '120 días'
        ]
    ];

    const CONDICIONES_PAGO = [
        [
            'id' => 1,
            'text' => 'Sin Anticipo'
        ],
        [
            'id' => 2,
            'text' => 'Anticipo 10%'
        ],
        [
            'id' => 3,
            'text' => 'Anticipo 20%'
        ],
        [
            'id' => 4,
            'text' => 'Anticipo 30%'
        ],
        [
            'id' => 5,
            'text' => 'Anticipo 40%'
        ],
        [
            'id' => 6,
            'text' => 'Anticipo 50%'
        ],
        [
            'id' => 7,
            'text' => 'Anticipo 60%'
        ],
        [
            'id' => 8,
            'text' => 'Anticipo 70%'
        ],
        [
            'id' => 9,
            'text' => 'Anticipo 80%'
        ],
        [
            'id' => 10,
            'text' => 'Anticipo 90%'
        ],
        [
            'id' => 11,
            'text' => 'Anticipo 100%'
        ]
    ];

    const ETAPAS_TECNICA_PENDIENTE = [
        'tecnica-pendiente' => 'tecnica-pendiente',
        'tecnica-pendiente-2' => 'tecnica-pendiente-2',
        'tecnica-pendiente-3' => 'tecnica-pendiente-3',
        'tecnica-pendiente-4' => 'tecnica-pendiente-4',
        'tecnica-presentada-5' => 'tecnica-presentada-5'
    ];



    public function concurso()
    {
        return $this->belongsTo(Concurso::class, 'id_concurso', 'id');
    }

    public function company()
    {
        return $this->belongsTo(OffererCompany::class, 'id_offerer', 'id');
    }

    public function evaluacion()
    {
        return $this->hasOne(Evaluacion::class, 'id', 'id_evaluacion');
    }

    public function proposals()
    {
        return $this->hasMany(Proposal::class, 'participante_id', 'id')->orderBy('id', 'ASC');
    }

    public function invitation()
    {
        return $this->hasOne(Invitation::class, 'participante_id', 'id');
    }

    public function getTechnicalProposalAttribute()
    {
        $techRound = $this->ronda_tecnica;
        return $this->proposals->where('is_technical', true)->where('ronda_tecnica', $techRound)->load('documents', 'documents.type')->last();
    }

    public function getTechnicalProposalsAttribute()
    {
        return $this->proposals->where('is_technical', true)->load('documents', 'documents.type');
    }

    public function getEconomicProposalAttribute()
    {
        return $this->proposals->where('is_economic', true)->load('documents', 'documents.type')->first();
    }

    public function getEconomicProposalsAttribute()
    {
        return $this->proposals->where('is_economic', true)->load('documents', 'documents.type')->all();
    }

    public function getStatusInvitacionTextAttribute()
    {
        return $this->has_invitacion_aceptada ? 'Aceptada' : (
                $this->is_invitacion_pendiente ? 'Pendiente' : 'Rechazada'
            );
    }

    public function go_documents()
    {
        return $this->hasMany(ParticipanteGoDocument::class, 'participante_id', 'id');
    }

    public function getAnalisisTecnicaValoresAttribute()
    {
        return $this->attributes['analisis_tecnica_valores'] ?
            json_decode($this->attributes['analisis_tecnica_valores'], true) :
            null;
    }

    public function getTipoAdjudicacionAttribute()
    {
        $tipo_adjudicacion = '';
        switch ((int) $this->attributes['adjudicacion']) {
            case 1:
                $tipo_adjudicacion = 'integral';
                break;
            case 2:
                $tipo_adjudicacion = 'individual';
                break;
            case 3:
                $tipo_adjudicacion = 'manual';
                break;
        }

        return $tipo_adjudicacion;
    }

    public function getIsSeleccionadoAttribute()
    {
        return $this->attributes['etapa_actual'] == $this::ETAPAS['seleccionado'];
    }

    public function getIsInvitacionPendienteAttribute()
    {
        return $this->attributes['etapa_actual'] == $this::ETAPAS['invitacion-pendiente'];
    }

    public function getIsInvitacionRechazadaAttribute()
    {
        return $this->attributes['etapa_actual'] == $this::ETAPAS['invitacion-rechazada'];
    }

    public function getIsTecnicaPendienteAttribute()
    {
        return in_array($this->attributes['etapa_actual'], $this::ETAPA_TECNICA_PENDIENTE);
    }

    public function getIsTecnicaPresentadaAttribute()
    {
        return in_array($this->attributes['etapa_actual'], $this::ETAPA_TECNICA_PRESENTADA);
    }

    public function getIsEconomicaPendienteAttribute()
    {

        return
            $this->attributes['etapa_actual'] == $this::ETAPAS['economica-pendiente']
            ||
            $this->attributes['etapa_actual'] == $this::ETAPAS['economica-pendiente-2']
            ||
            $this->attributes['etapa_actual'] == $this::ETAPAS['economica-pendiente-3']
            ||
            $this->attributes['etapa_actual'] == $this::ETAPAS['economica-pendiente-4']
            ||
            $this->attributes['etapa_actual'] == $this::ETAPAS['economica-pendiente-5'];
    }

    public function getIsEconomicaPendienteSegundaRondaAttribute()
    {
        return $this->attributes['etapa_actual'] == $this::ETAPAS['economica-2da-pendiente'];
    }

    public function getIsEconomicaPresentadaAttribute()
    {
        return $this->attributes['etapa_actual'] == $this::ETAPAS['economica-presentada'];
    }

    public function getIsEconomicaRevisadaAttribute()
    {
        return $this->attributes['etapa_actual'] == $this::ETAPAS['economica-revisada'];
    }

    public function getIsAdjudicacionPendienteAttribute()
    {
        return $this->attributes['etapa_actual'] == $this::ETAPAS['adjudicacion-pendiente'];
    }

    public function getIsAdjudicacionAceptadaAttribute()
    {
        return $this->attributes['etapa_actual'] == $this::ETAPAS['adjudicacion-aceptada'];
    }

    public function getIsAdjudicacionRechazadaAttribute()
    {
        return $this->attributes['etapa_actual'] == $this::ETAPAS['adjudicacion-rechazada'];
    }

    public function getHasInvitacionAceptadaAttribute()
    {
        // return $this->invitation ? $this->invitation->is_accepted : false;
        return $this->invitation && $this->invitation->is_accepted && !$this->is_concurso_rechazado;
    }

    public function getHasInvitacionVencidaAttribute()
    {

        $invitation = $this->invitation;
        date_default_timezone_set($this->concurso->cliente->customer_company->timeZone);
        $plazo_vencido = Carbon::now() > $this->concurso->fecha_limite;

        return (isset($invitation->is_expired) ? $invitation->is_expired : false) ||
            ((isset($invitation->is_pending) ? $invitation->is_pending : false) && $plazo_vencido);
    }

    public function getHasTecnicaPresentadaAttribute()
    {
        $concurso = $this->concurso;
        return (bool)
        $this->concurso->technical_includes &&
            (
                $concurso->is_go ?
                $this->technical_proposal && $this->has_economica_presentada :
                $this->technical_proposal
            ) &&
            !$this->is_tecnica_pendiente;
    }

    public function getHasTecnicaAprobadaAttribute()
    {
        $technical_proposal = $this->technical_proposal;

        return (bool) $this->is_go ? true : ($technical_proposal ? $technical_proposal->is_accepted : false);
    }

    public function getHasTecnicaRechazadaAttribute()
    {
        $technical_proposal = $this->technical_proposal;
        return (bool) $this->is_go ? false : ($technical_proposal ? $technical_proposal->is_rejected : false);
    }

    public function getHasTecnicaVencidaAttribute()
    {
        $technical_proposal = $this->technical_proposal;
        $plazo_vencido = Carbon::now() > $this->concurso->ficha_tecnica_fecha_limite;
        return
            (bool) $this->is_go || !$this->concurso->technical_includes ?
            false : (
                $technical_proposal ?
                $technical_proposal->is_expired || ($technical_proposal->is_pending && $plazo_vencido) : ($plazo_vencido ? true : false)
            );
    }

    public function getHasEconomicaPresentadaAttribute()
    {
        $economic_proposal = $this->economic_proposal;
        return (bool)
        $economic_proposal &&
            !$this->is_economica_pendiente &&
            $economic_proposal->values;
    }
    public function getHasEconomicaRevisadaAttribute()
    {
        $economic_proposal = $this->economic_proposal;

        return (bool)
        $economic_proposal &&
            !$this->is_economica_pendiente && !$this->is_economica_presentada &&
            $economic_proposal->values;
    }

    public function getHasEconomicaRechazadaAttribute()
    {
        $economic_proposal = $this->economic_proposal;
        return $economic_proposal ? $economic_proposal->is_rejected : false;
    }

    public function getHasEconomicaVencidaAttribute()
    {
        $economic_proposal = $this->economic_proposal;
        $nuevasRondas = $this->concurso->segunda_ronda_habilita;
        $plazo_vencido = Carbon::now() > $this->concurso->fecha_limite_economicas;
        if ($nuevasRondas == 'no') {
            return
                $economic_proposal ?
                $economic_proposal->is_expired || ($economic_proposal->is_pending && $plazo_vencido) : ($plazo_vencido ? true : false);
        }

        if ($nuevasRondas == 'si') {
            //$plazo_nueva = Carbon::now() > $this->concurso->segunda_ronda_fecha_limite; 
            $plazo_nueva = Carbon::now() > $this->concurso->fecha_limite_economicas; //Ahora para saber si esta expirado solo verifica que el campo en BD fecha_limite_economicas este bien
            return
                $economic_proposal ?
                $economic_proposal->is_expired || ($economic_proposal->is_pending && $plazo_nueva) : ($plazo_nueva ? true : false);
        }
    }

    public function getAdjudicacionStatusAttribute()
    {
        $result = null;

        switch ($this->attributes['etapa_actual']) {
            case $this::ETAPAS['adjudicacion-pendiente']:
            case $this::ETAPAS['adjudicacion-aceptada']:
            case $this::ETAPAS['adjudicacion-rechazada']:
                $result = $this::ADJUDICACION_NOMBRES[$this->attributes['etapa_actual']];
                break;
        }

        return $result;
    }

    public function getShowTechnicalAttribute()
    {
        return $this->has_invitacion_aceptada;
    }

    public function getShowEconomicAttribute()
    {
        $concurso = $this->concurso;
        return
            $this->has_invitacion_aceptada &&
            (
                !$concurso->technical_includes ||
                (
                    $concurso->technical_includes &&
                    (
                        $concurso->is_go ||
                        (
                            !$concurso->is_go &&
                            $this->has_tecnica_aprobada
                        )
                    )
                )
            );
    }

    public function getEnableTechnicalAttribute()
    {
        $concurso = $this->concurso;


        return (
                $concurso->technical_includes &&
                !$concurso->adjudicado &&
                !$concurso->trashed() &&
                !$this->rechazado &&
                !$this->isTecnicaByRoundByStep($this->ronda_tecnica, 'declinada') &&
                $this->show_technical
            )
            &&
            (
                $concurso->is_go ?
                (
                    !$this->has_economica_presentada
                ) : (
                    !$this->has_tecnica_vencida &&
                    !$this->has_tecnica_aprobada
                )
            );
    }

    public function getEnableEconomicAttribute()
    {
        $concurso = $this->concurso;

        return (
                !$concurso->adjudicado &&
                !$concurso->trashed() &&
                !$this->rechazado &&
                $this->show_economic
            ) &&
            (
                ($concurso->is_sobrecerrado || $concurso->is_go) &&
                (
                    !$this->has_economica_revisada &&
                    !$this->has_economica_vencida &&
                    !$this->has_economica_aprobada
                )
            ) ||
            (
                ($concurso->is_online) &&
                (
                    $concurso->countdown
                )
            );
    }

    public function getIsChatEnabledAttribute()
    {
        $chat_enabled = false;
        if ($this->has_invitacion_aceptada && !$this->rechazado) {
            $chat_enabled = $this->concurso->is_chat_enabled;
        }

        return $chat_enabled;
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'participante_id', 'id');
    }

    public function getFilePathAttribute()
    {
        return $this->company->file_path_offerer;
    }

    public function getParsedGoDocumentsAttribute()
    {
        $result = new \StdClass();
        $result->go_driver_documents = collect();
        $result->go_vehicle_documents = collect();
        $result->go_trailer_documents = collect();
        $result->go_nogcg_driver_documents = collect();
        $result->additional_driver_documents = collect();
        $result->additional_vehicle_documents = collect();
        $go = $this->concurso->go;

        if (!$go) {
            return null;
        }

        $filepath = filePath($this->file_path, true);


        // DOCUMENTOS GCG y NO-GCG
        foreach ($go->documents as $go_document) {
            $existent_document = $this->go_documents
                ->where('id_go_document', $go_document->id)
                ->first();

            $participante_document = [];
            // new \StdClass();
            // $participante_document->id              = $existent_document ? $existent_document->id : null;
            // $participante_document->document_id     = $go_document->id;
            // $participante_document->name            = $go_document->document->name;
            // $participante_document->cuit            = $go_document->cuit;
            // $participante_document->razon_social    = $go_document->razon_social;
            // $participante_document->filename        = $existent_document ? $existent_document->filename : null;
            // $participante_document->filepath        = $filepath;
            // $participante_document->action          = null;
            // $participante_document->types           = $go_document->document->types->pluck('description', 'code');

            if ($go_document->document->is_driver) {
                if ($go_document->document->is_gcg) {
                    $result->go_driver_documents->push(clone $participante_document);
                } else {
                    $result->go_nogcg_driver_documents->push(clone $participante_document);
                }
            } else {
                if ($go_document->document->is_vehicle) {
                    $result->go_vehicle_documents->push(clone $participante_document);
                }
                if ($go_document->document->is_trailer) {
                    $result->go_trailer_documents->push(clone $participante_document);
                }
            }
        }

        // DOCUMENTOS ADICIONALES
        foreach ($go->additional_documents as $additional_document) {
            $existent_document = $this->go_documents
                ->where('id_go_document_additional', $additional_document->id)
                ->first();

            $participante_document = new \StdClass();
            $participante_document->id = $existent_document ? $existent_document->id : null;
            $participante_document->document_id = $additional_document->id;
            $participante_document->name = $additional_document->name;
            $participante_document->filepath = $filepath;
            $participante_document->filename = $existent_document ? $existent_document->filename : null;
            $participante_document->action = null;

            switch ($additional_document->type) {
                case DocumentType::TYPE_SLUGS['driver']:
                    $result->additional_driver_documents->push(clone $participante_document);
                    break;
                case DocumentType::TYPE_SLUGS['vehicle']:
                    $result->additional_vehicle_documents->push(clone $participante_document);
                    break;
            }
        }

        return $result;
    }

    public function getParsedTechnicalProposalAttribute()
    {

        $proposal = $this->technical_proposal;
        $result = new \StdClass();
        $result->comment = $proposal ? $proposal->comment : null;
        $result->documents = collect();

        // DOCUMENTS
        foreach (Proposal::getTechnicalDocumentsTypes() as $type) {
            $existent_document =
                $proposal ?
                $proposal->documents
                ->where('type', $type)
                ->first() :
                null;

            $parsed_document = new \StdClass();
            $parsed_document->id = $existent_document ? $existent_document->id : null;
            $parsed_document->type_id = $type->id;
            $parsed_document->name = $type->description;
            $parsed_document->filename = $existent_document ? $existent_document->filename : null;
            $parsed_document->action = null;

            $result->documents->push(clone $parsed_document);
        }

        return $result;
    }

    public function getParsedEconomicProposalAttribute()
    {
        $concurso = $this->concurso;
        $ronda = $concurso->ronda_actual;

        // Seleccionar la última propuesta económica no eliminada (deleted_at IS NULL)
        // para este participante y ronda. Antes se tomaba la "first" (más antigua),
        // lo que provocaba que al haber varias filas la UI pudiera leer datos mezclados.
        $proposal = Proposal::where('participante_id', $this->id)
            ->where('type_id', 2)
            ->where('numero_ronda', $ronda)
            ->whereNull('deleted_at')
            ->orderBy('id', 'desc')
            ->first();

        $result = new \StdClass();
        $result->comment = $proposal ? $proposal->comment : null;
        $result->payment_deadline = $proposal ? $proposal->payment_deadline : null;
        $result->payment_condition = $proposal ? $proposal->payment_condition : null;
        $result->plazosPagos = $this::PLAZOS_PAGO;
        $result->condicionesPago = $this::CONDICIONES_PAGO;
        $result->values = collect();
        $result->documents = collect();

        // DOCUMENTS
        foreach (Proposal::getEconomicDocumentsTypes() as $type) {
            $existent_document =
                $proposal ?
                $proposal->documents
                ->where('type', $type)
                ->first() :
                null;

            $parsed_document = new \StdClass();
            $parsed_document->id = $existent_document ? $existent_document->id : null;
            $parsed_document->type_id = $type->id;
            $parsed_document->name = $type->description;
            $parsed_document->filename = $existent_document ? $existent_document->filename : null;
            $parsed_document->action = null;

            $result->documents->push(clone $parsed_document);
        }


        // VALUES
        foreach ($concurso->productos as $product) {
            $values =
                $proposal && $proposal->values ?
                array_values(
                    array_filter(
                        $proposal->values,
                        function ($item) use ($product) {
                            return $item['producto'] == $product->id;
                        }
                    )
                )[0] :
                [];

            $result->values = $result->values->push([
                'product_id' => $product->id,
                'product_name' => $product->nombre,
                'product_description' => $product->descripcion,
                'currency_id' => $concurso->tipo_moneda->id,
                'currency_name' => $concurso->tipo_moneda->nombre,
                'minimum_quantity' => $product->oferta_minima,
                'total_quantity' => $product->cantidad,
                'measurement_id' => $product->unidad_medida->id,
                'measurement_name' => $product->unidad_medida->name,
                // Form Fields
                'cotizacion' => $values ? $values['cotizacion'] : null,
                'cantidad' => $values ? $values['cantidad'] : null,
                'fecha' => $values ? $values['fecha'] : null,
                'creado' => $values ? (isset($values['creado']) ? $values['creado'] : null) : null,
                // Persistir estado del switch si existe
                'selected' => $values && array_key_exists('selected', $values) ? (bool) $values['selected'] : null,
            ]);
        }

        return $result;
    }

    public function getSendEditNotificationAttribute()
    {
        if ($this->etapa_actual)
            $invitacionEtapa = (!$this->is_seleccionado || !$this->is_invitacion_rechazada) && ($this->is_invitacion_pendiente || $this->has_invitacion_vencida);
        $tecnicaEtapa = !$this->has_tecnica_rechazada && ($this->has_tecnica_presentada || $this->has_tecnica_aprobada || $this->has_tecnica_vencida);
        $economicaEtapa = $this->has_economica_presentada || $this->has_economica_vencida || $this->is_economica_pendiente;

        return $invitacionEtapa || $tecnicaEtapa || $economicaEtapa;
    }

    public function getIsConcursoRechazadoAttribute()
    {
        $etapa_actual = $this->attributes['etapa_actual'];
        $rechazado = in_array($etapa_actual, $this::ETAPAS_RECHAZADAS);
        return $rechazado;
    }

    public function getIsTecnicaDeclinadaAttribute()
    {
        return in_array($this->attributes['etapa_actual'], $this::ETAPA_TECNICA_DECLINADA);
    }

    public function getIsTecnicaRechazadaAttribute()
    {
        return in_array($this->attributes['etapa_actual'], $this::ETAPA_TECNICA_RECHAZADA);
    }

    public function technicalByRound($techRound)
    {
        return $this->proposals->where('is_technical', true)->where('ronda_tecnica', $techRound)->load('documents', 'documents.type')->last();
    }

    public function parsedTechnicalByRound($techRound)
    {

        $proposal = $this->technicalByRound($techRound);
        $result = collect();
        // DOCUMENTS
        foreach (Proposal::getTechnicalDocumentsTypes() as $type) {
            $existent_document =
                $proposal ?
                $proposal->documents
                ->where('type', $type)
                ->first() :
                null;

            $parsed_document = new \StdClass();
            $parsed_document->id = $existent_document ? $existent_document->id : null;
            $parsed_document->type_id = $type->id;
            $parsed_document->name = $type->description;
            $parsed_document->filename = $existent_document ? $existent_document->filename : null;
            $parsed_document->action = null;
            $parsed_document->proposal = $existent_document ? $existent_document->proposal_id : null;

            $result->push(clone $parsed_document);
        }

        return $result;
    }

    public function isTecnicaByRoundByStep($techRound, $step)
    {
        $etapa = $techRound === 1 ? 'tecnica-' . $step : 'tecnica-' . $step . '-' . $techRound;

        return $this->attributes['etapa_actual'] == $etapa;
    }

    public function scopeFiltrarPorEtapa($query)
    {
        $etapas_excluidas = [
            'invitacion-pendiente',
            'invitacion-rechazada',
            'tecnica-declinada',
            'tecnica-rechazada',
            'economica-declinada',
        ];

        return $query->whereNotIn('etapa_actual', $etapas_excluidas);
    }
}
