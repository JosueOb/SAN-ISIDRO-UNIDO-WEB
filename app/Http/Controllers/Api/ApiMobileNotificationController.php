<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiBaseController;
use App\MobileNotification;
use App\User;
use Exception;

class ApiMobileNotificationController extends ApiBaseController
{

    /**
     * Retorna las notificaciones de un usuario
     * @param integer $user_id
     *
     * @return array
     */
    public function getNotificationsUser($user_id) {
        try {
            //Verificar si existe el usuario
            $user = User::findById($user_id)->first();
            if (is_null($user)) {
                return $this->sendError(404, 'no existe el usuario', ['notifications' => 'no existe el usuario']);
            }
            //Verificar si existe la notificacion
            $mobileNotifications = MobileNotification::userId((int) $user_id)->with(['user'])->orderBy('id', 'desc')
                ->get();
            if (is_null($mobileNotifications)) {
                return $this->sendError(404, 'no existen notificationes', ['notifications' => 'no existen notificationes']);
            } 
            //Si no es nulo, retornar las notificaciones
            return $this->sendResponse(200, 'success', $mobileNotifications);
        } catch (Exception $e) {
            return $this->sendError(500, "error", ['server_error' => $e->getMessage()]);
        }
    }
}
