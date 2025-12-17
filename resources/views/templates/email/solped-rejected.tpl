{extends 'email/base.tpl'}

{block 'content'}
    Hola {$solped->solicitante->full_name},<br><br>

    Lamentamos informarte que la solicitud N° <strong>#{$solped->id}</strong> <strong>{$solped->nombre}</strong> ha sido <strong>rechazada</strong>.<br><br>

    <strong>Motivo:</strong> {$reason}<br>
    <strong>Fecha de rechazo:</strong> {if $solped->fecha_rechazo}{$solped->fecha_rechazo|date_format:"%d-%m-%Y %H:%M"}{else}-{/if}<br><br>

    Si necesitas más información, ingresa al portal o contacta con el equipo de compras.<br><br>

    <div style="width: 100%; text-align: right;">
        Atentamente,<br>
        {$user->full_name}
    </div>
{/block}
