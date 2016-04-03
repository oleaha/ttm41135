<?php

namespace ttm4135\webapp;

class Sql
{
    static $pdo;

    function __construct()
    {
    }

    /**
     * Create tables.
     * shit is real
     */
    static function up() {
        $q1 = "CREATE TABLE users (id INTEGER PRIMARY KEY, username VARCHAR(50), password VARCHAR(50), email varchar(50),  bio varhar(50), isadmin INTEGER);";

        self::$pdo->exec($q1);

        print "[ttm4135] Done creating all SQL tables.".PHP_EOL;

        self::insertDummyUsers();
    }

    static function insertDummyUsers() {


        $hash1 = password_hash("admin123", PASSWORD_DEFAULT);
        $hash2 = password_hash("bob123", PASSWORD_DEFAULT);

        $q1 = "INSERT INTO users(username, password, isadmin) VALUES ('admin', '".$hash1."' , 1)";
        $q2 = "INSERT INTO users(username, password) VALUES ('bob', '".$hash2."')";

        self::$pdo->exec($q1);
        self::$pdo->exec($q2);

        print "[ttm4135] Done inserting dummy users.".PHP_EOL;
    }


    static function down() {
        $q1 = "DROP TABLE users";

        self::$pdo->exec($q1);

        print "[ttm4135] Done deleting all SQL tables.".PHP_EOL;
    }

}
try {
    // Create (connect to) SQLite database in file
    Sql::$pdo = new \PDO('sqlite:/home/gr20/apache/htdocs/db/secret.db');
    // Set errormode to exceptions
    Sql::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
} catch(\PDOException $e) {
    echo $e->getMessage();
    exit();
}
