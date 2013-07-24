<?php

namespace PHPDocSearch;

class PDOBuilder
{
    public function build(Environment $env)
    {
        $host = $env->getConfigOption('db.host');
        $dbname = $env->getConfigOption('db.name');
        $user = $env->getConfigOption('db.user');
        $pass = $env->getConfigOption('db.pass');
        $charset = 'utf8';

        $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
        $db = new \PDO($dsn, $user, $pass);

        $db->setAttribute(\PDO::ATTR_ERRMODE,            \PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(\PDO::ATTR_EMULATE_PREPARES,   false);
        $db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

        return $db;
    }
}
