<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Todo;
use Illuminate\Support\Facades\Validator;

class TodoController extends Controller
{
    private $model;
    private $code = 200;
    private $response = ['errors' => '', 'data' => ''];

    public function __construct(Todo $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {
        $todos =  $this->model->where('user_id', $request->user()->id)->simplePaginate();
        $this->response['data'] = $todos->items();
        return response()->json($this->response, $this->code);
    }

    public function create(Request $request)
    {
        $validate = $this->validateData($request);
        if (!$validate) {
            return response()->json($this->response, $this->code);
        }

        $request['user_id'] = $request->user()->id;
        $this->model->create($request->all());
        $this->response['data'] = 'Todo criado com sucesso!';
        return response()->json($this->response, $this->code);
    }

    public function show($id)
    {
        $todo = $this->findTodo($id);
        if (!$todo) {
            return response()->json($this->response, $this->code);
        }
        $this->response['data'] = $todo;
        return response()->json($this->response, $this->code);
    }

    public function update(Request $request, $id)
    {
        $todo = $this->findTodo($id);
        if (!$todo) {
            return response()->json($this->response, $this->code);
        }
        $validate = $this->validateData($request);

        if (!$validate) {
            return response()->json($this->response, $this->code);
        }
        $todo->update($request->all());
        
        $this->response['data'] = $todo;
        return response()->json($this->response, $this->code);
    }

    public function destroy($id)
    {
        $todo = $this->findTodo($id);
        if (!$todo) {
            return response()->json($this->response, $this->code);
        }
        $todo->delete();
        $this->response['data'] = 'Todo deletado com sucesso';
        return response()->json($this->response, $this->code);
    }

    private function validateData($request)
    {
        $rules = [
            'title' => 'required|min:3'
        ];

        $validator = Validator::make($request->all(), $rules, [
            'required' => 'O título é obrigatório',
            'min' => 'O titulo precisa ter no minimo 3 caracteres'
        ]);

        if ($validator->fails()) {
            $this->response['errors'] = $validator->messages();
            $this->code = 400;
            return false;
        }

        return true;
    }

    private function findTodo($id)
    {
        $todo = $this->model->where('id', $id)->first();
        if (empty($todo)) {
            $this->response['errors'] = 'Todo não encontrada';
            $this->code = 404;
            return false;
        }

        return $todo;
    }
}
