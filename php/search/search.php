<?php
require_once "../header.php";
require_once "../Mysql.php";
require_once "../checkToken.php";

$req_method = $_SERVER['REQUEST_METHOD'];



if ($req_method == "GET"){//搜索

    http_response_code(405);

}
else{
    http_response_code(405);
}
