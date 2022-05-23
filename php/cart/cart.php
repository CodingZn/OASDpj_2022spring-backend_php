<?php
require_once "../header.php";
require_once "../Mysql.php";

$req_method = $_SERVER['REQUEST_METHOD'];



if ($req_method == "GET"){


}elseif ($req_method == "POST"){
    http_response_code(405);
}
else{
    http_response_code(405);
}
