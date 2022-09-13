<?php

//namespace DBConnect;

class DBConnect
{
    const SERVER_NAME = "localhost";
    const USERNAME = "username";
    const PASSWORD = "password";
    const DB_NAME = "dbTest";
    const TABLE_NAME = "tableName";

    private static $conn;

    public function getConnection(): mysqli
    {
        if (is_null(self::$conn)) {
            self::$conn = $this->connect();
        }
        return self::$conn;
    }

    public function connect(): mysqli
    {
        try {
            return mysqli_connect(self::SERVER_NAME, self::USERNAME, self::PASSWORD, self::DB_NAME);
        } catch (mysqli_sql_exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function runQuery(string $sql): mysqli_result|bool
    {
        try {
            return $this->getConnection()->query($sql); //$useDBConnect ? "USE " . self::DB_NAME . "; " . $sql : $sql);
        } catch (mysqli_sql_exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function createDB():void
    {
        $sql = "CREATE DATABASE IF NOT EXISTS " . self::DB_NAME . ";";
        $conn = mysqli_connect(self::SERVER_NAME, self::USERNAME, self::PASSWORD);
        $conn->query($sql);
        $conn->close();
    }

    public function createTable(array $titles): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS " . self::TABLE_NAME . " (
            id INT AUTO_INCREMENT PRIMARY KEY, "
            . $titles[0] . " VARCHAR(255), "
            . $titles[1] . " VARCHAR(255), "
            . $titles[2] . " INT, "
            . $titles[3] . " INT);";
        //$sql = "CREATE TABLE IF NOT EXISTS tablename (`id` INT AUTO_INCREMENT, `test` VARCHAR(255), `рУсский_Тест` VARCHAR(255), `naniDeHeck` INT, `bruh` INT, PRIMARY KEY(`id`));";
        $this->runQuery($sql);
    }

    public function insertData(array $titles, array $arr): void
    {
        //$sql = "INSERT INTO tablename (Артикул, Наименование_товара, Интернет_цена, Остатки) VALUES (125, 1525, 5125, 1255);";
        $sql = "INSERT INTO " . self::TABLE_NAME . " (" . $titles[0] . ", " . $titles[1] . ", " . $titles[2] . ", " . $titles[3] .
            ") VALUES ('" . $arr[0] . "', '" . $arr[1] . "', '" . $arr[2] . "', '" . $arr[3] . "');";
        //echo $sql."\n";
        $this->runQuery($sql);
    }

    public function getData(array $titles)
    {
        $sql = "SELECT * FROM " . self::TABLE_NAME . ";";
        $result = $this->runQuery($sql);
        if ($result->num_rows > 0) {
            return $result;
        } else {
            return false;
        }
    }

    public function dropTable(): void
    {
        $sql = "DROP TABLE " . self::TABLE_NAME . ";";
        $this->runQuery($sql);
    }

    public function closeConn(): void
    {
        mysqli_close(self::$conn);
    }
}
