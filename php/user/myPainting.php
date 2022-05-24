<?php
require_once "../header.php";
require_once "../Mysql.php";
require_once "../checkToken.php";

$req_method = $_SERVER['REQUEST_METHOD'];

$userID = checkCustomerToken();
if (!$userID) {
    $data = array("message"=> "无操作权限！");
    http_response_code(401);
    exit(json_encode($data));
}

if ($req_method == "GET"){//获取自己的艺术品信息，包括已发布和已卖出
    if(array_key_exists('type', $_GET))
        $type = $_GET['type'];

    //查询操作
    if($type=='released'){
        $mysql = new Mysql();
        $paintingIDList=$mysql->selectAllPaintingIDofCustomer($userID);

        $paintingList=$mysql->selectPartShortPaintingsByIDList($paintingIDList);
    }
    elseif ($type=='sold'){
        $mysql = new Mysql();
        $soldPaintingIDList=$mysql->selectAllSoldPaintingIDofCustomer($userID);
        $soldPaintingList=$mysql->selectPartShortPaintingsByIDList($soldPaintingIDList);
//订单时间、购买人信息
    }
    else{
        http_response_code(400);
        exit(json_encode(array('message'=>'请求参数错误！')));
    }

    $data = array('releasedPaintings'=>$paintingList, 'message'=>'操作成功！');
    http_response_code(200);

    exit(json_encode($data));
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
