{extends file="email/base.tpl"}

{block name="content"}
<div style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <p>Estimado <strong>{$user->full_name}</strong>,</p>
    
    <p>Le comunicamos que su solicitud de compra <strong>#{$solped->id}</strong> ha comenzado el proceso de licitación.</p>
    
    <div style="background-color: #f5f5f5; border-left: 4px solid #007bff; padding: 15px; margin: 20px 0;">
        <p><strong>Detalles de la Solicitud:</strong></p>
        <ul style="margin: 10px 0; padding-left: 20px;">
            <li><strong>ID Solicitud:</strong> {$solped->id}</li>
            <li><strong>Código Interno:</strong> {$solped->codigo_interno}</li>
            <li><strong>Nombre:</strong> {$solped->nombre}</li>
            <li><strong>Área Solicitante:</strong> {$solped->area_sol}</li>
            <li><strong>Fecha de Inicio:</strong> {$fecha_creacion}</li>
        </ul>
    </div>
        
    <p>Podrá hacer seguimiento del proceso a través de nuestra plataforma. Si tiene alguna consulta, no dude en ponerse en contacto con nosotros.</p>
    
    <p>Saludos cordiales,<br>
    <strong>Equipo de Compras</strong></p>
</div>
{/block}
