<?php 

class MaintenanceRequest {
    private $id;
    private $requester;
    private $desc;
    private $address;
    private $area;
    private $problemType;

    public function __construct($id, $requester, $desc, $address, $area, $problemType)
    {
        $this->id = $id;
        $this->requester = $requester;
        $this->desc = $desc;
        $this->address = $address;
        $this->area = $area;
        $this->problemType = $problemType; 
    }

    public function getID() {
        return $this->id;
    }

    public function getRequester() {
        return $this->requester;
    }

    public function getDesc() {
        return $this->desc;
    }

    public function getAddress() {
        return $this->address;
    }

    public function getArea() {
        return $this->area;
    }

    public function getProblemType() {
        return $this->problemType;
    }

}
