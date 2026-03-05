<?php

namespace App\Http\Controllers\Estrategia;

use App\Http\Controllers\BaseController;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Models\EstrategiaLiberacion;
use App\Models\Concurso;
use App\Models\User;
use App\Models\Tipocambio;

class EstrategiaController extends BaseController
{
    public function serveMatriz(Request $request, Response $response, $params)
    {
        return $this->render($response, 'estrategia/index.tpl', [
            'page' => 'estrategia',
            'accion' => 'matriz',
            'title' => 'Estrategia de liberación - Matriz',
            'pre_scripts_child' => '',
            'post_scripts_child' => ''
        ]);
    }

    /**
     * Buscar usuario por rol y área dentro de la empresa
     */
    private function findUserByRolArea($customerCompanyId, $rol, $area = null)
    {
        $query = User::where('customer_company_id', $customerCompanyId)
            ->where('rol', $rol)
            ->whereNull('deleted_at');
        
        if ($area) {
            $query->where('area', $area);
        }
        
        $user = $query->first();
        
        if ($user) {
            return $user->first_name . ' ' . $user->last_name;
        }
        
        return null;
    }

    /**
     * Convertir monto a dólares usando el tipo de cambio
     */
    private function convertirADolares($monto, $monedaId)
    {
        // Si la moneda es null o 0, asumimos que ya está en dólares
        if (empty($monedaId)) {
            return (float) $monto;
        }

        // Buscar el tipo de cambio para esta moneda usando monedaId
        $tipoCambio = Tipocambio::where('monedaId', $monedaId)->first();
        
        if ($tipoCambio && $tipoCambio->cambio) {
            // El campo cambio viene en formato "1.453,16" (punto miles, coma decimal)
            // Primero quitar los puntos de miles, luego reemplazar coma por punto
            $cambioStr = $tipoCambio->cambio;
            $cambioStr = str_replace('.', '', $cambioStr);  // Quitar puntos de miles
            $cambioStr = str_replace(',', '.', $cambioStr); // Coma a punto decimal
            $cambioFloat = (float) $cambioStr;
            
            if ($cambioFloat > 0) {
                // Dividir el monto por el tipo de cambio para obtener dólares
                return (float) $monto / $cambioFloat;
            }
        }
        
        // Si no se encuentra tipo de cambio, retornar el monto original
        return (float) $monto;
    }

    /**
     * Obtener la estrategia de liberación de la empresa del usuario actual
     */
    public function get(Request $request, Response $response)
    {
        $success = false;
        $message = null;
        $status = 200;
        $data = null;

        try {
            $user = user();
            $customerCompanyId = $user->customer_company_id;
            
            // Obtener parámetros de la query
            $queryParams = $request->getQueryParams();
            $areaSolicitante = null;
            $montoAdjudicacion = null;
            $montoEnDolares = null;
            
            // Obtener el área y moneda del concurso si se pasa el ID
            $concurso = null;
            if (!empty($queryParams['concurso_id'])) {
                $concurso = Concurso::find($queryParams['concurso_id']);
                if ($concurso) {
                    $areaSolicitante = $concurso->area_sol;
                }
            }
            
            // Obtener el monto de la adjudicación si se pasa
            if (!empty($queryParams['monto_adjudicacion'])) {
                $montoAdjudicacion = (float) $queryParams['monto_adjudicacion'];
                
                // Convertir a dólares si tenemos el concurso
                if ($concurso) {
                    $montoEnDolares = $this->convertirADolares($montoAdjudicacion, $concurso->moneda);
                } else {
                    $montoEnDolares = $montoAdjudicacion;
                }
            }

            $estrategia = EstrategiaLiberacion::getByCompany($customerCompanyId);

            if ($estrategia) {
                // Construir array de niveles activos para la cadena de aprobación
                $nivelesAprobacion = [];
                $orden = 1;

                // Solo agregar niveles si el monto en dólares supera el umbral
                // Si no hay monto, mostrar todos los niveles configurados (comportamiento anterior)
                
                $montoNivel1 = (float) $estrategia->monto_nivel_1;
                $montoNivel2 = (float) $estrategia->monto_nivel_2;
                $montoNivel3 = (float) $estrategia->monto_nivel_3;

                // Nivel 1: Jefe de Compras
                if ($estrategia->jefe_compras) {
                    // Si hay monto, verificar si supera el umbral del nivel 1
                    $requiereAprobacion = ($montoEnDolares === null) || ($montoEnDolares > $montoNivel1);
                    
                    if ($requiereAprobacion) {
                        $usuarioJefeCompras = $this->findUserByRolArea($customerCompanyId, 'Jefe', 'Compras');
                        $nivelesAprobacion[] = [
                            'orden' => $orden++,
                            'nivel' => 'Nivel 1',
                            'rol' => 'Jefe de Compras',
                            'monto_umbral' => $montoNivel1,
                            'usuario' => $usuarioJefeCompras,
                            'estado' => 'Pendiente',
                            'fecha' => null,
                            'motivo' => null
                        ];
                    }
                }

                // Nivel 1: Jefe de Área Solicitante
                if ($estrategia->jefe_solicitante) {
                    $requiereAprobacion = ($montoEnDolares === null) || ($montoEnDolares > $montoNivel1);
                    
                    if ($requiereAprobacion) {
                        $rolJefeSolicitante = $areaSolicitante 
                            ? 'Jefe de ' . $areaSolicitante 
                            : 'Jefe de Área Solicitante';
                        $usuarioJefeSolicitante = $areaSolicitante 
                            ? $this->findUserByRolArea($customerCompanyId, 'Jefe', $areaSolicitante)
                            : null;
                        $nivelesAprobacion[] = [
                            'orden' => $orden++,
                            'nivel' => 'Nivel 1',
                            'rol' => $rolJefeSolicitante,
                            'monto_umbral' => $montoNivel1,
                            'usuario' => $usuarioJefeSolicitante,
                            'estado' => 'Pendiente',
                            'fecha' => null,
                            'motivo' => null
                        ];
                    }
                }

                // Nivel 2: Gerente de Compras
                if ($estrategia->gerente_compras) {
                    $requiereAprobacion = ($montoEnDolares === null) || ($montoEnDolares > $montoNivel2);
                    
                    if ($requiereAprobacion) {
                        $usuarioGerenteCompras = $this->findUserByRolArea($customerCompanyId, 'Gerente', 'Compras');
                        $nivelesAprobacion[] = [
                            'orden' => $orden++,
                            'nivel' => 'Nivel 2',
                            'rol' => 'Gerente de Compras',
                            'monto_umbral' => $montoNivel2,
                            'usuario' => $usuarioGerenteCompras,
                            'estado' => 'Pendiente',
                            'fecha' => null,
                            'motivo' => null
                        ];
                    }
                }

                // Nivel 2: Gerente de Área Solicitante
                if ($estrategia->gerente_solicitante) {
                    $requiereAprobacion = ($montoEnDolares === null) || ($montoEnDolares > $montoNivel2);
                    
                    if ($requiereAprobacion) {
                        $rolGerenteSolicitante = $areaSolicitante 
                            ? 'Gerente de ' . $areaSolicitante 
                            : 'Gerente de Área Solicitante';
                        $usuarioGerenteSolicitante = $areaSolicitante 
                            ? $this->findUserByRolArea($customerCompanyId, 'Gerente', $areaSolicitante)
                            : null;
                        $nivelesAprobacion[] = [
                            'orden' => $orden++,
                            'nivel' => 'Nivel 2',
                            'rol' => $rolGerenteSolicitante,
                            'monto_umbral' => $montoNivel2,
                            'usuario' => $usuarioGerenteSolicitante,
                            'estado' => 'Pendiente',
                            'fecha' => null,
                            'motivo' => null
                        ];
                    }
                }

                // Nivel 3: Gerente General
                if ($estrategia->gerente_general) {
                    $requiereAprobacion = ($montoEnDolares === null) || ($montoEnDolares > $montoNivel3);
                    
                    if ($requiereAprobacion) {
                        $usuarioGerenteGeneral = $this->findUserByRolArea($customerCompanyId, 'Gerente General');
                        $nivelesAprobacion[] = [
                            'orden' => $orden++,
                            'nivel' => 'Nivel 3',
                            'rol' => 'Gerente General',
                            'monto_umbral' => $montoNivel3,
                            'usuario' => $usuarioGerenteGeneral,
                            'estado' => 'Pendiente',
                            'fecha' => null,
                            'motivo' => null
                        ];
                    }
                }

                $data = [
                    'id' => $estrategia->id,
                    'customer_company_id' => $estrategia->customer_company_id,
                    'monto_nivel_0' => (float) $estrategia->monto_nivel_0,
                    'monto_nivel_1' => (float) $estrategia->monto_nivel_1,
                    'jefe_compras' => (bool) $estrategia->jefe_compras,
                    'jefe_solicitante' => (bool) $estrategia->jefe_solicitante,
                    'monto_nivel_2' => (float) $estrategia->monto_nivel_2,
                    'gerente_compras' => (bool) $estrategia->gerente_compras,
                    'gerente_solicitante' => (bool) $estrategia->gerente_solicitante,
                    'monto_nivel_3' => (float) $estrategia->monto_nivel_3,
                    'gerente_general' => (bool) $estrategia->gerente_general,
                    'habilitado' => (bool) $estrategia->habilitado,
                    'niveles_aprobacion' => $nivelesAprobacion,
                    'monto_adjudicacion_dolares' => $montoEnDolares
                ];
            }

            $success = true;

        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
            $status = 500;
        }

        return $this->json($response, [
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    /**
     * Guardar o actualizar la estrategia de liberación
     */
    public function store(Request $request, Response $response)
    {
        $success = false;
        $message = null;
        $status = 200;

        try {
            $user = user();
            $customerCompanyId = $user->customer_company_id;

            $body = $request->getParsedBody();

            if (empty($body)) {
                throw new \Exception('No se recibieron datos');
            }

            // Parsear los montos (quitar formato de miles)
            $parseMonto = function($valor) {
                if (is_null($valor) || $valor === '') return 0;
                // Quitar puntos de miles y reemplazar coma por punto para decimales
                $valor = str_replace('.', '', $valor);
                $valor = str_replace(',', '.', $valor);
                return (float) $valor;
            };

            $data = [
                'monto_nivel_0' => $parseMonto($body['monto_nivel_0'] ?? 0),
                'monto_nivel_1' => $parseMonto($body['monto_nivel_1'] ?? 0),
                'jefe_compras' => filter_var($body['jefe_compras'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'jefe_solicitante' => filter_var($body['jefe_solicitante'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'monto_nivel_2' => $parseMonto($body['monto_nivel_2'] ?? 0),
                'gerente_compras' => filter_var($body['gerente_compras'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'gerente_solicitante' => filter_var($body['gerente_solicitante'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'monto_nivel_3' => $parseMonto($body['monto_nivel_3'] ?? 0),
                'gerente_general' => filter_var($body['gerente_general'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'habilitado' => filter_var($body['habilitado'] ?? false, FILTER_VALIDATE_BOOLEAN)
            ];

            // Validar que los montos estén en orden ascendente
            if ($data['monto_nivel_2'] > 0 && $data['monto_nivel_2'] < $data['monto_nivel_1']) {
                throw new \Exception('El monto del Nivel 2 no puede ser menor al del Nivel 1');
            }
            
            if ($data['monto_nivel_3'] > 0 && $data['monto_nivel_3'] < $data['monto_nivel_2']) {
                throw new \Exception('El monto del Nivel 3 no puede ser menor al del Nivel 2');
            }

            $estrategia = EstrategiaLiberacion::saveStrategy($customerCompanyId, $data);

            $success = true;
            $message = 'Estrategia guardada correctamente';

        } catch (\Exception $e) {
            $success = false;
            $message = 'Error al guardar la estrategia: ' . $e->getMessage();
            $status = 500;
        }

        return $this->json($response, [
            'success' => $success,
            'message' => $message
        ], $status);
    }
}
