<?php 

class MaintenanceRequest {
    private $id;
    private $requester_name;
    private $requester_email;
    private $requester_phone;
    private $location;
    private $building;
    private $unit;
    private $description;
    private $priority;
    private $status;
    private $assigned_to;
    private $created_at;
    private $updated_at;
    private $completed_at;
    private $notes;
    private $archived;

    /**
     * constructor for maintenance request
     * @param string $id - unique identifier
     * @param string $requester_name - name of person requesting
     * @param string $requester_email - email of requester
     * @param string $requester_phone - phone of requester
     * @param string $location - location of issue
     * @param string $building - building name
     * @param string $unit - unit number
     * @param string $description - description of problem
     * @param string $priority - priority level (Low, Medium, High, Emergency)
     * @param string $status - current status (Pending, In Progress, Completed, Cancelled)
     * @param string $assigned_to - who it's assigned to
     * @param string $notes - additional notes
     * @param int $archived - whether request is archived (0 or 1)
     */
    public function __construct($id, $requester_name, $requester_email, $requester_phone, 
                               $location, $building, $unit, $description, $priority = 'Medium', 
                               $status = 'Pending', $assigned_to = '', $notes = '', $archived = 0) {
        $this->id = $id;
        $this->requester_name = $requester_name;
        $this->requester_email = $requester_email;
        $this->requester_phone = $requester_phone;
        $this->location = $location;
        $this->building = $building;
        $this->unit = $unit;
        $this->description = $description;
        $this->priority = $priority;
        $this->status = $status;
        $this->assigned_to = $assigned_to;
        $this->notes = $notes;
        $this->archived = $archived;
        
        // set timestamps
        $this->created_at = date('Y-m-d H:i:s');
        $this->updated_at = date('Y-m-d H:i:s');
        $this->completed_at = null;
    }

    // getter methods
    public function getID() {
        return $this->id;
    }

    public function getRequesterName() {
        return $this->requester_name;
    }

    public function getRequesterEmail() {
        return $this->requester_email;
    }

    public function getRequesterPhone() {
        return $this->requester_phone;
    }

    public function getLocation() {
        return $this->location;
    }

    public function getBuilding() {
        return $this->building;
    }

    public function getUnit() {
        return $this->unit;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getPriority() {
        return $this->priority;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getAssignedTo() {
        return $this->assigned_to;
    }


    public function getCreatedAt() {
        return $this->created_at;
    }

    public function getUpdatedAt() {
        return $this->updated_at;
    }

    public function getCompletedAt() {
        return $this->completed_at;
    }

    public function getNotes() {
        return $this->notes;
    }

    public function getArchived() {
        return $this->archived;
    }

    // setter methods for updating
    public function setRequesterName($name) {
        $this->requester_name = $name;
        $this->updated_at = date('Y-m-d H:i:s');
    }

    public function setRequesterEmail($email) {
        $this->requester_email = $email;
        $this->updated_at = date('Y-m-d H:i:s');
    }

    public function setRequesterPhone($phone) {
        $this->requester_phone = $phone;
        $this->updated_at = date('Y-m-d H:i:s');
    }

    public function setLocation($location) {
        $this->location = $location;
        $this->updated_at = date('Y-m-d H:i:s');
    }

    public function setBuilding($building) {
        $this->building = $building;
        $this->updated_at = date('Y-m-d H:i:s');
    }

    public function setUnit($unit) {
        $this->unit = $unit;
        $this->updated_at = date('Y-m-d H:i:s');
    }

    public function setDescription($description) {
        $this->description = $description;
        $this->updated_at = date('Y-m-d H:i:s');
    }

    public function setPriority($priority) {
        $this->priority = $priority;
        $this->updated_at = date('Y-m-d H:i:s');
    }

    public function setStatus($status) {
        $this->status = $status;
        $this->updated_at = date('Y-m-d H:i:s');
        
        // if completing, set completion date
        if ($status == 'Completed') {
            $this->completed_at = date('Y-m-d H:i:s');
        }
    }

    public function setAssignedTo($assigned_to) {
        $this->assigned_to = $assigned_to;
        $this->updated_at = date('Y-m-d H:i:s');
    }


    public function setNotes($notes) {
        $this->notes = $notes;
        $this->updated_at = date('Y-m-d H:i:s');
    }

    public function setArchived($archived) {
        $this->archived = $archived;
        $this->updated_at = date('Y-m-d H:i:s');
    }

    public function setCreatedAt($created_at) {
        $this->created_at = $created_at;
    }

    public function setUpdatedAt($updated_at) {
        $this->updated_at = $updated_at;
    }

    public function setCompletedAt($completed_at) {
        $this->completed_at = $completed_at;
    }

    // business logic methods
    public function isPending() {
        return $this->status == 'Pending';
    }

    public function isInProgress() {
        return $this->status == 'In Progress';
    }

    public function isCompleted() {
        return $this->status == 'Completed';
    }

    public function isCancelled() {
        return $this->status == 'Cancelled';
    }

    public function isHighPriority() {
        return $this->priority == 'High' || $this->priority == 'Emergency';
    }

    public function isEmergency() {
        return $this->priority == 'Emergency';
    }

    public function getFullLocation() {
        $location = $this->location;
        if (!empty($this->building)) {
            $location .= ', ' . $this->building;
        }
        if (!empty($this->unit)) {
            $location .= ', ' . $this->unit;
        }
        return $location;
    }

    public function getRequesterContact() {
        $contact = $this->requester_name;
        if (!empty($this->requester_email)) {
            $contact .= ' (' . $this->requester_email . ')';
        }
        if (!empty($this->requester_phone)) {
            $contact .= ' - ' . $this->requester_phone;
        }
        return $contact;
    }
}
