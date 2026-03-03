{extends 'email/base.tpl'}

{block 'content'}
    Estimado {$user->full_name}<br><br>
    Le informamos que la solicitud de aprobación ha sido <b>rechazada</b>.<br><br>
    <ul>
        <li><b>Nº Concurso</b>: {$concurso->id}<br><br></li>
        <li><b>Nombre de Concurso</b>: {$concurso->nombre}<br><br></li>
        <li><b>Tipo de Adjudicación</b>: {$approval->adjudication_type|ucfirst}<br><br></li>
        <li><b>Monto</b>: ${$approval->amount|number_format:2:',':'.'}<br><br></li>
        <li><b>Monto USD</b>: ${$approval->amount_usd|number_format:2:',':'.'}<br><br></li>
    </ul>
    <b>Motivo del rechazo:</b><br>
    {$reason}<br><br>
    Puede lanzar una nueva ronda o cancelar el concurso según corresponda.<br><br>
    <a href="{$app_url}/concursos/cliente/por-etapa/adjudicacion/{$concurso->id}">Ver Concurso</a>
{/block}
