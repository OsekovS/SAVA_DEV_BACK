<?php
function getLogs($indexName, $querry, $total) {
    
    if($total){
        $string = 'http://localhost:9200/'.$indexName.'/main/_search?scroll=1m';
    }else{
        $string = 'http://localhost:9200/'.$indexName.'/main/_search';
    }
    return file_get_contents(
		$string, 
		false, 
		stream_context_create(
			array(
				'http' => array(
                    'method' => 'POST',
                    'header' => 'Content-Type: application/json',
					'content' => $querry
				)
			)
		)
    );
}

function getQuerryString($POST) {
    // var_dump($POST['paramsFilter']);
    // var_dump($POST);
   
    if($POST['dashType']=='Table'){
        $logsStart = ((int)$POST['specialObject']['curPage']-1)*(int)$POST['specialObject']['logsCount'];
            $sortDirection = $POST['specialObject']['sortParam']['direction'];
            $sortType = $POST['specialObject']['sortParam']['field'];
            if($POST['specialObject']['sortParam']['type']=='text'){
                $sortType = $sortType.'.keyword';
            }
        $fromToString = '"from":'.$logsStart.',"size":'.$POST['specialObject']['logsCount'].',';
        $sortString = ',"sort":{"'.$sortType.'": "'.$sortDirection.'"}';
        // echo 'TABLE';
    }
    else{
        // echo $POST['specialObject']['fieldList'][0];
        $fromToString = ' ';//"from":0,"size":0
        $sortString = ' ';
    }
 
    if(isset( $POST['paramsFilter'])&&count($POST['paramsFilter'])){
        
        $querry = '{'.$fromToString.' "query": { "bool": { "must":[';
            $filter = $POST['paramsFilter'];
    // $arraydb['filter'] = var_dump($filter);
        // if()
        foreach($filter as $key => $value) {
            // if(count($value)>0){
                // var_dump(count($value));
            $str = '{ "bool" : { "should": [';
            for ($i = 0;$i < count($value) ; $i++) {//<=
                // var_dump($value[$i]);
                // var_dump(strval($value[$i]));
                // var_dump(!strval($value[$i])=="");
                if(!($value[$i]=="")){
                    // echo '!!!';
                    $str = $str.'{ "match_phrase": { "'.$key.'.keyword": "'.$value[$i].'" }},';
                    // var_dump($value);
                }
            }
            $str = substr($str, 0, strlen($str)-1);
            $querry=$querry.$str.']}    },';
            // }   
        }
        $querry = substr($querry, 0, strlen($querry)-1);
        if($POST['aggs']){
            $querry=$querry.',{  "range" : {  "time" : { "from": "'.$POST['timeFilter']['from'].'",  "to": "'.$POST['timeFilter']['to'].'"   }     }   }]}} ,"aggs": { "termsfast": { "terms": { "field": "'.$POST['aggsParam'].'.keyword","size":20 } } }}';  
            // ,"aggs": { "termsfast": { "terms": { "field": "'.$POST['aggsParam'].'.keyword","size":20 } } }   }';
        }else{
            $querry=$querry.',{  "range" : {  "time" : { "from": "'.$POST['timeFilter']['from'].'",  "to": "'.$POST['timeFilter']['to'].'"   }     }   }]}} '.$sortString.'}'; 
        }
    }
    else if($POST['aggs']){
        if($POST['paramsFilter']){
            foreach($filter as $key => $value) {
                $str = '{ "bool" : { "should": [';
                for ($i = 0;$i < count($value) ; $i++) {//<=
                    if(!($value[$i]=="")){
                        $str = $str.'{ "match_phrase": { "'.$key.'.keyword": "'.$value[$i].'" }},';
                    }
                }
                $str = substr($str, 0, strlen($str)-1);
                $querry=$querry.$str.']}    },';
                // }   
            }
        }
 



        $querry = '{  "query": { "bool": { "must":[{ "bool" : { "should": [{ "match_phrase": { "event_type.keyword": "Аудит успехов" }}]}    },{  "range" : {  "time" : { "from": "2019/12/12 00:00:00",  "to": "2020/03/12 17:46:00"   }     }   }]     }},"aggs": { "termsfast": { "terms": { "field": "'.$POST['aggsParam'].'.keyword","size":20 } } }   }';
        

    }
    else{
        if($POST['aggs']){
            $querry =   '{'.$fromToString.' "query": {  "range" : {  "time" : { "from": "'.$POST['timeFilter']['from'].'",  "to": "'.$POST['timeFilter']['to'].'"   }     }   },"aggs": { "termsfast": { "terms": { "field": "'.$POST['aggsParam'].'.keyword","size":20 } } }}';  
            // ,"aggs": { "termsfast": { "terms": { "field": "'.$POST['aggsParam'].'.keyword","size":20 } } }   }';
        }else{
            $querry =   '{'.$fromToString.' "query": {  "range" : {  "time" : { "from": "'.$POST['timeFilter']['from'].'",  "to": "'.$POST['timeFilter']['to'].'"   }     }   }'.$sortString.'}'; 
        }
        // $querry =   '{'.$fromToString.' "query": {  "range" : {  "time" : { "from": "'.$POST['timeFilter']['from'].'",  "to": "'.$POST['timeFilter']['to'].'"   }     }   }'.$sortString.'}';         // "must" :[{ "match": { "title": "Search"}}], 
    }
    return $querry;//$querry ''
}
// старый запрос
// {
//     "from":200,
//     "size":100,
//     "query":{  
//         "range" : {  
//             "time" : { 
//                 "from": "2019/03/20 00:00:00",  
//                 "to": "2020/03/20 12:56:00"   
//             }     
//         }   
//     },
//     "sort":{
//         "time": "asc"
//     }
// }

//новый запрос
// {
//     "size":100, 
//     "query": {
//       "range" : {
//         "time" : {
//           "from": "2020/01/16 12:47:53",
//           "to": "2020/01/16 12:47:57"
//           }     
        
//       }   
//     } ,
//     "search_after": [1579178870000, "cXWkOHABUx9GT4JqtUe7"],
//         "sort": [
//         {"time": "asc"},
//         {"_id": "asc"}
//     ]
// }

// {  "query": {  "range" : {  "time" : { "from": "2020/01/29 00:00:00",  "to": "2020/03/12 18:28:13"   }     }   },"aggs": { "termsfast": { "terms": { "field": "significance.keyword","size":20 } } }}

// GET sns_event/main/_search
// {
//     "from":0,"size":100, 
//     "query": {  
//       "range" : {  
//         "time" : { 
//           "from": "2019/12/19 00:00:00",
//           "to": "2020/03/19 17:23:00"
//           }
//         }
//       },
//       "sort":{"time": "asc"}
// }
// "from":9990,"size":11, ошибки
// "from":9991,"size":10, 

// GET sns_event/main/_search?scroll=1m в тотал более 10 тыс 

// GET sns_event/main/_search
// {
//   "query": {
//     "match_all": {}
//   }
//   ,
//    "search_after": [10001],
//        "sort": [
//         {"time": "asc"}
//     ]
// }
