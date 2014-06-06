<?php

namespace PHPDocSearch;

class PDOProvider
{
    /**
     * @var Config
     */
    private $config;

    /**
     * Constructor
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Get a new connection to the database
     *
     * @return \PDO
     */
    public function getConnection()
    {
        $host = $this->config->getOption('db.host');
        $user = $this->config->getOption('db.user');
        $pass = $this->config->getOption('db.pass');
        $name = $this->config->getOption('db.name');
        $charset = 'utf8';

        $dsn = "mysql:host=$host;dbname=$name;charset=$charset";
        $db = new \PDO($dsn, $user, $pass);

        $db->setAttribute(\PDO::ATTR_ERRMODE,            \PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(\PDO::ATTR_EMULATE_PREPARES,   false);
        $db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

        return $db;
    }
}
