<?php

class Mysql
{
    private $servername = "localhost";
    private $username = "root";
    private $password = "123456";
    private $dbname = "art";

    private $connect;//数据库连接
    private $select;//架构art连接

    //artistID->artistName
    private $columnNames_ShortPainting_paintings = array("PaintingID", "ArtistID", "Title",
        "ImageFileName", "MSRP", "Popularity", "ReleaseDate", "Description", "Status");

    //artistID->artistName, to add: GenreNames, SubjectNames, RatingID
    private $columnNames_Painting_paintings = array("PaintingID", "ArtistID", "Title",
        "ImageFileName", "MSRP", "Popularity", "ReleaseDate", "Description", "Status",
        "CustomerID_create", "YearOfWork", "Width", "Height", "Medium");

    private $columnNames_Customer_customers = array('CustomerID', 'UserName', 'Email', 'Address', 'Phone', 'UserAccount');
    private $columnNames_Review_reviews = array('RatingID', 'PaintingID', 'ReviewDate', 'Rating', 'Comment');
    private $columnNames_Order_orders = array('OrderID', 'CustomerID', 'DateStarted', 'PaintingID');


    public function __construct(){
        $this->connect=mysqli_connect($this->servername, $this->username, $this->password, $this->dbname);
        if(!$this->connect){
            die('数据库连接失败！' . mysqli_error($this->connect));
        }
        $this->select = mysqli_select_db($this->connect, $this->dbname );
        if (!$this->select){
            die('无此数据库！');
        }
    }

    public function func1($tablename, $username, $password){

        $sql = "SELECT * FROM $tablename WHERE UserName='$username' AND Pass='$password'";

        $result = mysqli_query($this->connect, $sql);
        if ($result === false){
            die('无法读取数据！' . mysqli_error($this->connect));
        }
        else{
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

            if ($row == null){
                return false;
            }
            else{
                return $row['CustomerID'];
            }

        }
    }

    public function query($sql){
        $result = mysqli_query($this->connect, $sql);
        if ($result === false){
            die('无法读取数据！' . mysqli_error($this->connect));
        }
        else{
            return $result;
        }
    }

    public function select($columnNames, $tableName, $condition = ""){
        if ($columnNames === "*"){
            $columnSql = "*";
        }
        else {
            $columnSql = $this->columnArrayToSql($columnNames);
        }
        $sql = "SELECT $columnSql FROM $tableName $condition";
        return $this->query($sql);
    }

    private function columnArrayToSql($columnNames){
        $n = count($columnNames);
        $columnSql = "";
        for ($i=0; $i < $n; $i++){
            $columnSql = $columnSql . $columnNames[$i] . ",";
        }
        return substr($columnSql, 0, strlen($columnSql)-1);
    }

    public function selectById($columnNames, $tableName, $idName, $idValue){
        $condition = "WHERE $idName=$idValue";
        return $this->select($columnNames, $tableName, $condition);
    }

    public function selectOneObjById($columnNames, $tableName, $idName, $idValue){
        $result = $this->selectById($columnNames, $tableName, $idName, $idValue);
        if ($result === false){
            die('无法读取数据！' . mysqli_error($this->connect));
        }else{
            $row = mysqli_fetch_assoc($result);
            return $row;
        }

    }

    public function selectAPaintingById($PaintingID){
        $result = $this->selectOneObjById($this->columnNames_Painting_paintings, "paintings", "PaintingID", $PaintingID);
        if ($result == null){
            die('无法读取数据！' . mysqli_error($this->connect));
        }else{
            $this->artistID2artistName($result);
            $this->addGenreName($result, $PaintingID);
            $this->addSubjectNames($result, $PaintingID);
            return (object) $result;
        }
    }

    private function artistID2artistName(&$result){
        $ArtistID = $result['ArtistID'];
        $result2 = mysqli_query($this->connect,"SELECT FirstName,LastName FROM artists WHERE ArtistID=$ArtistID");
        $Artist = mysqli_fetch_assoc($result2);
        $ArtistName = $Artist['FirstName'].' '.$Artist['LastName'];
        $result['ArtistName']=$ArtistName;

    }

    private function addGenreName(&$result, $PaintingID){
        $result2 = mysqli_query($this->connect,"SELECT GenreID FROM paintinggenres WHERE PaintingID=$PaintingID");
        $GenreIDList = array();
        $GenreNameList = array();
        while ($row = mysqli_fetch_assoc($result2)){
            array_push($GenreIDList, $row['GenreID']);
        }
        for ($i=0; $i<count($GenreIDList);$i++){
            $GenreID=$GenreIDList[$i];
            $result3 = mysqli_query($this->connect, "SELECT GenreName FROM genres WHERE GenreID=$GenreID");
            $row = mysqli_fetch_assoc($result3);
            array_push($GenreNameList, $row['GenreName']);
        }
        $result['GenreNames'] = $GenreNameList;
    }

    private function addSubjectNames(&$result, $PaintingID){
        $result2 = mysqli_query($this->connect,"SELECT SubjectID FROM paintingsubjects WHERE PaintingID=$PaintingID");
        $GenreIDList = array();
        $GenreNameList = array();
        while ($row = mysqli_fetch_assoc($result2)){
            array_push($GenreIDList, $row['SubjectID']);
        }
        for ($i=0; $i<count($GenreIDList);$i++){
            $GenreID=$GenreIDList[$i];
            $result3 = mysqli_query($this->connect, "SELECT SubjectName FROM subjects WHERE SubjectID=$GenreID");
            $row = mysqli_fetch_assoc($result3);
            array_push($GenreNameList, $row['SubjectName']);
        }
        $result['SubjectNames'] = $GenreNameList;
    }


    public function selectAShortPaintingById($PaintingID){
        $result = $this->selectOneObjById($this->columnNames_ShortPainting_paintings, "paintings", "PaintingID", $PaintingID);
        if ($result == null){
            die('无法读取数据！' . mysqli_error($this->connect));
        }else{
            $result['Description'] = substr($result['Description'],0,150);
            $this->artistID2artistName($result);
            $this->addGenreName($result, $PaintingID);
            return (object) $result;
        }
    }
/*
    public function selectAllPaintings(){
        $result = $this->select($this->columnNames_Painting_paintings, "paintings");
        if ($result == null){
            die('无法读取数据！' . mysqli_error($this->connect));
        }else{
            $paintings = mysqli_fetch_all($result);
            for($i=0;$i<count($paintings);$i++){
                $painting = $paintings[$i];
                $painting['Description'] = substr($painting['Description'],0,150);
                $painting = (object) $painting;
                $paintings[$i]=$painting;
            }
            return $paintings;
        }
    }
*/
    public function selectAllShortPaintings(){
        $result = $this->select($this->columnNames_ShortPainting_paintings, "paintings");
        if ($result == null){
            die('无法读取数据！' . mysqli_error($this->connect));
        }else{
            $paintings = mysqli_fetch_all($result, MYSQLI_ASSOC);
            for($i=0;$i<count($paintings);$i++){
                $painting = $paintings[$i];
                $painting = (object) $painting;
                $painting->Description = substr($painting->Description,0,150);

                $paintings[$i]=$painting;
            }
            return $paintings;
        }
    }

    public function selectPartShortPaintingsByIDList($PaintingIDList){
        $paintingList = array();
        for ($i=0;$i<count($PaintingIDList);$i++){
            $painting = $this->selectAShortPaintingById($PaintingIDList[$i]);
            array_push($paintingList, $painting);
        }
        return $paintingList;
    }

    //select ids

    public function selectAllPaintingIDinCart($CustomerID){
        $columnNames=array('PaintingID');
        $result=$this->select($columnNames, 'customer_cart', "WHERE CustomerID='$CustomerID'");
        return mysqli_fetch_all($result);
    }

    public function selectAllPaintingIDofCustomer($CustomerID){
        $columnNames=array('PaintingID');
        $result=$this->select($columnNames, 'paintings', "WHERE CustomerID_create='$CustomerID'");
        return mysqli_fetch_all($result);
    }

    public function selectAllSoldPaintingIDofCustomer($CustomerID){
        $columnNames=array('PaintingID');
        $result=$this->select($columnNames, 'paintings',
            "WHERE CustomerID_create='$CustomerID' AND Status='sold'");
        return mysqli_fetch_all($result);
    }

    public function selectAllOrderIDofCustomer($CustomerID){
        $columnNames=array('OrderID');
        $result=$this->select($columnNames, 'orders',
            "WHERE CustomerID='$CustomerID'");
        return mysqli_fetch_all($result);
    }

    //other entity

    public function selectACustomer($CustomerID){
        $result = $this->selectOneObjById($this->columnNames_Customer_customers, "customers", "CustomerID", $CustomerID);
        if ($result == null){
            die('无法读取数据！' . mysqli_error($this->connect));
        }else{
            return (object) $result;
        }
    }
    public function selectAReview($RatingID){
        $result = $this->selectOneObjById($this->columnNames_Review_reviews, "reviews", "RatingID", $RatingID);
        if ($result == null){
            die('无法读取数据！' . mysqli_error($this->connect));
        }else{
            return (object) $result;
        }
    }
    public function selectAOrder_raw($OrderID){
        $result = $this->selectOneObjById($this->columnNames_Order_orders, "orders", "OrderID", $OrderID);
        if ($result == null){
            die('无法读取数据！' . mysqli_error($this->connect));
        }else{
            return (object) $result;
        }
    }
    public function selectAOrder_full($OrderID){
        $order = $this->selectAOrder_raw($OrderID);
        $order->Painting = $this->selectAPaintingById($order->PaintingID);
        $order->Customer = $this->selectACustomer($order->CustomerID);
        return $order;
    }
}
