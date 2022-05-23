<?php
require_once "header.php";
require_once "Mysql.php";

$req_method = $_SERVER['REQUEST_METHOD'];

function aaa($value, &$key){//问助教吧
    var_dump($key);
    $key = $key[0];
    var_dump($key);
}

if ($req_method == "GET"){
    if (array_key_exists('QUERY_STRING',$_SERVER)){
        $req_query = $_SERVER['QUERY_STRING'];
        $query = array();
        parse_str($req_query, $query);
        var_dump($query);
array_walk($query, "aaa");
        var_dump($query);
    }
    if(!array_key_exists('PaintingID', $query)){
        $data = array("message" => "缺少必要参数！");
        exit(json_encode($data));
    }
    $PaintingID = $req_query['PaintingID'];


    $mysql = new Mysql();
    $columnNames = array('PaintingID', 'Title', 'ArtistID', 'MSRP', '??');
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