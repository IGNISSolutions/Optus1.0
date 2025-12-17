{extends file="email/base.tpl"}

{block name="content"}
<div style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <p>Estimado/a <strong>{$user->full_name}</strong>,</p>

    <p>
        Le informamos que la adjudicación correspondiente a su solicitud de compra
        <strong>#{$solped->id}</strong> (proceso: <strong>{$concurso->nombre}</strong>)
        ha sido <strong>aceptada</strong> por el proveedor adjudicado.
    </p>

    <div style="background-color: #f5f5f5; border-left: 4px solid #28a745; padding: 15px; margin: 20px 0;">
        <p><strong>Detalles de la Adjudicación</strong></p>
        <ul style="margin: 10px 0; padding-left: 20px;">
            <li><strong>Proveedor adjudicado:</strong> {$adjudicado}</li>
            <li><strong>Fecha y hora de aceptación:</strong> {$fecha_hora}</li>
            <li><strong>Solicitud:</strong> #{$solped->id} - {$solped->nombre}</li>
            {if isset($solped->codigo_interno) && $solped->codigo_interno}
                <li><strong>Código interno:</strong> {$solped->codigo_interno}</li>
            {/if}
            {if isset($solped->area_sol) && $solped->area_sol}
                <li><strong>Área solicitante:</strong> {$solped->area_sol}</li>
            {/if}
        </ul>
    </div>

    <p>
        En breve el equipo de compras continuará con los pasos siguientes. Para más información
        puede consultar el detalle en la plataforma.
    </p>

    <p>Saludos cordiales,<br>
    <strong>Equipo de Compras</strong></p>
</div>
{/block}
