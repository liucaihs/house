<?php
// +----------------------------------------------------------------------
// | 采集区域脚本
// +----------------------------------------------------------------------
// | Author: NickBai <1902822973@qq.com>
// +----------------------------------------------------------------------
set_time_limit(0);
require 'init.php';

//根据大区信息前往抓取
$sql = "select * from `area`";
$area = $db->query( $sql )->fetchAll( PDO::FETCH_ASSOC );

foreach($area as $key=>$vo){

	$url = $vo['url'];
	$result = $curl->read($url);

	$charset = preg_match("/<meta.+?charset=[^\w]?([-\w]+)/i", $result['content'], $temp) ? strtolower( $temp[1] ) : "";  
	phpQuery::$defaultCharset = $charset;  //设置默认编码

	$html = phpQuery::newDocumentHTML( $result['content'] );

	$span = $html['.items .sub-items a'];

	$st = $db->prepare("insert into area(name,url,pid) values(?,?,?)");
	foreach($span as $v){
	    $v = pq( $v );

	    //为方便分页抓取,先加入分页规则
	    $href = trim( $v->attr('href') ) . 'p*/#filtersort';
	    $st->execute([ trim( $v->text() ), $href, $vo['id']]);
	}
}

