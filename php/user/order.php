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

if ($req_method == "GET"){//获取自己的订单信息

    $mysql = new Mysql();
    $orderIDList = $mysql->selectAllOrderIDofCustomer($userID);
    $orderList=array();
    for ($i=0;$i<count($orderIDList);$i++){
        array_push($orderList, $mysql->selectAOrder($orderIDList[$i]));
    }
    $data=array('orderList'=>$orderList);

    http_response_code(200);

    exit(json_encode($data));
}
else{
    http_response_code(405);
}

