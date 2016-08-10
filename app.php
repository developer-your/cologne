<?php

require_once 'product.php';
require_once 'writer.php';
require_once 'error.php';
require_once 'person.php';
require_once 'crobo.php';


$dsn = 'sqlite:' . __DIR__ . DIRECTORY_SEPARATOR . 'products.db';
$pdo = new PDO( $dsn, null, null );
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$crtTableStmt =
        "CREATE TABLE products (
 id INTEGER PRIMARY KEY AUTOINCREMENT,
 type TEXT,
 firstname TEXT,
 mainname TEXT,
 title TEXT,
 price float,
 numpages int,
 playlength int,
 discount int )";

$insertStmt = "INSERT INTO products VALUES(1, 'cd', 'Exile on Coldharbour Lane',"
        . "'The', 'Alabama 3', 10.99, null, 60.33, 0)";

//if ($pdo->query($insertStmt)){
//    print_r('success');
//}
//else{
//    print_r('error');
//}

$obj = ShopProduct::getInstance(1, $pdo);