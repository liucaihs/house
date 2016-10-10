<?php
// +----------------------------------------------------------------------
// | 采集各区具体房源信息
// +----------------------------------------------------------------------
// | Author: NickBai <1902822973@qq.com>
// +----------------------------------------------------------------------
set_time_limit(0);
require 'init.php';

for($i = 1; $i <= 252750; $i++){

	$sql = "select * from `hdetail` limit $i, 1";
	$res = $db->query($sql)->fetch(PDO::FETCH_ASSOC);

	$sql = "insert into `mytest`.`hdetail`(pid,square,housetype,price,allprice,name,addr) ";
	$name = str_replace(chr(194) . chr(160), "", $res['name']);
	$sql .= " value( ". $res['pid'] .",". $res['square'] .", '". $res['housetype'] ."' ,". $res['price'] .",". $res['allprice'] .", '". $name ."' ,'". $res['addr'] ."' )";

	$db->query($sql);
}


	


