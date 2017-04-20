<?php
include '../JsonDb.php';

$json = new JsonDb('./files');

$data = ['name' => 'Gary', 'title' => 'PHP', 'website' => 'http://www.awaimai.com/'];
echo $json->demo->insert($data);      //return the inserted id
print_r($json->demo->selectAll());    // return all record
$json->demo->delete('*');