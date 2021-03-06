<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Config;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Client;
use App\Device;
use Psr\Http\Message\ResponseInterface;
use Illuminate\Support\Facades\Log;

class OnesignalNotification
{


    public static $errors = [];

    /**
     * Envia una petición POST a Onesignal para enviar una notificación PUSH y retorna el contenido de la respuesta
     * @param array  $body
     *
     * @return string
     */
    private static function sendPushNotification($body): ResponseInterface
    {
        $client = new Client(['http_errors' => true]);
        $url = "https://onesignal.com/api/v1/notifications";
        $onesignalAppId = Config::get('siu_config.ONESIGNAL_APP_ID');
        $onesignalRestApiKey = Config::get('siu_config.ONESIGNAL_REST_API_KEY');
        $authorization = "Basic $onesignalRestApiKey";
        $headers = [
            'Authorization' => $authorization,
            'Content-Type' => "application/json"
        ];
        $body["app_id"] = $onesignalAppId;
        
        return $client->post($url, ['body' => json_encode($body), 'headers' => $headers]);
    }

    /**
     * Obtiene los dispositivos asociados a un usuario
     * @param int  $user_id
     *
     * @return array
     */
    public static function getUserDevices($user_id)
    {
        $devices = Device::findByUserId($user_id)->get();
        $id_devices = [];
       
        foreach($devices as $device){
            if($device->phone_id && $device->phone_id != ''){
                array_push($id_devices, $device->phone_id);
            }
        }
        return $id_devices;
    }

    /**
     * Envia una notificacion con Onesignal a los dispositivos especificados en un array con sus ID`s
     * @param string  $title //TITULO DE LA NOTIFICACION
     * @param string  $description //DESCRIPCION DE LA NOTIFICACION
     * @param array  $aditionalData //DATA ADICIONAL PARA QUE EL CLIENTE RECIBA ESOS DATOS
     * @param array  $specificIDs //SON LOS ID`s DE LOS DISPOSITIVOS A LOS CUALES SE ENVIARA LA NOTIFICACION
     *
     * @return array
     */
    public static function sendNotificationByPlayersID($title = '', $description = '', $aditionalData = null, $specificIDs = [])
    {
        $bodyPeticionOnesignal = [
            "data" => $aditionalData,
            "contents" => [
                "es" => $description,
                "en" => $description
            ],
            "headings" => [
                "en" => $title,
                "es" => $title,
            ],
            "include_player_ids" => $specificIDs,
        ];
        
        try {
            $request = self::sendPushNotification($bodyPeticionOnesignal);
            $response = $request->getBody();
            return true;
        }
        catch(BadResponseException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            self::$errors = json_decode($responseBodyAsString);
            Log::info($responseBodyAsString, ['message' => $responseBodyAsString]);
            return false;
        }
    }

    /**
     * Envia una notificacion con Onesignal a todos los dispositivos subscriptos
     * @param string  $title
     * @param string  $description
     * @param array  $aditionalData
     * @param array  $segments
     *
     * @return array
     */
    public static function sendNotificationBySegments($title = '', $description = '', $aditionalData = null, $segments = ["All"])
    {
        $bodyPeticionOnesignal = [
            "included_segments" => $segments,
            "contents" => [
                "en" => $description,
                "es" => $description,
            ],
            "headings" => [
                "en" => $title,
                "es" => $title,
            ],
        ];
        if(!$aditionalData){
            $bodyPeticionOnesignal['data'] = (object)[];
        }else{
            $bodyPeticionOnesignal['data'] = $aditionalData;
        }
        try {
            $request= self::sendPushNotification($bodyPeticionOnesignal);
            $response = $request->getBody();
            return true;
        }
        catch(BadResponseException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            self::$errors = json_decode($responseBodyAsString);
            Log::info($responseBodyAsString, ['message' => $responseBodyAsString]);
            return false;
        }
    }
}
