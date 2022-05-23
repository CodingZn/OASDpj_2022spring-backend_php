<?php
require_once "Mysql.php";

//返回有效用户id或false
function checkCustomerToken(){
    if (!array_key_exists('CustomerID', $_COOKIE)){
        return false;
    }
    if (!array_key_exists('token', $_COOKIE)){
        return false;
    }
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
