{extends 'email/base.tpl'}

{block 'content'}
Estimado {$company_name}<br><br>

Le informamos que concurso <b>Nº Concurso</b>: {$concurso->id} <span class="text-bold">{$concurso->nombre}</span> ha llegado a su etapa de finalización. En
esta oportunidad sus propuestas técnicas y/o económicas no han resultado seleccionadas.<br><br>
Le agradecemos su interés y esfuerzo en la participación del proceso.<br><br>
<div style="width: 100%; text-align: right;">Atte, OPTUS – {$concurso->cliente->customer_company->business_name} en representación de {$concurso->cliente->customer_company->business_name}.</div>
{/block}