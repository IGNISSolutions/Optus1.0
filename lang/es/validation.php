<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'El campo :attribute must be accepted.',
    'active_url' => 'El campo :attribute is not a valid URL.',
    'after' => 'El campo :attribute must be a date after :date.',
    'after_or_equal' => 'El campo :attribute debe ser igual o mayor a :date.',
    'alpha' => 'El campo :attribute may only contain letters.',
    'alpha_dash' => 'El campo :attribute may only contain letters, numbers, dashes and underscores.',
    'alpha_num' => 'El campo :attribute may only contain letters and numbers.',
    'array' => 'El campo :attribute must be an array.',
    'before' => 'El campo :attribute must be a date before :date.',
    //'before_or_equal' => 'El campo :attribute must be a date before or equal to :date.',
    'before_or_equal' => 'El campo :attribute debe ser igual o menor a :date.',    
    'between' => [
        'numeric' => 'El valor :attribute debe estar entre :min y :max.',
        'file' => 'El valor :attribute debe estar entre :min y :max kilobytes.',
        'string' => 'El valor :attribute debe estar entre :min y :max caracteres.',
        'array' => 'El campo :attribute must have between :min and :max items.',
    ],
    'boolean' => 'El campo :attribute field must be true or false.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'date' => 'El campo :attribute no es una fecha válida.',
    'date_equals' => 'El campo :attribute must be a date equal to :date.',
    'date_format' => 'El campo :attribute debe tener el formato :format.',
    'different' => 'El campo :attribute and :other must be different.',
    'digits' => 'El campo :attribute must be :digits digits.',
    'digits_between' => 'El valor :attribute debe estar entre :min y :max dígitos.',
    'dimensions' => 'El campo :attribute has invalid image dimensions.',
    'distinct' => 'El campo :attribute tiene un valor duplicado.',
    'email' => ':attribute debe contener un E-mail válido.',
    'exists' => 'El valor seleccionado para :attribute es inválido.',
    'file' => 'El campo :attribute must be a file.',
    'filled' => 'El campo :attribute field must have a value.',
    'gt' => [
        'numeric' => 'El campo :attribute debe ser mayor a :value.',
        'file' => 'El campo :attribute must be greater than :value kilobytes.',
        'string' => 'El campo :attribute must be greater than :value characters.',
        'array' => 'El campo :attribute must have more than :value items.',
    ],
    'gte' => [
        'numeric' => 'El campo :attribute debe ser mayor o igual a :value.',
        'file' => 'El campo :attribute must be greater than or equal :value kilobytes.',
        'string' => 'El campo :attribute must be greater than or equal :value characters.',
        'array' => 'El campo :attribute must have :value items or more.',
    ],
    'image' => 'El campo :attribute must be an image.',
    'in' => 'El :attribute seleccionado es inválido.',
    'in_array' => 'El campo :attribute field does not exist in :other.',
    'integer' => 'El campo :attribute debe ser entero.',
    'ip' => 'El campo :attribute must be a valid IP address.',
    'ipv4' => 'El campo :attribute must be a valid IPv4 address.',
    'ipv6' => 'El campo :attribute must be a valid IPv6 address.',
    'json' => 'El campo :attribute must be a valid JSON string.',
    'lt' => [
        'numeric' => 'El campo :attribute must be less than :value.',
        'file' => 'El campo :attribute must be less than :value kilobytes.',
        'string' => 'El campo :attribute must be less than :value characters.',
        'array' => 'El campo :attribute must have less than :value items.',
    ],
    'lte' => [
        'numeric' => 'El campo :attribute debe ser menor o igual a :value.',
        'file' => 'El campo :attribute must be less than or equal :value kilobytes.',
        'string' => 'El campo :attribute must be less than or equal :value characters.',
        'array' => 'El campo :attribute must not have more than :value items.',
    ],
    'max' => [
        'numeric' => 'El campo :attribute may not be greater than :max.',
        'file' => 'El campo :attribute may not be greater than :max kilobytes.',
        'string' => 'El campo :attribute no debe tener más de :max caracteres.',
        'array' => 'El campo :attribute may not have more than :max items.',
    ],
    'mimes' => 'El campo :attribute must be a file of type: :values.',
    'mimetypes' => 'El campo :attribute must be a file of type: :values.',
    'min' => [
        'numeric' => 'El valor :attribute debe ser al menos :min.',
        'file' => 'El valor :attribute debe ser al menos :min kilobytes.',
        'string' => 'El campo :attribute no debe tener menos de :min caracteres.',
        'array' => 'El campo :attribute must have at least :min items.',
    ],
    'not_in' => 'El valor :attribute seleccionado es inválido.',
    'not_regex' => 'El campo :attribute format is invalid.',
    'numeric' => 'El campo :attribute debe ser numérico.',
    'present' => 'El campo :attribute field must be present.',
    'regex' => 'El campo :attribute format is invalid.',
    'required' => 'El campo :attribute es obligatorio.',
    'required_if' => 'El campo :attribute field is required when :other is :value.',
    'required_unless' => 'El campo :attribute field is required unless :other is in :values.',
    'required_with' => 'El campo :attribute es obligatorio cuando :values está presente.',
    'required_with_all' => 'El campo :attribute field is required when :values are present.',
    'required_without' => 'El campo :attribute field is required when :values is not present.',
    'required_without_all' => 'El campo :attribute field is required when none of :values are present.',
    'same' => 'Campos :attribute y :other deben coincidir.',
    'size' => [
        'numeric' => 'El campo :attribute must be :size.',
        'file' => 'El campo :attribute must be :size kilobytes.',
        'string' => 'El campo :attribute must be :size characters.',
        'array' => 'El campo :attribute must contain :size items.',
    ],
    'starts_with' => 'El campo :attribute must start with one of the following: :values',
    'string' => 'El campo :attribute debe ser de texto.',
    'timezone' => 'El campo :attribute must be a valid zone.',
    'unique' => 'El campo :attribute ya existe con ese valor.',
    'uploaded' => 'El campo :attribute failed to upload.',
    'url' => 'El campo :attribute format is invalid.',
    'uuid' => 'El campo :attribute must be a valid UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'products' => [
            'required' => 'Debe crear al menos un Item para el Concurso.',
        ],
        'codigo_ERP' => [
            'unique' => 'ya existe con ese valor en los campos Código ERP y Descripción.',
        ]        
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        // COMMON
        'username'      => 'Usuario',
        'password'      => 'Contraseña',
        'business_name' => 'Razón Social',
        'cuit'          => 'CUIT',
        'country'       => 'País',
        'province'      => 'Provincia',
        'city'          => 'Ciudad',
        'address'       => 'Dirección',
        'latitude'      => 'Latitud',
        'longitude'     => 'Longitud',
        'postal_code'   => 'Código Postal',
        'first_name'    => 'Nombre',
        'last_name'     => 'Apellido',
        'phone'         => 'Teléfono',
        'cellphone'     => 'Celular',
        'email'         => 'Email',
        'website'       => 'Sitio Web',
        'comments'      => 'Observaciones',
        'status_id'     => 'Estado',
        'name'          => 'Nombre',
        'nombre' => 'Nombre',
        'tipo_operacion' => 'Tipo de Operación',
        'imagen' => 'Imagen',
        'resena'    => 'Reseña',
        'puntaje_minimo' => 'Puntuación',
        'payroll.*.atributo'    => 'Atributo',
        'payroll.*.ponderacion'  => 'Ponderación',
        'payroll.*.puntaje'  => 'Puntaje',
        'payroll.*.id_plantilla'    => 'Plantilla',
        'products.*.nombre' => 'Nombre',
        'products.*.unidad' => 'Unidad de Medida',
        'products.*.cantidad' => 'Cantidad',
        'products.*.oferta_minima' => 'Oferta Mínima',
        'descripcion' => 'Descripción',
        'pais' => 'País',
        'provincia' => 'Provincia',
        'localidad' => 'Localidad',
        'direccion' => 'Dirección',
        'cp' => 'Código Postal (CP)',
        'latitud' => 'Latitud',
        'longitud' => 'Longitud',
        'tipo_convocatoria' => 'Tipo de Convocatoria',
        'finalizacion_consultas' => 'Finalización Consultas',
        'aceptacion_terminos' => 'Aceptación Términos',
        'fecha_limite' => 'Fecha Límite',
        'seguro_caucion' => 'Seguro Caución',
        'diagrama_gant' => 'Diagrama de Gant',
        'usuario_califica_reputacion' => 'Usuario Califica Reputación',
        'moneda' => 'Moneda',
        'ficha_tecnica_incluye' => 'Incluye Ficha Técnica',
        'ficha_tecnica_fecha_limite' => 'Fecha Límite Ficha Técnica',
        'ficha_tecnica_usuario_evalua' => 'Usuario Evalúa Ficha Técnica',
        'ficha_tecnica_plantilla' => 'Plantilla Ficha Técnica',
        // ONLINE
        'inicio_subasta' => 'Inicio Subasta',
        'duracion' => 'Duración',
        'tiempo_adicional' => 'Tiempo Adicional',
        'plantilla_economicas' => 'Plantilla Económicas',
        'tipo_valor_ofertar' => 'Tipo de Valor a Ofertar',
        'precio_maximo' => 'Precio Máximo',
        'precio_minimo' => 'Precio Mínimo',
        'unidad_minima' => 'Unidad Mínima',
        // SOBRE CERRADO
        'fecha_limite_economicas' => 'Fecha Límite Económicas',
        'segunda_ronda_fecha_limite' => 'Fecha Límite Segunda Ronda',
        // GO
        'type_id'               => 'Tipo Optus GO',
        'load_type_id'          => 'Tipo de Carga',
        'peso'                  => 'Peso',
        'ancho'                 => 'Ancho',
        'largo'                 => 'Largo',
        'alto'                  => 'Alto',
        'unidades_bultos'       => 'Cantidad de Unidades/Bultos',
        'payment_method_id'     => 'Medio de Pago',
        'plazo_pago'            => 'Plazo de Paga',
        'fecha_desde'           => 'Fecha Desde',
        'fecha_hasta'           => 'Fecha Hasta',
        'calle_desde'           => 'Calle Desde',
        'calle_hasta'           => 'Calle Hasta',
        'numeracion_desde'      => 'Numeración Desde',
        'numeracion_hasta'      => 'Numeración Hasta',
        'ciudad_desde_id'       => 'Ciudad Desde',
        'ciudad_hasta_id'       => 'Ciudad Hasta',
        'provincia_desde_id'    => 'Provincia Desde',
        'provincia_hasta_id'    => 'Provincia Hasta',
        // EMPRESAS
        'supplier_code'     => 'Código de Proveedor',
        'rate_system_id'    => 'Sistema Tarifario',
        // PROPUESTAS
        'comment'               => 'Comentario',
        'values.*.cotizacion'   => 'Cotización',
        'values.*.cantidad'     => 'Cantidad Cotizada',
        'values.*.fecha'        => 'Plazo de Entrega',
        // EVALUACIÓN
        'comentario'                => 'Comentario',
        'valores.Puntualidad'       => 'Puntualidad',
        'valores.Calidad'           => 'Calidad',
        'valores.OrdenYlimpieza'    => 'Orden y Limpieza',
        'valores.MedioAmbiente'     => 'Medio Ambiente',
        'valores.HigieneYseguridad' => 'Higiene y Seguridad',
        'valores.Experiencia'       => 'Experiencia'
    ],
];
