<?php
require_once "../header.php";
require_once "../Mysql.php";
require_once "../checkToken.php";

$req_method = $_SERVER['REQUEST_METHOD'];



if ($req_method == "GET"){//搜索

//参数，都为optional
    if(array_key_exists('text', $_GET))
        $text = $_GET['text'];
    else
        $text = "";

    if(array_key_exists('text', $_GET))
        $orderby = $_GET['orderby'];
    else
        $orderby = "title";

    if(array_key_exists('text', $_GET))
        $page = $_GET['page'];
    else
        $page = "1";

    if(array_key_exists('text', $_GET))
        $pagesize = $_GET['pagesize'];
    else
        $pagesize = "10";


    http_response_code(200);

}
else{
    http_response_code(405);
}

