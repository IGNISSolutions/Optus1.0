<template id="chat-template" data-bind="if: isLoaded()">
    {capture 'post_scripts_child'}
        <script>
            $(function() {
                $('.slimScrollDiv').slimScroll({
                    alwaysVisible: true,
                    color: '#00f',
                    height: '500px',
                    wheelStep: 30
                });
            });
        </script>
        <script src="{asset('/global/plugins/jquery.pulsate.min.js')}" type="text/javascript"></script>
        <script src="{asset('/global/scripts/xlsx-js-style/dist/xlsx.bundle.js')}" type="text/javascript"></script>
    {/capture}
    {$post_scripts_child[] = $smarty.capture.post_scripts_child scope="global"}

    <!-- ko if: showListChat -->
    {include file='concurso/detail/partials/chat/listChat.tpl'}
    <!-- /ko -->

    <!-- ko if: showNewChat -->
    {include file='concurso/detail/partials/chat/newChat.tpl'}
    <!-- /ko -->

    <!-- ko if: showChat -->
    {include file='concurso/detail/partials/chat/viewChat.tpl'}
    <!-- /ko -->

</template>

<script type="text/javascript">
    ko.components.register('chat-component', {
        viewModel: function(params) {
            var tipos = [{
                    'id': 'tecnica',
                    'text': 'Técnica'
                },
                {
                    'id': 'comercial',
                    'text': 'Comercial'
                },
                {
                    'id': 'aviso',
                    'text': 'Aviso'
                }
            ];

            var categorias = [{
                    'id': 'si',
                    'text': 'Si'
                },
                {
                    'id': 'no',
                    'text': 'No'
                },
            ];

            var estados = [{
                    'id': 2,
                    'text': 'Por Aprobar'
                },
                {
                    'id': 1,
                    'text': 'Aprobadas'
                },
                {
                    'id': 'leidas',
                    'text': 'Leidas'
                },
                {
                    'id': 'no_leidas',
                    'text': 'No Leidas'
                },
                {
                    'id': 3,
                    'text': 'Rechazada'
                },
            ];

            var self = this;
            this.showNewChat = ko.observable(false);
            this.showListChat = ko.observable(true);
            this.showChat = ko.observable(false);
            this.MessageSelected = ko.observable(null);
            this.IdConcurso = ko.observable(params.IdConcurso());
            this.IsClient = ko.observable(params.IsClient());
            this.IsProv = ko.observable(params.IsProv());
            this.Enabled = ko.observable(false);
            this.Messages = ko.observableArray([]);
            this.NewMessage = ko.observable(null);
            this.DatosConcurso = ko.observable(null);
            this.NewResp = ko.observable(null);
            this.HasNewMessage = ko.observable(false);
            this.Tipos = ko.observable(tipos); //tecnica o comercial
            this.Categorias = ko.observable(categorias); //todas, respondidas, sin responder
            this.Estados = ko.observable(estados); //sin aprob, aprobadas, rechazadas
            this.TipoSelected = ko.observable(null);
            this.CategoriaSelected = ko.observable(null);
            this.EstadoSelected = ko.observable(null);
            this.Path = ko.observable(null);
            this.ActiveView = ko.observable(null);
            this.ChatEnable = ko.observable(params.ChatEnable());
            this.MensajeIndividual = ko.observable('no')
            this.Proveedores = ko.observable([])
            this.Proveedor = ko.observable(null)
            this.MensajesPorProv = ko.observable([])
            this.MensajeProvSelected = ko.observable(null)

            self.TipoSelected.subscribe((value) => {
                if (!first_time) getList();
            })

            self.CategoriaSelected.subscribe((value) => {
                if (!first_time) getList();
            })

            self.EstadoSelected.subscribe((value) => {
                if (!first_time) getList();
            })

            self.MensajeProvSelected.subscribe((value) => {
                if (!first_time) getList();
            })

            this.FechaHoy = ko.observable(params.FechaHoy())
            this.HoraHoy = ko.observable(params.HoraHoy())
            this.CierreMuroConsultasHora = ko.observable(params.CierreMuroConsultasHora())
            this.CierreMuroConsultas = ko.observable(params.CierreMuroConsultas())
            
            this.EsHoraDeEnviarMensaje = ko.computed(() => {
                const fechaHoyStr = this.FechaHoy(); // "28-07-2025"
                const horaHoyStr = this.HoraHoy();   // "10:04:24"
                const fechaCierreStr = this.CierreMuroConsultas(); // "30-07-2025"
                const horaCierreStr = this.CierreMuroConsultasHora(); // "13:00:00"

                if (!fechaHoyStr || !horaHoyStr || !fechaCierreStr || !horaCierreStr) {
                    return false;
                }

                const fechaHoyParts = fechaHoyStr.split('-');
                const fechaHoy = new Date(
                    fechaHoyParts[2] + '-' + fechaHoyParts[1] + '-' + fechaHoyParts[0] + 'T' + horaHoyStr
                );

                const fechaCierreParts = fechaCierreStr.split('-');
                const fechaCierre = new Date(
                    fechaCierreParts[2] + '-' + fechaCierreParts[1] + '-' + fechaCierreParts[0] + 'T' + horaCierreStr
                );  
                
                return fechaHoy < fechaCierre;
            });

            var Mensaje = function(data) {
                var self = this;
                this.id = ko.observable(data.id);
                this.UserId = ko.observable(data.UserId);
                this.usuario = ko.observable(data.usuario);
                this.usuario_imagen = ko.observable(data.usuario_imagen);
                this.concurso = ko.observable(data.concurso);
                this.fecha = ko.observable(data.fecha);
                this.mensaje = ko.observable(data.mensaje);
                this.estado = ko.observable(data.estado);
                this.is_admin = ko.observable(data.is_admin);
                this.tipo_name = ko.observable(data.tipo_name);
                this.tipo_pregunta = ko.observable(data.tipo_pregunta);
                this.respondida = ko.observable(data.respondida);
                this.remitente = ko.observable(data.usuario);
                this.leido = ko.observable(data.messageRead);
                this.answerLeido = ko.observable(data.answerRead);
                this.respuestas = ko.observableArray([]);
                if (data.respuestas.length > 0) {
                    data.respuestas.forEach(item => {
                        self.respuestas.push(new Respuesta(item));
                    });
                }
                this.filename = ko.observable(new File(data.id, data.filename));
                this.type_id = ko.observable(data.type_id);
                this.type_name = ko.observable(data.type_name);
                this.to = ko.observable(data.to);
                this.date = ko.observable(data.date);
            }

            var Respuesta = function(data) {
                var self = this;
                this.id = ko.observable(data.id);
                this.UserId = ko.observable(data.UserId);
                this.usuario = ko.observable(data.usuario);
                this.usuario_imagen = ko.observable(data.usuario_imagen);
                this.concurso = ko.observable(data.concurso);
                this.fecha = ko.observable(data.fecha);
                this.mensaje = ko.observable(data.mensaje);
                this.estado = ko.observable(data.estado);
                this.is_admin = ko.observable(data.is_admin);
                this.tipo_name = ko.observable(data.tipo_name);
                this.respondida = ko.observable(data.respondida);
                this.filename = ko.observable(new File(data.id, data.filename));
                this.date = ko.observable(data.date);
            }

            var File = function(id, filename) {
                var self = this;
                this.id = ko.observable(id);
                this.filename = ko.observable(filename);
                this.action = ko.observable(null);
            }

            var RespuestaNueva = function(data) {
                var self = this;
                this.message = ko.observable(data.nuevaResp);
                this.file = ko.observable(new File(data.id, data.filename));
            }

            var MensajeNuevo = function(data) {
                var self = this;
                this.message = ko.observable(data.nuevoMensaje);
                this.file = ko.observable(new File(data.id, data.filename));
            }

            this.NewChat = function(show) {
                data = {
                    'id': 0,
                    'nuevoMensaje': null,
                    'filename': null
                }
                self.NewMessage(new MensajeNuevo(data))

                self.showNewChat(true)
                self.showListChat(false)

            };

            this.ListChat = function(show) {
                getList()
                self.showChat(false)
                self.showNewChat(false)
                self.showListChat(true)
                self.ActiveView('chatList')
            };
            
            self.searchMessage = ko.observable("");

            self.filteredMessages = ko.computed(function () {
                const query = self.searchMessage().toLowerCase();
                return query
                    ? ko.utils.arrayFilter(self.Messages(), function (msg) {
                        const texto = msg.mensaje && msg.mensaje().toLowerCase();
                        return texto && texto.includes(query);
                    })
                    : self.Messages();
            });

            this.Chat = function(mensaje) {
                data = {
                    'id': 0,
                    'nuevaResp': null,
                    'filename': null
                }
                self.NewResp(new RespuestaNueva(data))
                getMessage(mensaje);
            };

            this.filename = ko.observable();

            this.cleanFilters = () => {
                self.TipoSelected(null);
                self.CategoriaSelected(null);
                self.EstadoSelected(null);
                self.MensajeProvSelected(null);
                getList()
            }

            this.checkRead = () => {
                var url = '/concursos/chat/check';
                var data = {
                    IdConcurso: self.IdConcurso()
                }
                Services.Post(url, {
                        UserToken: User.Token,
                        Data: JSON.stringify(ko.toJS(data))
                    },
                    (response) => {
                        if (response.success) {
                            self.HasNewMessage(response.data.new_messages);
                        }
                    },
                    (error) => {
                        swal('Error', error.message, 'error');
                    },
                    null,
                    null
                );
            }

            this.sendMessage = (parent) => {
                $.blockUI();
                var url = '/concursos/chat/store';
                if (self.MensajeIndividual() == 'si' && !self.Proveedor()) {
                    $.unblockUI();
                    swal('Error', "Debe Seleccionar un proveedor", 'error');
                    return;
                }
                // Muro inicia el cliente
                if (parent == 0 && self.IsClient()) {
                    var data = {
                        IdConcurso: self.IdConcurso(),
                        Message: self.NewMessage(),
                        Parent: 0,
                        Tipo: 'aviso',
                        MensajeIndividual: self.MensajeIndividual(),
                        Proveedor: self.Proveedor()
                    }
                    Services.Post(url, {
                            UserToken: User.Token,
                            Data: JSON.stringify(ko.toJS(data))
                        },
                        (response) => {
                            $.unblockUI();
                            if (response.success) {
                                let dataWS = {
                                    'tipo': self.IsClient() ?
                                        'newMessageClient' : 'newMessageProv',
                                    'concurso_id': self.IdConcurso()
                                }
                                currentChatConn.send(JSON.stringify(dataWS));
                                self.ListChat()
                                self.MensajeIndividual('no')
                                self.Proveedor(null)
                            }
                        },
                        (error) => {
                            $.unblockUI();
                            swal('Error', error.message, 'error');
                        },
                        null,
                        null
                    );
                } else if (parent == 0 && self.IsProv()) { //proveedor inicia pregunta
                    swal({
                            title: "Seleccione tipo de pregunta",
                            type: "info",
                            showCancelButton: true,
                            confirmButtonClass: "green",
                            cancelButtonClass: "blue",
                            confirmButtonText: "Comercial",
                            cancelButtonText: "Técnica",
                            closeOnConfirm: true,
                            closeOnCancel: true
                        },
                        function(isConfirm) {
                            if (isConfirm) {
                                var data = {
                                    IdConcurso: self.IdConcurso(),
                                    Message: self.NewMessage(),
                                    Parent: 0,
                                    Tipo: 'comercial'
                                }
                                Services.Post(url, {
                                        UserToken: User.Token,
                                        Data: JSON.stringify(ko.toJS(data))
                                    },
                                    (response) => {
                                        $.unblockUI();
                                        if (response.success) {
                                            let dataWS = {
                                                'tipo': self.IsClient() ?
                                                    'newMessageClient' : 'newMessageProv',
                                                'concurso_id': self.IdConcurso()
                                            }
                                            currentChatConn.send(JSON.stringify(dataWS));
                                            self.ListChat()
                                        }
                                    },
                                    (error) => {
                                        $.unblockUI();
                                        swal('Error', error.message, 'error');
                                    },
                                    null,
                                    null
                                );
                            } else {
                                var data = {
                                    IdConcurso: self.IdConcurso(),
                                    Message: self.NewMessage(),
                                    Parent: 0,
                                    Tipo: 'tecnica'
                                }
                                Services.Post(url, {
                                        UserToken: User.Token,
                                        Data: JSON.stringify(ko.toJS(data))
                                    },
                                    (response) => {
                                        $.unblockUI();
                                        if (response.success) {
                                            let dataWS = {
                                                'tipo': self.IsClient() ?
                                                    'newMessageClient' : 'newMessageProv',
                                                'concurso_id': self.IdConcurso()
                                            }
                                            currentChatConn.send(JSON.stringify(dataWS));
                                            self.ListChat()
                                        }
                                    },
                                    (error) => {
                                        $.unblockUI();
                                        swal('Error', error.message, 'error');
                                    },
                                    null,
                                    null
                                );
                            }
                        }
                    );
                } else {
                    var data = {
                        IdConcurso: self.IdConcurso(),
                        Message: self.NewResp(),
                        Parent: parent(),
                        Tipo: 'respuesta'
                    }
                    Services.Post(url, {
                            UserToken: User.Token,
                            Data: JSON.stringify(ko.toJS(data))
                        },
                        (response) => {
                            $.unblockUI();
                            if (response.success) {
                                data = {
                                    'id': 0,
                                    'nuevaResp': null,
                                    'filename': null
                                }
                                self.NewResp(new RespuestaNueva(data))
                                getMessage(parent());
                                let dataWS = {
                                    'tipo': self.IsClient() ?
                                        'newRespClient' : 'newRespProv',
                                    'concurso_id': self.IdConcurso(),
                                    'parent': parent()
                                }
                                currentChatConn.send(JSON.stringify(dataWS));
                            }
                        },
                        (error) => {
                            $.unblockUI();
                            swal('Error', error.message, 'error');
                        },
                        null,
                        null
                    );
                }
            }

            this.approveOrReject = (message_id, action) => {
                $.blockUI();
                var url = '/concursos/chat/approveOrReject';
                var data = {
                    IdConcurso: self.IdConcurso(),
                    IdMessage: message_id,
                    Action: action
                }
                Services.Post(url, {
                        UserToken: User.Token,
                        Data: JSON.stringify(ko.toJS(data))
                    },
                    (response) => {
                        $.unblockUI();
                        if (response.success) {
                            let dataWS = {
                                'tipo': 'newMessageProvApproved',
                                'concurso_id': self.IdConcurso(),
                                'parent': response.parent
                            }
                            currentChatConn.send(JSON.stringify(dataWS));
                            getMessage(response.parent);
                        }
                    },
                    (error) => {
                        $.unblockUI();
                        swal('Error', error.message, 'error');
                    },
                    null,
                    null
                );
            }

            this.toggleRead = (message) => {
                var url = '/concursos/chat/toggleRead';
                var data = {
                    IdConcurso: self.IdConcurso(),
                    Message: message
                }
                Services.Post(url, {
                        UserToken: User.Token,
                        Data: JSON.stringify(ko.toJS(data))
                    },
                    (response) => {
                        if (response.success) {
                            self.HasNewMessage(false);
                        }
                    },
                    (error) => {},
                    null,
                    null
                );
            }

            this.expandtoggleRead = () => {
                var display = $("#chats").css("display");
                if (display == "none") {
                    $("#chatsDetaill").removeClass("expand");
                    $("#chatsDetaill").addClass("collapse");
                    $("#chats").css("display", "block");

                    var scrollDiv = $('.slimScrollDiv');
                    var scrollHeight = scrollDiv.prop('scrollHeight');
                    scrollDiv.scrollTop(scrollHeight);

                } else {
                    $("#chatsDetaill").removeClass("collapse");
                    $("#chatsDetaill").addClass("expand");
                    $("#chats").css("display", "none");
                }
            }

            this.getListOnly = (mensaje) => {
                var focusedElement = $(':focus');
                var focusedElementId = focusedElement.attr('id');
                var url = '/concursos/chat/list';
                var data = {
                    IdConcurso: self.IdConcurso()
                }
                Services.Post(url, {
                        UserToken: User.Token,
                        Data: JSON.stringify(ko.toJS(data))
                    },
                    (response) => {
                        if (response.success) {
                            if (response.data.list.length > 0) {
                                self.Messages([]);
                                response.data.list.forEach(item => {
                                    self.Messages.push(new Mensaje(item));
                                });
                                self.Chat(mensaje);
                            }
                            self.DatosConcurso(response.data.concurso);
                            self.Enabled(response.data.enabled);
                            // self.applyFilters()
                            $('#' + focusedElementId).focus();
                            var scrollDiv = $('.slimScrollDiv');
                            var scrollHeight = scrollDiv.prop('scrollHeight');
                            scrollDiv.scrollTop(scrollHeight);

                        }
                    },
                    (error) => {},
                    null,
                    null
                );
            }

            var getList = (is_pregunta = false, pregunta_id = 0) => {
                var url = '/concursos/chat/list';
                var data = {
                    IdConcurso: self.IdConcurso(),
                    tipo: self.TipoSelected(),
                    categoria: self.CategoriaSelected(),
                    estado: self.EstadoSelected(),
                    tiposPreguntas: self.MensajeProvSelected()
                }
                Services.Post(url, {
                        UserToken: User.Token,
                        Data: JSON.stringify(ko.toJS(data))
                    },
                    (response) => {
                        if (response.success) {
                            if (response.data.list.length > 0) {
                                self.Messages([]);
                                response.data.list.forEach(item => {
                                    self.Messages.push(new Mensaje(item));
                                });
                            } else {
                                self.Messages([]);
                            }
                            self.DatosConcurso(response.data.concurso);
                            self.Enabled(response.data.enabled);
                            self.Path(response.data.filepath);
                            self.Proveedores(response.data.proveedores);
                            self.MensajesPorProv([{
                                    'id': 'all',
                                    'text': 'Grupal'
                                },
                                ...response.data.proveedores
                            ])
                            if (first_time) {
                                first_time = false;
                                self.checkRead();
                            } else {
                                var scrollDiv = $('.slimScrollDiv');
                                var scrollHeight = scrollDiv.prop('scrollHeight');
                                scrollDiv.scrollTop(scrollHeight);
                            }
                            if (is_pregunta) {
                                self.ListChat(true);
                            }

                            if (!is_pregunta && pregunta_id > 0) {
                                self.Chat(pregunta_id)
                            }

                        }
                    },
                    (error) => {},
                    null,
                    null
                );
            }

            this.exportMuro = async () => {
                var datosConcurso = self.DatosConcurso()
                var messages = self.Messages()
                var wb = XLSX.utils.book_new();
                wb.Props = {
                    Title: "Reporte Muro de consultas concurso No " + datosConcurso.idConcurso,
                    Subject: "Reporte Muro de consultas",
                    Author: "Optus",
                    CreatedDate: new Date()
                };
                var headerPreguntas = datosConcurso.muroTipo != 'Proveedor' ? [
                    'Nº',
                    'Tipo',
                    'Pregunta',
                    'Fecha',
                    'Usuario',
                    'Tipo usuario',
                    // 'Respuesta',
                    // 'Respondido por',
                    // 'Fecha',
                    // 'Usuario'
                ] : [
                    'Nº',
                    'Tipo',
                    'Pregunta',
                    'Fecha',
                    // 'Respuesta',
                ]

                wb.SheetNames.push("Preguntas");
                var ws_preguntas = [
                    ['Muro de Consultas'],
                    ['Concurso Nº', datosConcurso.idConcurso],
                    ['Nombre licitación', datosConcurso.nombre],
                    ['Cliente', datosConcurso.cliente],
                    ['Comprador', datosConcurso.comprador],
                    [],
                    [],
                    headerPreguntas
                ];
                messages.forEach((message) => {
                    const vItem = datosConcurso.muroTipo != 'Proveedor' ? [
                            message.id(),
                            message.tipo_pregunta(),
                            message.mensaje(),
                            message.date(),
                            message.usuario(),
                            message.tipo_name()
                        ] : [
                            message.id(),
                            message.tipo_pregunta(),
                            message.mensaje(),
                            message.date()
                        ];
                        ws_preguntas.push(vItem)
                    if (message.respondida() == 'Si') {
                        const vItemResp = [
                            "Respuestas Mensaje nº " + message.id() 
                        ]
                        ws_preguntas.push(vItemResp)

                        const headerResponse = datosConcurso.muroTipo != 'Proveedor' ? [
                            '',
                            'Respuesta',
                            'Respondido por',
                            'Fecha',
                            'Usuario'
                        ]:[
                            '',
                            'Respuesta',
                            'Respondido por',
                            'Fecha',
                        ]

                        ws_preguntas.push(headerResponse)


                         message.respuestas().forEach((respuesta) => {
                            const vItem = datosConcurso.muroTipo != 'Proveedor' ? [
                                '',
                                respuesta.mensaje(),
                                respuesta.tipo_name(),
                                respuesta.date(),
                                respuesta.usuario()
                            ] : [
                                '',
                                respuesta.mensaje(),
                                respuesta.tipo_name(),
                                respuesta.date(),
                            ];
                            ws_preguntas.push(vItem)
                        })

                        ws_preguntas.push([])
                    }else{
                        []
                    }
                        
                    
                    // } else {
                    //     const vItem = datosConcurso.muroTipo != 'Proveedor' ? [
                    //         message.id(),
                    //         message.tipo_pregunta(),
                    //         message.mensaje(),
                    //         message.fecha(),
                    //         message.usuario(),
                    //         message.tipo_name(),
                    //     ] : [
                    //         message.id(),
                    //         message.tipo_pregunta(),
                    //         message.mensaje(),
                    //     ];
                    //     ws_preguntas.push(vItem)
                    // }
                })
                var wsPreguntas = XLSX.utils.aoa_to_sheet(ws_preguntas);


                wb.Sheets["Preguntas"] = wsPreguntas;

                XLSX.writeFile(wb, 'Muro_Consultas.xlsx');
            }

            var muroCliente = (message) => {
                let vItem = [
                    message.id,
                    message.tipo_pregunta,
                    message.mensaje,
                    message.fecha,
                    message.usuario,
                    message.tipo_name,
                    'N/A',
                    'N/A',
                    'N/A',
                    'N/A'
                ]
                if (message.respondida == 'Si') {
                    const respuestas = message.respuestas;
                    respuestas.forEach((respuesta) => {
                        preguntas.push(
                            message.id,
                            message.tipo_pregunta,
                            message.mensaje,
                            message.fecha,
                            message.usuario,
                            message.tipo_name
                        )
                    });
                }
                return vItem

            }

            var muroProveedor = (message) => {
                return [
                    message.id,
                    message.tipo_pregunta,
                    message.mensaje,
                ]
            }

            this.downloadAdj = function(path, type = null, id = null, ) {
                $.blockUI();
                var data = {
                    Id: id,
                    Type: type,
                    Path: path,
                };
                var url = '/media/file/download';

                Services.Post(url, {
                        UserToken: User.Token,
                        Entity: JSON.stringify(ko.toJS(data))
                    },
                    (response) => {
                        $.unblockUI();
                        if (response.success) {
                            window.open(response.data.public_path);
                        } else {
                            swal('Error', response.message, 'error');
                        }
                    },
                    (error) => {
                        $.unblockUI();
                        swal('Error', error.message, 'error');
                    },
                    null,
                    null
                );
            }

            self.ActiveView.subscribe((value) => {
                ChatSocket(value);
            })

            var currentChatConn = null;
            var ChatSocket = function(view) {
                const concurso_id = params.IdConcurso();
                var query = '?concurso_id=' + concurso_id + '&user_id=' + User.Id +
                    '&vista=' + view + '&isClient=' + self.IsClient();
                var path = 'wss://' + location.host + '/wss/chat';
                var chatConn = new WebSocket(path + query);

                if (currentChatConn !== null) {
                    // Cierra la conexión existente antes de establecer una nueva
                    currentChatConn.close();
                }

                currentChatConn = chatConn;

                currentChatConn.onopen = function(e) {

                };

                currentChatConn.onclose = function(e) {

                };

                currentChatConn.onerror = function(e) {

                };

                currentChatConn.onmessage = function(e) {
                    data = JSON.parse(e.data)

                    switch (data.tipo) {
                        case 'newMessageClient':
                            if (self.showListChat()) getList(true);
                            break;
                        case 'newMessageProvApproved':
                        case 'newRespClient':
                            if (self.showListChat()) getList(true);
                            else if (self.showChat()) getMessage(data.parent)
                            break;
                        case 'newMessageProv':
                        case 'newRespProv':
                            if (self.showListChat() && self.IsClient()) getList(true);
                            else if (self.showChat() && self.IsClient()) getMessage(data.parent)
                            break;
                    }
                };


            }

            self.MessageSelectedFormatted = ko.computed(function () {
                if (!self.MessageSelected() || !self.MessageSelected().mensaje) return '';
                return self.MessageSelected().mensaje().replace(/\n/g, '<br>');
            });

            self.formatMessageWithLineBreaks = function (mensaje) {
                return mensaje ? mensaje.replace(/\n/g, '<br>') : '';
            };

            var getMessage = function(parent_id) {
                var url = '/concursos/chat/message';
                var data = {
                    IdConcurso: self.IdConcurso(),
                    parent_id: parent_id
                }
                Services.Post(url, {
                        UserToken: User.Token,
                        Data: JSON.stringify(ko.toJS(data))
                    },
                    (response) => {
                        if (response.success) {
                            let message = new Mensaje(response.data.selectedMessage)
                            self.MessageSelected(message);
                            self.showListChat(false)
                            self.showNewChat(false)
                            self.showChat(true)
                            self.ActiveView('chatView')
                            self.toggleRead(message);
                        }
                    },
                    (error) => {},
                    null,
                    null
                );
            }


            self.ActiveView('chatList');

            var first_time = true;
            getList();
        },
        template: {
            element: 'chat-template'
        },
    });

    // Chrome allows you to debug it thanks to this
    {chromeDebugString('dynamicScriptChat')}
</script>