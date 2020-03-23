<?php

require "../lib/Vehicle.php";
require "../lib/ORM/Repository.php";

$repo = new \ORM\Repository("../db/vehicle.db");
$vehicle = new Vehicle();
$tables = [$vehicle];
$repo->createTables($tables);