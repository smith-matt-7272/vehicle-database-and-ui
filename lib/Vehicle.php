<?php
/**************************************
 * File Name: Vehicle.php
 * User: cst226, cst230, cst 232
 * Date: 2019-11-27
 * Project: cst226cst230cst232cweb280a3
 *
 * An entity representing a vehicle and its relevant information.
 **************************************/

require_once 'ORM/Entity.php';


class Vehicle extends ORM\Entity
{
    /**
     * A method called by $entity->validate.  Checks that the submitted vehicle matches all vehicleID validation rules.
     * @return array - if validation fails, an array containing an error message paired with the appropriate key, empty otherwise
     */
    public function validate_vehicleID(){
        $validationResult=[];
        $dispName = $this->getDisplayName('vehicleID');
        if(!empty($this->vehicleID) && $this->vehicleID<=0){$validationResult['vehicleID'] = $dispName . ' must be an integer greater than 0.';}
        return $validationResult;
    }
    public $vehicleID;

    /**
     * A method called by $entity->validate.  Checks that the submitted vehicle matches all make validation rules.
     * @return array - if validation fails, an array containing an error message paired with the appropriate key, empty otherwise
     */
    public function validate_make(){
        $validationResult=[];
        $dispName = $this->getDisplayName('make');
        if(empty(trim($this->make))){$validationResult['make'] = $dispName . ' is required and cannot be all spaces.';}
        elseif(strlen($this->make)>25){$validationResult['make'] = $dispName . ' has a maximum length of 25 characters.';}
        return $validationResult;
    }
    public $make;

    /**
     * A method called by $entity->validate.  Checks that the submitted vehicle matches all model validation rules.
     * @return array - if validation fails, an array containing an error message paired with the appropriate key, empty otherwise
     */
    public function validate_model(){
        $validationResult=[];
        $dispName = $this->getDisplayName('model');
        if(empty(trim($this->model))){$validationResult['model'] = $dispName . ' is required and cannot be all spaces.';}
        elseif(strlen($this->model)>25){$validationResult['model'] = $dispName . ' has a maximum length of 25 characters.';}
        return $validationResult;
    }
    public $model;

    /**
     * A method called by $entity->validate.  Checks that the submitted vehicle matches all type validation rules.
     * @return array - if validation fails, an array containing an error message paired with the appropriate key, empty otherwise
     */
    public function validate_type(){
        $validationResult=[];
        $dispName = $this->getDisplayName('type');
        if(empty(trim($this->type))){$validationResult['type'] = $dispName . ' is required and cannot be all spaces.';}
        elseif(strlen($this->type)>10){$validationResult['type'] = $dispName . ' has a maximum length of 10 characters.';}
        elseif(!in_array($this->type, $this->validTypes)){$validationResult['type'] = $dispName . ' must be ' . $this->typeString();}
        return $validationResult;
    }
    public $type;

    private $validTypes = ['Sedan', 'Compact', 'Cross Over', 'Truck']; //An array of the acceptable type values for ease of modification.

    /**
     * Converts the contents of $this->validTypes into a formatted string to be returned as an error message.
     * @return string - a string listing the acceptable values for type
     */
    private function typeString(){
        $rString = '';
        for ($i = 0; $i < count($this->validTypes) - 1; $i++)
        {
            $rString .= $this->validTypes[$i] . ', ';
        }
        $rString .= 'or ' . $this->validTypes[count($this->validTypes) - 1] . '.';
        return $rString;
    }

    /**
     * A method called by $entity->validate.  Checks that the submitted vehicle matches all year validation rules.
     * @return array - if validation fails, an array containing an error message paired with the appropriate key, empty otherwise
     */
    public function validate_year(){
        $validationResult=[];
        $dispName = $this->getDisplayName('year');
        //https://www.php.net/manual/en/function.intval.php intval function to guarantee that the year is in a number format
        //https://www.php.net/manual/en/function.date.php date function with the format key 'Y' returns only the year
        if($this->year > intval(date("Y")) + 1 || $this->year < 1898){$validationResult['year'] = $dispName . ' must be a year and cannot be newer than the current year plus one, or older than 1898.';}
        return $validationResult;
    }
    public $year;

    /***
     * Vehicle constructor.
     */
    public function __construct()
    {
        $this->addColumnDefinition('vehicleID','INTEGER','PRIMARY KEY AUTOINCREMENT');
        $this->addColumnDefinition('make','NVARCHAR(25)','not null');
        $this->addColumnDefinition('model','NVARCHAR(25)','not null');
        $this->addColumnDefinition('type','NVARCHAR(10)','');
        $this->addColumnDefinition('year','INTEGER','');
        $this->displayNames = [
            'vehicleID' => 'Vehicle ID',
            'make' => 'Make',
            'model' => 'Model',
            'type' => 'Type',
            'year' => 'Year'
        ];
    }
}