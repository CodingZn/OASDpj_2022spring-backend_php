<?php

class Mysql
{
    private $servername = "localhost";
    private $username = "root";
    private $password = "123456";
    private $dbname = "art";

    private $connect;//数据库连接
    private $select;//架构art连接

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
}
