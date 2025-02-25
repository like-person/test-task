<?php
namespace Lib;
use PDO;
/*
    DB - класс для подключения к MySQL и выполнения запросов
*/
class DB {
    const HOST = '127.0.0.1';
    const DB = 'test_base';
    const USER = 'root';
    const PASS = '';
    const CHARSET = 'utf8';
    private $pdo;
    public function __construct() {
        $dsn = "mysql:host=".self::HOST.";dbname=".self::DB.";charset".self::CHARSET;
        $this->pdo = new PDO($dsn, self::USER, self::PASS);
    }
    public function query($sql) {
        $result = $this->pdo->query($sql);
        return $result;
    }
    public function execute($sql, $params) {
        $result = false;
        try {
            $result = $this->pdo->prepare($sql);
            $result->execute($params);
        } catch (\PDOException $e) {
            throw $e;
        }
        return $result;
    }
}