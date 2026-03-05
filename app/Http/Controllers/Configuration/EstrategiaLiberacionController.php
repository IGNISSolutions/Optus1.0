<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\BaseController;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Models\EstrategiaLiberacion;
use App\Models\Concurso;
use App\Models\Participante;
use Carbon\Carbon;
use App\Models\Tipocambio;

class EstrategiaLiberacionController extends BaseController
{
    public function serveCreate(Request $request, Response $response, $params)
    {

        return $this->render($response, 'estrategialiberacion/create.tpl', [
            'page' => 'configuraciones',
            'accion' => 'nuevo-politica-estrategia-liberacion',
            'title' => 'Nueva Politica',
            'urlBack' => route('estrategialiberacion.serveList'),
        ]);
    }


    public function serveList(Request $request, Response $response, $params)
    {
        return $this->render($response, 'estrategialiberacion/list.tpl', [
            'page'   => 'configuraciones',
            'accion' => 'listado-estrategia-liberacion',
            'title'  => 'Políticas de Estrategia de Liberación',
            'list'   => $list['data']['list']
        ]);
    }

    public function list(Request $request, Response $response)
    {
        $success = false;
        $message = null;
        $status = 200;
        $list = [];
        $user = user();
        $compania = $user->customer_company_id;

        try {
            $estrategiasliberacion = EstrategiaLiberacion::where('customer_company_id', $compania)->get();
            foreach ($estrategiasliberacion as $estrategialiberacion) {
                array_push($list, [
                    'Idestrategia'=> $estrategialiberacion->idestrategia_liberacion,
                    'Habilitado'=> $estrategialiberacion->habilitado,
                    'Nivel0'=> $estrategialiberacion->nivel0,
                    'Nivel1'=> $estrategialiberacion->nivel1,
                    'Nivel2'=> $estrategialiberacion->nivel2,
                    'Nivel3'=> $estrategialiberacion->nivel3,
                    'Nivel4'=> $estrategialiberacion->nivel4,
                    'Compania' => $estrategialiberacion->customer_company_id,
                ]);
            }  
            
            json_encode($list);
            $success = true;

        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
        }

        // Breadcrumbs
        $breadcrumbs = [
            ['description' => 'Estrategia de Liberación', 'url' => null]
        ];

        return $this->json($response, [
            'success' => $success,
            'message' => $message,
            'data' => [
                'list' => $list,
                'breadcrumbs' => $breadcrumbs
            ]
        ], $status);
    }

    
    
}