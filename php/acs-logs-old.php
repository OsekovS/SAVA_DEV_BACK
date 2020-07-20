// $querry =   `"query": {
    //     "range" : {
    //         "timestamp" : {
    //             "gte" : "now-11d/d",
    //             "lt" :  "now/d"
    //         }
    //     }
    // }`;
    // var_dump($_POST['_gte']);
    // $querry = '{"size":10000, "query" : { "range" : { "time" : {"gte" : "'.$_POST['_gte'].'","lt" :  "now/d"}} } }';
    // $max_size =   '{"size":10000}';
    
//     $opts = array('http' =>
//     array(
//       'method'  => 'GET',
//       'header'  => "Content-Type: application/json".
//         // "Authorization: Basic ".base64_encode("$https_user:$https_password")."\r\n",
//     //   'content' => $body,
//       'timeout' => 60
//     )
//   );
                         
//   $context  = stream_context_create($opts);
//   $url = 'http://localhost:9200/skud/event/_search/?pretty=true';
//   $result = file_get_contents($url, false, $context, -1, 40000);
//     echo $result;
    // system("curl -H \"Content-Type: application/json\" -XGET 'http://localhost:9200/skud/event/_search/?pretty=true'");//!!!!!!!!!!!!!!!!!!!!!
    // system("curl -XGET 'http://127.0.0.1:9200/sava/skud/_search/?pretty=true'");

    // получаем данные для запроса
	// list($boundary, $content) = getContent($postData, $files);
	// отправляем запрос
	// $responce = file_get_contents(
	// 	'http://localhost:9200/skud/event/_search/?pretty=true', 
	// 	false, 
	// 	stream_context_create(
	// 		array(
	// 			'http' => array(
    //                 'method' => 'GET',
    //                 'header' => 'Content-Type: application/json'
	// 				// 'header' => 'Content-Type: multipart/form-data; boundary=' . $boundary,
	// 				// 'content' => $content
	// 			)
	// 		)
	// 	)
    // );
    
    // $querry =   `"query": {
    //     "pretty" : {
    //         "true"
    //     },
    // }`;

    // $arraydb['bar1old'] = ;