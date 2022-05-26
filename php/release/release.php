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

//判断新增艺术家或从已有里选择，获取ArtistID，只能单选
function getOrCreateArtist($data, Mysql $mysql)
{
    if (!array_key_exists('ArtistName', $data)) {//表单中不存在新name，为已有
        $ArtistID = $data['ArtistID'];
    } else {//新增一个Artist，仅包括Name
        $ArtistName = $data['ArtistName'];
        //create artist with name
        $columnNames = array('FirstName', 'LastName');
        $columnValues = array('', $ArtistName);
        $result = $mysql->insert('artists', $columnNames, $columnValues);
        if ($result)
            $ArtistID = mysqli_fetch_assoc($result)['LAST_INSERT_ID()'];
        else return null;
    }
    return $ArtistID;
}

//判断新增流派或从已有里选择，获取GenreID，可以多选，返回数组
function getOrCreateGenre($data, Mysql $mysql)
{
    $GenreIDList = array();
    if (!array_key_exists('GenreName', $data)) {//表单中不存在name，为已有
        $GenreIDList = $data['GenreID'];
    } else {
        if (array_key_exists('GenreID', $data)){
            $GenreIDList = $data['GenreID'];
        }
        //新增一个Genre，仅包括Name
        $GenreName = $data['GenreName'];
        $result = $mysql->select("*", 'genres', "WHERE GenreName='$GenreName'");
        if ($result){//已经存在该name
            $GenreID = mysqli_fetch_assoc($result)['GenreID'];
            array_push($GenreIDList, $GenreID);
        }
        else{//create genre with name
            $columnNames = array('GenreName');
            $columnValues = array($GenreName);
            $result = $mysql->insert('genres', $columnNames, $columnValues);
            if ($result){
                $GenreID = mysqli_fetch_assoc($result)['LAST_INSERT_ID()'];
                array_push($GenreIDList, $GenreID);
            }
        }
    }
    return $GenreIDList;
}

//判断新增主题或从已有里选择，获取SubjectID，可以多选，返回数组
function getOrCreateSubject($data, Mysql $mysql)
{
    $SubjectIDList = array();
    if (!array_key_exists('SubjectName', $data)) {//表单中不存在name，为已有
        $SubjectIDList = $data['SubjectID'];
    } else {
        if (array_key_exists('SubjectID', $data)){
            $SubjectIDList = $data['SubjectID'];
        }
        //新增一个Subject，仅包括Name
        $SubjectName = $data['SubjectName'];
        $result = $mysql->select("*", 'genres', "WHERE SubjectID='$SubjectName'");
        if ($result){//已经存在该name
            $SubjectID = mysqli_fetch_assoc($result)['SubjectID'];
            array_push($SubjectIDList, $SubjectID);
        }
        else{//create with name
            $columnNames = array('SubjectName');
            $columnValues = array($SubjectName);
            $result = $mysql->insert('subjects', $columnNames, $columnValues);
            if ($result){
                $SubjectID = mysqli_fetch_assoc($result)['LAST_INSERT_ID()'];
                array_push($SubjectIDList, $SubjectID);
            }
        }
    }
    return $SubjectIDList;
}

if ($req_method == "GET"){//查找一个艺术品
    if (array_key_exists('PaintingID',$_GET)){
        $PaintingID = $_GET['PaintingID'];
    }
    else {
        $data = array("message" => "缺少必要参数！");
        exit(json_encode($data));
    }

    $mysql = new Mysql();
    $painting = $mysql->selectAPaintingById($PaintingID);

    $data = array('painting'=>$painting);
    exit(json_encode($data));

}
elseif ($req_method == "POST"){
//获取表单
    $data = json_decode(file_get_contents('php://input'), true);
    $mysql = new Mysql();
    $ArtistID = getOrCreateArtist($data, $mysql);
    $GenreIDs = getOrCreateGenre($data, $mysql);
    $SubjectIDs = getOrCreateSubject($data, $mysql);

    $Title = $data['Title'];
    $Description = $data['Description'];
    $YearOfWork = $data['YearOfWork'];
    $Width = $data['Width'];
    $Height = $data['Height'];
    $MSRP = $data['MSRP'];

    $columnNames = array('ArtistID', 'Title', 'Description',
        'YearOfWork', 'Width', 'Height', 'MSRP', 'CustomerID_create');
    $columnValues = array($ArtistID, $Title, $Description,
        $YearOfWork, $Width, $Height, $MSRP, $userID);

    $result = $mysql->insert('paintings', $columnNames, $columnValues);
    $PaintingID = mysqli_fetch_assoc($result)['LAST_INSERT_ID()'];

    $ImageFileName = str_pad($PaintingID, 6, '0', STR_PAD_LEFT);
    $map = array('ImageFileName'=>$ImageFileName);
    $mysql->update('paintings', $map, "WHERE PaintingID='$PaintingID'");

    foreach ($GenreIDs as $GenreID){
        $mysql->insert('paintinggenres', array('PaintingID', 'GenreID'), array($PaintingID, $GenreID));
    }
    foreach ($SubjectIDs as $SubjectID){
        $mysql->insert('paintingsubjects', array('PaintingID', 'SubjectID'), array($PaintingID, $SubjectID));
    }

    if (!$result){
        http_response_code(500);
        exit(json_encode(array('message'=>"未知错误！")));
    }

    http_response_code(200);
    exit(json_encode($map));
}
elseif ($req_method == "PUT"){
    if(array_key_exists('PaintingID', $_GET))
        $PaintingID = $_GET['PaintingID'];
    else{
        http_response_code(400);
        exit(json_encode(array("message"=>"缺少必要的请求参数！")));
    }
    $mysql = new Mysql();
    $painting = $mysql->selectAPaintingById($PaintingID);
    if (!$painting){
        http_response_code(404);
        exit(json_encode(array('message'=>'此艺术品不存在！')));
    }
    if ($painting->CustomerID_create != $userID){
        http_response_code(403);
        exit(json_encode(array('message'=>'您没有权限修改此艺术品！')));
    }

//获取表单
    $data = json_decode(file_get_contents('php://input'), true);
    $mysql = new Mysql();
    $ArtistID = getOrCreateArtist($data, $mysql);
    $GenreIDs = getOrCreateGenre($data, $mysql);
    $SubjectIDs = getOrCreateSubject($data, $mysql);

    $Title = $data['Title'];
    $Description = $data['Description'];
    $YearOfWork = $data['YearOfWork'];
    $Width = $data['Width'];
    $Height = $data['Height'];
    $MSRP = $data['MSRP'];

    $columnNames = array('ArtistID', 'Title', 'Description',
        'YearOfWork', 'Width', 'Height', 'MSRP');
    $columnValues = array($ArtistID, $Title, $Description,
        $YearOfWork, $Width, $Height, $MSRP);

    $maps = array_combine($columnNames, $columnValues);

    $result = $mysql->update('paintings', $maps, "WHERE PaintingID='$PaintingID'");

    $mysql->delete('paintinggenres', "WHERE PaintingID='$PaintingID'");
    foreach ($GenreIDs as $GenreID){
        $mysql->insert('paintinggenres', array('PaintingID', 'GenreID'), array($PaintingID, $GenreID));
    }

    $mysql->delete('paintingsubjects', "WHERE PaintingID='$PaintingID'");
    foreach ($SubjectIDs as $SubjectID){
        $mysql->insert('paintingsubjects', array('PaintingID', 'SubjectID'), array($PaintingID, $SubjectID));
    }
    if (!$result){
        http_response_code(500);
        exit(json_encode(array('message'=>"未知错误！")));
    }

    $ImageFileName = str_pad($PaintingID, 6, '0', STR_PAD_LEFT);

    http_response_code(200);
    exit(json_encode(array('PaintingID'=>$PaintingID, 'ImageFileName'=>$ImageFileName)));
}
else{
    http_response_code(405);
}
