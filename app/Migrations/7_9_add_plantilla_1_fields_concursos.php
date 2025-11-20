<?php

namespace App\Migrations;

require __DIR__ . '/../OldServices/rest.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// Listado de equipos y herramientas
if (!Capsule::schema()->hasColumn('concursos', 'listado_equipos_herramientas'))
{
    Capsule::schema()->table('concursos', function ($table) {
        $table->enum('listado_equipos_herramientas', ['no', 'si'])
            ->nullable()
            ->default('no')
            ->comment = 'Plantilla 1 - Listado de equipos y herramientas';
    });        
}

// Equipo humano y competencias
if (!Capsule::schema()->hasColumn('concursos', 'equipo_humano_competencias'))
{
    Capsule::schema()->table('concursos', function ($table) {
        $table->enum('equipo_humano_competencias', ['no', 'si'])
            ->nullable()
            ->default('no')
            ->comment = 'Plantilla 1 - Equipo humano y competencias';
    });        
}

// Balances y estados de resultados
if (!Capsule::schema()->hasColumn('concursos', 'balances_estados_resultados'))
{
    Capsule::schema()->table('concursos', function ($table) {
        $table->enum('balances_estados_resultados', ['no', 'si'])
            ->nullable()
            ->default('no')
            ->comment = 'Plantilla 1 - Balances y estados de resultados';
    });        
}

// Estatuto o contrato social
if (!Capsule::schema()->hasColumn('concursos', 'estatuto_contrato_social'))
{
    Capsule::schema()->table('concursos', function ($table) {
        $table->enum('estatuto_contrato_social', ['no', 'si'])
            ->nullable()
            ->default('no')
            ->comment = 'Plantilla 1 - Estatuto o contrato social';
    });        
}

// Actas de designación de autoridades
if (!Capsule::schema()->hasColumn('concursos', 'actas_designacion_autoridades'))
{
    Capsule::schema()->table('concursos', function ($table) {
        $table->enum('actas_designacion_autoridades', ['no', 'si'])
            ->nullable()
            ->default('no')
            ->comment = 'Plantilla 1 - Actas de designación de autoridades';
    });        
}
