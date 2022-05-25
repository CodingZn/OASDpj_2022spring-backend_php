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

$base_upload_path = "../../../images/works/";

if ($req_method == "POST" || $req_method == 'PUT'){
    $PaintingID = $_GET['PaintingID'];
    $mysql = new Mysql();
    $painting = $mysql->selectAPaintingById($PaintingID);
    if (!$painting){
        http_response_code(404);
        exit(json_encode(array('message'=> "不存在该艺术品！请合法操作！")));
    }

    //判断文件是否合法
    $uploadFile = $_FILES['uploadPic'];
    if (!($uploadFile['type'] == 'image/jpeg' ||
        $uploadFile['type'] == 'image/jpg' ||
        $uploadFile['type'] == 'image/png' ||
        $uploadFile['type'] == 'image/gif')){
        http_response_code(400);
        exit(json_encode(array('message'=> "只能上传.jpg, .jpeg, .png, .gif格式的图片！")));
    }
    if ($uploadFile['size'] > 204800){
        http_response_code(400);
        exit(json_encode(array('message'=> "图片大小不能超过200KB！")));
    }

    //重命名
    $ImageFileName = $painting->ImageFileName;
    $uploadFile["name"] = $ImageFileName;

    //判断文件是否存在
    if (file_exists($base_upload_path . $uploadFile["name"]))
    {
        if ($req_method == "POST"){
            http_response_code(400);
            exit(json_encode(array('message'=> $uploadFile['name']." 文件已经存在！")));
        }
        $result = unlink($base_upload_path . $uploadFile["name"]);
        move_uploaded_file($uploadFile["tmp_name"], $base_upload_path . $uploadFile["name"]);
        exit(json_encode(array('message'=> "文件存储在: " . $base_upload_path . $uploadFile["name"])));
    }
    else
    {
        if ($req_method == "PUT"){
            http_response_code(404);
            exit(json_encode(array('message'=> "艺术品所对应的图片不存在！")));
        }
        move_uploaded_file($uploadFile["tmp_name"], $base_upload_path . $uploadFile["name"]);
        exit(json_encode(array('message'=> "文件存储在: " . $base_upload_path . $uploadFile["name"])));
    }
}
else{
    http_response_code(405);
}