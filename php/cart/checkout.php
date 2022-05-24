<?php
require_once "../header.php";
require_once "../Mysql.php";
require_once "../checkToken.php";

$req_method = $_SERVER['REQUEST_METHOD'];

$userID = checkCustomerToken();
if (!$userID) {
    $data = array("message"=> "无操作权限！");
    http_response_code(401);
    exit(json_encode($data));
}


if ($req_method == "POST"){//下单并付款
    if (array_key_exists('range',$_GET)){

        $range = $_GET['range'];
    }
    else {
        $range = "all";
    }


    http_response_code(200);

}
else{
    http_response_code(405);
}

