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
    $sql = "SELECT CustomerID,UserName FROM customers WHERE UserName='$username' OR Email='$username'";
    $result = $mysql->query($sql);
    if (!$result){
        http_response_code(403);
        exit(json_encode(array('message'=>'用户不存在！')));
    }

    $user = mysqli_fetch_assoc($result);
    $CustomerID = $user['CustomerID'];

//查找密码和盐
    $sql = "SELECT Pass,Salt FROM customers WHERE CustomerID='$CustomerID'";
    $result = $mysql->query($sql);

    $user_logon=mysqli_fetch_assoc($result);

    $salt = $user_logon['Salt'];
    $hashed_password = $user_logon['Pass'];

    if (crypt($password, $salt) === $hashed_password)
        $success = true;
    else $success = false;

    if ($success){
        $token = crypt($CustomerID, $salt);
        $data = array("message" => "登录成功！", "token"=> $token);
        $data["user"]=$username;
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
