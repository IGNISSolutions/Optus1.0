<?php

namespace App\Migrations;

require '../OldServices/rest.php';

use Illuminate\Database\Capsule\Manager as Capsule;

Capsule::schema()->dropIfExists('estrategia_liberacion');
Capsule::schema()->create('estrategia_liberacion', function ($table)
{
    $table->increments('id');
    $table->unsignedInteger('customer_company_id');
    $table->decimal('monto_nivel_0', 15, 2)->default(0);
    $table->decimal('monto_nivel_1', 15, 2)->default(0);
    $table->boolean('jefe_compras')->default(false);
    $table->boolean('jefe_solicitante')->default(false);
    $table->decimal('monto_nivel_2', 15, 2)->default(0);
    $table->boolean('gerente_compras')->default(false);
    $table->boolean('gerente_solicitante')->default(false);
    $table->decimal('monto_nivel_3', 15, 2)->default(0);
    $table->boolean('gerente_general')->default(false);
    $table->boolean('habilitado')->default(false);
    $table->timestamps();
    
    // Foreign key
    $table->foreign('customer_company_id')->references('id')->on('customer_companies');
    
    // Unique constraint - una estrategia por empresa
    $table->unique('customer_company_id');
});
