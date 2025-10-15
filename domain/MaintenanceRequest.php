<?php 

class MaintenanceRequest {
    private $id;
    private $requester;
    private $desc;
    private $address;
    private $area;

    public function __construct($id, $requester, $desc, $address, $area)
    {
        $this->id = $id;
        $this->requester = $requester;
        $this->desc = $desc;
        $this->address = $address;
        $this->area = $area;
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

}
