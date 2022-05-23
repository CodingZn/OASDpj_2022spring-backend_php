<?php
require_once "../header.php";
require_once "../Mysql.php";
require_once "../checkToken.php";

$req_method = $_SERVER['REQUEST_METHOD'];



if ($req_method == "GET"){//获取自己的艺术品信息，包括已发布和已卖出

    http_response_code(405);

}
elseif ($req_method == "PATCH"){//修改已发布的艺术品
    http_response_code(405);

}
elseif ($req_method == "DELETE"){//删除已发布的艺术品
    http_response_code(405);

}
else{
    http_response_code(405);
}
