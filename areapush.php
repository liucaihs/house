<?php
/**
 * User: Cai liu
 * Date: 16-10-9
 * Time: 上午10:20
 * Desc: 
 */
set_time_limit(0);
require 'init.php';
$urls = "http://guangzhou.anjuke.com/sale/";
$result = $curl->read( $urls );
$html = phpQuery::newDocumentHTML( $result['content'] );
$charset = preg_match("/<meta.+?charset=[^\w]?([-\w]+)/i", $result['content'], $temp) ? strtolower( $temp[1] ) : "";
phpQuery::$defaultCharset = $charset;  //设置默认编码

$html = phpQuery::newDocumentHTML( $result['content'] );
$chapter = pq(".items:eq(0) .elems-l:eq(0) a");
foreach($chapter as  $a){
    $areaurl  =  pq($a)->attr('href'); //
    $title = pq($a)->text();
    echo $areaurl  ,$title, PHP_EOL;
    $sql = "insert into area";
    $sql .= " values(null,'". $title ."' , '". $areaurl ."' ,'". 0 ."' )";
    $db->query($sql);
}
//print_r($chapter);