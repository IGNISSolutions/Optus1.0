<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{$title}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #337ab7; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background-color: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-top: none; }
        .info-box { background-color: #fff; border: 1px solid #e0e0e0; border-radius: 4px; padding: 15px; margin: 15px 0; }
        .btn { display: inline-block; padding: 12px 30px; background-color: #337ab7; color: white; text-decoration: none; border-radius: 4px; margin-top: 15px; }
        .warning { background-color: #fcf8e3; border: 1px solid #faebcc; color: #8a6d3b; padding: 15px; border-radius: 4px; margin: 15px 0; }
        .footer { text-align: center; padding: 20px; color: #777; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{$title}</h1>
    </div>
    <div class="content">
        <p>Querído <strong>{$user->first_name} {$user->last_name}</strong>,</p>
        <p>Un pedido de adjudicacion requiere su aprobación</p>
        <div class="info-box">
            <p><strong>Concurso:</strong> #{$concurso->id} - {$concurso->nombre}</p>
            <p><strong>Tipo:</strong> {$approval->adjudication_type|ucfirst}</p>
            <p><strong>Cantidad:</strong> ${$approval->amount|number_format:2:',':'.'}</p>
            <p><strong>Cantidad USD:</strong> ${$approval->amount_usd|number_format:2:',':'.'}</p>
            <p><strong>Su rol:</strong> {$approval->role}</p>
        </div>
        <div class="warning">
            <strong>Acción requerída:</strong> Por favor apruebe o niegue el pedido de adjudicación.
        </div>
        <p style="text-align: center;">
            <a href="{$app_url}/concursos/cliente/por-etapa/adjudicacion/{$concurso->id}" class="btn">Ver Concurso</a>
        </p>
    </div>
    <div class="footer">
        <p>&copy; {$ano}</p>
    </div>
</body>
</html>
