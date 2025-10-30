{extends 'email/base.tpl'}

{block 'content'}
    Estimado/a <strong>{$nombre_cliente}</strong>,
    <br><br>
    Le informamos que la <strong>ETAPA ECONÓMICA</strong> del siguiente concurso ha finalizado:
    <br><br>
    
    <table style="width:100%; background-color:#f8f9fa; border-radius:6px; margin:20px 0; border-collapse:collapse;">
        <tr>
            <td style="padding:15px;">
                <strong style="color:#2B3643;">Concurso:</strong><br/>
                <span style="font-size:16px; color:#26C281;">{$nombre_concurso}</span>
                <br><br>
                <strong style="color:#2B3643;">Fecha y hora de finalización:</strong><br/>
                <span style="font-size:16px; color:#2B3643;">{$fecha_finalizacion}</span>
            </td>
        </tr>
    </table>
    Puede ingresar a su cuenta para revisar los detalles y resultados de esta etapa.
    <br><br>
    <span style="font-size:12px; color:#666666;">
        Este es un mensaje automático generado por el sistema. Por favor, no responda a este correo.
    </span>
    <br><br>
    Atte, Optus
{/block}
