<?php

require_once "header.php";
require_once "Mysql.php";


$req_method = $_SERVER['REQUEST_METHOD'];

if ($req_method=="POST"){

    $data = json_decode(file_get_contents('php://input'), true);

    $username = $data["username"];
    $password = $data["password"];

    $mysql = new Mysql();
//查找用户名和邮箱
    $columnNames = array('CustomerID','UserName');
    $tablename = 'customers';
    $condition = "WHERE UserName='$username' OR Email='$username'";
    $result = $mysql->select($columnNames, $tablename, $condition);
    if (!$result){
        http_response_code(403);
        exit(json_encode(array('message'=>'用户不存在！')));
    }

    $user = mysqli_fetch_assoc($result);

    $CustomerID = $user['CustomerID'];

//查找密码和盐
    $result = $mysql->select(array('Pass', 'Salt'), 'customerlogon', "WHERE CustomerID='$CustomerID'");

    $user_logon=mysqli_fetch_assoc($result);

    $salt = $user_logon['Salt'];
    $hashed_password = $user_logon['Pass'];

    if (crypt($password, $salt) === $hashed_password)
        $success = true;
    else $success = false;

    if ($success){
        $token = crypt($CustomerID, $salt);
        $data = array("message" => "登录成功！", "token"=> $token);
        $data["username"]=$username;
        $data['CustomerID']=$CustomerID;
    }
    else{
        $data = array("message" => "密码错误！");
        http_response_code(403);
    }

    exit(json_encode($data));
}
else{
    http_response_code(405);
}
