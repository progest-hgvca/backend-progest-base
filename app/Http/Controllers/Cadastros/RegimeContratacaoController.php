<?php

namespace App\Http\Controllers\Cadastros;

use App\Http\Requests\RegimeContratacaoRequest;
use Illuminate\Http\Request;
use App\Models\RegimeContratacao; 

class RegimeContratacaoController
{
    public function add(RegimeContratacaoRequest $request){

    }

    public function listAll(Request $request){  
        $data = $request->all();
        $filters = $data['filters'] ?? [];  

        $regimes = $filters;
        $regimesQuery = RegimeContratacao::query();
        foreach ($filters as $condition) {
            foreach ($condition as $field => $value) {
                $regimesQuery->where($field, $value);
            }
        }

        if (!isset($data['paginate'])) {
            $regimes = $regimesQuery
                ->select('id', 'nome', 'descricao', 'status')
                ->orderBy('nome')
                ->get();
        } else {
            $regimes = $regimesQuery
                ->select('id', 'nome', 'descricao', 'status')
                ->orderBy('nome')
                ->get();
        }

        return ['status' => true, 'data' => $regimes];
    }

    public function listData(Request $request){

    }

    public function update(RegimeContratacaoRequest $request){

    }

    public function delete(Request $request){

    }
}