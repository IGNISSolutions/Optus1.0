{extends 'email/base.tpl'}

{block 'content'}
    Hola {$solped->solicitante->full_name},<br><br>

    Tu solicitud N° <strong>#{$solped->id}</strong> <strong>{$solped->nombre}</strong> ha sido <strong>aprobada</strong>.<br><br>

    <strong>Fecha de aprobación:</strong> {if $solped->fecha_aceptacion}{$solped->fecha_aceptacion|date_format:"%d-%m-%Y %H:%M"}{else}-{/if}<br><br>

    En breve continuaremos con el proceso. Si necesitas más información, ingresa al portal o contacta al equipo de compras.<br><br>

    <div style="width: 100%; text-align: right;">
        Atentamente,<br>
        {$user->full_name}
    </div>
{/block}
