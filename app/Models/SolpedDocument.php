<?php

namespace App\Models;

use App\Models\Model;
use App\Models\Solped;
use Carbon\Carbon;

class SolpedDocument extends Model
{   
    protected $table = 'solped_docs';

    public $timestamps = true;

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $fillable = [
        'id',
        'solped_id',
        'filename'
    ];

    public function solped()
    {
    	return $this->belongsTo(Solped::class, 'solped_id', 'id');
    }


} 