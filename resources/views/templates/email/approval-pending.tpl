{extends 'email/base.tpl'}

{block 'content'}
    Estimado {$user->full_name}<br><br>
    Un pedido de adjudicación requiere su aprobación.<br><br>
    <ul>
        <li><b>Nº Concurso</b>: {$concurso->id}<br><br></li>
        <li><b>Nombre de Concurso</b>: {$concurso->nombre}<br><br></li>
        <li><b>Tipo de Adjudicación</b>: {$approval->adjudication_type|ucfirst}<br><br></li>
        <li><b>Monto</b>: ${$approval->amount|number_format:2:',':'.'}<br><br></li>
        <li><b>Monto USD</b>: ${$approval->amount_usd|number_format:2:',':'.'}<br><br></li>
        <li><b>Su rol</b>: {$approval->role}<br><br></li>
    </ul>
    <b>Acción requerida:</b> Por favor ingrese al portal a la seccion de monitor para aprobar o rechazar este pedido de adjudicación.<br><br>
    <a href="{$app_url}/concursos/cliente/por-etapa/adjudicacion/{$concurso->id}">Ver Concurso</a>
{/block}
