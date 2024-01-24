<?php

use FpDbTest\Database;
use FpDbTest\DatabaseTest;

spl_autoload_register(function ($class) {
    $a = array_slice(explode('\\', $class), 1);
    if (!$a) {
        throw new Exception();
    }
    $filename = implode('/', [__DIR__, ...$a]) . '.php';
    require_once $filename;
});

$mysqli = @new mysqli('localhost', 'root', 'root', 'rgk', 3306);
if ($mysqli->connect_errno) {
    throw new Exception($mysqli->connect_error);
}
$parser = new \FpDbTest\DatabaseReplaceParser($mysqli);
$db = new Database($mysqli, $parser);
$test = new DatabaseTest($db);
try {
    $test->testBuildQuery();
} catch (Exception $e) {
    exit($e->getMessage());
}

exit('OK');
