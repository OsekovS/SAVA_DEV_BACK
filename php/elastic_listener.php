<?php
    function in_between($var)
    {
        return(strtotime($var->_source->time)>time()-10*3600*24);
    }

// '{ \"query\" : { \"term\" : { \"ip_cam\" : \"192.168.3.110\" } } }'
if(isset($_POST['giveMeData'])){
    // $querry =   `"query": {
    //     "range" : {
    //         "timestamp" : {
    //             "gte" : "now-11d/d",
    //             "lt" :  "now/d"
    //         }
    //     }
    // }`;
    // var_dump($_POST['_gte']);
    $querry = '{"size":10000, "query" : { "range" : { "time" : {"gte" : "'.$_POST['_gte'].'","lt" :  "now/d"}} } }';
    // $max_size =   '{"size":10000}';

    $sURL = "http://localhost:9200/skud*/main/_search"; // URL-адрес POST 
    // $sURL = "http://localhost:9200/_nodes/_local/stats/fs"; статистика
    $sPD =$querry ; // Данные POST
    $aHTTP = array(
    'http' => // Обертка, которая будет использоваться
        array(
        'method'  => 'POST', // Метод запроса
        'header'  => 'Content-Type: application/json',
        'content' => $sPD
        )
    );
    $elastic_resp = stream_context_create($aHTTP);
    $elastic_resp = file_get_contents($sURL, false, $elastic_resp);
    $logs = json_decode($elastic_resp);
    
    // $response['result'] = $result;
    echo json_encode($logs->hits->hits);
    // var_dump($logs->hits->hits);
    // var_dump($logs->hits->hits);
    // var_dump(array_filter($logs->hits->hits, "in_between"));
  
}

    //  $_COOKIE["el_del_res"] = system(curl -H "Content-Type: application/json" -XPOST 'http://localhost:9200/actcamrealtime/main/_delete_by_query' -d '{ "query" : { "term" : { "ip_cam" : "192.168.3.109" } } }');
	// echo "server response";
	//system("curl -H \"Content-Type: application/json\" -XPOST 'http://localhost:9200/firmwarevers/main/_delete_by_query' -d '{ \"query\" : { \"term\" : { \"ip_cam\" : \"192.168.3.110\" } } }'");
	//system("curl -H \"Content-Type: application/json\" -XPOST 'http://localhost:9200/actcamrealtime/main/_delete_by_query' -d '{ \"query\" : { \"term\" : { \"ip_cam\" : \"192.168.3.110\" } } }'");
    // $sURL = "http://localhost:9200/allevent/main/_search?size=10000&pretty";

    // var_dump(strtotime("6 November 2019 12 hours 53 minutes 11 seconds"));
    // var_dump(strtotime("2019/11/06 12:53:11"));
    //var_dump(strtotime("2019/11/06")<time()-13*3600*24);
    //var_dump(time());
    // var_dump(time()); "2019/11/06 12:53:11"
    // var_dump(strtotime($logs->hits->hits[0]->_source->time));