<?php
require_once "Mysql.php";

$mysql = new Mysql();
var_dump($mysql->selectAllShortPaintings());
