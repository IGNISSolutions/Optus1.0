<!-- ko if: $root.UserType() === 'customer' || $root.UserType() === 'supervisor'-->
<div class="m-heading-1 border-default m-bordered text-left">
    <h4 class="block bold" style="margin-top: 0; padding-top: 0;">
        <span style="text-aling: left; float: left;" data-bind="text:titleResultados()"></span>
        <span style="text-aling: right; float: right;" data-bind="text:Ronda()"></span>
    </h4>
    <br><br>
    <ul data-bind="foreach: RondasOfertas()" class="nav nav-pills nav-justified">
        <li data-bind="css: { active: active ? 'active' : '' }">
            <a data-toggle="pill" data-bind="text:title, attr: { href: '#'+ref }"></a>
        </li>
    </ul>


    <div class="tab-content" data-bind="foreach: RondasOfertas()">
        <div class="tab-pane fade"
            data-bind="attr: { id: ref }, css: { in: active ? 'in' : '', active: active ? 'active' : '' }">
            {* Comparativas de ofertas *}
            <div class="m-heading-1 border-default m-bordered text-left"
                style="display: flex; justify-content: space-between; flex-direction: column;">
                {include file='concurso/detail/customer/partials/evaluacion/comparativa-ofertas.tpl'}
            </div>
        </div>
    </div>
</div>
<!-- /ko -->
