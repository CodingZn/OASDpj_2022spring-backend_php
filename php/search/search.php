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
        $orderby = "Title";

    if(array_key_exists('text', $_GET))
        $page = $_GET['page'];
    else
        $page = "1";

    if(array_key_exists('text', $_GET))
        $pagesize = $_GET['pagesize'];
    else
        $pagesize = "10";

    $mysql = new Mysql();
    $paintingList_all = $mysql->selectAllShortPaintings();

    $text = strtolower($text);
    $selected_paintingList = array();
    foreach ($paintingList_all as $item){
        if ((!$text) || strpos(strtolower($item->Title), $text) || strpos(strtolower($item->ArtistName), $text)){
            array_push($selected_paintingList, $item);
        }
    }

    switch ($orderby){
        case 'Title':
            usort($selected_paintingList, 'sort_by_Title');
            break;
        case 'Popularity':
            usort($selected_paintingList, 'sort_by_Popularity');
            break;
        case 'Price':
            usort($selected_paintingList, 'sort_by_Price');
            break;
        case 'ReleaseDate':
            usort($selected_paintingList, 'sort_by_ReleaseDate');
            break;
    }

    $paintingList = array();

    for($i=0; $i<$pagesize && (($page - 1) * $pagesize + $i < count($selected_paintingList)); $i++){
        array_push($paintingList, $selected_paintingList[($page - 1) * $pagesize + $i]);
    }

    $totalPages = ceil(count($selected_paintingList) / $pagesize);

    $data = array('paintings'=>$paintingList, 'totalPages'=> $totalPages, 'message'=>'操作成功！');

    http_response_code(200);

    exit(json_encode($data));
}
else{
    http_response_code(405);
}

function sort_by_Title($painting1, $painting2){
    if ($painting1->Title < $painting2->Title)
        return -1;
    if ($painting1->Title == $painting2->Title)
        return 0;
    if ($painting1->Title > $painting2->Title)
        return 1;
}

function sort_by_Price($painting1, $painting2){
    if ($painting1->MSRP < $painting2->MSRP)
        return -1;
    if ($painting1->MSRP == $painting2->MSRP)
        return 0;
    if ($painting1->MSRP > $painting2->MSRP)
        return 1;
}

function sort_by_ReleaseDate($painting1, $painting2){
    if ($painting1->ReleaseDate < $painting2->ReleaseDate)
        return 1;
    if ($painting1->ReleaseDate == $painting2->ReleaseDate)
        return 0;
    if ($painting1->ReleaseDate > $painting2->ReleaseDate)
        return -1;
}

function sort_by_Popularity($painting1, $painting2){
    if ($painting1->Popularity < $painting2->Popularity)
        return 1;
    if ($painting1->Popularity == $painting2->Popularity)
        return 0;
    if ($painting1->Popularity > $painting2->Popularity)
        return -1;
}

