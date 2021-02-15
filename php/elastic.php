<?php
function getLogs($indexName, $querry, $mode) {
    switch ($mode) {
        case 'long':
            $string = 'http://localhost:9200/'.$indexName.'/main/_search?scroll=1m';
            break;
        case 'short':
            $string = 'http://localhost:9200/'.$indexName.'/main/_search';
            break;
        case 'delete':
            $string = 'http://localhost:9200/'.$indexName.'/_delete_by_query';
            break;
        default:
            # code...
            break;
    }
    // if($total){
    //     $string = 'http://localhost:9200/'.$indexName.'/main/_search?scroll=1m';
    // }else{
    //     $string = 'http://localhost:9200/'.$indexName.'/main/_search';
    // }
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

//     "search_after": [1579178870000, "cXWkOHABUx9GT4JqtUe7"],
//         "sort": [
//         {"time": "asc"},
//         {"_id": "asc"}
//     ]

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
        // if($POST['specialObject']['id']==8){ $sortDirection=='asc' ||
        if($POST['specialObject']['logsCount']!=1){
            // Новый запрос
            $fromToString = '"size":'.$POST['specialObject']['logsCount'].',';
            //16.07
            $search_after_value = $POST['specialObject']['sortParam']['type']=='date'?''.$POST['specialObject']['search_after'][0].'':'"'.$POST['specialObject']['search_after'][0].'"';
            //$search_after_value ='"'.$POST['specialObject']['search_after'][0].'"';
            if( $POST['specialObject']['search_after']!='first')
                //16.07
                $sortString = ',"search_after": ['.$search_after_value.',"'.$POST['specialObject']['search_after'][1].'"],"sort":[{"'.$sortType.'": "'.$sortDirection.'"},{"_id": "'.$POST['specialObject']['idSort'].'"}]';
                //$sortString = ',"sort":[{"'.$sortType.'": "'.$sortDirection.'"},{"_id": "'.$POST['specialObject']['idSort'].'"}]';
            else {
                // if($POST['specialObject']['search_after']=='first')
                // else
                    $sortString = ',"sort":[{"'.$sortType.'": "'.$sortDirection.'"},{"_id": "'.$POST['specialObject']['idSort'].'"}]';
            }
        }else{
            // Старый запрос фром то ( не годится после 10000)
            $fromToString = '"from":'.$logsStart.',"size":'.$POST['specialObject']['logsCount'].',';
            $sortString = ',"sort":{"'.$sortType.'": "'.$sortDirection.'"}';
            // $fromToString = ':)))';
            // $sortString = ':)))';
        }
        
    }
    else{
        // echo $POST['specialObject']['fieldList'][0];
        $fromToString = ' ';//"from":0,"size":0
        $sortString = ' ';
    }

    if(isset( $POST['paramsFilter'])&&count($POST['paramsFilter'])){
        $timeFilterStr = isset( $POST['timeFilter']) ?
            ',{  "range" : {  "time" : { "from": "'.$POST['timeFilter']['from'].'",  "to": "'.$POST['timeFilter']['to'].'"   }     }   }' 
            : '';        
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
            switch ($POST['need']) {
                case 'Bar_Diagram':
                    $aggsString='"aggs": {"my_buckets": { "composite": {"size": '.$POST['specialObject']['barsCount'].', "sources": [{ "date": { "date_histogram": { "field": "time", "calendar_interval": "'.$POST['specialObject']['calendar_interval'].'", "order": "'.$POST['specialObject']['order'].'" } } } ] }} }';
                    break;
                case 'Circle_Diagram':
                    $aggsString='"aggs": { "termsfast": { "terms": { "field": "'.$POST['aggsParam'].'.keyword","size":'.$POST['aggs'].' } } }';
                    break;
                default:
                    # code...
                    break;
            }
             $querry=$querry.$timeFilterStr.']}} ,'.$aggsString.'}';  
            // $querry = $_POST['need']=='Bar_Diagram'?
            // '{"size":0,"aggs": {"my_buckets": { "composite": {"size": '.$POST['specialObject']['barsCount'].', "sources": [{ "date": { "date_histogram": { "field": "time", "calendar_interval": "'.$POST['specialObject']['calendar_interval'].'", "order": "'.$POST['specialObject']['order'].'" } } } ] }} }}'
            // :'{'.$fromToString.' "query": {  '.$timeFilterStr.'  },"aggs": { "termsfast": { "terms": { "field": "'.$POST['aggsParam'].'.keyword","size":'.$POST['aggs'].' } }}}';
       
        }else{
            $querry=$querry.$timeFilterStr.']}} '.$sortString.'}'; 
        }
    }
    else {
        $timeFilterStr = isset( $POST['timeFilter']) ?
        '"range" : {  "time" : { "from": "'.$POST['timeFilter']['from'].'",  "to": "'.$POST['timeFilter']['to'].'"   }     }   ' 
        : '';
            if($POST['aggs']){
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
            $querry = $_POST['need']=='Bar_Diagram'?
                '{"size":0,"aggs": {"my_buckets": { "composite": {"size": '.$POST['specialObject']['barsCount'].', "sources": [{ "date": { "date_histogram": { "field": "time", "calendar_interval": "'.$POST['specialObject']['calendar_interval'].'", "order": "'.$POST['specialObject']['order'].'" } } } ] }} }}'
                :'{'.$fromToString.' "query": {  '.$timeFilterStr.'  },"aggs": { "termsfast": { "terms": { "field": "'.$POST['aggsParam'].'.keyword","size":'.$POST['aggs'].' } }}}';
            // $querry = '{  "query": { "bool": { "must":[{ "bool" : { "should": [{ "match_phrase": { "event_type.keyword": "Аудит успехов" }}]}    },{  "range" : {  "time" : { "from": "2019/12/12 00:00:00",  "to": "2020/03/12 17:46:00"   }     }   }]     }},"aggs": { "termsfast": { "terms": { "field": "'.$POST['aggsParam'].'.keyword","size":'.$POST['aggs'].' } } }   }';
            // $querry =   '{'.$fromToString.' "query": {  '.$timeFilterStr.'  },"aggs": { '.$aggsQuerry.' }}';  
        }
        else{
            // if($POST['aggs']){
            //     $querry =   '{'.$fromToString.' "query": { '.$timeFilterStr.'  },"aggs": { "termsfast": { "terms": { "field": "'.$POST['aggsParam'].'.keyword","size":'.$POST['aggs'].' } } }}';  
            //     // ,"aggs": { "termsfast": { "terms": { "field": "'.$POST['aggsParam'].'.keyword","size":100 } } }   }';
            // }else{
                $querry =   '{'.$fromToString.' "query": {  '.$timeFilterStr.'   }'.$sortString.'}'; 
            // }
            // $querry =   '{'.$fromToString.' "query": {  "range" : {  "time" : { "from": "'.$POST['timeFilter']['from'].'",  "to": "'.$POST['timeFilter']['to'].'"   }     }   }'.$sortString.'}';         // "must" :[{ "match": { "title": "Search"}}], 
        }
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





// {  "query": {  "range" : {  "time" : { "from": "2020/01/29 00:00:00",  "to": "2020/03/12 18:28:13"   }     }   },"aggs": { "termsfast": { "terms": { "field": "significance.keyword","size":100 } } }}

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
