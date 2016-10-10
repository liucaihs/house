<?php
require "init.php";

$data = unserialize( file_get_contents('./data/gz.data') );
if( empty( $data ) ){

	//全南京
	$sql = "select avg(price) price from hdetail";
	$nanjing = intval( $db->query($sql)->fetch( PDO::FETCH_ASSOC )['price'] );

	//其余数据
	$data = [
		$nanjing,
		//getOtherPrice('1,2,3,4,5,6,7,8,10,11,12'),
		getOtherPrice('1'),
		getOtherPrice('2'),
		getOtherPrice('3'),
		getOtherPrice('4'),
		getOtherPrice('5'),
		getOtherPrice('6'),
		getOtherPrice('7'),
		getOtherPrice('8'),
		getOtherPrice('9'),
		getOtherPrice('10'),
		getOtherPrice('11'),
		getOtherPrice('12'),
	//	getOtherPrice('13')
	];

	//添加缓存
	file_put_contents('./data/gz.data', serialize( $data ));
}

//均价最高TOP10
$sql = "select avg(price) price,name from hdetail GROUP BY name ORDER BY price desc limit 10";
$res = $db->query($sql)->fetchAll( PDO::FETCH_ASSOC );
$x = "";
$y = "";
foreach($res as $vo){
	$x .= "'" . trim($vo['name']) . "',";
	$y .= intval( $vo['price'] ). ",";
}

//均价最低TOP10
$sql = "select avg(price) price,name from hdetail GROUP BY name ORDER BY price asc limit 10";
$res = $db->query($sql)->fetchAll( PDO::FETCH_ASSOC );
$xl = "";
$yl = "";
foreach($res as $vo){
	$xl .= "'" . trim($vo['name']) . "',";
	$yl .= intval( $vo['price'] ). ",";
}

//区域数据
$sql = "select name from area where id < 13";
$res = $db->query($sql)->fetchAll( PDO::FETCH_ASSOC );
$areastr = "";
foreach($res as $vo){
    $areastr .= "'" . $vo['name'] . "',";
}
$areastr = trim($areastr , ',');
//交易房型数据
$sql = "select count(0) allnum, housetype from hdetail GROUP BY housetype order by allnum desc";
$res = $db->query($sql)->fetchAll( PDO::FETCH_ASSOC );
$htype = "";
foreach($res as $vo){
	$htype .= "[ '" . $vo['housetype'] . "', " .$vo['allnum']. "],";
}

$htype = rtrim($htype, ',');

//交易的房屋面积数据
$square = ['50平米以下', '50-70平米', '70-90平米', '90-120平米', '120-150平米', '150-200平米', '200-300平米', '300平米以上'];
$sql = "select count(0) allnum, square from hdetail GROUP BY square";
$squ = $db->query($sql)->fetchAll( PDO::FETCH_ASSOC );

$p50 = 0;
$p70 = 0;
$p90 = 0;
$p120 = 0;
$p150 = 0;
$p200 = 0;
$p250 = 0;
$p300 = 0;

foreach($squ as $key=>$vo){
	if( $vo['square'] < 50 ){
		$p50 += $vo['allnum'];
	}
	if( $vo['square'] >= 50 &&  $vo['square'] < 70 ){
		$p70 += $vo['allnum'];
	}
	if( $vo['square'] >= 70 &&  $vo['square'] < 90 ){
		$p90 += $vo['allnum'];
	}
	if( $vo['square'] >= 90 &&  $vo['square'] < 120 ){
		$p120 += $vo['allnum'];
	}
	if( $vo['square'] >= 120 &&  $vo['square'] < 150 ){
		$p150 += $vo['allnum'];
	}
	if( $vo['square'] >= 150 &&  $vo['square'] < 200 ){
		$p200 += $vo['allnum'];
	}
	if( $vo['square'] >= 200 &&  $vo['square'] < 300 ){
		$p250 += $vo['allnum'];
	}
	if( $vo['square'] >= 300 ){
		$p300 += $vo['allnum'];
	}
}

$num = [ $p50, $p70, $p90, $p120, $p150, $p200, $p250, $p300 ];

$sqStr = "";
foreach($square as $key=>$vo){
	$sqStr .= "[ '" . $vo . "', " .$num[$key]. "],";
}


//根据获取ids字符串获取对应的均价信息
function getOtherPrice($str){
	global $db;

	$sql = "select id from area where pid in(" . $str . ")";
	$city = $db->query($sql)->fetchAll( PDO::FETCH_ASSOC );
	$ids = "";
	foreach($city as $v){
		$ids .= $v['id'] . ",";
	}
	$sql = "select avg(price) price from hdetail where pid in (".rtrim($ids, ",").")";
	$price = intval( $db->query($sql)->fetch( PDO::FETCH_ASSOC )['price'] );

	return $price;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>广州房价分析</title>
    <link rel="shortcut icon" href="favicon.ico"> <link href="css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="css/font-awesome.min.css?v=4.4.0" rel="stylesheet">
    <link href="css/animate.min.css" rel="stylesheet">
    <link href="css/style.min.css?v=4.1.0" rel="stylesheet">
</head>
<body class="gray-bg">
    <div class="wrapper wrapper-content">
        <div class="row">
            <div class="col-sm-12">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <h5>全广州以及各区二手房均价</h5>
                                <div class="ibox-tools">
                                    <a class="collapse-link">
                                        <i class="fa fa-chevron-up"></i>
                                    </a>
                                    <a class="close-link">
                                        <i class="fa fa-times"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="ibox-content">
                               <div id="container"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <h5>均价最高的小区TOP10</h5>
                                <div class="ibox-tools">
                                    <a class="collapse-link">
                                        <i class="fa fa-chevron-up"></i>
                                    </a>
                                    <a class="close-link">
                                        <i class="fa fa-times"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="ibox-content">
                               <div id="avgpriceh"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <h5>均价最低的小区TOP10</h5>
                                <div class="ibox-tools">
                                    <a class="collapse-link">
                                        <i class="fa fa-chevron-up"></i>
                                    </a>
                                    <a class="close-link">
                                        <i class="fa fa-times"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="ibox-content">
                               <div id="avgpricel"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <h5>交易房型比例</h5>
                                <div class="ibox-tools">
                                    <a class="collapse-link">
                                        <i class="fa fa-chevron-up"></i>
                                    </a>
                                    <a class="close-link">
                                        <i class="fa fa-times"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="ibox-content">
                               <div id="htype"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <h5>交易房屋面积比例</h5>
                                <div class="ibox-tools">
                                    <a class="collapse-link">
                                        <i class="fa fa-chevron-up"></i>
                                    </a>
                                    <a class="close-link">
                                        <i class="fa fa-times"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="ibox-content">
                               <div id="square"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <script type="text/javascript" src="js/jquery.min.js?v=2.1.4"></script>
    <script type="text/javascript" src="js/bootstrap.min.js?v=3.3.6"></script>
    <script type="text/javascript" src="http://cdn.hcharts.cn/highcharts/highcharts.js"></script>
    <script type="text/javascript">
    	$(function () {
		    $('#container').highcharts({
		        chart: {
		            type: 'column'
		        },
		        title: {
		            text: '全广州以及各区二手房均价'
		        },
		        subtitle: {
		            text: '来源于安居客8.16的数据'
		        },
		        xAxis: {

                        categories: ['全广州',<?php echo $areastr; ?>],
//		            categories: ['全广州','江宁区','鼓楼区','白下区','玄武区','建邺区','秦淮区','下关区','雨花台区','浦口区','栖霞区','六合区',
//		            '溧水区','高淳区','大厂'],
		            crosshair: true
		        },
		        yAxis: {
		            min: 0,
		            title: {
		                text: '元/m²'
		            }
		        },
		        tooltip: {
		            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
		            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
		            '<td style="padding:0"><b>{point.y:.1f} 元/m²</b></td></tr>',
		            footerFormat: '</table>',
		            shared: true,
		            useHTML: true
		        },
		        plotOptions: {
		            column: {
		                pointPadding: 0.2,
		                borderWidth: 0,
		                dataLabels:{
                         enabled:true// dataLabels设为true    
                    	}
		            } 
		        },
		        series: [{
		            name: '平均房价',
		            data: [<?php echo implode(',', $data); ?>]
		        }]
		    });

		    //均价最高top10
		    $('#avgpriceh').highcharts({
		        chart: {
		            type: 'column'
		        },
		        title: {
		            text: '均价最高的小区TOP10'
		        },
		        subtitle: {
		            text: '来源于安居客8.16的数据'
		        },
		        xAxis: {
		            categories: [<?=$x; ?>],
		            crosshair: true
		        },
		        yAxis: {
		            min: 0,
		            title: {
		                text: '元/m²'
		            }
		        },
		        tooltip: {
		            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
		            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
		            '<td style="padding:0"><b>{point.y:.1f} 元/m²</b></td></tr>',
		            footerFormat: '</table>',
		            shared: true,
		            useHTML: true
		        },
		        plotOptions: {
		            column: {
		                pointPadding: 0.2,
		                borderWidth: 0,
		                dataLabels:{
                         enabled:true// dataLabels设为true    
                    	}
		            } 
		        },
		        series: [{
		            name: '平均房价',
		            data: [<?=$y; ?>]
		        }]
		    });

		    //均价最低top10
		    $('#avgpricel').highcharts({
		        chart: {
		            type: 'column'
		        },
		        title: {
		            text: '均价最低的小区TOP10'
		        },
		        subtitle: {
		            text: '来源于安居客8.16的数据'
		        },
		        xAxis: {
		            categories: [<?=$xl; ?>],
		            crosshair: true
		        },
		        yAxis: {
		            min: 0,
		            title: {
		                text: '元/m²'
		            }
		        },
		        tooltip: {
		            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
		            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
		            '<td style="padding:0"><b>{point.y:.1f} 元/m²</b></td></tr>',
		            footerFormat: '</table>',
		            shared: true,
		            useHTML: true
		        },
		        plotOptions: {
		            column: {
		                pointPadding: 0.2,
		                borderWidth: 0,
		                dataLabels:{
                         enabled:true// dataLabels设为true    
                    	}
		            } 
		        },
		        series: [{
		            name: '平均房价',
		            data: [<?=$yl; ?>]
		        }]
		    });

		     // Radialize the colors
		    Highcharts.getOptions().colors = Highcharts.map(Highcharts.getOptions().colors, function (color) {
		        return {
		            radialGradient: { cx: 0.5, cy: 0.3, r: 0.7 },
		            stops: [
		                [0, color],
		                [1, Highcharts.Color(color).brighten(-0.3).get('rgb')] // darken
		            ]
		        };
		    });
		    //房型类型
		    $('#htype').highcharts({
		        chart: {
		            plotBackgroundColor: null,
		            plotBorderWidth: null,
		            plotShadow: false
		        },
		        title: {
		            text: '交易的二手房型比例'
		        },
		        tooltip: {
		            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
		        },
		        plotOptions: {
		            pie: {
		                allowPointSelect: true,
		                cursor: 'pointer',
		                dataLabels: {
		                    enabled: true,
		                    format: '<b>{point.name}</b>: {point.percentage:.1f} %',
		                    style: {
		                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
		                    },
		                    connectorColor: 'silver'
		                }
		            }
		        },
		        series: [{
		            type: 'pie',
		            name: 'Browser share',
		            data: [
		                <?=$htype; ?>
		            ]
		        }]
		    });

		    //房型面积类型
		    $('#square').highcharts({
		        chart: {
		            plotBackgroundColor: null,
		            plotBorderWidth: null,
		            plotShadow: false
		        },
		        title: {
		            text: '交易的二手房面积比例'
		        },
		        tooltip: {
		            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
		        },
		        plotOptions: {
		            pie: {
		                allowPointSelect: true,
		                cursor: 'pointer',
		                dataLabels: {
		                    enabled: true,
		                    format: '<b>{point.name}</b>: {point.percentage:.1f} %',
		                    style: {
		                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
		                    },
		                    connectorColor: 'silver'
		                }
		            }
		        },
		        series: [{
		            type: 'pie',
		            name: 'Browser share',
		            data: [
		                <?=$sqStr; ?>
		            ]
		        }]
		    });

		});
    </script>
</body>
</html>
