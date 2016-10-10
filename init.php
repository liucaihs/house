<?php
ini_set("memory_limit", "-1");
include('CURL.php');
include('phpQuery.php');
$db=new PDO('mysql:dbname=house;host=127.0.0.1','root','123');
$db->exec("set names utf8");
$curl=new CUrl();