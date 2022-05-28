<?php

require_once "./header.php";
require_once "./Mysql.php";

$req_method = $_SERVER['REQUEST_METHOD'];
if (array_key_exists('QUERY_STRING',$_SERVER))
    $req_query = $_SERVER['QUERY_STRING'];

if ($req_method == "GET"){

    $rolling5Pics = getRolling5Pics();
    $newest3Paintings = getNewest3Paintings();
    $popular3Paintings = getPopular3Paintings();

    $data = array("rolling5Pics"=> $rolling5Pics,
        "newest3Paintings"=>$newest3Paintings, "popular3Paintings"=>$popular3Paintings);
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
    $mysql = new Mysql();
    $rows = $mysql->selectAllShortPaintings();

    usort($rows, "sort_by_ReleaseDate");
    $len = count($rows);
    for ($i=0;$i<3;$i++){
        array_push($newest3Paintings, $rows[$len - $i - 1]);
    }
    return $newest3Paintings;
}

function getPopular3Paintings(){
    $newest3Paintings = array();
    $mysql = new Mysql();
    $rows = $mysql->selectAllShortPaintings();

    usort($rows, "sort_by_Popularity");
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

function sort_by_ReleaseDate($painting1, $painting2){
    if ($painting1->ReleaseDate < $painting2->ReleaseDate)
        return -1;
    if ($painting1->ReleaseDate == $painting2->ReleaseDate)
        return 0;
    if ($painting1->ReleaseDate > $painting2->ReleaseDate)
        return 1;
}

function sort_by_Popularity($painting1, $painting2){
    if ($painting1->Popularity < $painting2->Popularity)
        return -1;
    if ($painting1->Popularity == $painting2->Popularity)
        return 0;
    if ($painting1->Popularity > $painting2->Popularity)
        return 1;
}