<?php
require_once "../header.php";
require_once "../Mysql.php";
require_once "../checkToken.php";

$req_method = $_SERVER['REQUEST_METHOD'];



if ($req_method == "GET"){//获取自己的艺术品信息，包括已发布和已卖出
    if(array_key_exists('type', $_GET))
        $type = $_GET['type'];
    else
        $type = "all";

    //查询操作

    http_response_code(200);

}
elseif ($req_method == "PATCH"){//修改已发布的艺术品
    if(array_key_exists('PaintingID', $_GET))
        $PaintingID = $_GET['PaintingID'];
    else{
        http_response_code(400);
        exit(json_encode(array("message"=>"缺少必要的请求参数！")));
    }

    //修改操作
    http_response_code(200);

}
elseif ($req_method == "DELETE"){//删除已发布的艺术品
    if(array_key_exists('PaintingID', $_GET))
        $PaintingID = $_GET['PaintingID'];
    else{
        http_response_code(400);
        exit(json_encode(array("message"=>"缺少必要的请求参数！")));
    }

    //删除操作

    http_response_code(200);

}
else{
    http_response_code(405);
}
