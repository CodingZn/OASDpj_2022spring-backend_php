<?php
require_once "../header.php";
require_once "../Mysql.php";

$req_method = $_SERVER['REQUEST_METHOD'];

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
    if (!$painting){
        http_response_code(404);
        exit(json_encode(array('message'=>"该艺术品不存在！")));
    }
    $popularity = $painting->Popularity;
    $popularity++;
    $mysql->update('paintings', array('Popularity'=>$popularity),
        "WHERE PaintingID='$PaintingID'");

    $data = array("painting" => $painting);
    exit(json_encode($data));

}
else{
    http_response_code(405);
}