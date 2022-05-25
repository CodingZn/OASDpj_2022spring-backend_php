<?php

require_once "header.php";
require_once "Mysql.php";

$req_method = $_SERVER['REQUEST_METHOD'];

if ($req_method=="POST"){

    $data = json_decode(file_get_contents('php://input'), true);

    $username = $data["username"];
    $phone = $data["phone"];
    $address = $data["address"];
    $email = $data["email"];
    $password = $data["password"];

    $mysql=new Mysql();
//检查表单格式
    if (!checkFormats()){
        $data = array("message" => "信息格式有误！");
        http_response_code(400);
        exit(json_encode($data));
    }
//检查用户名或邮箱是否已经存在
    $result = $mysql->select(array('CustomerID'), 'customers', "WHERE UserName='$username'");
    if ($result){
        $data = array("message" => "用户名已存在！");
        http_response_code(400);
        exit(json_encode($data));
    }
    $result = $mysql->select(array('CustomerID'), 'customers', "WHERE Email='$email'");
    if ($result){
        $data = array("message" => "邮箱已存在！");
        http_response_code(400);
        exit(json_encode($data));
    }

    //生成盐和加密密码
    $salt = md5(rand());
    $hashed_password = crypt($password, $salt);

    //在logon表中加入相应信息，并获取ID
    $columnNames = array('Pass', 'Salt');
    $columnValues = array($hashed_password, $salt);
    $result = $mysql->insert('customerlogon', $columnNames, $columnValues);
    if (!$result){
        $data = array("message" => "未知原因，注册失败！");
        http_response_code(500);
        exit(json_encode($data));
    }

    $row = mysqli_fetch_assoc($result);
    $CustomerID = $row['LAST_INSERT_ID()'];

//向customers表里插入数据
    $columnNames = array('CustomerID', 'UserName', 'Email', 'Address', 'Phone');
    $columnValues = array($CustomerID, $username, $email, $address, $phone);
    $result = $mysql->insert('customers', $columnNames, $columnValues);
    if (!$result){
        $data = array("message" => "未知原因，注册失败！");
        http_response_code(500);
        exit(json_encode($data));
    }

    $data = array("message" => "注册成功！");
    exit(json_encode($data));

}
else{
    http_response_code(405);
}

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
