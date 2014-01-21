<?php

class MysqlDatabaseXmlToSchema {

    private $conn;
    private $database;
    private $host;
    private $password;
    private $user;

    public function __construct($database, $host, $user, $password) {
        $this->setDatabaseName($database);
        $this->setDatabaseHost($host);
        $this->setDatabasePassword($host);
        $this->setDatabaseUser($host);
        $this->conn = @mysql_connect($this->host, $this->user, $this->password);
        if (!$this->conn) {
            throw new Exception('Invalid mysql resource');
        }
    }

    public function setDatabaseName($database) {
        $this->database = (string) $database;
    }

    public function setDatabaseHost($host) {
        $this->host = (string) $host;
    }

    public function setDatabaseUser($user) {
        $this->user = (string) $user;
    }

}
