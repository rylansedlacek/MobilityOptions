<?php
/**
 * Encapsulated version of a dbs entry.
 */
class Event {
    private $id;
    private $name;
    private $type;
    private $startDate;
    private $startTime;
    private $endTime;
    private $endDate;
    private $description;
    private $capacity;
    private $location;
    private $attended;
    private $dropoff_contact;
    private $access;
    private $completed;

    // new dbevents fields
    private $rider_id;
    private $driver_id;
    private $vehicle_id;
    private $pickup_location;
    private $dropoff_location;
    private $trip_status;
    private $mileage_start;
    private $mileage_end;

    function __construct($id, $name, $type, $startDate, $startTime, $endTime, $endDate, $description, $capacity, $location, $attended, $dropoff_contact, $access, $completed,
                         $rider_id = null, $driver_id = null, $vehicle_id = null,
                         $pickup_location = null, $dropoff_location = null,
                         $trip_status = null, $mileage_start = null, $mileage_end = null) {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->startDate = $startDate;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->endDate = $endDate;
        $this->description = $description;
        $this->capacity = $capacity;
        $this->location = $location;
        $this->attended = $attended;
        $this->dropoff_contact = $dropoff_contact;
        $this->access = $access;
        $this->completed = $completed;
        
        // new dbevents fields
        $this->rider_id = $rider_id;
        $this->driver_id = $driver_id;
        $this->vehicle_id = $vehicle_id;
        $this->pickup_location = $pickup_location;
        $this->dropoff_location = $dropoff_location;
        $this->trip_status = $trip_status;
        $this->mileage_start = $mileage_start;
        $this->mileage_end = $mileage_end;
    }

    function getID() {
        return $this->id;
    }

    function getName() {
        return $this->name;
    }

    function getStartDate() {
        return $this->startDate;
    }

    function getStartTime() {
        return $this->startTime;
    }

    function getEndTime() {
        return $this->endTime;
    }

    function getEndDate() {
        return $this->endDate;
    }

    function getDescription() {
        return $this->description;
    }

    function getLocation() {
        return $this->location;
    }

    function getCapacity() {
        return $this->capacity;
    }

    function getCompleted() {
        return $this->completed;
    }

    function getEventType(){
        return $this->type;
    }

    function getDropoffContact(){
        return $this->dropoff_contact;
    }

    function getAttended(){
        return $this->attended;
    }

    function getAccess(){
        return $this->access;
    }

    // add new fields for dbevents
    function getRiderId(){
        return $this->rider_id;
    }

    function getDriverId(){
        return $this->driver_id;
    }

    function getVehicleId(){
        return $this->vehicle_id;
    }

    function getPickupLocation(){
        return $this->pickup_location;
    }

    function getDropoffLocation(){
        return $this->dropoff_location;
    }

    function getTripStatus(){
        return $this->trip_status;
    }

    function getMileageStart(){
        return $this->mileage_start;
    }

    function getMileageEnd(){
        return $this->mileage_end;
    }
}
