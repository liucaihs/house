<?php
// +----------------------------------------------------------------------
// | 采集各区具体房源信息
// +----------------------------------------------------------------------
// | Author: NickBai <1902822973@qq.com>
// +----------------------------------------------------------------------
set_time_limit(0);
require 'init.php';

//查询各板块数据
$sql = "select * from `area` where id > 82 and id < 100";
$allarea = $db->query($sql)->fetchAll( PDO::FETCH_ASSOC );
//http://guangzhou.anjuke.com/sale/页面不存在时,会跳转到首页

foreach($allarea as $key=>$vo){

	$url = $vo['url'];
	$i = 1;

	while ( true ){

		$urls = str_replace( "*" , $i, $url);
		$result = $curl->read( $urls );
		if( "http://guangzhou.anjuke.com/sale/" == $result['info']['url'] ){
			break;
		}

		$charset = preg_match("/<meta.+?charset=[^\w]?([-\w]+)/i", $result['content'], $temp) ? strtolower( $temp[1] ) : "";  
		phpQuery::$defaultCharset = $charset;  //设置默认编码

		$html = phpQuery::newDocumentHTML( $result['content'] );

		$div = $html['#houselist-mod li .house-details'];
		$isGet = count( $div->elements );  //未采集到内容跳出,视为结束
		if( 0 == $isGet ){
			break;
		}

		foreach($div as $v){

			$sql = "insert into hdetail(pid,square,housetype,price,allprice,name,addr) ";
			$pid = $vo['id'];
			$square =  rtrim( trim( pq($v)->find("div:eq(1) span:eq(0)")->text() ), "平方米");
			$htype = trim( pq($v)->find("div:eq(1) span:eq(1)")->text() );
			$price = rtrim ( trim( pq($v)->find("div:eq(1) span:eq(2)")->text() ), "元/m²");
			$area = explode(" ", trim( pq($v)->find("div:eq(2) span")->text() ) );
			$name = trim ( array_shift($area)  );
			$addr = rtrim( ltrim (trim( array_pop($area) ) , "["), "]" );
			$allprice = trim( pq($v)->siblings(".pro-price")->find("span strong")->text() );

			$sql .= " value( ". $pid .",". $square .", '". $htype ."' ,". $price .",". $allprice .", '". $name ."' ,'". $addr ."' )";
			$db->query($sql);
		}

		echo mb_convert_encoding($vo['name'], "gbk", "utf-8") . " PAGE : ". $i . PHP_EOL;
		$i++;

	}

}

