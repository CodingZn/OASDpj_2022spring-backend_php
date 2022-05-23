<?php

require_once "header.php";
require_once "Mysql.php";

$data = json_decode(file_get_contents('php://input'), true);

$username = $data["username"];
$password = $data["password"];

$mysql = new Mysql();

$tablename = "customerlogon";

$sql = "SELECT * FROM $tablename WHERE UserName='$username'";

$result = $mysql->query($sql);

$row = mysqli_fetch_assoc($result);

$CustomerID = $row['CustomerID'];

$salt = $row['Salt'];
$hashed_password = $row['Pass'];

if (crypt($password, $salt) === $hashed_password)
    $success = true;
else $success = false;

if ($success){
    $token = crypt($CustomerID, $salt);
    $data = array("message" => "登录成功！", "token", $token);
    $data["user"]=$username;
}
else{
    $data = array("message" => "用户名或密码错误！");
    http_response_code(400);
}

exit(json_encode($data));