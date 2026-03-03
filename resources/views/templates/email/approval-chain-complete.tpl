{extends 'email/base.tpl'}

{block 'content'}
    Estimado {$user->full_name}<br><br>
    <b>¡Buenas Noticias!</b> La cadena de aprobación de la adjudicación ha sido completada correctamente.<br><br>
    Todos los usuarios de la estrategia de liberación aprobaron la adjudicación. Puede proceder.<br><br>
    <ul>
        <li><b>Nº Concurso</b>: {$concurso->id}<br><br></li>
        <li><b>Nombre de Concurso</b>: {$concurso->nombre}<br><br></li>
        <li><b>Tipo de Adjudicación</b>: {$approval->adjudication_type|ucfirst}<br><br></li>
        <li><b>Monto</b>: ${$approval->amount|number_format:2:',':'.'}<br><br></li>
        <li><b>Monto USD</b>: ${$approval->amount_usd|number_format:2:',':'.'}<br><br></li>
    </ul>
    <a href="{$app_url}/concursos/cliente/por-etapa/adjudicacion/{$concurso->id}">Procesar Adjudicación</a>
{/block}
