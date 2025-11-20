<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\BaseController;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Models\Concurso;
use App\Models\PlantillaTecnicaItem;

class PayrollController extends BaseController
{
    public function edit(Request $request, Response $response, $params)
    {
        $success = false;
        $message = null;
        $status = 200;
        $result = [];

        try {
            $plantilla_tecnica_item = PlantillaTecnicaItem::where('id_plantilla', $params['id'])->get();

            $result = [
                'PlantillaTecnicaSeleccionada' => $plantilla_tecnica_item
            ];

            $success = true;

        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
        }

        return $this->json($response, [
            'success'   => $success,
            'message'   => $message,
            'data'      => [
                'list'  => $result
            ]
        ], $status);
    }

    public function get(Request $request, Response $response, $params)
    {
        $success = false;
        $message = null;
        $status = 200;
        $result = [];

        try {
            $concurso = Concurso::find($params['id']);
            
            $result = [
                'PlantillaTecnicaSeleccionada' => $concurso->plantilla_tecnica->parsed_items
            ];

            $success = true;

        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : (method_exists($e, 'getCode') ? $e->getCode() : 500);
        }
        return $this->json($response, [
            'success'   => $success,
            'message'   => $message,
            'data'      => [
                'list'  => $result
            ]
        ], $status);
    }
}