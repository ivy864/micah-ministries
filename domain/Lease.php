<?php 

class Lease {
    private $id;
    private $tenant_first_name;
    private $tenant_last_name;
    private $property_street;
    private $unit_number;
    private $property_city;
    private $property_state;
    private $property_zip;
    private $start_date;
    private $expiration_date;
    private $monthly_rent;
    private $security_deposit;
    private $lease_form;
    private $case_manager;
    private $program_type;
    private $status;
    private $created_at;
    private $updated_at;

    /**
     * constructor for lease
     * @param string $id - unique identifier
     * @param string $tenant_first_name - tenant's first name
     * @param string $tenant_last_name - tenant's last name
     * @param string $property_street - street address of property
     * @param string $unit_number - unit number
     * @param string $property_city - city of property
     * @param string $property_state - state of property (2-letter code)
     * @param string $property_zip - zip code of property
     * @param string $start_date - lease start date (YYYY-MM-DD)
     * @param string $expiration_date - lease expiration date (YYYY-MM-DD)
     * @param decimal $monthly_rent - monthly rent amount
     * @param decimal $security_deposit - security deposit amount
     * @param MEDIIUMBLOB $lease_form - pdf lease form
     * @param string $case_manager - user of type Case Manager assigned to a lease
     * @param string $program_type - program type (e.g., "Emergency Housing", "Transitional Housing")
     * @param string $status - lease status (Active, Expired, Terminated)
     */
    public function __construct($id, $tenant_first_name, $tenant_last_name, $property_street, 
                               $unit_number, $property_city, $property_state, $property_zip,
                               $start_date, $expiration_date, $case_manager, $lease_form, $monthly_rent = null, 
                               $security_deposit = null, $program_type = null, $status = 'Active') {
        $this->id = $id;
        $this->tenant_first_name = $tenant_first_name;
        $this->tenant_last_name = $tenant_last_name;
        $this->property_street = $property_street;
        $this->unit_number = $unit_number;
        $this->property_city = $property_city;
        $this->property_state = $property_state;
        $this->property_zip = $property_zip;
        $this->start_date = $start_date;
        $this->expiration_date = $expiration_date;
        $this->monthly_rent = $monthly_rent;
        $this->security_deposit = $security_deposit;
        $this->lease_form = $lease_form;
        $this->case_manager = $case_manager;
        $this->program_type = $program_type;
        $this->status = $status;
        
        // set timestamps
        $this->created_at = date('Y-m-d H:i:s');
        $this->updated_at = date('Y-m-d H:i:s');
    }

    // getter methods
    public function getID() {
        return $this->id;
    }

    public function getTenantFirstName() {
        return $this->tenant_first_name;
    }

    public function getTenantLastName() {
        return $this->tenant_last_name;
    }

    public function getPropertyStreet() {
        return $this->property_street;
    }

    public function getUnitNumber() {
        return $this->unit_number;
    }

    public function getPropertyCity() {
        return $this->property_city;
    }

    public function getPropertyState() {
        return $this->property_state;
    }

    public function getPropertyZip() {
        return $this->property_zip;
    }

    public function getStartDate() {
        return $this->start_date;
    }

    public function getExpirationDate() {
        return $this->expiration_date;
    }

    public function getMonthlyRent() {
        return $this->monthly_rent;
    }

    public function getSecurityDeposit() {
        return $this->security_deposit;
    }

    public function getLeaseForm() {
        return $this->lease_form;
    }

    public function getCaseManager() {
        return $this->case_manager;
    }

    public function getProgramType() {
        return $this->program_type;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getCreatedAt() {
        return $this->created_at;
    }

    public function getUpdatedAt() {
        return $this->updated_at;
    }

    // setter methods for updating
    public function setTenantFirstName($name) {
        $this->tenant_first_name = $name;
        $this->updated_at = date('Y-m-d H:i:s');
    }

    public function setTenantLastName($name) {
        $this->tenant_last_name = $name;
        $this->updated_at = date('Y-m-d H:i:s');
    }

    public function setPropertyStreet($street) {
        $this->property_street = $street;
        $this->updated_at = date('Y-m-d H:i:s');
    }

    public function setUnitNumber($unit) {
        $this->unit_number = $unit;
        $this->updated_at = date('Y-m-d H:i:s');
    }

    public function setPropertyCity($city) {
        $this->property_city = $city;
        $this->updated_at = date('Y-m-d H:i:s');
    }

    public function setPropertyState($state) {
        $this->property_state = $state;
        $this->updated_at = date('Y-m-d H:i:s');
    }

    public function setPropertyZip($zip) {
        $this->property_zip = $zip;
        $this->updated_at = date('Y-m-d H:i:s');
    }

    public function setStartDate($date) {
        $this->start_date = $date;
        $this->updated_at = date('Y-m-d H:i:s');
    }

    public function setExpirationDate($date) {
        $this->expiration_date = $date;
        $this->updated_at = date('Y-m-d H:i:s');
    }

    public function setMonthlyRent($rent) {
        $this->monthly_rent = $rent;
        $this->updated_at = date('Y-m-d H:i:s');
    }

    public function setSecurityDeposit($deposit) {
        $this->security_deposit = $deposit;
        $this->updated_at = date('Y-m-d H:i:s');
    }

    public function setLeaseForm($lease_form) {
        $this->lease_form = $lease_form;
        $this->updated_at = date('Y-m-d H:i:s');
    }

    public function setCaseManager($case_manager) {
        $this->case_manager = $case_manager;
        $this->updated_at = date('Y-m-d H:i:s');
    }

    public function setProgramType($type) {
        $this->program_type = $type;
        $this->updated_at = date('Y-m-d H:i:s');
    }

    public function setStatus($status) {
        $this->status = $status;
        $this->updated_at = date('Y-m-d H:i:s');
    }

    public function setCreatedAt($created_at) {
        $this->created_at = $created_at;
    }

    public function setUpdatedAt($updated_at) {
        $this->updated_at = $updated_at;
    }

    // business logic methods
    public function isActive() {
        return $this->status == 'Active';
    }

    public function isExpired() {
        return $this->status == 'Expired';
    }

    public function isTerminated() {
        return $this->status == 'Terminated';
    }

    public function isExpiringSoon($days = 30) {
        if (!$this->isActive()) {
            return false;
        }
        $expiration = strtotime($this->expiration_date);
        $threshold = strtotime("+{$days} days");
        return $expiration <= $threshold && $expiration >= time();
    }

    public function getTenantFullName() {
        return trim($this->tenant_first_name . ' ' . $this->tenant_last_name);
    }

    public function getFullAddress() {
        $address = $this->property_street;
        if (!empty($this->unit_number)) {
            $address .= ', Unit ' . $this->unit_number;
        }
        $address .= ', ' . $this->property_city . ', ' . $this->property_state . ' ' . $this->property_zip;
        return $address;
    }

    public function getDaysUntilExpiration() {
        $expiration = strtotime($this->expiration_date);
        $now = time();
        $diff = $expiration - $now;
        return floor($diff / (60 * 60 * 24));
    }
}

