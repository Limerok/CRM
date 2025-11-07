<?php
class Database
{
    private $pdo;

    public function __construct($config)
    {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;port=%s;charset=%s',
            $config['hostname'],
            $config['database'],
            $config['port'],
            isset($config['charset']) ? $config['charset'] : 'utf8mb4'
        );

        $options = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        );

        $this->pdo = new PDO($dsn, $config['username'], $config['password'], $options);
    }

    public function query($sql, $params = array())
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetch($sql, $params = array())
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    public function fetchAll($sql, $params = array())
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }
}
