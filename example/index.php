<?php
include '../JsonDb.php';

$json = new JsonDb('demo');

$data = ['name' => 'Gary', 'title' => 'PHP', 'website' => 'http://www.awaimai.com/'];
echo $json->insert($data);  //return the inserted id
print_r($json->selectAll()); // return all record
$json->delete('*');