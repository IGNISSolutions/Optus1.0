{extends 'email/base.tpl'}

{block 'content'}
    Estimado/a {$company_name}:<br><br>

    Le informamos que se extendió el plazo para presentar la oferta económica del siguiente concurso:<br><br>

    <ul>
        <li><b>N.º de concurso:</b> {$concurso->id}</li>
        <li><b>Nombre:</b> {$concurso->nombre}</li>
        <li><b>Nueva fecha límite:</b> {$new_deadline}</li>
        <li><b>Zona horaria:</b> {$timezone}</li>
    </ul>

    Le recomendamos ingresar al portal para revisar el concurso y presentar o actualizar su oferta dentro del nuevo plazo.<br><br>

    Contamos con su participación.<br><br>
    <b>¡Una nueva oportunidad lo está esperando!</b>
{/block}
