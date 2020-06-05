<?php

namespace App\HelpersClass;

class AdditionalData 
{
    protected $emergency;
    protected $event;
    protected $problem;
    protected $activity;
    protected $post;
   
    public function __construct() 
    {
        $this->emergency = [
            "attended_by" => null,
            'rechazed_by' => null,
            'rechazed_reason' => null,
            "approved_by" => null, 
            "status_attendance" => 'pendiente' //atendido, rechazado, pendiente
        ];
        $this->event = [
            "responsible" => null,
            "range_date" => [
                'start_date' => date("Y-m-d"),
                'end_date' => date("Y-m-d",strtotime(date("Y-m-d")."+ 1 week")),
                'start_time' => date("H:i:s"),
                'end_time' => date("H:i:s", strtotime('+3 hours', strtotime(date("H:i:s")))) 
            ],
            "approved_by" => null, 
            "status_attendance" => 'pendiente' //atendido, rechazado, pendiente
        ];
        $this->problem = [
            "approved_by" => null, 
            "status_attendance" => 'pendiente' //atendido, rechazado, pendiente
        ];
        $this->activity = [
            "approved_by" => null, 
            "status_attendance" => 'pendiente' //atendido, rechazado, pendiente
        ];
    }
    
    public function getInfoEmergency() 
    {
        return $this->emergency;
    }

    public function setInfoEmergency($info_emergency) 
    {
        $this->emergency = array_merge($this->emergency, $info_emergency);
    }
    
    public function getInfoEvent() 
    {
        return $this->event;
    }

    public function setInfoEvent($info_event) 
    {
        $this->event = array_merge($this->event, $info_event);
    }

    public function getInfoSocialProblem() 
    {
        return $this->problem;
    }

    public function setInfoSocialProblem($info_social_problem) 
    {
        $this->problem = array_merge($this->problem, $info_social_problem);
    }

    public function getInfoActivity() 
    {
        return $this->activity;
    }

    public function setInfoActivity($info_activity) 
    {
        $this->activity = array_merge($this->activity, $info_activity);
    }

    public function getAll()
    {
        return 
        [
            'emergency'   => $this->getInfoEmergency(),
            'event' => $this->getInfoEvent(),
            'problem' => $this->getInfoSocialProblem(),
            'activity' => $this->getInfoActivity(),
        ];
    }

    public function getEmergencyData()
    {
        return 
        [
            'emergency'   => $this->getInfoEmergency()
        ];
    }

    public function getEventData()
    {
        return 
        [
            'event' => $this->getInfoEvent()
        ];
    }

    public function getProblemData()
    {
        return 
        [
            'problem' => $this->getInfoSocialProblem()
        ];
    }

    public function getActivityData()
    {
        return 
        [
            'activity' => $this->getInfoActivity(),
        ];
    }
}