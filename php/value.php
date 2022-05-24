<?php
require_once "header.php";
require_once "Mysql.php";

$req_method = $_SERVER['REQUEST_METHOD'];

if ($req_method == "GET"){
    if (array_key_exists('property',$_GET)){
        $property = $_GET['property'];
    }
    else {
        $data = array("message" => "缺少必要参数！");
        http_response_code(400);
        exit(json_encode($data));
    }

    if ($property == 'genre'){
        $mysql = new Mysql();
        $genres = $mysql->getGenres();
        $data = array("genres" => $genres);
        exit(json_encode($data));
    }

    if ($property == 'subject'){
        $mysql = new Mysql();
        $genres = $mysql->getSubjects();
        $data = array("subjects" => $genres);
        exit(json_encode($data));
    }

    if ($property == 'artist'){
        $mysql = new Mysql();
        $genres = $mysql->getArtists();
        $data = array("artists" => $genres);
        exit(json_encode($data));
    }


}
else{
    http_response_code(405);
}