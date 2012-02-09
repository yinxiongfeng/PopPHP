<?php

require_once '../../bootstrap.php';

use Pop\Db\Db,
    Pop\Record\Record;

/*
 * Placing a class here is highly unorthodox.
 * This is just for example purposes only.
 */
class Users extends Record { }

try {
    // Define DB credentials
    $db = Db::factory('Mysql', array(
        'database' => 'helloworld',
        'host'     => 'localhost',
        'username' => 'hello',
        'password' => '12world34'
    ));

    Users::setDb($db);
    $users = Users::findAll('id DESC', 'access', 'admin');
    print_r($users->rows);

    echo PHP_EOL . PHP_EOL;
} catch (\Exception $e) {
    echo $e->getMessage() . PHP_EOL . PHP_EOL;
}
?>