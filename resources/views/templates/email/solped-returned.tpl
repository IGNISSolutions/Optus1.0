{extends 'email/base.tpl'}

{block 'content'}
    Hola {$solped->solicitante->full_name},<br><br>

    Tu solicitud N° <strong>#{$solped->id}</strong> <strong>{$solped->nombre}</strong> ha sido <strong>devuelta</strong> para modificación.<br><br>

    <strong>Motivo:</strong> {$reason}<br>
    <strong>Fecha de devolución:</strong> {if $solped->fecha_devolucion}{$solped->fecha_devolucion|date_format:"%d-%m-%Y %H:%M"}{else}-{/if}<br><br>

    Para continuar con el proceso ingresa al portal, revisa y actualiza la información solicitada para continuar con el proceso.<br><br>

    <div style="width: 100%; text-align: right;">
        Atentamente,<br>
        {$user->full_name}
    </div>
{/block}
