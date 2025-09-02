{extends 'email/base.tpl'}

{block 'content'}
    Estimado {$company_name}<br><br>
    Le recordamos que a la fecha no hemos recibido su oferta económica requerida en el <b>Nº Concurso</b>: {$concurso->id},
    "{$concurso->nombre}".<br><br>
    <ul>
        <li><b>Nº Concurso</b>: {$concurso->id}<br><br></li>
        <li><b>Nombre de Concurso</b>: {$concurso->nombre}<br><br></li>

        <li><b>Zona Horaria</b>: {$timeZone}<br><br></li>
        <li><b>Fecha límite para presentación de ofertas económicas</b>:
            {$concurso->fecha_limite_economicas->format('d-m-Y H:i:s')}<br><br></li>
    </ul>
    Para cumplir esta etapa deberá ingresar a www.optus.com.ar. En caso de no cumplir este requisito, no podrá continuar
    participando del concurso.<br><br>
    <b>Contamos con su participación!</b>
{/block}