{extends file='email/base.tpl'}

{block name="content"}
<tr>
    <td style="padding: 40px 30px;">
        <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 1.6; color: #34495e;">
            Estimado/a <strong>{$nombre_cliente}</strong>,
        </p>
        
        <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 1.6; color: #34495e;">
            Nos complace informarle que <strong>todos los proveedores</strong> del concurso 
            <strong>"{$nombre_concurso}"</strong> han presentado sus propuestas económicas 
            <strong>antes del cierre de la etapa</strong>.
        </p>
        
        <div style="background-color: #e8f5e9; border-left: 4px solid #4caf50; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <p style="margin: 0; font-size: 15px; color: #2e7d32;">
                <strong>Estado:</strong> Todas las propuestas económicas recibidas<br>
                <strong>Ronda:</strong> {$ronda_economica}<br>
                <strong>Completado el:</strong> {$fecha_completado}<br>
                <strong>Fecha límite:</strong> {$fecha_limite}
            </p>
        </div>
        
        <p style="margin: 0 0 30px 0; font-size: 16px; line-height: 1.6; color: #34495e;">
            Ya puede proceder a revisar y evaluar las propuestas desde el monitor.
        </p>
        
        <p style="margin: 30px 0 0 0; font-size: 14px; line-height: 1.6; color: #7f8c8d;">
            Este es un mensaje automático del sistema de gestión de concursos.
        </p>
    </td>
</tr>
{/block}
