{extends 'index.tpl'}

{block 'title'}
    Estrategia de Liberación
{/block}

{block 'styles'}
<style>
    .estrategia-table {
        border-collapse: collapse;
        margin-top: 20px;
        width: 100%;
    }
    .estrategia-table th, .estrategia-table td {
        border: 1px solid #ddd;
        padding: 12px;
        text-align: center;
        vertical-align: middle;
    }
    .estrategia-table th {
        background-color: #364150;
        color: #ffffff;
        font-weight: bold;
    }
    .estrategia-table th.header-group {
        background-color: #7b8aa0;
    }
    .estrategia-table tbody tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    .estrategia-table tbody tr:hover {
        background-color: #f1f1f1;
    }
    .monto-input {
        width: 100px;
        text-align: right;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }
    .monto-input:focus {
        border-color: #26c281;
        outline: none;
        box-shadow: 0 0 5px rgba(38, 194, 129, 0.3);
    }
    .nivel-label {
        font-weight: 500;
        color: #555;
    }
    .nivel-disabled {
        color: #bbb;
        font-style: italic;
    }
    .btn-guardar-estrategia {
        margin-top: 20px;
        padding: 10px 30px;
    }
    .currency-symbol {
        font-weight: bold;
        color: #333;
    }
    .monto-input:disabled {
        background-color: #eee;
        cursor: not-allowed;
        color: #666;
    }
    .row-disabled {
        background-color: #f5f5f5 !important;
    }
    .onoffswitch {
        margin: 0 auto;
    }
    .status-container {
        margin-top: 20px;
        padding: 15px;
        background-color: #f9f9f9;
        border-radius: 4px;
        border: 1px solid #ddd;
    }
    .status-label {
        font-weight: bold;
        font-size: 16px;
        color: #333;
    }
    .status-value {
        font-weight: bold;
        font-size: 16px;
        margin-left: 10px;
    }
    .status-habilitada {
        color: #26c281;
    }
    .status-deshabilitada {
        color: #e7505a;
    }
</style>
{/block}

{block 'main'}
<div class="row">
    <div class="col-md-12">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption">
                    <i class="icon-layers font-green"></i>
                    <span class="caption-subject font-green bold uppercase">Configuración de Niveles de Aprobación</span>
                </div>
            </div>
            <!-- Status de la estrategia -->
                <div class="status-container">
                    <span class="status-label">Estado de la Estrategia:</span>
                    <span class="status-value status-deshabilitada" id="statusEstrategia">Deshabilitada</span>
                </div>
            <div class="portlet-body">
                <table class="estrategia-table">
                    <thead>
                        <tr>
                            <th rowspan="2">Nivel de aprobación<br><small>(Descripción)</small> </th>
                            <th rowspan="2">Monto<br><small>(Dólares)</small></th>
                            <th colspan="2" class="header-group">Estrategia de compras</th>
                            <th colspan="2" class="header-group">Estrategia del solicitante</th>
                        </tr>
                        <tr>
                            <th>Selección de nivel</th>
                            <th>Aprobadores Área de Compras</th>
                            <th>Selección de nivel</th>
                            <th>Aprobadores Área Solicitante</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Nivel 0 - Comprador -->
                        <tr class="row-disabled">
                            <td>
                                <span class="nivel-label">Ordenes de compra menores a:</span>
                            </td>
                            <td>
                                <span class="currency-symbol">$</span>
                                <input type="text" class="monto-input" id="monto_nivel_0" value="1.000" data-nivel="0" disabled>
                            </td>
                            <td>
                                <span class="nivel-disabled">-</span>
                            </td>
                            <td>
                                <span class="nivel-label">Comprador</span>
                            </td>
                            <td>
                                <span class="nivel-disabled">-</span>
                            </td>
                            <td>
                                <span class="nivel-disabled">-</span>
                            </td>
                        </tr>
                        <!-- Nivel 1 -->
                        <tr class="row-disabled">
                            <td>
                                <span class="nivel-label">Ordenes de compra mayores iguales a:</span>
                            </td>
                            <td>
                                <span class="currency-symbol">$</span>
                                <input type="text" class="monto-input" id="monto_nivel_1" value="0" data-nivel="1" disabled>
                            </td>
                            <td>
                                <div class="onoffswitch">
                                    <input type="checkbox" class="onoffswitch-checkbox" id="switch_compras_1" data-nivel="1">
                                    <label class="onoffswitch-label" for="switch_compras_1">
                                        <span class="onoffswitch-inner"></span>
                                        <span class="onoffswitch-switch"></span>
                                    </label>
                                </div>
                            </td>
                            <td>
                                <span class="nivel-label">Jefe de Compras</span>
                            </td>
                            <td>
                                <div class="onoffswitch">
                                    <input type="checkbox" class="onoffswitch-checkbox" id="switch_solicitante_1" data-nivel="1">
                                    <label class="onoffswitch-label" for="switch_solicitante_1">
                                        <span class="onoffswitch-inner"></span>
                                        <span class="onoffswitch-switch"></span>
                                    </label>
                                </div>
                            </td>
                            <td>
                                <span class="nivel-label">Jefe de Área Solicitante</span>
                            </td>
                        </tr>
                        <!-- Nivel 2 -->
                        <tr class="row-disabled">
                            <td>
                                <span class="nivel-label">Ordenes de compra mayores iguales a:</span>
                            </td>
                            <td>
                                <span class="currency-symbol">$</span>
                                <input type="text" class="monto-input" id="monto_nivel_2" value="0" data-nivel="2" disabled>
                            </td>
                            <td>
                                <div class="onoffswitch">
                                    <input type="checkbox" class="onoffswitch-checkbox" id="switch_compras_2" data-nivel="2">
                                    <label class="onoffswitch-label" for="switch_compras_2">
                                        <span class="onoffswitch-inner"></span>
                                        <span class="onoffswitch-switch"></span>
                                    </label>
                                </div>
                            </td>
                            <td>
                                <span class="nivel-label">Gerente de Compra</span>
                            </td>
                            <td>
                                <div class="onoffswitch">
                                    <input type="checkbox" class="onoffswitch-checkbox" id="switch_solicitante_2" data-nivel="2">
                                    <label class="onoffswitch-label" for="switch_solicitante_2">
                                        <span class="onoffswitch-inner"></span>
                                        <span class="onoffswitch-switch"></span>
                                    </label>
                                </div>
                            </td>
                            <td>
                                <span class="nivel-label">Gerente de Área Solicitante</span>
                            </td>
                        </tr>
                        <!-- Nivel 3 - Gerente General (solo una columna) -->
                        <tr class="row-disabled">
                            <td>
                                <span class="nivel-label">Ordenes de compra mayores iguales a:</span>
                            </td>
                            <td>
                                <span class="currency-symbol">$</span>
                                <input type="text" class="monto-input" id="monto_nivel_3" value="0" data-nivel="3" disabled>
                            </td>
                            <td>
                                <span class="nivel-disabled">-</span>
                            </td>
                            <td>
                                <span class="nivel-disabled">-</span>
                            </td>
                            <td>
                                <div class="onoffswitch">
                                    <input type="checkbox" class="onoffswitch-checkbox" id="switch_solicitante_3" data-nivel="3">
                                    <label class="onoffswitch-label" for="switch_solicitante_3">
                                        <span class="onoffswitch-inner"></span>
                                        <span class="onoffswitch-switch"></span>
                                    </label>
                                </div>
                            </td>
                            <td>
                                <span class="nivel-label">Gerente General</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <!-- Mensaje informativo sobre cómo funciona la estrategia -->
                <div class="alert alert-info" style="margin-top: 20px;">
                    <i class="fa fa-info-circle"></i>
                    <strong>¿Cómo funciona la estrategia de liberación?</strong>
                    <ul style="margin-top: 10px; margin-bottom: 5px;">
                        <li><strong>Para activar la estrategia:</strong> Active al menos un nivel de aprobación usando los switches y establezca el monto mínimo (umbral) a partir del cual se requerirá dicha aprobación.</li>
                        <li><strong>Para desactivar la estrategia:</strong> Desactive todos los switches de todos los niveles. El estado cambiará a <span style="color: #e7505a; font-weight: bold;">Deshabilitada</span> y las órdenes de compra no requerirán aprobación adicional.</li>
                    </ul>
                    <hr style="margin: 10px 0;">
                    <strong>Aclaración:</strong>
                    <ul style="margin-top: 5px; margin-bottom: 5px;">
                        <li>Por favor verifique que con usuarios dados de alta en la plataforma con roles de <strong>Jefe</strong> y <strong>Gerente</strong> de las areas de compra y areas solicitantes (Administración, Logística, Producción, etc)</li>
                    </ul>
                </div>
                <div class="text-right" style="margin-top: 20px;">
                    <button type="button" class="btn btn-success btn-guardar-estrategia" id="btnGuardarEstrategia">
                        <i class="fa fa-save"></i> Guardar Estrategia
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
{/block}

{block 'knockout' append}
<script>
    var EstrategiaViewModel = function() {
        var self = this;
        this.Breadcrumbs = ko.observableArray([
            { description: 'Estrategia de liberación', url: null },
            { description: 'Matriz', url: null }
        ]);
    };

    jQuery(document).ready(function() {
        window.E = new EstrategiaViewModel();
        AppOptus.Bind(E);

        // Función para actualizar el monto del nivel 0 basado en el primer nivel activo
        // Jerarquía: Jefe Compras -> Jefe Área Solicitante -> Gerente Compras -> Gerente Área Solicitante -> Gerente General
        function actualizarMontoNivel0() {
            var montoNivel0 = null;
            
            // Orden jerárquico de switches
            var jerarquia = [
                { switch: '#switch_compras_1', monto: '#monto_nivel_1' },      // Jefe de Compras
                { switch: '#switch_solicitante_1', monto: '#monto_nivel_1' },  // Jefe de Área Solicitante
                { switch: '#switch_compras_2', monto: '#monto_nivel_2' },      // Gerente de Compras
                { switch: '#switch_solicitante_2', monto: '#monto_nivel_2' },  // Gerente de Área Solicitante
                { switch: '#switch_solicitante_3', monto: '#monto_nivel_3' }   // Gerente General
            ];
            
            // Buscar el primer switch activo en el orden jerárquico
            for (var i = 0; i < jerarquia.length; i++) {
                var switchEl = $(jerarquia[i].switch);
                if (switchEl.length && switchEl.is(':checked')) {
                    montoNivel0 = $(jerarquia[i].monto).val();
                    break;
                }
            }
            
            // Si no hay ningún nivel activo, usar el monto del nivel 3
            if (montoNivel0 === null) {
                montoNivel0 = $('#monto_nivel_3').val();
            }
            
            $('#monto_nivel_0').val(montoNivel0);
        }

        // Ejecutar al cargar
        actualizarMontoNivel0();

        // Función para habilitar/deshabilitar el monto según el estado de los switches
        function actualizarEstadoMontos() {
            // Para niveles 1 y 2: deshabilitar monto si AMBOS switches están desactivados
            for (var i = 1; i <= 2; i++) {
                var switchCompras = $('#switch_compras_' + i);
                var switchSolicitante = $('#switch_solicitante_' + i);
                var montoInput = $('#monto_nivel_' + i);
                var fila = montoInput.closest('tr');
                
                // Solo deshabilitar si AMBOS están apagados
                if (!switchCompras.is(':checked') && !switchSolicitante.is(':checked')) {
                    montoInput.prop('disabled', true);
                    montoInput.val('0'); // Setear monto a 0 cuando ambos switches están desactivados
                    fila.addClass('row-disabled');
                } else {
                    montoInput.prop('disabled', false);
                    fila.removeClass('row-disabled');
                }
            }
            
            // Para nivel 3: deshabilitar monto si el switch de solicitante está desactivado (es el único)
            var switchSolicitante3 = $('#switch_solicitante_3');
            var montoInput3 = $('#monto_nivel_3');
            var fila3 = montoInput3.closest('tr');
            
            if (!switchSolicitante3.is(':checked')) {
                montoInput3.prop('disabled', true);
                montoInput3.val('0'); // Setear monto a 0 cuando el switch está desactivado
                fila3.addClass('row-disabled');
            } else {
                montoInput3.prop('disabled', false);
                fila3.removeClass('row-disabled');
            }
        }

        // Función para actualizar el status de la estrategia
        function actualizarStatusEstrategia() {
            var hayAlgunSwitchActivo = false;
            
            // Verificar si hay algún switch activo
            $('.onoffswitch-checkbox').each(function() {
                if ($(this).is(':checked')) {
                    hayAlgunSwitchActivo = true;
                    return false; // Salir del each
                }
            });
            
            var statusElement = $('#statusEstrategia');
            if (hayAlgunSwitchActivo) {
                statusElement.text('Habilitada');
                statusElement.removeClass('status-deshabilitada').addClass('status-habilitada');
            } else {
                statusElement.text('Deshabilitada');
                statusElement.removeClass('status-habilitada').addClass('status-deshabilitada');
            }
        }

        // Ejecutar al cargar
        actualizarEstadoMontos();
        actualizarStatusEstrategia();

        // Actualizar cuando cambie algún switch
        $('.onoffswitch-checkbox').on('change', function() {
            actualizarMontoNivel0();
            actualizarEstadoMontos();
            actualizarStatusEstrategia();
        });

        // Actualizar en tiempo real cuando se escribe un monto (excepto nivel 0)
        $('.monto-input:not(#monto_nivel_0)').on('input keyup', function() {
            actualizarMontoNivel0();
        });

        // Función para obtener valor numérico de un input de monto
        function obtenerValorNumerico(selector) {
            var val = $(selector).val();
            if (!val) return 0;
            return parseInt(val.replace(/\./g, '').replace(/,/g, '')) || 0;
        }

        // Función para validar que el monto no sea menor al nivel inferior
        function validarMontoNivel(nivelActual, valorNuevo) {
            // Nivel 1: no tiene restricción inferior (solo debe ser >= 0)
            if (nivelActual === 1) {
                return { valido: true };
            }
            
            // Nivel 2: debe ser >= Nivel 1
            if (nivelActual === 2) {
                var montoNivel1 = obtenerValorNumerico('#monto_nivel_1');
                if (valorNuevo < montoNivel1) {
                    return { 
                        valido: false, 
                        mensaje: 'El monto del Nivel 2 no puede ser menor al del Nivel 1 ($' + montoNivel1.toLocaleString('es-AR') + ')'
                    };
                }
            }
            
            // Nivel 3: debe ser >= Nivel 2
            if (nivelActual === 3) {
                var montoNivel2 = obtenerValorNumerico('#monto_nivel_2');
                if (valorNuevo < montoNivel2) {
                    return { 
                        valido: false, 
                        mensaje: 'El monto del Nivel 3 no puede ser menor al del Nivel 2 ($' + montoNivel2.toLocaleString('es-AR') + ')'
                    };
                }
            }
            
            return { valido: true };
        }

        // Formatear montos con separadores de miles y validar
        $('.monto-input:not(#monto_nivel_0)').on('blur', function() {
            var value = $(this).val().replace(/\./g, '').replace(/,/g, '');
            if (!isNaN(value) && value !== '') {
                var valorNumerico = parseInt(value);
                var nivel = parseInt($(this).data('nivel'));
                
                // Validar que no sea menor al nivel inferior
                var validacion = validarMontoNivel(nivel, valorNumerico);
                if (!validacion.valido) {
                    swal({
                        title: 'Monto inválido',
                        text: validacion.mensaje,
                        type: 'warning',
                        confirmButtonText: 'OK',
                        confirmButtonClass: 'btn btn-warning'
                    });
                    // Restaurar al valor mínimo permitido
                    if (nivel === 2) {
                        var minimo = obtenerValorNumerico('#monto_nivel_1');
                        $(this).val(minimo.toLocaleString('es-AR'));
                    } else if (nivel === 3) {
                        var minimo = obtenerValorNumerico('#monto_nivel_2');
                        $(this).val(minimo.toLocaleString('es-AR'));
                    }
                } else {
                    $(this).val(valorNumerico.toLocaleString('es-AR'));
                }
                actualizarMontoNivel0();
            }
        });

        // Guardar estrategia
        $('#btnGuardarEstrategia').on('click', function() {
            // Validar montos antes de guardar
            var montoNivel1 = obtenerValorNumerico('#monto_nivel_1');
            var montoNivel2 = obtenerValorNumerico('#monto_nivel_2');
            var montoNivel3 = obtenerValorNumerico('#monto_nivel_3');
            
            // Verificar que los niveles estén en orden ascendente
            if (montoNivel2 > 0 && montoNivel2 < montoNivel1) {
                swal({
                    title: 'Error de validación',
                    text: 'El monto del Nivel 2 ($' + montoNivel2.toLocaleString('es-AR') + ') no puede ser menor al del Nivel 1 ($' + montoNivel1.toLocaleString('es-AR') + ')',
                    type: 'error',
                    confirmButtonText: 'OK',
                    confirmButtonClass: 'btn btn-danger'
                });
                return;
            }
            
            if (montoNivel3 > 0 && montoNivel3 < montoNivel2) {
                swal({
                    title: 'Error de validación',
                    text: 'El monto del Nivel 3 ($' + montoNivel3.toLocaleString('es-AR') + ') no puede ser menor al del Nivel 2 ($' + montoNivel2.toLocaleString('es-AR') + ')',
                    type: 'error',
                    confirmButtonText: 'OK',
                    confirmButtonClass: 'btn btn-danger'
                });
                return;
            }
            
            $.blockUI();
            
            // Verificar si hay algún switch activo para determinar el estado
            var hayAlgunSwitchActivo = false;
            $('.onoffswitch-checkbox').each(function() {
                if ($(this).is(':checked')) {
                    hayAlgunSwitchActivo = true;
                    return false;
                }
            });

            var estrategia = {
                UserToken: User.Token,
                monto_nivel_0: $('#monto_nivel_0').val(),
                monto_nivel_1: $('#monto_nivel_1').val(),
                jefe_compras: $('#switch_compras_1').is(':checked'),
                jefe_solicitante: $('#switch_solicitante_1').is(':checked'),
                monto_nivel_2: $('#monto_nivel_2').val(),
                gerente_compras: $('#switch_compras_2').is(':checked'),
                gerente_solicitante: $('#switch_solicitante_2').is(':checked'),
                monto_nivel_3: $('#monto_nivel_3').val(),
                gerente_general: $('#switch_solicitante_3').is(':checked'),
                habilitado: hayAlgunSwitchActivo
            };

            $.ajax({
                url: '/estrategia/store',
                type: 'POST',
                data: estrategia,
                success: function(response) {
                    $.unblockUI();
                    if (response.success) {
                        swal({
                            title: '¡Éxito!',
                            text: 'Estrategia guardada con éxito',
                            type: 'success',
                            confirmButtonText: 'OK',
                            confirmButtonClass: 'btn btn-success'
                        });
                    } else {
                        swal({
                            title: 'Error',
                            text: response.message || 'Error al guardar la estrategia',
                            type: 'error',
                            confirmButtonText: 'OK',
                            confirmButtonClass: 'btn btn-danger'
                        });
                    }
                },
                error: function(xhr) {
                    $.unblockUI();
                    var errorMsg = 'Error al guardar la estrategia';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    swal({
                        title: 'Error',
                        text: errorMsg,
                        type: 'error',
                        confirmButtonText: 'OK',
                        confirmButtonClass: 'btn btn-danger'
                    });
                }
            });
        });

        // Cargar estrategia existente al iniciar
        function cargarEstrategia() {
            $.ajax({
                url: '/estrategia/get',
                type: 'GET',
                data: { UserToken: User.Token },
                success: function(response) {
                    if (response.success && response.data) {
                        var data = response.data;
                        
                        // Cargar switches
                        $('#switch_compras_1').prop('checked', data.jefe_compras);
                        $('#switch_solicitante_1').prop('checked', data.jefe_solicitante);
                        $('#switch_compras_2').prop('checked', data.gerente_compras);
                        $('#switch_solicitante_2').prop('checked', data.gerente_solicitante);
                        $('#switch_solicitante_3').prop('checked', data.gerente_general);
                        
                        // Cargar montos (formatear con separador de miles)
                        var formatMonto = function(valor) {
                            if (valor === 0 || valor === null) return '0';
                            return parseInt(valor).toLocaleString('es-AR');
                        };
                        
                        $('#monto_nivel_0').val(formatMonto(data.monto_nivel_0));
                        $('#monto_nivel_1').val(formatMonto(data.monto_nivel_1));
                        $('#monto_nivel_2').val(formatMonto(data.monto_nivel_2));
                        $('#monto_nivel_3').val(formatMonto(data.monto_nivel_3));
                        
                        // Actualizar estados visuales
                        actualizarEstadoMontos();
                        actualizarStatusEstrategia();
                    }
                },
                error: function(xhr) {
                    console.log('Error al cargar estrategia:', xhr);
                }
            });
        }

        // Cargar estrategia al iniciar
        cargarEstrategia();
    });
</script>
{/block}
