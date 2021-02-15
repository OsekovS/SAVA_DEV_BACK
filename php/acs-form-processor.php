<?php
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

// use Elasticsearch\ClientBuilder;
// require 'vendor/autoload.php';

// $client = ClientBuilder::create()->build();
include 'db.php';
include 'elastic.php';
// system("curl -H \"Content-Type: application/json\" -XPOST 'http://localhost:9200/skud*/main/_delete_by_query' -d '{ \"query\" : { \"term\" : { \"ip_cam\" : \"192.168.3.110\" } } }'");
//подключение к БД

$conn = connectBD('acs_castle_ep2');
$_POST = json_decode(file_get_contents('php://input'), true);
// var_dump($_POST);
if($_POST['need']=='logsClear'){
    // $POST['paramsFilter']
    // $POST['timeFilter']['from']
    $conn = connectBD('sava_core');
    $pass_query= pg_query($conn, "SELECT pass FROM users WHERE login='".$_POST['login']."'");
	$data =  pg_fetch_assoc($pass_query);
    if($data['pass'] == hash('sha1' , $_POST['password'])){
        $arraydb['_POST'] = $_POST;
        $querry  = getQuerryString($_POST);
        $QuerryResult = json_decode(getLogs($_POST['indexName'],$querry,'delete'));
        $arraydb['amount'] = $QuerryResult->deleted;
        $arraydb['QuerryString'] = $querry;
        $arraydb['result'] = $QuerryResult != null? 'success':'error';
    }else{
        $arraydb['result'] = 'passwErr';
    }
    

    pg_close($conn);
}
if($_POST['need'] == 'shiftDash'){
    $arraydb['post'] = $_POST;
    $connect = connectBD($_POST['dbName']);//shiftedId
    // $data=pg_query($connect, "UPDATE dashboards SET (id) = ('".$_POST['siblingId']."') WHERE (shiftedId) = (".$_POST['siblingId'].")");
    // $data=pg_query($connect, "UPDATE dashboards SET (id) = ('".$_POST['shiftedId']."') WHERE (siblingId) = (".$_POST['siblingId'].")");
    $arraydb['obj1'] = pg_fetch_assoc(pg_query($connect,"SELECT * FROM dashboards WHERE (master, id) = ('".$_POST['login']."', ".$_POST['siblingId'].")"));
    $arraydb['obj2'] = pg_fetch_assoc(pg_query($connect,"SELECT * FROM dashboards WHERE (master, id) = ('".$_POST['login']."', ".$_POST['shiftedId'].")"));
    pg_query($connect,"delete from dashboards where (master, id) = ('".$_POST['login']."', ".$_POST['siblingId'].")");
    pg_query($connect,"delete from dashboards where (master, id) = ('".$_POST['login']."', ".$_POST['shiftedId'].")");
    //$a = "INSERT INTO dashboards ".$arraydb['obj1']['id']."";
    pg_query($connect,"INSERT INTO dashboards (id, indexname, json, master, name, type) VALUES (".$arraydb['obj2']['id'].", '".$arraydb['obj1']['indexname']."', '".$arraydb['obj1']['json']."', '".$arraydb['obj1']['master']."', '".$arraydb['obj1']['name']."', '".$arraydb['obj1']['type']."')");
    pg_query($connect,"INSERT INTO dashboards (id, indexname, json, master, name, type) VALUES (".$arraydb['obj1']['id'].", '".$arraydb['obj2']['indexname']."', '".$arraydb['obj2']['json']."', '".$arraydb['obj2']['master']."', '".$arraydb['obj2']['name']."', '".$arraydb['obj2']['type']."')");

    // $arraydb['obj1'] = $obj1;
    // $arraydb['obj2'] = $obj2;
    pg_close($connect);

/*
id: "8"
indexname: "acs_castle_ep2_event"
json: "{"field":"route","style":{"width":435,"minWidth":435},"indexName":"acs_castle_ep2_event","logs":[],"timeFilter":{"from":"2019\/07\/28 00:00:00","to":"2020\/07\/28 11:13:00"},"uploads":{"uploads":false,"timeKind":1,"timeNum":11000,"to":"now\/d"},"paramFilter":[]}"
master: "admin2"
name: "8"
type: "Circle_Diagram"
*/


}
if($_POST['need']=='changeDashName'){
    $arraydb['!!'] = $_POST;
    $connect = connectBD($_POST['dbName']);
    $data=pg_query($connect, "UPDATE dashboards SET (name) = ('".$_POST['newName']."') WHERE (id,master) = (".$_POST['id'].",'".$_POST['login']."')");
    pg_close($connect);
}
if($_POST['need']=='TableList'){
    $tableName = $_POST['tableName'];
    $connect = connectBD($_POST['dbName']);
    $querryStr = "SELECT * FROM ".$tableName."";
    $querry =getDB_assoc($connect,$querryStr);

    $arraydb[$tableName] =($querry);
    pg_close($connect);
}
if($_POST['need']=='dashboards'){
    $conn = connectBD($_POST['dbName']);
    $arraydb['!']=$_POST['dbName'];
    $arraydb['login']=$_POST['login'];
    $personalDash = getDB($conn,"SELECT * FROM dashboards WHERE master = '".$_POST['login']."'");
    
    // if(is_array($_POST['isChangeFilter'])){
    //     foreach ($personalDash as $key => $value) {
    //         // $arraydb['xxx'][$key] = $value;
    //         if($value[2]=="Table"&&$value[5]==$_POST['isChangeFilter']['indexName']){
    //             $newJson = json_decode($value[4]);
    //             // if(!isset($newJson->paramFilter->significance)) $newJson->paramFilter->significance = array();
    //             $newJson->paramFilter['significance'] = ($_POST['isChangeFilter']['filter']['significance']);
    //             $arraydb['xxx'] = $newJson;//[1];//$newJson;
    //             $personalDash[$key][4] = json_encode( $newJson);
    //         }
    //         // 5;
    //         // "paramFilter":{"source_log":["Logon","Logoff","None","Special Logon","Kerberos Service Ticket Operations","\u0410\u0443\u0442\u0435\u043d\u0442\u0438\u0444\u0438\u043a\u0430\u0446\u0438\u044f","\u041a\u043e\u043d\u0442\u0440\u043e\u043b\u044c \u043f\u0440\u0438\u043b\u043e\u0436\u0435\u043d\u0438\u0439","Security Group Management","\u041e\u0431\u0449\u0438\u0435","Audit Policy Change","User Account Management","Kerberos Authentication Service","General","Other System Events","(1028)","\u0423\u043f\u0440\u0430\u0432\u043b\u0435\u043d\u0438\u0435 ","\u0412\u0445\u043e\u0434\/\u0432\u044b\u0445\u043e\u0434","\u041a\u043e\u043d\u0442\u0440\u043e\u043b\u044c \u0446\u0435\u043b\u043e\u0441\u0442\u043d\u043e\u0441\u0442\u0438","Directory Service Access","Process Creation","\u0421\u0438\u0441\u0442\u0435\u043c\u043d\u044b\u0435","System Integrity","\u0410\u0433\u0435\u043d\u0442 \u0426\u0435\u043d\u0442\u0440\u0430 \u043e\u0431\u043d\u043e\u0432\u043b\u0435\u043d\u0438\u044f Windows","Credential Validation","\u0421\u043e\u0431\u044b\u0442\u0438\u0435 \u0441\u043e\u0441\u0442\u043e\u044f\u043d\u0438\u044f \u0441\u043b\u0443\u0436\u0431\u044b","\u0420\u0435\u0433\u0438\u0441\u0442\u0440\u0430\u0446\u0438\u044f","\u0410\u043d\u0442\u0438\u0432\u0438\u0440\u0443\u0441","(1014)","(47)","\u0420\u0430\u0437\u0433\u0440\u0430\u043d\u0438\u0447\u0435\u043d\u0438\u0435 \u0434\u043e\u0441\u0442\u0443\u043f\u0430 \u043a \u0443\u0441\u0442\u0440\u043e\u0439\u0441\u0442\u0432\u0430\u043c","Log clear","Server","\u0410\u0434\u043c\u0438\u043d\u0438\u0441\u0442\u0440\u0438\u0440\u043e\u0432\u0430\u043d\u0438\u0435","Security State Change","\u0414\u0440\u0430\u0439\u0432\u0435\u0440","\u041e\u0447\u0438\u0441\u0442\u043a\u0430 \u0436\u0443\u0440\u043d\u0430\u043b\u0430","(5)","(7005)","\u0421\u0435\u0440\u0432\u0438\u0441 \u0437\u0430\u0433\u0440\u0443\u0437\u043a\u0438","\u0412\u0435\u0434\u0435\u043d\u0438\u0435 \u0436\u0443\u0440\u043d\u0430\u043b\u0430 \u0438 \u0432\u043e\u0441\u0441\u0442\u0430\u043d\u043e\u0432\u043b\u0435\u043d\u0438\u0435","(1101)","\u041a\u043e\u043d\u0442\u0440\u043e\u043b\u044c \u043a\u043e\u043d\u0444\u0438\u0433\u0443\u0440\u0430\u0446\u0438\u0438","(1102)","Service State Event","(103)","\u041f\u0430\u0441\u043f\u043e\u0440\u0442 \u041f\u041e","(32)","(33)","(57)","(58)","(1)","(203)","(31)","(6)","(62)","Authentication Policy Change","Other Policy Change Events","\u041e\u0431\u0449\u0438\u0435 \u0441\u043e\u0431\u044b\u0442\u0438\u044f","\u0421\u043b\u0443\u0436\u0431\u0430 \u043f\u043e\u0438\u0441\u043a\u0430","(2)","Windows Update Agent","\u0417\u0430\u0432\u0435\u0440\u0448\u0435\u043d\u0438\u0435 \u0440\u0430\u0431\u043e\u0442\u044b \u0441\u043b\u0443\u0436\u0431\u044b","\u041f\u043e\u043b\u043d\u043e\u043c\u043e\u0447\u043d\u043e\u0435 \u0443\u043f\u0440\u0430\u0432\u043b\u0435\u043d\u0438\u0435 \u0434\u043e\u0441\u0442\u0443\u043f\u043e\u043c","\u0420\u0430\u0441\u0448\u0438\u0440\u0435\u043d\u0438\u0435 \u0433\u0440\u0443\u043f\u043f\u043e\u0432\u043e\u0439 \u043f\u043e\u043b\u0438\u0442\u0438\u043a\u0438","TM","(102)","(64)","\u0421\u0430\u043c\u043e\u0437\u0430\u0449\u0438\u0442\u0430","\u0421\u0435\u0440\u0432\u0438\u0441 \u043e\u0431\u043d\u043e\u0432\u043b\u0435\u043d\u0438\u0439","(159)","SVC","Service shutdown","(21)","Computer Account Management","Backup","\u0421\u043e\u0431\u044b\u0442\u0438\u044f \u0441\u0431\u043e\u0435\u0432 \u043f\u0440\u0438\u043b\u043e\u0436\u0435\u043d\u0438\u044f","(10)","(243)","(63)","CM","CRM","OS information","Setup information","Trust Access","\u041e\u0431\u0440\u0430\u0431\u043e\u0442\u043a\u0430 \u0441\u043e\u0431\u044b\u0442\u0438\u044f"]}}"
    //     }
    // }
    $arraydb['dashboards'] = $personalDash;
}
if($_POST['need']=='addDashboard'){
    $arraydb['post'] = $_POST;
    
    $conn = connectBD($_POST['dbName']);
    //не забудь улучшить функцию 
    
    $dash=  pg_fetch_assoc(pg_query($conn, "SELECT * FROM dashboards WHERE (master,type,indexname) = ('core','Circle_Diagram','".$_POST['indexName']."')"));
    $json = json_decode($dash['json']);
    $json->field =$_POST['mainField'];
    $id = get_id2($conn,'dashboards',$_POST['login']);
    // $arraydb['new'] = $json;
    $jsonString = json_encode($json);
    $arraydb['1'] = $jsonString;

    pg_query($conn,"INSERT INTO dashboards (id, name, type, master, json, indexname) VALUES ($id, '".$_POST['name']."', 'Circle_Diagram', '".$_POST['login']."', '".$jsonString."', '".$_POST['indexName']."')");

    //INSERT INTO dashboards (id, name, type, master, json, indexname) VALUES (1,'1','1','1','1','1')
    // $arraydb['!']=$_POST['dbName'];
    $arraydb['login']=$_POST['login'];
    // $addQuerry = getDB($conn,"SELECT * FROM dashboards WHERE master = '".$_POST['login']."'");


    // $personalDash = getDB($conn,"SELECT * FROM dashboards WHERE master = '".$_POST['login']."'");
    $newDash = array(
        'id'=>$id,
        'name'=>$_POST['name'],
        'type'=>'Circle_Diagram',
        'master'=>$_POST['login'],
        'json'=>$json,
        'indexname'=>$_POST['indexName']
    );

    // $arraydb['login'] = $_POST['login'];
    // var_dump($dashStandart);
    // $standartDash = getDB($conn,"SELECT * FROM dashboards WHERE master = 'standart'");
    // $arraydb['dashboards'] = array_merge($standartDash, $personalDash);

    $arraydb['dashboard'] =  $newDash;
    /*
    $arraydb['filters'] = getDB($conn,"SELECT * FROM filters");
    */
}
// if($_POST['need']=='changeLastTime'){
//     $connection = connectBD($_POST['dbName']);
//     $arraydb['result'] = pg_fetch_assoc(pg_query($connection, "UPDATE LastIndexLook SET lastTime='".$_POST['lastTime']."' WHERE (login,indexName) = ('".$_POST['login']."','".$_POST['indexName']."')"));
//     pg_close($connection);
// }
if($_POST['need']=='delDashboard'){
    $arraydb['post'] = $_POST;
    $conn = connectBD($_POST['dbName']);
    $id = $_POST['id'];
    $arraydb['string']="DELETE FROM dashboards WHERE (id,master) = ($id,'".$_POST['login']."')";
    $arraydb['result'] = pg_fetch_assoc(pg_query($conn, "DELETE FROM dashboards WHERE (id,master) = ($id,'".$_POST['login']."')"));
    $personalDash = getDB($conn,"SELECT * FROM dashboards WHERE master = '".$_POST['login']."'");
    $arraydb['dashboards'] = $personalDash;
}

if($_POST['need']=='changeTimeMark'){
    $connection = connectBD($_POST['dbName']);
    $arraydb['post']=$_POST;
    //тут можно для начала к дБ подключиться
    // $data=pg_query($connection, "UPDATE LastIndexLook SET (top) = ('".$_POST['time']."') WHERE (login,indexName) = ('".$_POST['login']."','".$_POST['indexName']."')");
    $data= pg_query($connection, "SELECT json FROM LastIndexLook WHERE (login,indexName) = ('".$_POST['login']."','".$_POST['indexName']."')");
    $json = json_decode(pg_fetch_assoc($data)['json']);
    foreach ($_POST['editedList'] as $key => $field) {
        $json->$field->lastTime = $_POST['time'];
        // $json[$field]['lastTime'] = $_POST['time'];
    }
    // $data =  pg_fetch_assoc($pass_query);
    pg_query($connection, "UPDATE LastIndexLook SET json='".json_encode($json)."' WHERE (login,indexName) = ('".$_POST['login']."','".$_POST['indexName']."')");
    $arraydb['xxx'] = "UPDATE LastIndexLook SET json=".json_encode($json)." WHERE (login,indexName) = ('".$_POST['login']."','".$_POST['indexName']."')";//$data['modules'];
    pg_close($connection);
}
pg_query($connection, "SELECT json FROM LastIndexLook WHERE (login,indexName) = ('".$_POST['login']."','".$_POST['indexName']."')");
if($_POST['need']=='modulesInfo'){
    // $arraydb['post'] = $_POST;
    $modules = $_POST['modules'];//json_decode(getDB($usersConn,"SELECT modules FROM users WHERE login = '".$_POST['login']."'")[0][0]);
    // $arraydb['@@@'] =  $_POST;
        //идем по всем модулям

		foreach($modules as $moduleKey => $module) {

            // бд модуля
            // $arraydb[$moduleKey] = $moduleKey;
            $moduleConn = connectBD($moduleKey);

			$indexes = getDB($moduleConn,"SELECT * FROM LastIndexLook WHERE login = '".$_POST['login']."'");//array_slice(getDB($moduleConn,"SELECT * FROM LastIndexLook WHERE login = '".$_POST['upload']['login']."'"), 2);
            $_POST['timeFilter'] = array('to' =>  date("Y/m/d H:i:s"));
            // $arraydb['asds'][$moduleKey]['indexes'] = $indexes;
            $query = pg_query($moduleConn,"SELECT * FROM LastIndexLook WHERE login = '".$_POST['login']."'");

            
            for ($i=0; $i < count($indexes); $i++) { 
                
                $fetch = pg_fetch_assoc($query);
                $indexName = $fetch['indexname'];//$indexes[$i][0];

                $fields = json_decode($fetch['json']);
                foreach ($fields as $key => $field) {
                    $_POST['timeFilter']['from'] = $field->lastTime;
                    $_POST['paramsFilter']=array("significance"=>array($key));
                    
                    $querry  = getQuerryString($_POST);
                    // $arraydb['serv'][$moduleKey][$indexName][$key] = $querry;//смотрим по каким полям идет поиск
                    $total = json_decode(getLogs($indexName,$querry,'long'))->hits->total->value;
                    $arraydb[$moduleKey][$indexName][$key] = array("style"=>$field->style , "lastTime"=>$field->lastTime, "key" => $key, "doc_count" => $total );
                }
                //
                
            //     // $arraydb[$moduleKey][$indexName] = json_decode($fetch['json']);;//$stylesedBuckets;
            }
            pg_close($moduleConn);
		}
}
// старая версия подсчета количества логов агрегацией
// for ($i=0; $i < count($indexes); $i++) { 
                
//     $fetch = pg_fetch_assoc($query);
//     $indexName = $fetch['indexname'];//$indexes[$i][0];
//     // "{  "query": {  "range" : {  "time" : { "from": "2019/01/29 00:00:00",  "to": "2020/05/08 10:56:17"   }     }   },"aggs": { "termsfast": { "terms": { "field": "significance.keyword","size":4 } } }}"
//     // "{  "query": {  "range" : {  "time" : { "from": "2020/04/24 09:45:02",  "to": "2020/05/08 10:57:26"   }     }   },"aggs": { "termsfast": { "terms": { "field": "significance.keyword","size":4 } } }}"
//     // $events = array_slice($indexes[$i],2);
//     $_POST['timeFilter']['from'] = $fetch['top'];//$events[0];
//     $_POST['aggs']= 8;
//     $_POST['aggsParam'] = 'significance';
//     $querry  = getQuerryString($_POST);
    
//     // $arraydb['querries'][$moduleKey][$indexName] = $querry;//$_POST;//$events[0];
//     // $querry = '{"aggs" : {		"t_shirts" :{ "range" : {"field" : "time",   "ranges" : [{ "from": "2019/10/29 00:00:00",  "to": "2020/01/29 16:56:00"   }]     },	"aggs": { "termsfast": { "terms": { "field": "significance.keyword" } } } }}}';
//     // $arraydb[$moduleKey][$indexName] = getLogs($indexName,$querry);
//     // $arraydb['result'] =$querry ;
//     $stylesedBuckets = array();
//     // key: "Низкий"
//     $signObj = json_decode($fetch['json']);
//     $buckets = json_decode(getLogs($indexName,$querry,true))->aggregations->termsfast->buckets;
//     for ($j=0; $j < count($buckets); $j++) { 
//         foreach($signObj as $signObjKey => $signObjElem) {
//             if((in_array($buckets[$j]->key , $signObjElem))){
//                 // $arraydb['sadas'][$moduleKey][$indexName][$signObjKey] =$signObjElem;//(in_array($buckets[$j]->key , $signObjElem));
//                 $stylesedBuckets[$j] = $buckets[$j];
//                 $stylesedBuckets[$j]->style = $signObjKey;
//             }
            
//         }
//     }
//     $arraydb[$moduleKey][$indexName] = $stylesedBuckets;
// }

// "{  "query": { "bool": { "must":[{ "bool" : { "should": [{ "match_phrase": { "significance.keyword": "Средний" }},{ "match_phrase": { "significance.keyword": "Низкий" }}]}    },{ "bool" : { "should": [{ "match_phrase": { "fieldName.keyword": "Санаторий" }},{ "match_phrase": { "fieldName.keyword": "Дет.сад 'вишенка'" }},{ "match_phrase": { "fieldName.keyword": "Больница #4" }},{ "match_phrase": { "fieldName.keyword": "Администрация" }}]}    },{  "range" : {  "time" : { "from": "2019/04/22 00:00:00",  "to": "2020/04/22 10:41:00"   }     }   }]}} ,"aggs": { "termsfast": { "terms": { "field": "object.keyword","size":100 } } }}"
// "{  "query": { "bool": { "must":[{ "bool" : { "should": [{ "match_phrase": { "significance.keyword": "Средний" }},{ "match_phrase": { "significance.keyword": "Низкий" }}]}    },{ "bool" : { "should": [{ "match_phrase": { "object.keyword": "Санаторий" }},{ "match_phrase": { "object.keyword": "Дет.сад 'вишенка'" }},{ "match_phrase": { "object.keyword": "Больница #4" }},{ "match_phrase": { "object.keyword": "Администрация" }}]}    },{  "range" : {  "time" : { "from": "2019/04/22 00:00:00",  "to": "2020/04/22 10:41:00"   }     }   }]}} ,"aggs": { "termsfast": { "terms": { "field": "object.keyword","size":100 } } }}"
if($_POST['need']=='Circle_Diagram'){
    $arraydb['post']=$_POST;

    $_POST['aggs']= 100;
    $_POST['aggsParam'] = $_POST['specialObject']['fieldName'];
    // $_POST['paramsFilter'][$_POST['specialObject']['fieldName']] = $_POST['specialObject']['fieldList'];
    $querry  = str_replace('\\','\\\\',getQuerryString($_POST));//replaceInStr(getQuerryString($_POST) ,"\\", "\\\\") ;//getQuerryString($_POST) 
    $arraydb['!!!'] = $querry;
    // $arraydb['res'] = json_decode(getLogs($_POST['indexName'],$querry,true));
    $labels = array();
    $counts = array();
    $result = json_decode(getLogs($_POST['indexName'],$querry,'long'));
    $arraydb['took'] = $result->took;
    $agrList = $result->aggregations->termsfast->buckets;
    
    // $arraydb['agrList'] = $agrList;
    foreach($agrList as $agrListKey => $agrListElem) {
         array_push($labels, $agrListElem->key);
         array_push($counts, $agrListElem->doc_count);
    }
    $arraydb['logs']['count'] = $counts; //gettype
    $arraydb['logs']['labels'] = $labels;
}
if($_POST['need']=='Bar_Diagram'){
    $arraydb['post']=$_POST;
    
    $_POST['aggs']= 100;
    // $_POST['aggsParam'] = $_POST['specialObject']['fieldName'];
    // $_POST['paramsFilter'][$_POST['specialObject']['fieldName']] = $_POST['specialObject']['fieldList'];
    $querry  = str_replace('\\','\\\\',getQuerryString($_POST));//replaceInStr(getQuerryString($_POST) ,"\\", "\\\\") ;//getQuerryString($_POST) 
    $arraydb['!!!'] = $querry;
    
    $result = json_decode(getLogs($_POST['indexName'],$querry,'short'))
        ->aggregations->my_buckets;
    $buckets = $result->buckets;
    $arraydb['after_key'] = $result->after_key;
    $logs = array();
    foreach($buckets as $agrListKey => $agrListElem) {
         array_push($logs, array(
            'doc_count'  =>  $agrListElem->doc_count,
            'date'  => $agrListElem->key->date
         ));
        //  array_push($counts, $agrListElem->doc_count);
    }
    $arraydb['logs'] = $logs; 
}
// if($_POST['need']=='Circle_Diagram'){
//     $arraydb['post']=$_POST;
//     $customArray =  array(
//         'dashType'  =>  $_POST['dashType'],
//         'timeFilter'  =>  $_POST['timeFilter']
//     );
//     for ($i = 0; $i < count($_POST['specialObject']['fieldList']); $i++) {
//         $customArray['paramsFilter'] = array_merge($_POST['paramsFilter'], array($_POST['specialObject']['fieldName']  => array( $_POST['specialObject']['fieldList'][$i])));
//         $querry = getQuerryString($customArray);
//         // $arraydb['querries'][$i] = $querry;
//         $logs = getLogs($_POST['indexName'],$querry,true);
//         $raw_logs = json_decode($logs);
//         // $arraydb['all'][$i] = $raw_logs;
        
//         $count = $raw_logs->hits->total->value; //gettype
//         // if(count($count)>100) {
//         //     $arraydb['logs']['count'][$i] = array_slice($count, 99);
//         //     $arraydb['logs']['labels'][$i] = array_slice($_POST['specialObject']['fieldList'][$i], 99);
//         // }
//         // else{
//             $arraydb['logs']['count'][$i] = $count;
//             $arraydb['logs']['labels'][$i] = $_POST['specialObject']['fieldList'][$i];
//         // }
       
//     }
// }
// "UPDATE dashboards SET json=''{"field":"object","style":{"width":435,"minWidth":435},"indexName":"acs_castle_ep2_userlog","logs":[],"timeFilter":{"from":"2019\/04\/23 00:00:00","to":"2020\/04\/23 10:28:00"},"uploads":{"uploads":false,"timeKind":1,"timeNum":11000,"to":"now\/d"},"paramFilter":[]}'' WHERE (master,id,indexname) = (''admin1'',''4'',''acs_castle_ep2_userlog'')
// UPDATE dashboards SET json='{"field":"significance","style":{"width":435,"minWidth":435},"indexName":"acs_castle_ep2_userlog","logs":[],"timeFilter":{"from":"2019\/04\/23 00:00:00","to":"2020\/04\/23 10:33:00"},"uploads":{"uploads":false,"timeKind":1,"timeNum":11000,"to":"now\/d"},"paramFilter":{"fieldName":["\u0421\u0430\u043d\u0430\u0442\u043e\u0440\u0438\u0439","\u0414\u0435\u0442.\u0441\u0430\u0434 ''\u0432\u0438\u0448\u0435\u043d\u043a\u0430''","\u0411\u043e\u043b\u044c\u043d\u0438\u0446\u0430 #4","\u0410\u0434\u043c\u0438\u043d\u0438\u0441\u0442\u0440\u0430\u0446\u0438\u044f"]}}' WHERE (master,id,indexname) = ('admin1','4','acs_castle_ep2_userlog')
// UPDATE dashboards SET json='{"field":"significance","style":{"width":435,"minWidth":435},"indexName":"acs_castle_ep2_userlog","logs":[],"timeFilter":{"from":"2019\/04\/23 00:00:00","to":"2020\/04\/23 10:33:00"},"uploads":{"uploads":false,"timeKind":1,"timeNum":11000,"to":"now\/d"},"paramFilter":{"fieldName":["\u0421\u0430\u043d\u0430\u0442\u043e\u0440\u0438\u0439","\u0414\u0435\u0442.\u0441\u0430\u0434 ''\u0432\u0438\u0448\u0435\u043d\u043a\u0430''","\u0411\u043e\u043b\u044c\u043d\u0438\u0446\u0430 #4","\u0410\u0434\u043c\u0438\u043d\u0438\u0441\u0442\u0440\u0430\u0446\u0438\u044f"]}}' WHERE (master,id,indexname) = ('admin1','4','acs_castle_ep2_userlog')
if($_POST['need']=='changeDash'){
    $connection = connectBD($_POST['dbName']);
    $personalDash = getDB($connection,"SELECT json FROM dashboards WHERE (master,id,indexName) = ('".$_POST['login']."','".$_POST['id']."','".$_POST['indexName']."')");//,login='".$_POST['login']."'

    // $arraydb['login'] = $_POST['login'];
    // var_dump($dashStandart);
    $arraydb['post']=$_POST;  
    
    //получаем дошик который нужно поменять.пока берем 4 по определению сама строка лежит на 4 позиции
    $old_dashboard = json_decode($personalDash[0][0]);
    // $new_dashboard = $_POST['change'];
    // $arraydb['standartDash'] = json_decode($personalDash[0][0]);

    // // $arraydb['old_dashboard'] = $old_dashboard;
    foreach($_POST['change'] as $key => $value) {
        $old_dashboard->$key = $value;
    }
    // $json= json_encode($old_dashboard);
    $json = replaceInStr(json_encode($old_dashboard) ,"'", "''") ;
    $sqlQuerry = "UPDATE dashboards SET json='".$json."' WHERE (master,id,indexname) = ('".$_POST['login']."','".$_POST['id']."','".$_POST['indexName']."')";
    
    $arraydb['standartDash'] = $sqlQuerry;
    // UPDATE dashboards SET json='!' WHERE (master,id,indexname) = ('admin1','0','sns_event')";
    // // $arraydb['old_dashboard'] = $old_dashboard;
    pg_query($connection, $sqlQuerry);
    // pg_query($connection, "UPDATE dashboards SET json='".$json."' WHERE (master,id,indexName) = ('".$_POST['login']."','".$_POST['id']."','".$_POST['indexName']."')");//,login='".$_POST['login']."'
    pg_close($connection);
}

if($_POST['need']=='changeSettField'){ 
    $connect = connectBD($_POST['dbName']);
    $fields= '';
    $values= '';
    foreach($_POST['form'] as $key => $value) {
        $fields = $fields.$key.', ';
        $values = $values."'".$value."'".', ';
    }
    $fields = substr($fields, 0,strlen($fields)-2);
    $values = substr($values, 0,strlen($values)-2);
    $str = "UPDATE ".$_POST['tableName']." SET (".$fields.") = (".$values.") WHERE id = ".$_POST['id']."";
    $result = pg_query($connect, $str);
    if ($result) {
		$arraydb['result'] = 'done';
	}
	else {
		$arraydb['result'] = 'nope';
	}
    $arraydb['post'] = $_POST;
    $arraydb['str'] = $str;
    //      
    // 
    // 
    // 
    // 

    pg_close($connect);
}
// var_dump($_POST);
if($_POST['need']=='addSettField'){ 
    $connect = connectBD($_POST['dbName']);
    $arraydb['post'] = $_POST;
    
    if(isset($_POST['pushed']["name"])){
        $result = pg_query($connect, "SELECT * FROM ".$_POST['tableName']." WHERE name = '".$_POST['pushed']["name"]."'");
        if(pg_numrows($result)>0){
            $arraydb['result'] = 'именем';
        }
    }
    if(isset($_POST['pushed']["ip"])&&isset($_POST['pushed']["port"])){
        $result = pg_query($connect, "SELECT * FROM ".$_POST['tableName']." WHERE (ip,port) = ('".$_POST['pushed']["ip"]."','".$_POST['pushed']["port"]."')");
        // $arraydb['reZ1']=$result;
        // $arraydb['reZ2']=pg_numrows($result);
        $arraydb['sqlstr']="SELECT * FROM ".$_POST['tableName']." WHERE (ip,port) = ('".$_POST['pushed']["ip"]."','".$_POST['pushed']["port"]."')";
        if(pg_numrows($result)>0){
            $arraydb['result'] = 'номером ip и порта';
        }
    }
    if(!isset($arraydb['result'])){
        $fields= '';
        $values= '';
        // $_POST['form']['aue'] = 'aui';
        foreach($_POST['pushed'] as $key => $value) {
            $fields = $fields.$key.', ';
            $values = $values."'".$value."'".', ';
        }
        $fields = substr($fields, 0,strlen($fields)-2);
        $values = substr($values, 0,strlen($values)-2);

        pg_query($connect,"INSERT INTO ".$_POST['tableName']." (".$fields.") VALUES (".$values.")");
        // "INSERT INTO endpoints (id, obj, name, ip, port, login, domen, pass, usename) VALUES ('2', 'Санаторий', '1', '192.168.3.41', '11', '2', '2', '2', 'нет')"
        // $arraydb['sqlstr']="INSERT INTO ".$_POST['tableName']." (".$fields.") VALUES (".$values.")";
        $arraydb['result'] = 'done';
        // $arraydb['id'] = $id;
    }
    pg_close($connect);
}
if($_POST['need']=='delSettField'){
    $connect = connectBD($_POST['dbName']);

    $arraydb['array'] = $_POST;
    

    // $dopDelete = json_decode(getDB($connect, "SELECT * FROM tablesDependences ".$_POST['tableName']." WHERE title = '".$_POST['tableName']."'")[1]);

    //массив связанных таблиц из которых тоже нужно удалить
    $dopDelete =json_decode(getDB($connect, "SELECT * FROM tablesDependences ".$_POST['tableName']." WHERE title = '".$_POST['tableName']."'")[0][1]);
    $arraydb['dopDelete'] = $dopDelete;
    //удаляем все что связано
    foreach($dopDelete as $key => $value) {
        // $arraydb[$key] = array_keys($value)[0];
        // $arraydb[$key.'|val'] = $value[array_keys($value)[0]];
        // $arraydb['str'] = "DELETE FROM ".$key." WHERE ".array_keys($value)[0]." = ".$value[array_keys($value)[0]]."";

        $masterName = $value->master;
        $slaveName = $value->slave;

        

        $whatWeDelete = getDB($connect, "SELECT ".$masterName." FROM  ".$_POST['tableName']." WHERE id = ".$_POST['id']."")[0][0];
        // $arraydb[':('] = "SELECT '".$masterName."' FROM  ".$_POST['tableName']." WHERE id = '".$_POST['id']."'";

        // информация для frontend
        $arraydb['dependences'][$key]['field'] = $slaveName;
        $arraydb['dependences'][$key]['whatWeDelete'] = $whatWeDelete;


        // $arraydb['!'] = $whatWeDelete;

        
        // $result = "DELETE FROM ".$key." WHERE ".$slaveName." = '".$whatWeDelete."'";
        $result = pg_query($connect, "DELETE FROM ".$key." WHERE ".$slaveName." = '".$whatWeDelete."'");
    }
    // $arraydb[':(']=  "DELETE FROM ".$_POST['tableName']." WHERE id = ".$_POST['id']."";
    $result = pg_query($connect, "DELETE FROM ".$_POST['tableName']." WHERE id = ".$_POST['id']."");
    
    if ($result) {
		$arraydb['result'] = 'done';
	}
	else{
		$arraydb['result'] = 'nope';
	}

    pg_close($connect);
    // $result = pg_query($connect, "SELECT * FROM ".$_POST['tableName']." WHERE name = '".$_POST['pushed']["name"]."'");

    // if(isset($_POST['objName'])){
    //     $result = pg_query($conn, "DELETE FROM endpoints WHERE obj = '".$_POST['objName']."'");
    // }

	// $result = pg_query($conn, "DELETE FROM ".$_POST['tb_name']." WHERE id = ".$_POST['id']."");

	// if ($result) {
	// 	$arraydb['result'] = 'done';
	// }
	// else{
	// 	$arraydb['result'] = 'nope';
	// }
}
if($_POST['need']=='logs'){

    $arraydb['post']=$_POST;  
    //запрос для получения логов
    $querry = getQuerryString($_POST);
    $arraydb['querry'] = ($querry); 
    $logs = getLogs($_POST['indexName'],$querry,'short');
    $raw_logs = json_decode($logs);
    $arraydb['raw_logs'] = json_decode($logs);
    $logs_arr = $raw_logs->hits->hits;   
    $arraydb['logs'] = ($logs_arr);

    // if($_POST['specialObject']['id']=='8'){
    $arraydb['search_after'] = $raw_logs->hits->hits[$_POST['specialObject']['logsCount']-1]->sort;
    // }

    //запрос для получения количества логов
    $_POST['specialObject']['logsCount'] = 1;
    $_POST['specialObject']['curPage'] = 1;
    $querry = getQuerryString($_POST);
    $arraydb['count_querry'] = ($querry); 
    $logs = getLogs($_POST['indexName'],$querry,'long');
    $raw_logs = json_decode($logs);
    $arraydb['total'] = $raw_logs->hits->total->value;

}
// "{"size":250, "query": { "bool": { "must":[{ "bool" : { "should": [{ "match_phrase": { "object.keyword": "Санаторий" }}]}    },{ "bool" : { "should": [{ "match_phrase": { "significance.keyword": "Средний" }}]}    },{  "range" : {  "time" : { "from": "2019/04/23 00:00:00",  "to": "2020/04/23 10:44:00"   }     }   }]}} ,"search_after": [0,"0"],"sort":[{"time": "asc"},{"_id": "asc"}]}"
// "{"size":250, "query": { "bool": { "must":[{ "bool" : { "should": [{ "match_phrase": { "object.keyword": "Санаторий" }}]}    },{ "bool" : { "should": [{ "match_phrase": { "significance.keyword": "Средний" }}]}    },{  "range" : {  "time" : { "from": "2019/04/23 00:00:00",  "to": "2020/04/23 10:44:00"   }     }   }]}} ,"search_after": [1581511982000,"R3bQOHABUx9GT4Jq8CSt"],"sort":[{"time": "asc"},{"_id": "asc"}]}"
if($_POST['need']=='logs_pretty'){

    $arraydb['post']=$_POST;  
    //запрос для получения логов
    // if($_POST['specialObject']['id']=='8'){
        // $_POST['specialObject']['logsCount']++;
        $logsCount = $_POST['specialObject']['logsCount'];
        $oldPage = $_POST['specialObject']['oldPage'];
        $curPage = $_POST['specialObject']['curPage']; 
        $_POST['specialObject']['logsCount'] = abs($curPage-$oldPage)*$logsCount;
        // $arraydb['logsCount'] = $_POST['specialObject']['logsCount'];
    // }
    $querry = getQuerryString($_POST);
    $arraydb['querry:)'] = ($querry); 
    $logs = getLogs($_POST['indexName'],$querry,'short');
    $raw_logs = json_decode($logs);
    $arraydb['raw_logs'] = json_decode($logs);
    $logs_arr = $raw_logs->hits->hits;   
    // $arraydb['logs'] = ($logs_arr);
    
    // if($_POST['specialObject']['id']=='8'){
    $arraydb['logs'] = array_slice($logs_arr, (abs($curPage-$oldPage)-1)*$logsCount);
    // $arraydb['cutted_logs'] = $curPage-$oldPage>0?array_slice($logs_arr, ($curPage-$oldPage-1)*$logsCount):array_slice($logs_arr, 0, $logsCount);
    // $arraydb['all_logs'] = ($logs_arr);

    $hits =  $raw_logs->hits;
    // $arraydb['search_after'] = array();
    $arraydb['search_after'] = array();

    $arraydb['search_after']['tail'] = $curPage-$oldPage>0?$hits->hits[($curPage-$oldPage-1)*$logsCount]->sort:$hits->hits[abs($curPage-$oldPage)*$logsCount-1]->sort;
    $arraydb['search_after']['head'] = $curPage-$oldPage>0?$hits->hits[($curPage-$oldPage)*$logsCount-1]->sort:$hits->hits[(abs($curPage-$oldPage)-1)*$logsCount]->sort;

    $tail = $arraydb['search_after']['tail'][0];//$curPage-$oldPage>0?$hits->hits[($curPage-$oldPage-1)*$logsCount]->sort:$hits->hits[abs($curPage-$oldPage)*$logsCount-1]->sort;
    $arraydb['all'] = $hits;
    $new_tail = $tail;
    $pasted = 0;
    for($i=0;$i<mb_strlen($tail);$i++){
        // echo $i;
        if($tail[$i]=='\\'){
            $new_tail = mb_substr($new_tail,0,$i+$pasted).'\\\\'.mb_substr($tail,$i+1,mb_strlen($tail));
            $pasted++;
        }
    }
    $arraydb['search_after']['tail'][0] = $new_tail;

    $head = $arraydb['search_after']['head'][0];//$curPage-$oldPage>0?$hits->hits[($curPage-$oldPage)*$logsCount-1]->sort:$hits->hits[(abs($curPage-$oldPage)-1)*$logsCount]->sort;

    $new_head = $head;
    $pasted = 0;
    for($i=0;$i<mb_strlen($head);$i++){
        // echo $i;
        if($head[$i]=='\\'){
            $new_head = mb_substr($new_head,0,$i+$pasted).'\\\\'.mb_substr($head,$i+1,mb_strlen($head));
            $pasted++;
        }
    }
    $arraydb['search_after']['head'][0] = $new_head;


        // $arraydb['search_after']['tail'] =  $hits->hits[0]->sort;
        // $arraydb['search_after']['head'] =$hits->hits[$_POST['specialObject']['logsCount']-1]->sort;

        // $_POST['specialObject']['id']=0;
    // }

    //запрос для получения количества логов
    $_POST['specialObject']['logsCount'] = 1;
    $_POST['specialObject']['curPage'] = 1;
    // $_POST['specialObject']['count'] = true;
    $querry = getQuerryString($_POST);
    $arraydb['count_querry'] = ($querry);
    $logs = getLogs($_POST['indexName'],$querry,'long');
    $raw_logs = json_decode($logs);
    $arraydb['raw_logs_total'] = $logs;
    $arraydb['total'] = $raw_logs->hits->total->value;

}

// if($_POST['need']=='last_log'){
//     $arraydb['post']=$_POST;  
//     $maxLoaded = 10000;
//     $total = $_POST['specialObject']['curPage']*$_POST['specialObject']['logsCount'];
    
//     $_POST['specialObject']['logsCount'] = $maxLoaded;
//     // $POST['specialObject']['id'] = 0;

//     $arraydb['ceil'] = ceil($total/$maxLoaded); 
//     // вот столько нужно нам раз бабахнуть по эластику вытягивая по 10 тыс search_after'ом
//     $arraydb['round'] = round($total/$maxLoaded); 

//     for ($i = 0; $i < round($total/$maxLoaded); $i++) {
//         //запрос для получения логов
//         $querry = getQuerryString($_POST);
//         $arraydb[$i]['querry'] = ($querry); 
//         $logs = getLogs($_POST['indexName'],$querry,false);
//         $raw_logs = json_decode($logs);
//         // $arraydb['raw_logs'] = json_decode($logs);
//         $logs_arr = $raw_logs->hits->hits;   
        
//         $arraydb[$i]['logs'] = array_slice($logs_arr, $maxLoaded-5);

//         if($_POST['specialObject']['id']=='8'){
//             $search_after = $raw_logs->hits->hits[$_POST['specialObject']['logsCount']-1]->sort;
//             $arraydb[$i]['search_after'] = $search_after;
//             $_POST['specialObject']['search_after'] = $search_after;
//         }
//     }
//     // Добрались до последней 10000
//     $querry = getQuerryString($_POST);
//     $arraydb['querry'] = ($querry); 
//     $logs = getLogs($_POST['indexName'],$querry,false);
//     $raw_logs = json_decode($logs);
//     // $arraydb['raw_logs'] = json_decode($logs);
//     $logs_arr = $raw_logs->hits->hits;   
    
//     $arraydb['logs'] = array_slice($logs_arr, $maxLoaded-5);

//     if($_POST['specialObject']['id']=='8'){
//         $search_after = $raw_logs->hits->hits[$_POST['specialObject']['logsCount']-1]->sort;
//         $arraydb[$i]['search_after'] = $search_after;
//         $_POST['specialObject']['search_after'] = $search_after;
//     }


// }

pg_close($conn);
echo json_encode($arraydb);
// echo ($logss);

// <iframe src="http://192.168.3.26:5601/app/kibana#/visualize/edit/de23dc40-fa5f-11e9-ba35-7dacb7b32d29?embed=true&_g=(filters:!(),refreshInterval:(pause:!t,value:0),time:(from:'2019-07-02T09:30:56.867Z',to:'2020-01-14T09:46:02.538Z'))&_a=(filters:!(),linked:!f,query:(language:kuery,query:''),uiState:(vis:(params:(sort:(columnIndex:3,direction:asc)))),vis:(aggs:!((enabled:!t,id:'1',params:(),schema:metric,type:count),(enabled:!t,id:'2',params:(customLabel:%D0%92%D1%80%D0%B5%D0%BC%D1%8F,field:time,missingBucket:!f,missingBucketLabel:Missing,order:desc,orderBy:'1',otherBucket:!f,otherBucketLabel:Other,size:5),schema:bucket,type:terms),(enabled:!t,id:'3',params:(customLabel:'ip+%D0%BA%D0%B0%D0%BC%D0%B5%D1%80%D1%8B',field:ip_cam.keyword,missingBucket:!f,missingBucketLabel:Missing,order:desc,orderBy:'1',otherBucket:!f,otherBucketLabel:Other,size:5),schema:bucket,type:terms),(enabled:!t,id:'4',params:(customLabel:'%D0%A2%D0%B8%D0%BF+%D1%81%D0%BE%D0%B1%D1%8B%D1%82%D0%B8%D1%8F',field:type_log.keyword,missingBucket:!f,missingBucketLabel:Missing,order:desc,orderBy:'1',otherBucket:!f,otherBucketLabel:Other,size:5),schema:bucket,type:terms),(enabled:!t,id:'5',params:(customLabel:%D0%9A%D0%BE%D0%BC%D0%BC%D0%B5%D0%BD%D1%82%D0%B0%D1%80%D0%B8%D0%B9,field:comment.keyword,missingBucket:!f,missingBucketLabel:Missing,order:desc,orderBy:'1',otherBucket:!f,otherBucketLabel:Other,size:5),schema:bucket,type:terms)),params:(computedColsPerSplitCol:!f,computedColumns:!(),dimensions:(bucket:!((accessor:0,aggType:terms,format:(id:terms,params:(id:date,missingBucketLabel:Missing,otherBucketLabel:Other)),params:()),(accessor:1,aggType:terms,format:(id:terms,params:(id:string,missingBucketLabel:Missing,otherBucketLabel:Other)),params:()),(accessor:2,aggType:terms,format:(id:terms,params:(id:string,missingBucketLabel:Missing,otherBucketLabel:Other)),params:()),(accessor:3,aggType:terms,format:(id:terms,params:(id:string,missingBucketLabel:Missing,otherBucketLabel:Other)),params:())),metric:!((accessor:4,aggType:count,format:(id:number),params:()))),filterAsYouType:!f,filterBarHideable:!f,filterBarWidth:'25%25',filterCaseSensitive:!f,filterHighlightResults:!f,filterTermsSeparately:!f,hiddenColumns:Count,hideExportLinks:!f,perPage:10,showFilterBar:!f,showMetricsAtAllLevels:!f,showPartialRows:!f,showTotal:!f,sort:(columnIndex:!n,direction:!n),totalFunc:sum),title:'%D0%A1%D0%BF%D0%B8%D1%81%D0%BE%D0%BA+%D0%BB%D0%BE%D0%B3%D0%BE%D0%B2',type:enhanced-table))" height="600" width="800"></iframe>
    // curl -XGET 'http://localhost:9200/acs_castle_ep2_event/main/_search?&pretty=true&size=5' -d '{ "sort": [  {    "_script": { "script": "try { Integer.parseInt(doc[\"significance\"].value); } catch(Exception e){ return Integer.MAX_VALUE;}",  "type": "number", "order": "asc", "lang": "groovy"     }  }   ]  }'

//!!!
function replaceInStr($tail ,$replacedSymb, $replacedBy) 
{
    $new_tail = $tail;
    $pasted = 0;
    // $tail = mb_substr($tail,0,1);
    for($i=0;$i<mb_strlen($tail);$i++){
        // echo $i;
        if($tail[$i]==$replacedSymb){
            $new_tail = mb_substr($new_tail,0,$i+$pasted).$replacedBy.mb_substr($tail,$i+1,mb_strlen($tail));
            $pasted++;
        }
    }
	return $new_tail;
}

   