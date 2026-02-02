<?php

use App\Middleware\AuthMiddleware;

// HOME
app()->get('/', 'App\Http\Controllers\Home\HomeController:serveHome')->add(new AuthMiddleware())->setName('serveHome');

// AUTH
app()->get('/resources/datatables', 'App\Http\Controllers\Auth\AuthController:resourcesDatatable')->add(new AuthMiddleware())->setName('general.resourcesDatatable');
app()->get('/login', 'App\Http\Controllers\Auth\AuthController:serveLogin')->setName('login');
app()->post('/login/send', 'App\Http\Controllers\Auth\AuthController:login')->setName('login.send');
app()->get('/ad/callback', 'App\Http\Controllers\AD\ADController:callback')->setName('login.callback');
app()->get('/ad/login/LG', 'App\Http\Controllers\AD\ADController:loginLG')->setName('login.lg');
app()->get('/ad/login/TLC', 'App\Http\Controllers\AD\ADController:loginTLC')->setName('login.tlc');
app()->post('/logout', 'App\Http\Controllers\Auth\AuthController:logout')->add(new AuthMiddleware())->setName('logout');
app()->post('/send', 'App\Http\Controllers\Auth\AuthController:sendRecover')->setName('sendRecover');

// AUTH0
app()->get('/a0/login/SCR', 'App\Http\Controllers\A0\A0Controller:loginSCR')->setName('login.scr');
app()->get('/a0/callback', 'App\Http\Controllers\A0\A0Controller:callback')->setName('a0.callback');
app()->get('/a0/logout', 'App\Http\Controllers\A0\A0Controller:logoutA0')->setName('a0.logout');

//2FA
app()->post('/send-code', 'App\Http\Controllers\Auth\AuthController:sendResetCode')->setName('sendResetCode'); 
app()->get('/verify-code', 'App\Http\Controllers\Auth\AuthController:serverTwoFA')->setName('serverTwoFA');
app()->get('/verify-code-advice', 'App\Http\Controllers\Auth\AuthController:serverTwoFAAdvice')->setName('serverTwoFAAdvice');
app()->get('/process-verify-code', 'App\Http\Controllers\Auth\AuthController:verifyCode')->setName('verifyCode');


app()->get('/send/{usuario}', 'App\Http\Controllers\Auth\AuthController:serverReset')->setName('serverReset');
app()->get('/change-password', 'App\Http\Controllers\Auth\AuthController:serverResetChangePassword')->setName('serverResetChangePassword');
app()->patch('/recover', 'App\Http\Controllers\Auth\AuthController:updatePassword')->setName('updatePassword');

// REDIRECCIÓN PARA SERVICIOS VIEJOS
app()->group('/services', function () {
    $this->any('/{file}', function ($request, $response, $args) {
        $path = rootPath() . '/app/OldServices/' . $args['file'];
        // dd($path);
        return require $path;
    });
})->add(new AuthMiddleware());

// DASHBOARD
app()->group('/dashboard', function () {
    app()->get('', 'App\Http\Controllers\Dashboard\DashboardController:serveList')->setName('dashboard.serveList');
    app()->get('/list', 'App\Http\Controllers\Dashboard\DashboardController:list')->setName('dashboard.list');
});

// LISTAS GENÉRICAS
app()->group('/lists', function () {
    $this->get('/categorieswithareas', 'App\Http\Controllers\Lists\ListsController:getCategoriesWithAreas')->setName('lists.categorieswithareas');
    $this->get('/countrieswithprovinces', 'App\Http\Controllers\Lists\ListsController:getCountriesWithProvincesList')->setName('lists.countrieswithprovinces');

    $this->get('/areas', 'App\Http\Controllers\Lists\ListsController:getAreas')->setName('lists.areas');
    $this->get('/provinces', 'App\Http\Controllers\Lists\ListsController:getProvinces')->setName('lists.provinces');
    $this->get('/cities', 'App\Http\Controllers\Lists\ListsController:getCities')->setName('lists.cities');
    $this->get('/companies', 'App\Http\Controllers\Lists\ListsController:getCompanies')->setName('lists.companies');
})->add(new AuthMiddleware());

// MULTIMEDIA
app()->group('/media', function () {
    // Files
    $this->post('/file/download', 'App\Http\Controllers\Media\MediaController:downloadFile')->setName('media.file.download');
    $this->post('/file/upload', 'App\Http\Controllers\Media\MediaController:uploadFile')->setName('media.file.upload');
    $this->post('/file/delete', 'App\Http\Controllers\Media\MediaController:deleteFile')->setName('media.file.delete');
    $this->post('/file/rollback', 'App\Http\Controllers\Media\MediaController:rollbackFile')->setName('media.file.rollback');
    // Zip
    $this->post('/file/zip/download', 'App\Http\Controllers\Media\MediaController:downloadZip')->setName('media.file.zip.download');
})->add(new AuthMiddleware());

// CONCURSOS
app()->group('/concursos', function () {
    // CLIENTE
    // Monitor
    $this->get('/cliente', 'App\Http\Controllers\Customer\ConcursoController:serveList')->add(new AuthMiddleware())->setName('concursos.cliente.serveList');
    $this->get('/cliente/list', 'App\Http\Controllers\Customer\ConcursoController:list')->add(new AuthMiddleware())->setName('concursos.cliente.list');
    $this->post('/cliente/list', 'App\Http\Controllers\Customer\ConcursoController:listFilter')->add(new AuthMiddleware())->setName('concursos.cliente.listFilter');
    // Lista por Tipo
    $this->get('/cliente/{type}', 'App\Http\Controllers\Customer\ConcursoController:serveTypeList')->add(new AuthMiddleware())->setName('concursos.cliente.serveTypeList');
    $this->get('/cliente/{type}/list', 'App\Http\Controllers\Customer\ConcursoController:typeList')->add(new AuthMiddleware())->setName('concursos.cliente.type.list');
    // Detalle
    $this->get('/cliente/{type}/{step}/{id}', 'App\Http\Controllers\Customer\ConcursoController:serveDetail')->add(new AuthMiddleware())->setName('concursos.cliente.serveDetail');
    $this->get('/cliente/{type}/{step}/{id}/detail', 'App\Http\Controllers\Customer\ConcursoController:detail')->add(new AuthMiddleware())->setName('concursos.cliente.getDetail');
    // Segunda Ronda
    $this->post('/cliente/SecondRound/send', 'App\Http\Controllers\Customer\ConcursoController:sendSecondRound')->add(new AuthMiddleware())->setName('concursos.cliente.sendSecondRound');

    // OFERENTE
    // Monitor
    $this->get('/oferente', 'App\Http\Controllers\Offerer\ConcursoController:serveList')->add(new AuthMiddleware())->setName('concursos.oferente.serveList');
    $this->get('/oferente/list', 'App\Http\Controllers\Offerer\ConcursoController:list')->add(new AuthMiddleware())->setName('concursos.oferente.list');
    $this->post('/oferente/list', 'App\Http\Controllers\Offerer\ConcursoController:listFilter')->add(new AuthMiddleware())->setName('concursos.oferente.listFilter');
    // Detalle
    $this->get('/oferente/{type}/{step}/{id}', 'App\Http\Controllers\Offerer\ConcursoController:serveDetail')->add(new AuthMiddleware())->setName('concursos.oferente.serveDetail');
    $this->get('/oferente/{type}/{step}/{id}/detail', 'App\Http\Controllers\Offerer\ConcursoController:detail')->add(new AuthMiddleware())->setName('concursos.oferente.getDetail');
    // Pagos
    $this->post('/oferente/payments/verify', 'App\Http\Controllers\Payment\PaymentMpController:verify')->add(new AuthMiddleware())->setName('concursos.oferentes.payments.verify');
    // Adjudicación
    $this->post('/oferente/adjudication/send', 'App\Http\Controllers\Adjudication\AdjudicationController:acceptOrDecline')->add(new AuthMiddleware())->setName('concursos.adjudication.acceptOrDecline');
    $this->post('/oferente/declination', 'App\Http\Controllers\Offerer\ConcursoController:declination')->add(new AuthMiddleware())->setName('concursos.Decline');

    // COMPLEMENTOS
    // Adjudicación
    $this->get('/adjudication/products/{id}', 'App\Http\Controllers\Adjudication\AdjudicationController:getProduct')->add(new AuthMiddleware())->setName('concursos.cliente.adjudication.getProduct');
    $this->get('/adjudication/products/{id}/offerers/{offerer_id}', 'App\Http\Controllers\Adjudication\AdjudicationController:getOffererByProduct')->add(new AuthMiddleware())->setName('concursos.cliente.adjudication.getOffererByProduct');
    $this->post('/adjudication/products/check', 'App\Http\Controllers\Adjudication\AdjudicationController:check')->add(new AuthMiddleware())->setName('concursos.cliente.adjudication.check');
    $this->post('/adjudication/send', 'App\Http\Controllers\Adjudication\AdjudicationController:send')->add(new AuthMiddleware())->setName('concursos.cliente.adjudication.send');

    // Chat
    $this->post('/chat/list', 'App\Http\Controllers\Chat\ChatController:list')->add(new AuthMiddleware(['chat-read']))->setName('concursos.chat.list');
    $this->post('/chat/check', 'App\Http\Controllers\Chat\ChatController:check')->add(new AuthMiddleware(['chat-read']))->setName('concursos.chat.check');
    $this->post('/chat/store', 'App\Http\Controllers\Chat\ChatController:store')->add(new AuthMiddleware(['chat-read']))->setName('concursos.chat.store');
    $this->post('/chat/toggleRead', 'App\Http\Controllers\Chat\ChatController:toggleRead')->add(new AuthMiddleware(['chat-read']))->setName('concursos.chat.toggleRead');
    $this->post('/chat/approveOrReject', 'App\Http\Controllers\Chat\ChatController:approveOrReject')->add(new AuthMiddleware(['chat-admin']))->setName('concursos.chat.approveOrReject');
    $this->post('/chat/message', 'App\Http\Controllers\Chat\ChatController:getMessage')->add(new AuthMiddleware(['chat-read']))->setName('concursos.chat.message');

    // Productos
    $this->post('/products/check', 'App\Http\Controllers\Customer\ConcursoController:checkProducts')->add(new AuthMiddleware())->setName('concursos.products.check');
    // Plantilla Técnica
    $this->get('/payrolls/edit/{id}', 'App\Http\Controllers\Payroll\PayrollController:edit')->add(new AuthMiddleware())->setName('concursos.payroll.edit');
    $this->get('/{id}/payroll', 'App\Http\Controllers\Customer\PayrollController:get')->add(new AuthMiddleware())->setName('concursos.payroll.get');
    // Verificar Documentación GO
    $this->post('/documentation/check', 'App\Http\Controllers\Offerer\DocumentationController:check')->add(new AuthMiddleware())->setName('concursos.oferente.documentacion.check');
    // Recordatorio Documentación GO
    $this->post('/documentation/reminder', 'App\Http\Controllers\Offerer\DocumentationController:sendReminder')->add(new AuthMiddleware())->setName('concursos.oferente.documentacion.sendReminder');
    // Evaluaciones
    $this->post('/evaluations/save', 'App\Http\Controllers\Customer\EvaluationController:store')->add(new AuthMiddleware())->setName('concursos.cliente.evaluations.store');
    // Propuesta Técnica
    $this->post('/proposal/technical/send', 'App\Http\Controllers\Proposal\TechnicalProposalController:send')->add(new AuthMiddleware())->setName('concursos.proposal.technical.send');
    $this->post('/proposal/technical/update', 'App\Http\Controllers\Proposal\TechnicalProposalController:update')->add(new AuthMiddleware())->setName('concursos.proposal.technical.update');
    $this->post('/proposal/technical/acceptorreject', 'App\Http\Controllers\Proposal\TechnicalProposalController:acceptOrReject')->add(new AuthMiddleware())->setName('concursos.proposal.technical.acceptorreject');
    $this->post('/proposal/technical/newround', 'App\Http\Controllers\Proposal\TechnicalProposalController:newRound')->add(new AuthMiddleware())->setName('concursos.proposal.technical.newRound');
    // Propuesta Económica
    $this->post('/proposal/economic/send', 'App\Http\Controllers\Proposal\EconomicProposalController:send')->add(new AuthMiddleware())->setName('concursos.proposal.economic.send');
    $this->post('/proposal/economic/update', 'App\Http\Controllers\Proposal\EconomicProposalController:update')->add(new AuthMiddleware())->setName('concursos.proposal.economic.update');
    $this->post('/proposal/auction/cotizar', 'App\Http\Controllers\Proposal\EconomicProposalController:auctionCotizar')->add(new AuthMiddleware())->setName('concursos.proposal.auction.cotizar');
    $this->post('/proposal/auction/anular', 'App\Http\Controllers\Proposal\EconomicProposalController:auctionAnular')->add(new AuthMiddleware())->setName('concursos.proposal.auction.anular');
    $this->post('/proposal/auction/update', 'App\Http\Controllers\Proposal\EconomicProposalController:auctionUpdate')->add(new AuthMiddleware())->setName('concursos.proposal.auction.update');
    // Invitaciones
    $this->post('/invitations/filter', 'App\Http\Controllers\Customer\InvitationController:filter')->add(new AuthMiddleware())->setName('concursos.invitations.filter');
    $this->post('/invitations/send', 'App\Http\Controllers\Customer\InvitationController:send')->add(new AuthMiddleware())->setName('concursos.invitations.send');
    // invitar nuevo oferente luego de enviar invitaciones
    $this->post('/invitations/sendNew', 'App\Http\Controllers\Customer\InvitationController:newInvitation')->add(new AuthMiddleware())->setName('concursos.invitations.sendNew');
    $this->post('/invitations/reminder', 'App\Http\Controllers\Customer\InvitationController:sendReminder')->add(new AuthMiddleware())->setName('concursos.invitations.sendReminder');
    $this->post('/invitations/acceptorreject', 'App\Http\Controllers\Offerer\InvitationController:acceptOrReject')->add(new AuthMiddleware())->setName('concursos.invitations.acceptOrReject');

    // CRUD
    
    //Control vertical
    $this->post('/guardar-token-acceso', 'App\Http\Controllers\Customer\ConcursoController:guardarTokenAcceso')
    ->add(new AuthMiddleware())
    ->setName('concursos.guardarTokenAcceso');

    //Control vertical oferente
    $this->post('/oferente/guardar-token-acceso', 'App\Http\Controllers\Offerer\ConcursoController:guardarTokenAcceso')
    ->add(new AuthMiddleware())
    ->setName('concursos.oferente.guardarTokenAcceso');
    
    // Creación
    $this->get('/{type}/nuevo', 'App\Http\Controllers\Customer\ConcursoController:serveCreate')->add(new AuthMiddleware())->setName('concursos.cliente.serveCreate');
    // Edición
    $this->get('/{type}/edicion/{id}', 'App\Http\Controllers\Customer\ConcursoController:serveEdit')->add(new AuthMiddleware())->setName('concursos.cliente.serveEdit');
    // Edición/Creación
    $this->get('/{type}/{action}[/{id}]', 'App\Http\Controllers\Customer\ConcursoController:editOrCreate')->add(new AuthMiddleware())->setName('concursos.cliente.editOrCreate');
    // Guardar
    $this->post('/{type}/save[/{id}]', 'App\Http\Controllers\Customer\ConcursoController:store')->add(new AuthMiddleware())->setName('concursos.cliente.store');
    // Guardar Borrador
    $this->post('/{type}/save-draft[/{id}]', 'App\Http\Controllers\Customer\ConcursoController:storeDraft')->add(new AuthMiddleware())->setName('concursos.cliente.storeDraft');
    // Eliminar
    $this->post('/delete/{id}', 'App\Http\Controllers\Customer\ConcursoController:delete')->add(new AuthMiddleware())->setName('concursos.cliente.delete');
    $this->post('/verOfertas/{id}', 'App\Http\Controllers\Customer\ConcursoController:verOfertas')->add(new AuthMiddleware())->setName('concursos.cliente.verOfertas');
    $this->post('/setToken/{id}', 'App\Http\Controllers\Customer\ConcursoController:setToken')->add(new AuthMiddleware())->setName('concursos.cliente.setToken');
    // Crear desde SOLPEDs
    $this->post('/cliente/from-solpeds', 'App\Http\Controllers\Customer\ConcursoController:createFromSolpeds')->add(new AuthMiddleware())->setName('concursos.cliente.createFromSolpeds');
    $this->post('/cliente/auction-from-solpeds', 'App\Http\Controllers\Customer\ConcursoController:createAuctionFromSolpeds')->add(new AuthMiddleware())->setName('concursos.cliente.createAuctionFromSolpeds');
});

// ESTRATEGIA DE LIBERACIÓN
app()->group('/estrategia', function () {
    $this->get('/matriz', 'App\Http\Controllers\Estrategia\EstrategiaController:serveMatriz')->add(new AuthMiddleware())->setName('estrategia.serveMatriz');
    $this->get('/get', 'App\Http\Controllers\Estrategia\EstrategiaController:get')->add(new AuthMiddleware())->setName('estrategia.get');
    $this->post('/store', 'App\Http\Controllers\Estrategia\EstrategiaController:store')->add(new AuthMiddleware())->setName('estrategia.store');
});

// ADJUDICATION APPROVAL CHAIN
app()->group('/approval', function () {
    // Get pending approvals for current user (for type-list.tpl)
    $this->get('/my-pending', 'App\Http\Controllers\Approval\ApprovalController:getMyPending')
        ->add(new AuthMiddleware())
        ->setName('approval.myPending');
    
    // Generate session token for approver access
    $this->post('/generate-token', 'App\Http\Controllers\Approval\ApprovalController:generateToken')
        ->add(new AuthMiddleware())
        ->setName('approval.generateToken');

    $this->post('/start', 'App\Http\Controllers\Approval\ApprovalController:startApproval')
        ->add(new AuthMiddleware())
        ->setName('approval.start');
    
    $this->get('/status/{contest_id}', 'App\Http\Controllers\Approval\ApprovalController:getStatus')
        ->add(new AuthMiddleware())
        ->setName('approval.status');
    
    $this->post('/approve', 'App\Http\Controllers\Approval\ApprovalController:approve')
        ->add(new AuthMiddleware())
        ->setName('approval.approve');
    
    $this->post('/reject', 'App\Http\Controllers\Approval\ApprovalController:reject')
        ->add(new AuthMiddleware())
        ->setName('approval.reject');
    
    $this->post('/cancel', 'App\Http\Controllers\Approval\ApprovalController:cancel')
        ->add(new AuthMiddleware())
        ->setName('approval.cancel');
    
    $this->post('/process', 'App\Http\Controllers\Approval\ApprovalController:processAdjudication')
        ->add(new AuthMiddleware())
        ->setName('approval.process');
});
// SOLPED
app()->group('/solped', function () {

    //Enviar Solicitud a comprador
    $this->post('/solicitante/send', 'App\Http\Controllers\Solped\SolpedController:send')->add(new AuthMiddleware())->setName('solped.send');
    //Cancelar Solicitud por solicitante
    $this->post('/solicitante/cancelSolped', 'App\Http\Controllers\Solped\SolpedController:cancelSolped')->add(new AuthMiddleware())->setName('solped.cancelSolped');
    //Aceptar/Rechazar Solicitud comprador
    //$this->post('/cliente/accept-or-reject', 'App\Http\Controllers\Customer\SolpedController:acceptOrReject')->add(new AuthMiddleware())->setName('solped.acceptOrReject');
    $this->post('/cliente/send-back', 'App\Http\Controllers\Customer\SolpedController:sendBack')->add(new AuthMiddleware())->setName('solped.sendBack');
    $this->post('/cliente/reject', 'App\Http\Controllers\Customer\SolpedController:reject')->add(new AuthMiddleware())->setName('solped.reject');
    $this->post('/cliente/approve', 'App\Http\Controllers\Customer\SolpedController:approve')->add(new AuthMiddleware())->setName('solped.approve');
    //Detail Cliente 
    $this->get('/cliente/{etapa}/{id:[0-9]+}', 'App\Http\Controllers\Customer\SolpedController:serveDetail')->add(new AuthMiddleware())->setName('solped.serveDetail');
    $this->get('/cliente/{etapa}/{id:[0-9]+}/detail', 'App\Http\Controllers\Customer\SolpedController:detail')->add(new AuthMiddleware())->setName('solped.detail');

    //Monitor Cliente
    $this->get('/cliente/monitor', 'App\Http\Controllers\Solped\SolpedController:serveTypeList')->add(new AuthMiddleware())->setName('solped.serveTypeList');
    $this->get('/cliente/monitor/list', 'App\Http\Controllers\Solped\SolpedController:list')->add(new AuthMiddleware())->setName('solped.list');
    $this->post('/cliente/monitor/list', 'App\Http\Controllers\Solped\SolpedController:listFilter')->add(new AuthMiddleware())->setName('solped.listFilter');

    //Monitor Solicitante
    $this->get('/solicitante/monitor', 'App\Http\Controllers\Solped\SolpedController:serveTypeList')->add(new AuthMiddleware())->setName('solped.serveTypeList');
    $this->get('/solicitante/monitor/list', 'App\Http\Controllers\Solped\SolpedController:list')->add(new AuthMiddleware())->setName('solped.list');
    $this->post('/solicitante/monitor/list', 'App\Http\Controllers\Solped\SolpedController:listFilter')->add(new AuthMiddleware())->setName('solped.listFilter');

    //Enviar Solicitud Solicitante
    $this->post('/invitations/send', 'App\Http\Controllers\Solped\SolpedInvitationController:send')->add(new AuthMiddleware())->setName('solped.invitations.send');

    //Detalle Solicitante
    $this->get('/solicitante/{etapa}/{id:[0-9]+}', 'App\Http\Controllers\Solped\SolpedController:serveDetail')->add(new AuthMiddleware())->setName('solped.serveDetail');
    $this->get('/solicitante/{etapa}/{id:[0-9]+}/detail', 'App\Http\Controllers\Solped\SolpedController:detail')->add(new AuthMiddleware())->setName('solped.detail');

    $this->get('/solicitudes', 'App\Http\Controllers\Solped\SolpedController:serveList')->add(new AuthMiddleware())->setName('solped.serveList');
        
    $this->get('/list', 'App\Http\Controllers\Solped\SolpedController:listJson')->add(new AuthMiddleware())->setName('solped.listJson');

    $this->get('/nuevo', 'App\Http\Controllers\Solped\SolpedController:serveCreate')->add(new AuthMiddleware())->setName('solped.serveCreate');

    $this->get('/edicion/{id:[0-9]+}', 'App\Http\Controllers\Solped\SolpedController:serveEdit')->add(new AuthMiddleware())->setName('solped.serveEdit');

    // API unificada create/edit (JSON) → usa regex para NO capturar "save"
    $this->get('/{action:create|edit}[/{id:[0-9]+}]','App\Http\Controllers\Solped\SolpedController:editOrCreate')->add(new AuthMiddleware())->setName('solped.editOrCreate');

    // Chequear productos
    $this->post('/products/check','App\Http\Controllers\Solped\SolpedController:checkProducts')->add(new AuthMiddleware())->setName('solped.products.check');

    // Guardar (POST)
    $this->post('/save[/{id:[0-9]+}]','App\Http\Controllers\Solped\SolpedController:store')->add(new AuthMiddleware())->setName('solped.solicitante.store');
    //Eliminar
    $this->post('/delete/{id}', 'App\Http\Controllers\Solped\SolpedController:delete')->add(new AuthMiddleware())->setName('solped.solicitante.delete');


});

// USUARIOS Y PERMISOS
app()->group('/usuarios', function () {
    // USUARIOS

    //Control vertical
    $this->post('/guardar-id-edicion', 'App\Http\Controllers\User\UserController:guardarIdEdicion')
     ->add(new AuthMiddleware())
     ->setName('usuarios.guardarIdEdicion');

     $this->post('/guardar-id-permisos', 'App\Http\Controllers\User\PermissionController:guardarIdPermisos')
     ->add(new AuthMiddleware())
     ->setName('usuarios.guardarIdPermisos');

     $this->post('/guardar-id-detalle', 'App\Http\Controllers\User\UserController:guardarIdDetalle')
     ->add(new AuthMiddleware())
     ->setName('usuarios.guardarIdDetalle');
     
    // Listado
    $this->get('/tipo/{type}', 'App\Http\Controllers\User\UserController:serveList')->add(new AuthMiddleware())->setName('usuarios.serveList');
    $this->get('/list/{type}', 'App\Http\Controllers\User\UserController:list')->add(new AuthMiddleware())->setName('usuarios.list');
    // Creación
    $this->get('/nuevo/{type}', 'App\Http\Controllers\User\UserController:serveCreate')->add(new AuthMiddleware())->setName('usuarios.serveCreate');
    // Detalle
    $this->get('/detalle/{id}', 'App\Http\Controllers\User\UserController:serveDetail')->add(new AuthMiddleware())->setName('usuarios.serveDetail');
    $this->get('/detalle/data/{id}', 'App\Http\Controllers\User\UserController:detail')->add(new AuthMiddleware())->setName('usuarios.detail');
    // Edición
    $this->get('/edicion/{id}', 'App\Http\Controllers\User\UserController:serveEdit')->add(new AuthMiddleware())->setName('usuarios.serveEdit');
    // Edición/Creación
    $this->get('/{action}/{type}/data[/{id}]', 'App\Http\Controllers\User\UserController:editOrCreate')->add(new AuthMiddleware())->setName('usuarios.editOrCreate');
    // Guardar
    $this->post('/save[/{id}]', 'App\Http\Controllers\User\UserController:store')->add(new AuthMiddleware())->setName('usuarios.store');
    // Eliminar
    $this->post('/delete/{id}', 'App\Http\Controllers\User\UserController:delete')->add(new AuthMiddleware())->setName('usuarios.delete');

    // PERMISOS
    // Listado
    $this->get('/edicion/{id}/permisos', 'App\Http\Controllers\User\PermissionController:serveEdit')->add(new AuthMiddleware())->setName('permisos.serveEdit');
    $this->get('/edicion/{id}/permisos/data', 'App\Http\Controllers\User\PermissionController:edit')->add(new AuthMiddleware())->setName('permisos.edit');
    $this->post('/edicion/{id}/permisos/save', 'App\Http\Controllers\User\PermissionController:store')->add(new AuthMiddleware())->setName('permisos.store');
});

// EMPRESAS
app()->group('/empresas', function () {

    //Control vertical

    $this->post('/guardar-id-edicion', 'App\Http\Controllers\Company\CompanyController:guardarIdEdicion')
     ->add(new AuthMiddleware())
     ->setName('empresas.guardarIdEdicion');

     $this->post('/guardar-id-detalle', 'App\Http\Controllers\Company\CompanyController:guardarIdDetalle')
     ->add(new AuthMiddleware())
     ->setName('empresas.guardarIdDetalle');

     $this->post('/guardar-id-usuarios', 'App\Http\Controllers\Company\CompanyController:guardarIdUsuarios')
     ->add(new AuthMiddleware())
     ->setName('empresas.guardarIdUsuarios');

    // Listado
    $this->get('/{role}', 'App\Http\Controllers\Company\CompanyController:serveList')->add(new AuthMiddleware())->setName('empresas.serveList');
    $this->get('/{role}/list', 'App\Http\Controllers\Company\CompanyController:list')->add(new AuthMiddleware())->setName('empresas.list');
    $this->post('/{role}/filter', 'App\Http\Controllers\Company\CompanyController:filter')->add(new AuthMiddleware())->setName('empresas.filter');
    // Creación
    $this->get('/{role}/nuevo', 'App\Http\Controllers\Company\CompanyController:serveCreate')->add(new AuthMiddleware())->setName('empresas.serveCreate');
    // Edición
    $this->get('/{role}/edicion/{id}', 'App\Http\Controllers\Company\CompanyController:serveEdit')->add(new AuthMiddleware())->setName('empresas.serveEdit');
    // Detalle
    $this->get('/{role}/detalle/{id}', 'App\Http\Controllers\Company\CompanyController:serveDetail')->add(new AuthMiddleware())->setName('empresas.serveDetail');
    $this->get('/{role}/detalle/data/{id}', 'App\Http\Controllers\Company\CompanyController:detail')->add(new AuthMiddleware())->setName('empresas.detail');
    // Edición/Creación
    $this->get('/{role}/{action}/data[/{id}]', 'App\Http\Controllers\Company\CompanyController:editOrCreate')->add(new AuthMiddleware())->setName('empresas.editOrCreate');
    // Edición Perfil
    $this->get('/{role}/{action}/dataPerfil[/{id}]', 'App\Http\Controllers\Company\CompanyController:editProfile')->add(new AuthMiddleware())->setName('empresas.editProfile');
    // Guardar
    $this->post('/{role}/save[/{id}]', 'App\Http\Controllers\Company\CompanyController:store')->add(new AuthMiddleware())->setName('empresas.store');
    // Eliminar
    $this->post('/{role}/delete/{id}', 'App\Http\Controllers\Company\CompanyController:delete')->add(new AuthMiddleware())->setName('empresas.delete');
    // Asociar
    $this->post('/{role}/association', 'App\Http\Controllers\Company\CompanyController:toggleAssociation')->add(new AuthMiddleware())->setName('empresas.toggleAssociation');
    // Perfil
    $this->get('/{role}/perfil/edicion', 'App\Http\Controllers\Company\CompanyController:serveProfileEdit')->add(new AuthMiddleware())->setName('empresas.serveProfileEdit');
    //buscar si eiste oferente
    $this->get('/{role}/{cuit}', 'App\Http\Controllers\Company\CompanyController:searchCuit')->add(new AuthMiddleware())->setName('empresas.searchCuit');
    // usuarios por oferente asociado
    $this->get('/{type}/usuarios/{id}', 'App\Http\Controllers\User\UserController:serveList')->add(new AuthMiddleware())->setName('empresas.usuariosOferentes');
    // lista de usuarios por oferente
    $this->get('/{type}/usuarios/list/{id}', 'App\Http\Controllers\User\UserController:list')->add(new AuthMiddleware())->setName('empresas.usuariosOferentesList');
    // Verificacion de cuit existente (para asociar o no)
    $this->get('/offerer/by-cuit/{cuit}', 'App\Http\Controllers\Company\CompanyController:getOffererByCuit')->add(new AuthMiddleware())->setName('empresas.getOffererByCuit');

});

// MATERIALES DE EMPRESAS
app()->group('/materiales', function () {
    // Listado
    $this->get('', 'App\Http\Controllers\Customer\MaterialController:serveList')->setName('materiales.serveList');
    $this->get('/list', 'App\Http\Controllers\Customer\MaterialController:list')->setName('materiales.list');
    // Creación
    $this->get('/nuevo', 'App\Http\Controllers\Customer\MaterialController:serveCreate')->setName('materiales.serveCreate');
    // Edición
    $this->get('/edicion/{id}', 'App\Http\Controllers\Customer\MaterialController:serveEdit')->setName('materiales.serveEdit');
    // Edición/Creación
    $this->get('/{action}/data[/{id}]', 'App\Http\Controllers\Customer\MaterialController:editOrCreate')->setName('materiales.editOrCreate');
    // Guardar
    $this->post('/save[/{id}]', 'App\Http\Controllers\Customer\MaterialController:store')->setName('materiales.store');
    // Activar/Desactivar
    $this->post('/toggle/{id}', 'App\Http\Controllers\Customer\MaterialController:toggle')->setName('materiales.toggle');
    // Importar
    $this->post('/file/import', 'App\Http\Controllers\Customer\MaterialController:import')->setName('materiales.import');
})->add(new AuthMiddleware(['companies-materials']));

// RUBROS
app()->group('/configuraciones/rubros', function () {
    // Listado
    $this->get('', 'App\Http\Controllers\Configuration\AreaController:serveList')->setName('rubros.serveList');
    $this->get('/list', 'App\Http\Controllers\Configuration\AreaController:list')->setName('rubros.list');
    // Creación
    $this->get('/nuevo', 'App\Http\Controllers\Configuration\AreaController:serveCreate')->setName('rubros.serveCreate');
    // Edición
    $this->get('/edicion/{id}', 'App\Http\Controllers\Configuration\AreaController:serveEdit')->setName('rubros.serveEdit');
    // Edición/Creación
    $this->get('/{action}/data[/{id}]', 'App\Http\Controllers\Configuration\AreaController:editOrCreate')->setName('rubros.editOrCreate');
    // Guardar
    $this->post('/save[/{id}]', 'App\Http\Controllers\Configuration\AreaController:store')->setName('rubros.store');
    // Eliminar
    $this->post('/delete/{id}', 'App\Http\Controllers\Configuration\AreaController:delete')->setName('rubros.delete');

    // SUB-RUBROS
    // Listado
    $this->get('/{parent_id}/subrubros', 'App\Http\Controllers\Configuration\AreaController:serveListSubRubro')->setName('subrubros.serveList');
    $this->get('/{parent_id}/subrubros/list', 'App\Http\Controllers\Configuration\AreaController:listSubRubro')->setName('subrubros.list');
    // Creación
    $this->get('/{parent_id}/subrubros/nuevo', 'App\Http\Controllers\Configuration\AreaController:serveCreateSubRubro')->setName('subrubros.serveCreate');
    // Edición
    $this->get('/{parent_id}/subrubros/edicion/{id}', 'App\Http\Controllers\Configuration\AreaController:serveEditSubRubro')->setName('subrubros.serveEdit');
    // Edición/Creación
    $this->get('/{parent_id}/subrubros/{action}/data[/{id}]', 'App\Http\Controllers\Configuration\AreaController:editOrCreateSubRubro')->setName('subrubros.editOrCreate');
    // Guardar
    $this->post('/{parent_id}/subrubros/save[/{id}]', 'App\Http\Controllers\Configuration\AreaController:storeSubRubro')->setName('subrubros.store');
    // Eliminar
    $this->post('/{parent_id}/subrubros/delete/{id}', 'App\Http\Controllers\Configuration\AreaController:deleteSubRubro')->setName('subrubros.delete');
})->add(new AuthMiddleware(['configurations-areas']));

// UNIDADES
app()->group('/configuraciones/unidades', function () {
    // Listado
    $this->get('', 'App\Http\Controllers\Configuration\MeasurementController:serveList')->setName('unidades.serveList');
    $this->get('/list', 'App\Http\Controllers\Configuration\MeasurementController:list')->setName('unidades.list');
    // Creación
    $this->get('/nuevo', 'App\Http\Controllers\Configuration\MeasurementController:serveCreate')->setName('unidades.serveCreate');
    // Edición
    $this->get('/edicion/{id}', 'App\Http\Controllers\Configuration\MeasurementController:serveEdit')->setName('unidades.serveEdit');
    // Edición/Creación
    $this->get('/{action}/data[/{id}]', 'App\Http\Controllers\Configuration\MeasurementController:editOrCreate')->setName('unidades.editOrCreate');
    // Guardar
    $this->post('/save[/{id}]', 'App\Http\Controllers\Configuration\MeasurementController:store')->setName('unidades.store');
    // Activar/Desactivar
    $this->post('/toggle/{id}', 'App\Http\Controllers\Configuration\MeasurementController:toggle')->setName('unidades.toggle');
})->add(new AuthMiddleware(['configurations-measurements']));

// TIPO CAMBIO
app()->group('/configuraciones/tipocambio', function () {
    // Listado
    $this->get('', 'App\Http\Controllers\Configuration\TipoCambioController:serveList')->setName('tipocambio.serveList');
    $this->get('/list', 'App\Http\Controllers\Configuration\TipoCambioController:list')->setName('tipocambio.list');
})->add(new AuthMiddleware(['configurations-tipocambio']));


// ESTRATEGIA LIBERACION
app()->group('/configuraciones/estrategia-liberacion', function () {
    //Listado
    $this->get('', 'App\Http\Controllers\Configuration\EstrategiaLiberacionController:serveList')->setName('estrategialiberacion.serveList');
    $this->get('/list', 'App\Http\Controllers\Configuration\EstrategiaLiberacionController:list')->setName('estrategialiberacion.list');
    //Nuevo
    $this->get('/create', 'App\Http\Controllers\Configuration\EstrategiaLiberacionController:serveCreate')->setName('estrategialiberacion.serveCreate');
})->add(new AuthMiddleware(['configurations-estrategia-liberacion']));

// CATALOGOS
app()->group('/configuraciones/catalogos', function () {
    // Listado
    $this->get('', 'App\Http\Controllers\Configuration\CatalogoController:serveList')->setName('catalogos.serveList');
    $this->get('/list', 'App\Http\Controllers\Configuration\CatalogoController:list')->setName('catalogos.list');
    // Creación
    $this->get('/nuevo', 'App\Http\Controllers\Configuration\CatalogoController:serveCreate')->setName('catalogos.serveCreate');
    // Edición
    $this->get('/edicion/{id}', 'App\Http\Controllers\Configuration\CatalogoController:serveEdit')->setName('catalogos.serveEdit');
    // Edición/Creación
    $this->get('/{action}/data[/{id}]', 'App\Http\Controllers\Configuration\CatalogoController:editOrCreate')->setName('catalogos.editOrCreate');
    // Guardar
    $this->post('/save[/{id}]', 'App\Http\Controllers\Configuration\CatalogoController:store')->setName('catalogos.store');
    // Activar/Desactivar
    $this->post('/toggle/{id}', 'App\Http\Controllers\Configuration\CatalogoController:toggle')->setName('catalogos.toggle');
})->add(new AuthMiddleware(['configurations-catalogs']));

// Consultas (Mesa de ayuda)
app()->group('/consultas', function () {
    // Creación
    $this->get('/nuevo', 'App\Http\Controllers\Consultas\ConsultaController:serveCreate')->setName('consultas.serveCreate');
    // Enviar
    $this->post('/send', 'App\Http\Controllers\Consultas\ConsultaController:send')->setName('consultas.send');
})->add(new AuthMiddleware());

// reportes
app()->group('/reportes', function () {
    // listado
    $this->get('/adjudicados', 'App\Http\Controllers\Reports\ReportsController:serveListAdj')->setName('reports.serveListAdj');
    $this->get('/adjudicados/list', 'App\Http\Controllers\Reports\ReportsController:listAdj')->setName('reports.listAdj');
    $this->post('/adjudicados/filter', 'App\Http\Controllers\Reports\ReportsController:filterAdj')->setName('reports.filterAdj');

    $this->get('/evaluados', 'App\Http\Controllers\Reports\ReportsController:serveListEval')->setName('reports.serveListEval');
    $this->get('/evaluados/list', 'App\Http\Controllers\Reports\ReportsController:listEval')->setName('reports.listEval');
    $this->post('/evaluados/filter', 'App\Http\Controllers\Reports\ReportsController:filterEval')->setName('reports.filterEval');
})->add(new AuthMiddleware());

// Tutoriales

app()->group('/tutoriales', function () {
    $this->get('', 'App\Http\Controllers\Tutorials\TutorialController:serveList')->setName('tutoriales.serveList');
    $this->get('/list', 'App\Http\Controllers\Tutorials\TutorialController:list')->setName('tutoriales.list');
    $this->post('/edit', 'App\Http\Controllers\Tutorials\TutorialController:edit')->setName('tutoriales.edit');
    $this->post('/new', 'App\Http\Controllers\Tutorials\TutorialController:store')->setName('tutoriales.store');
    $this->post('/delete', 'App\Http\Controllers\Tutorials\TutorialController:delete')->setName('tutoriales.delete');

})->add(new AuthMiddleware());