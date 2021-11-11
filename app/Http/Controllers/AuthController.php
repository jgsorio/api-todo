<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private $model;
    private $response = ['errors' => '', 'data' => ''];
    private $code = 200;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function login(Request $request)
    {
        $rules = [
            'email' => 'required|email',
            'password' => 'required'
        ];

        $messages = [
            'email.required' => 'O email é obrigatório',
            'password.required' => 'A senha é obrigatória',
            'email.email' => 'O email precisa ser válido'
        ];

        $validate = Validator::make($request->all(), $rules, $messages);
        if ($validate->fails()) {
            $this->response['errors'] = $validate->messages();
            $this->code = 400;
            return response()->json($this->response, $this->code);
        }

        if (!Auth::attempt($request->all())) {
            $this->response['errors'] = 'Email e ou senha inválidos';
            $this->code = 400;
            return response()->json($this->response, $this->code);
        }

        $user = $this->model->where('email', $request->input('email'))->first();
        $token = $this->tokenGenerate($user);
        $user->update(['token' => $token]);
        $this->response['data'] = $token;

        return response()->json($this->response, $this->code);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();
        $this->response['data'] = "Usuário deslogado com sucesso!";
        return response()->json($this->response, $this->code);
    }

    public function store(Request $request)
    {
        $validate = $this->validateUser($request);
        if (!$validate) {
            return response()->json($this->response, $this->code);
        }

        $request['token'] = '';
        $request['password'] = bcrypt($request->input('password'));
        $this->model->create($request->all());
        $this->response['data'] = 'Usuário criado com sucesso!';

        return response()->json($this->response, $this->code);
    }

    private function tokenGenerate($user)
    {
        return $user->createToken(time().rand(0,999))->plainTextToken;
    }

    private function validateUser($request)
    {
        $rules = [
            'name' => 'required|min:6',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8'
        ];

        $messages = [
            'name.required' => 'O nome é obrigatório',
            'name.min' => 'O nome precisa ter no mínimo 6 caracteres',
            'email.required' => 'O email é obrigatório',
            'email.email' => 'O email precisa ser válido',
            'email.unique' => 'Esse email já está cadastrado',
            'password.required' => 'A senha é obrigatória',
            'password.min' => 'A senha precisa ter no mínimo 8 caracteres'
        ];

        $validate = Validator::make($request->all(), $rules, $messages);

        if ($validate->fails()) {
            $this->response['errors'] = $validate->messages();
            $this->code = 400;
            return false;
        }

        return true;
    }
}
