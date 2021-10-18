<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller{

    private array $response = ["error"=> [], "result" => []];

    public function __construct(){
        $this->middleware("auth",["except" => ["login","refresh"]]);
    }

    public function login(Request $request):JsonResponse{

      $this->validate($request,[
        "email" => "required",
        "password" => "required",
      ]);

      $credentials = $request->only(["email", "password"]);
      $token = Auth::attempt($credentials);

      if (!$token) {
        $this->response["error"] = "Email e/ou senha invalido!";
        return Response()->json($this->response, 401);
    }

      $this->response["result"] = $token;
      return Response()->json($this->response, 200);

  }

    public function logout():JsonResponse
    {
        if (Auth::check()) {
            Auth::logout();
            $this->response["error"] = "voce saiu com sucesso";
            return Response()->json($this->response, 200);
        }

        $this->response["error"] = "erro ao deslogar";
        return Response()->json($this->response, 200);
    }

    public function refresh()
    {


    }

}
