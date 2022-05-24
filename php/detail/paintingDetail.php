<?php
require_once "../header.php";
require_once "../Mysql.php";

$req_method = $_SERVER['REQUEST_METHOD'];

//if (!checkCustomerToken()) {
//    $data = array("message"=> "无操作权限！");
//    http_response_code(401);
//    exit(json_encode($data));
//}

if ($req_method == "GET"){
    if (array_key_exists('PaintingID',$_GET)){

        $PaintingID = $_GET['PaintingID'];
    }
    else {
        $data = array("message" => "缺少必要参数！");
        exit(json_encode($data));
    }


    $mysql = new Mysql();
    $painting = $mysql->selectAPaintingById($PaintingID);

    $data = array("painting" => $painting);
    exit(json_encode($data));

}
else{
    http_response_code(405);
}