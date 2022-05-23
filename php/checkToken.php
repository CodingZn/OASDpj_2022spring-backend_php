<?php
require_once "Mysql.php";

function checkCustomerToken(){
    $CustomerID = $_COOKIE['CustomerID'];
    $token = $_COOKIE['token'];

    $mysql = new Mysql();

    $row = $mysql->selectOneObjById('*', 'customerlogon',
        'CustomerID', $CustomerID);
    $salt = $row['Salt'];

    if (crypt($CustomerID, $salt) == $token){
        return $CustomerID;
    }
    else return false;
}
