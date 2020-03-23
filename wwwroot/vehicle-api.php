<?php
/**************************************
 * File Name: vehicle-api.php
 * User: cst226, cst230, cst 232
 * Date: 2019-11-27
 * Project: cst226cst230cst232cweb280a3
 *
 * The API for communicating between the Vehicle UI and Vehicle database.
 **************************************/

sleep(3);

require_once '../lib/ORM/Repository.php';
require_once '../lib/Vehicle.php';

//If FormData js object is not used in the UI then PHP will not fill $_POST superglobal
//We need to use a file that is created at 'php://input'
//$_REQUEST contains the keys/values of both $_POST and $_GET - if it is empty, then use php://input
$requestData = empty($_REQUEST)?json_decode(file_get_contents('php://input'), true) : $_REQUEST;//decode json as assoc array

$repo = new ORM\Repository('../db/vehicle.db');

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $resultToJSONEncode=handleGET($repo,$requestData['searchFor']);
        break;
    case 'POST':
        $resultToJSONEncode=handlePOST((new Vehicle())->parseArray($requestData), $repo);
        break;
    case 'PUT':
        $resultToJSONEncode=handlePUT((new Vehicle())->parseArray($requestData), $repo);
        break;
    default:
        header("http/1.1 405 Method Not Allowed");
        $resultToJSONEncode = "METHOD NOT ALLOWED";
}


$repo->close(); //Don't forget to close the repo!

header('Content-type:application/json');
echo json_encode($resultToJSONEncode);

//GET - SELECT VEHICLES
function handleGET($repo, $searchString) {
    $vehicle = new Vehicle();
    if(!empty($searchString)){
        //set values for all text vehicle properties - using wildcards
        $vehicle->make = '%'.$searchString.'%';
        $vehicle->model = '%'.$searchString.'%';
        $vehicle->type = '%'.$searchString.'%';
        $vehicle->year = '%'.$searchString.'%';
    }
    $result = $repo->select($vehicle, true);
    if(!is_array($result)){//not array means an error code was returned
        header("http/1.1 418 I'm a teapot");
        $result = $repo->getLastStatement(); //return sql debug
    }elseif (empty($result)){
        header("http/1.1 404 Not Found");
    }
    //default status code is 200 - no need to add header
    return $result;
}

//POST - INSERT VEHICLE
function handlePOST($vehicle, $repo) {
    $result = $vehicle->validate();
    if(count($result)) {
        header("http/1.1 422 Unprocessable Entity");
    }elseif($repo->insert($vehicle)<1){//indicated database error - if insert returns an int less than 1
        header("http/1.1 418 I'm a teapot");
        $result = $repo->getLastStatement(); //return sql debug
    }else{
        header("http/1.1 201 Created");
        $result = $vehicle; //send back the vehicle with generated ID
    }
    return $result;
}

//PUT - UPDATE VEHICLE
function handlePUT($vehicle, $repo) {
    $result = $vehicle->validate();
    if(count($result)) {
        header("http/1.1 422 Unprocessable Entity");
    }elseif($repo->update($vehicle)<1){//indicated database error - if insert returns an int less than 1
        header("http/1.1 418 I'm a teapot");
        $result = $repo->getLastStatement(); //return sql debug
    }else{
        $result = $vehicle; //send back the vehicle as an indication of success
    }
    return $result;
}