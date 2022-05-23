<?php
require_once "../header.php";
require_once "../Mysql.php";
require_once "../checkToken.php";

$req_method = $_SERVER['REQUEST_METHOD'];

if (!checkCustomerToken()) {
    $data = array("message"=> "无操作权限！");
    http_response_code(401);
    exit(json_encode($data));
}

if ($req_method == "GET"){//获取用户信息

    http_response_code(200);

}
elseif ($req_method == "POST"){

    http_response_code(200);
    //充值
}
else{
    http_response_code(405);
}
