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

if ($req_method == "GET"){
    if (array_key_exists('PaintingID',$_GET)){
        $PaintingID = $_GET['PaintingID'];
    }
    else {
        $data = array("message" => "缺少必要参数！");
        exit(json_encode($data));
    }

    $mysql = new Mysql();
    $reviews = $mysql->selectAPaintingById($PaintingID);

    $data = array('painting'=>$reviews);
    exit(json_encode($data));

}
elseif ($req_method == "POST"){
    if(array_key_exists('PaintingID', $_GET))
        $PaintingID = $_GET['PaintingID'];
    else{
        http_response_code(400);
    exit(json_encode(array("message"=>"缺少必要的请求参数！")));
}

    //新增
    http_response_code(200);
}
elseif ($req_method == "PATCH"){
    if(array_key_exists('PaintingID', $_GET))
        $PaintingID = $_GET['PaintingID'];
    else{
        http_response_code(400);
        exit(json_encode(array("message"=>"缺少必要的请求参数！")));
    }

    //修改
    http_response_code(200);
}
else{
    http_response_code(405);
}
