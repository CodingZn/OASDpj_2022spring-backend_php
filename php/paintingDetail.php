<?php
require_once "header.php";
require_once "Mysql.php";

$req_method = $_SERVER['REQUEST_METHOD'];


if ($req_method == "GET"){
    if (array_key_exists('PaintingID',$_GET)){

        $PaintingID = $_GET['PaintingID'];
        var_dump($PaintingID);
    }
    else {
        $data = array("message" => "缺少必要参数！");
        exit(json_encode($data));
    }


    $mysql = new Mysql();
    $columnNames = array('PaintingID', 'Title', 'ArtistID', 'MSRP');//to add
    $painting = $mysql->selectOneObjById($columnNames,'paintings', 'PaintingID', $PaintingID);

    //process foreign key

    $data = array("painting" => $painting);
    exit(json_encode($data));

}elseif ($req_method == "POST"){
    http_response_code(405);
}
else{
    http_response_code(405);
}