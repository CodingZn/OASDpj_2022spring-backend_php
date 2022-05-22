<?php

require_once "header.php";
require_once "Mysql.php";

$data = json_decode(file_get_contents('php://input'), true);

$username = $data["username"];
$phone = $data["phone"];
$address = $data["address"];
$email = $data["email"];
$password = $data["password"];

if (!checkFormats()){
    $data = array("message" => "信息格式有误！");
    http_response_code(400);
    exit(json_encode($data));
}


$mysql = new Mysql();

$tablename = 'customerlogon';

$sql = "INSERT INTO $tablename ".
    "(UserName, Pass) ".
    "VALUES ".
    "('$username','$password')";

$result = $mysql->query($sql);


if ($result){
    $data = array("message" => "注册成功！");
}
else{
    $data = array("message" => "注册失败！");
    http_response_code(400);
}

exit(json_encode($data));


function checkFormats(){
    global $address, $email, $password, $phone, $username;
    return (checkAddress($address) && checkEmail($email) && checkPassword($password)
        && checkPhone($phone) && checkUsername($username));
}
function checkUsername($value){
    if ($value == null || $value == "" || !preg_match("/^((?![^(0-9A-Za-z-_)]+).)*$/", $value)) {
        var_dump($value);
        return false;
    }
    return true;
}

function checkPhone($value){
    if ($value == null || $value == ""){
        var_dump($value);
        return false;
    }
    else if (!preg_match("/[0-9]+/", $value))
        return false;
    else
        return true;
}

function checkAddress($value){
    if ($value == null || $value == ""){
        var_dump($value);
        return false;
    }
    else
        return true;
}

function checkEmail($value){
    if ($value == null || $value == ""){
        var_dump($value);
        return false;
    }
    else if (!preg_match("/^\\w+@[a-z0-9]+.[a-z]{2,4}$/", $value)){
        var_dump($value);
        return false;
    }
    else
        return true;
}

function checkPassword($value){
    if ($value == null || $value == ""){
        var_dump($value);
        return false;
    }
    else {
        if (strlen($value) < 6) return false;
        $i = 0; $j = 0;
        if (preg_match("/[0-9]+/", $value)) $i++;
        if (preg_match("/[A-Za-z]+/", $value)) $i++; $j++;
        if (preg_match("/[+\-*=.:;]+/", $value)) $i++; $j++;
        if (preg_match("/[^0-9A-Za-z\+\-\*=\.:;_]+/", $value)) return false;
        if ($i <= 1 && $j == 0) return false;
        return true;
    }
}
