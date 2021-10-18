<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use http\Client\Curl\Versions\CURL;
use Illuminate\Http\JsonResponse;

class SimulatedController extends Controller
{
    public function __construct()
    {
        //$this->middleware("auth");
    }

    public function simulated(Request $request)
    {

        $this->validate($request, [
            "cep" => "required",
            "value" => "required",
            "struct" => "required",
        ]);
        $responseAddress = $this->getAddress($request->cep);

        if(!$responseAddress){
            $this->response["error"] = "CEP não encontrado";
            return Response()->json($this->response, 40);
        }

        $responseLatAtLng = $this->getLatAndLng($request->cep);
        $this->response["result"] = $this->getApiSolResult($responseAddress,$request,$responseLatAtLng);
        return Response()->json($this->response,200);
    }

    private function  getAddress($cep){
        $curl = curl_init();
        $url =  'https://viacep.com.br/ws/'.$cep.'/json/';
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if($err){
            return false;
        }
        return json_decode($response);
    }

    private function getLatAndLng($cep){
        $url = "https://trueway-geocoding.p.rapidapi.com/Geocode?address={$cep}&language=pt-Br";

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'x-rapidapi-host: trueway-geocoding.p.rapidapi.com',
                'x-rapidapi-key: '
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response)->results[0]->location;
    }

    public function getApiSolResult(object $address,$request, object $location){


        $struct =  str_contains($request->struct," ") ? str_replace(" ","-",$request->struct) : $request->struct;
        $struct = 'estrutura='.urlencode($struct);
        $state = 'estado='.$address->uf;
        $city = "cidade=".urlencode($address->localidade);
        $value = "valor_conta=".$this->valueFormatted($request->value);
        $cep = "cep=".$address->cep;
        $lat = "latitude=".$location->lat;
        $lng = "longitude=".$location->lng;
        $url = 'https://api2.77sol.com.br/busca-cep?'.$struct.'&'.$state.'&'.$city.'&'.$value.'&'.$cep.'&'.$lat.'&'.$lng.'';
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if($err){
            return $err;
        }
        return json_decode($response);

    }

    private function valueFormatted($value){

        $value = !str_contains($value ,".") ? $value : str_replace(".","",$value);
        $value  = !str_contains($value ,",") ? $value : str_replace(",","",$value);
        return $value;

    }


}
