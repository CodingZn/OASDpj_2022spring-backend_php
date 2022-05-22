<?php

require_once "./header.php";
require_once "./Mysql.php";

$req_method = $_SERVER['REQUEST_METHOD'];
if (array_key_exists('QUERY_STRING',$_SERVER))
    $req_query = $_SERVER['QUERY_STRING'];

if ($req_method == "GET"){

    $rolling5Pics = getRolling5Pics();
    $newest3Paintings = getNewest3Paintings();

    $data = array("rolling5Pics"=> $rolling5Pics, "newest3Paintings"=>$newest3Paintings);
    exit(json_encode($data));

}elseif ($req_method == "POST"){
    http_response_code(405);
}
else{
    http_response_code(405);
}


function getRolling5Pics(){
    $rolling5Pics = array();
    $columns = array("PaintingID", "ImageFileName");
    $mysql = new Mysql();
    $result = $mysql->select($columns, "paintings");
    $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
    shuffle($rows);
    for ($i=0;$i<5;$i++){
        array_push($rolling5Pics, $rows[$i]);
    }
    return $rolling5Pics;
}

function getNewest3Paintings(){
    $newest3Paintings = array();
    $columns = array("PaintingID", "ImageFileName", "Title", "MSRP", "ReleaseDate");
    $mysql = new Mysql();
    $result = $mysql->select($columns, "paintings");
    $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
    usort($rows, "sort_by_PaintingID");
    $len = count($rows);
    for ($i=0;$i<3;$i++){
        array_push($newest3Paintings, $rows[$len - $i - 1]);
    }
    return $newest3Paintings;
}

function sort_by_PaintingID($painting1, $painting2){
    if ($painting1["PaintingID"] < $painting2["PaintingID"])
        return -1;
    if ($painting1["PaintingID"] == $painting2["PaintingID"])
        return 0;
    if ($painting1["PaintingID"] > $painting2["PaintingID"])
        return 1;
}