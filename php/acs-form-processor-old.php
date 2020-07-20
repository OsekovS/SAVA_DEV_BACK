<?php
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

// use Elasticsearch\ClientBuilder;
// require 'vendor/autoload.php';

// $client = ClientBuilder::create()->build();
include 'db.php';
// system("curl -H \"Content-Type: application/json\" -XPOST 'http://localhost:9200/skud*/main/_delete_by_query' -d '{ \"query\" : { \"term\" : { \"ip_cam\" : \"192.168.3.110\" } } }'");
//подключение к БД



$conn = connectBD('acs_castle_ep2');
$_POST = json_decode(file_get_contents('php://input'), true);
// var_dump($_POST);
if($_POST['need']=='settings'){
	$arraydb['objects'] = getDB($conn,"SELECT id, name FROM objects");
    $arraydb['endpoints'] = getDB($conn,"SELECT id, obj, ip, login FROM endpoints");
    // var_dump($arraydb['objects']);
}
if($_POST['need']=='dashboards'){
    $personalDash = getDB($conn,"SELECT * FROM dashboards WHERE master = '".$_POST['login']."'");
    // $arraydb['login'] = $_POST['login'];
    // var_dump($dashStandart);
    $standartDash = getDB($conn,"SELECT * FROM dashboards WHERE master = 'standart'");
    $arraydb['dashboards'] = array_merge($standartDash, $personalDash);
    //$arraydb['dashboards'] = getDB($conn,"SELECT * FROM dashboards");
}
if($_POST['need']=='logs'){
    
    $logsStart = ((int)$_POST['curPage']-1)*(int)$_POST['logsCount']0;
    $sortDirection = $_POST['sortParam']['direction'];
    $sortType = $_POST['sortParam']['field'];
    if($_POST['sortParam']['type']=='text'){
        $sortType = $sortType.'.keyword';
    }


    if(isset( $_POST['paramsFilter'])&&count($_POST['paramsFilter'])){
        
        $arraydb['querry'] = '{"from":'.$logsStart.',"size":'.$_POST['logsCount'].', "query": { "bool": { "must":[';
            $filter = $_POST['paramsFilter'];
    $arraydb['filter'] = ($filter);
    
        foreach($filter as $key => $value) {
            // if(count($value)>0){
            $str = '{ "bool" : { "should": [';
            for ($i = 0;$i <= count($value) ; $i++) {
                if(!$value[$i]==""){
                    $str = $str.'{ "match_phrase": { "'.$key.'.keyword": "'.$value[$i].'" }},';
                }
            }
            $str = substr($str, 0, strlen($str)-1);
            $arraydb['querry']=$arraydb['querry'].$str.']}    },';
            // }   
        }
    $arraydb['querry'] = substr($arraydb['querry'], 0, strlen($arraydb['querry'])-1);

    // $querry = '{"from":0,"size":10,"query": { "bool": { "must": [{ "bool" : { "should": [  { "match_phrase": { "event": "Доступ запрещен. Отсутствует разрешение на проход." }},   { "match_phrase": { "event": "Зарегистрирован проход." }}]}    },{ "bool" : { "should": [  { "match_phrase": { "person": "Виталий Трубной" }},   { "match_phrase": { "person": "Алеша" }} ]   }    }]}}   }';
    $arraydb['querry']=$arraydb['querry'].',{  "range" : {  "time" : { "from": "'.$_POST['timeFilter']['from'].'",  "to": "'.$_POST['timeFilter']['to'].'"   }     }   }]}} ,"sort":{"'.$sortType.'": "'.$sortDirection.'"}}'; // , "sort" : [{"object" : {"order" : "asc"}}]}'; 
    $querry = $arraydb['querry'];
    }
    else{
        $querry =   '{"from":'.$logsStart.',"size":'.$_POST['logsCount'].', "query": {  "range" : {  "time" : { "from": "'.$_POST['timeFilter']['from'].'",  "to": "'.$_POST['timeFilter']['to'].'"   }     }   },"sort":{"'.$sortType.'": "'.$sortDirection.'"}}';         // "must" :[{ "match": { "title": "Search"}}], 

        $arraydb['querry'] = $querry; 
    }
    // if(!strlen($_POST['sortParam'])==0){

    // }
    //acs_castle_ep2_event
    $arraydb['indexName'] = $_POST['indexName'];
	$logs = file_get_contents(
		'http://localhost:9200/'.$_POST['indexName'].'/main/_search', 
		false, 
		stream_context_create(
			array(
				'http' => array(
                    'method' => 'POST',
                    'header' => 'Content-Type: application/json',
					// 'header' => 'Content-Type: multipart/form-data; boundary=' . $boundary,
					'content' => $querry
				)
			)
		)
	);
	
	// $logs = $responce;
    $bar1 = array(
        'series' => array('data' => array()),
        'xLabels' => array()
        // 'date' => $logss->hits->hits[0]
    );

    for ($i = 0;$i <= 23 ; $i++) {


        $bar1['series']['data'][strval($i)]=0;
        $bar1['xLabels'][strval($i)]=strval($i).':00';
        // $raw_Obj => hours => $i = 0;
    }
    $bar2 =  $bar1;

    $raw_logs = json_decode($logs);
    $arraydb['total'] = $raw_logs->hits->total->value;
    $logs_arr = $raw_logs->hits->hits;
    $arraydb['logs'] = ($logs_arr);
    // $arraydb['request'] = $raw_logs;
    $arraydb['bar1old'] = $bar1;
    $arraydb['bar2old'] = $bar2;
    foreach ($logs_arr as $i => $value) {
       
        $date = new DateTime($logs_arr[$i]->_source->time);
        
       if($logs_arr[$i]->_source->route == 'Выход'){
        // $arraydb['date'][$i]=  ($date->format('H'))[1];
            if((int)($date->format('H'))<10)
             $bar1['series']['data'][($date->format('H'))[1]]=$bar1['series']['data'][$date->format('H')[1]]+1;
            else $bar1['series']['data'][$date->format('H')]=$bar1['series']['data'][$date->format('H')]+1;
        }
        else{
            if((int)($date->format('H'))<10)
             $bar2['series']['data'][(int)($date->format('H'))[1]]=$bar2['series']['data'][$date->format('H')[1]]+1;
            else $bar2['series']['data'][$date->format('H')]=$bar2['series']['data'][$date->format('H')]+1;  
    }
    }
    $arraydb['bar1'] = $bar1;
    $arraydb['bar2'] = $bar2;
    // $arraydb['hours'] = $raw_Obj['hours'];
    // $arraydb['hours']['hours'] = $raw_Obj['hours'];
    

  
}
// var_dump($_POST);
if(isset($_POST['addField']))
{    
    $fields = '';
    $fields= '';
    // $_POST['form']['aue'] = 'aui';
    foreach($_POST['form'] as $key => $value) {
        $fields = $fields.$key.', ';
        $values = $values."'".$value."'".', ';
    }
    
    $fields = substr($fields, 0,strlen($fields)-2);
    $values = substr($values, 0,strlen($values)-2);
    // var_dump($fields);
    // var_dump($values);
    $result = pg_query($conn, "SELECT * FROM ".$_POST['addField']['tb_name']." WHERE ".$_POST['addField']["uni_type"]." = '".$_POST['addField']["uni_val"]."'");
    
	if(pg_numrows($result)>0){
		$arraydb['result'] = 'nope';
	}
	else{
        $id=get_id($conn,$_POST['addField']['tb_name']);

		pg_query($conn,"INSERT INTO ".$_POST['addField']['tb_name']." (id, ".$fields.") VALUES ($id, ".$values.")");
        $arraydb['result'] = 'done';
        $arraydb['id'] = $id;
	}


}
if(isset($_POST['delete']))
{
    if(isset($_POST['objName'])){
        $result = pg_query($conn, "DELETE FROM endpoints WHERE obj = '".$_POST['objName']."'");
    }

	$result = pg_query($conn, "DELETE FROM ".$_POST['tb_name']." WHERE id = ".$_POST['id']."");

	if ($result) {
		$arraydb['result'] = 'done';
	}
	else{
		$arraydb['result'] = 'nope';
	}
}

pg_close($conn);
echo json_encode($arraydb);
// echo ($logss);

// <iframe src="http://192.168.3.26:5601/app/kibana#/visualize/edit/de23dc40-fa5f-11e9-ba35-7dacb7b32d29?embed=true&_g=(filters:!(),refreshInterval:(pause:!t,value:0),time:(from:'2019-07-02T09:30:56.867Z',to:'2020-01-14T09:46:02.538Z'))&_a=(filters:!(),linked:!f,query:(language:kuery,query:''),uiState:(vis:(params:(sort:(columnIndex:3,direction:asc)))),vis:(aggs:!((enabled:!t,id:'1',params:(),schema:metric,type:count),(enabled:!t,id:'2',params:(customLabel:%D0%92%D1%80%D0%B5%D0%BC%D1%8F,field:time,missingBucket:!f,missingBucketLabel:Missing,order:desc,orderBy:'1',otherBucket:!f,otherBucketLabel:Other,size:5),schema:bucket,type:terms),(enabled:!t,id:'3',params:(customLabel:'ip+%D0%BA%D0%B0%D0%BC%D0%B5%D1%80%D1%8B',field:ip_cam.keyword,missingBucket:!f,missingBucketLabel:Missing,order:desc,orderBy:'1',otherBucket:!f,otherBucketLabel:Other,size:5),schema:bucket,type:terms),(enabled:!t,id:'4',params:(customLabel:'%D0%A2%D0%B8%D0%BF+%D1%81%D0%BE%D0%B1%D1%8B%D1%82%D0%B8%D1%8F',field:type_log.keyword,missingBucket:!f,missingBucketLabel:Missing,order:desc,orderBy:'1',otherBucket:!f,otherBucketLabel:Other,size:5),schema:bucket,type:terms),(enabled:!t,id:'5',params:(customLabel:%D0%9A%D0%BE%D0%BC%D0%BC%D0%B5%D0%BD%D1%82%D0%B0%D1%80%D0%B8%D0%B9,field:comment.keyword,missingBucket:!f,missingBucketLabel:Missing,order:desc,orderBy:'1',otherBucket:!f,otherBucketLabel:Other,size:5),schema:bucket,type:terms)),params:(computedColsPerSplitCol:!f,computedColumns:!(),dimensions:(bucket:!((accessor:0,aggType:terms,format:(id:terms,params:(id:date,missingBucketLabel:Missing,otherBucketLabel:Other)),params:()),(accessor:1,aggType:terms,format:(id:terms,params:(id:string,missingBucketLabel:Missing,otherBucketLabel:Other)),params:()),(accessor:2,aggType:terms,format:(id:terms,params:(id:string,missingBucketLabel:Missing,otherBucketLabel:Other)),params:()),(accessor:3,aggType:terms,format:(id:terms,params:(id:string,missingBucketLabel:Missing,otherBucketLabel:Other)),params:())),metric:!((accessor:4,aggType:count,format:(id:number),params:()))),filterAsYouType:!f,filterBarHideable:!f,filterBarWidth:'25%25',filterCaseSensitive:!f,filterHighlightResults:!f,filterTermsSeparately:!f,hiddenColumns:Count,hideExportLinks:!f,perPage:10,showFilterBar:!f,showMetricsAtAllLevels:!f,showPartialRows:!f,showTotal:!f,sort:(columnIndex:!n,direction:!n),totalFunc:sum),title:'%D0%A1%D0%BF%D0%B8%D1%81%D0%BE%D0%BA+%D0%BB%D0%BE%D0%B3%D0%BE%D0%B2',type:enhanced-table))" height="600" width="800"></iframe>
    // curl -XGET 'http://localhost:9200/acs_castle_ep2_event/main/_search?&pretty=true&size=5' -d '{ "sort": [  {    "_script": { "script": "try { Integer.parseInt(doc[\"significance\"].value); } catch(Exception e){ return Integer.MAX_VALUE;}",  "type": "number", "order": "asc", "lang": "groovy"     }  }   ]  }'