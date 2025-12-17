{extends 'email/base.tpl'}

{block 'content'}
    <p>Estimado <strong>{$compradorNombre}</strong>,</p>
    
    <p>Le informamos que ha recibido una nueva <strong>Solicitud de Pedido</strong> para su revisión y análisis.</p>
    
    <div style="background-color: #f5f5f5; padding: 15px; margin: 20px 0; border-left: 4px solid #2B3643;">
        <h3 style="margin-top: 0; color: #2B3643;">Detalles de la Solicitud</h3>
        
        <p>
            <strong>Nombre de la Solicitud:</strong><br>
            {$nombreSolicitud}
        </p>
        
        <p>
            <strong>Área Solicitante:</strong><br>
            {$areaSolicitante}
        </p>
        
        <p>
            <strong>Fecha de Resolución Esperada:</strong><br>
            {$fechaResolucion}
        </p>
    </div>
        
    <p>Para acceder a los detalles completos de la solicitud, haga clic en el siguiente enlace:</p>
    
    <div style="text-align: center; margin: 20px 0;">
        <a href="{$enlaceAcceso}" style="background-color: #2B3643; color: #ffffff; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;">
            Ver Solicitud
        </a>
    </div>
    
    <p>Si tiene alguna pregunta o necesita mayor información, no dude en comunicarse con el solicitante.</p>
    
    <p>Saludos cordiales,<br>
    <strong>Sistema OPTUS</strong></p>
{/block}
