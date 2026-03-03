<?php

namespace App\Migrations;

require '../OldServices/rest.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// Agregar un nuevo sheet type para archivos solo de adjudicado
$sheetAdjidicadoExists = Capsule::table('sheet_types')->where('code', 'adjudicado')->exists();

if (!$sheetAdjidicadoExists) {
    Capsule::table('sheet_types')->insert([
        'code' => 'adjudicado',
        'description' => 'Archivos Solo Adjudicado'
    ]);
}
