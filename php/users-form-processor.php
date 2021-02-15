<?php

include 'db.php';
include 'elastic.php';
// раскомментируй, чтобы видеть ошибки
// ini_set('error_reporting', E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
//подключение к БД
$conn = connectBD('sava_core');
$_POST = json_decode(file_get_contents('php://input'), true);
// var_dump();
// обработка нажатия на кнопку "добавить"
// обновляем наш список

setcookie("aue", 'aui', time()+60*60*24*30);
function generateCode($length=6) {
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHI JKLMNOPRQSTUVWXYZ0123456789";
	$code = "";
	$clen = strlen($chars) - 1;  
	while (strlen($code) < $length) {
			$code .= $chars[mt_rand(0,$clen)];  
	}
	
	return $code;
}
// setcookie("aue", 'aui', time()+60*60*24*30);
if(isset($_POST['upload'])){
	// var_dump($_POST['upload']['login']);
	if(is_null($_POST['upload']['login'])||($_POST['upload']['login'])==''){
		 $arraydb['result'] = false;

	}
	else{
		// var_dump($_POST['upload']);
		$hash_query= pg_query($conn, "SELECT user_hash,modules FROM users WHERE login='".$_POST['upload']['login']."'");
		$data =  pg_fetch_assoc($hash_query);
		// var_dump($_POST['upload']);
		// $arraydb['data']=$data;
		$arraydb['result'] = ($data['user_hash'] == $_POST['upload']['hash']);
		// $arraydb['post'] = json_decode($_POST['upload']['modules']);
		$modules = json_decode($_POST['upload']['modules']);
		$modulesForUser=json_decode($data['modules']);
	
		$arraydb['mo$ul$$es'] = $modules;
		// бежим по всем модулям собирая фильтры
		foreach($modules as $moduleKey => $module) {
			
			$connection = connectBD($moduleKey);
			// $arraydb[$moduleKey] =gettype($modules);// $modules[$moduleKey]['indexes'];
			
			foreach($module as $indexKey => $index) {
				//считали фильтр из индекса
				$query = pg_query($connection, "SELECT json FROM filters WHERE indexName = '".$index."'");
				$filters[$moduleKey][$index] = json_decode(pg_fetch_assoc($query)['json']);//json_decode(pg_query($connection, "SELECT json FROM filters WHERE indexName='".$indexKey."'"));
				$modulesForUser->$moduleKey->indexes->$index->filter = $filters[$moduleKey][$index];
				// $query = pg_query($connection, "SELECT top FROM LastIndexLook WHERE (login,indexName) = ('".$_POST['upload']['login']."','".$index."')");
				// $modulesForUser->$moduleKey->indexes->$index->lastViewed=  pg_fetch_assoc($query)['top'];
				$query = pg_query($connection, "SELECT json,lastTime FROM LastIndexLook WHERE (login,indexName) = ('".$_POST['upload']['login']."','".$index."')");
				$res = pg_fetch_assoc($query);
				$modulesForUser->$moduleKey->indexes->$index->newLogs=  json_decode($res['json']);
				$DateTime = new DateTime();
				// $lasttime = json_decode($res['lasttime']);
				// $modulesForUser->$moduleKey->indexes->$index->lastViewed = $DateTime->format('Y/M/D H:m:s');
				$modulesForUser->$moduleKey->indexes->$index->lastViewed = json_decode($res['lasttime']);//($DateTime->sub(date_interval_create_from_date_string("".strval($lasttime->fromNum)." ".strval($lasttime->fromLetter)."")))->format('Y/m/d H:m:s');//$res['lasttime'];
				
			}
		
			pg_close($connection);
		}
		// $arraydb['filters'] = $filters;
		
		$arraydb['modules'] = $modulesForUser;
		$arraydb['filters'] =$filters;
		$arraydb['modulesLen'] = count($modulesForUser);
		
		// делаем запрос на занимаемое место 
		exec("curl -XGET 'http://127.0.0.1:9200/_cat/indices?v' | awk '{print $3, $7, $8, $9}'",$diskInfo);
		$diskInfoResult = array();
		for ($i=1; $i < count($diskInfo); $i++) { 
			$exploded = explode(" ",$diskInfo[$i]);
			$diskInfoResult[$i - 1] = array(
				'Название таблицы elasticsearch' =>  $exploded[0],
				'Количество документов' =>  $exploded[1],
				'Документов удалено' =>  $exploded[2],
				'Занимаемый размер' =>  $exploded[3],
			);
		}
		$arraydb['diskInfo'] = $diskInfoResult;
		exec("df -h | awk 'NR==4 {print $4}'", $arraydb['dataAvail']);

		//$arraydb['diskInfo'] = passthru("curl -XGET 'http://127.0.0.1:9200/_cat/indices?v' | awk '{print $3, $7, $8, $9, \"|\"}' | xarg");
		// $arraydb['DISK1'] = exec("curl -XGET 'http://127.0.0.1:9200/_cat/indices?v'");// | awk 'NR==2 { for( i=1; i<=2; i++ ) {   {print $3,$7,$8,$9} } } '");		
		// $arraydb['DISK1'] = exec("curl -XGET 'http://127.0.0.1:9200/_cat/indices?v' | awk 'NR==2 {print $3,$7,$8,$9}'");
		// $arraydb['DISK1'] = exec("curl -XGET 'http://127.0.0.1:9200/_cat/indices?v' | awk 'NR==2 {print $3} {print $7} {print $8} {print $9}'");
		
		$DateTime = new DateTime();
		$arraydb['post'] = $DateTime->sub(date_interval_create_from_date_string('10 months'))->format('Y/m/d H:m:s');// DateInterval::createFromDateString('1 day + 12 hours');
	}
}

if(isset($_POST['auth'])){
	// echo 'auth!'; //признак что авторизовались
	$pass_query= pg_query($conn, "SELECT pass FROM users WHERE login='".$_POST['auth']['login']."'");
	$data =  pg_fetch_assoc($pass_query);
	$arraydb['our'] = hash('sha1' , $_POST['auth']['pass']);
	$arraydb['real'] = $data['pass'];
	$arraydb['result'] = ($data['pass'] == hash('sha1' , $_POST['auth']['pass']));
	$_POST['timeFilter'] = array('to' =>  date("Y/m/d H:i:s"));
	// var_dump(isset($arraydb['result']));
	// echo 'sss';
	// echo ($data['pass'] == hash('sha1' , $_POST['auth']['pass']));

	if($arraydb['result'] ){
	// if($data['pass'] == hash('sha1' , $_POST['auth']['pass'])) {
		// $query = pg_query($conn, "SELECT * FROM allDashboards");
		// $arraydb['allDashBoards'] = json_decode(pg_fetch_assoc($query)['json']);
		# Генерируем случайное число и шифруем его
		$hash = md5(generateCode(10));
		# Записываем в БД новый хеш авторизации и IP
		// pg_query($conn, "UPDATE users SET user_hash='".$hash."' ".$insip." WHERE id='".$data['id']."'");
		pg_query($conn, "UPDATE users SET user_hash='".$hash."' WHERE login='".$_POST['auth']['login']."'");

		// $rights_query= pg_query($conn, "SELECT rights FROM users WHERE login='".$_POST['upload']['login']."'");
		// $rights =  pg_fetch_assoc($rights_query);

		$query = pg_query($conn, "SELECT * FROM users WHERE login = '".$_POST['auth']['login']."'");
		$data = pg_fetch_assoc($query);
		$arraydb['cookies']['admin'] = $data['rights'];
		$arraydb['cookies']['hash'] = $hash;
		$arraydb['cookies']['login'] = $_POST['auth']['login'];
		// $arraydb['cookies']['modules'] = json_decode($data['modules']);
		$arraydb['modulez'] =json_decode($data['modules']);
		$modules = json_decode($data['modules']);
		$filters = array();
		// var_dump($modules);
		//идем по всем модулям
		foreach($modules as $moduleKey => $module) {
			$connection = connectBD($moduleKey);
			//идем по всем индексам 
			foreach($module->indexes as $indexKey => $index) {
				$query = pg_query($connection, "SELECT json,lastTime FROM LastIndexLook WHERE (login,indexName) = ('".$_POST['auth']['login']."','".$indexKey."')");
				$res = pg_fetch_assoc($query);
				$modules->$moduleKey->indexes->$indexKey->newLogs=  json_decode($res['json']);
				$DateTime = new DateTime();
				$modules->$moduleKey->indexes->$indexKey->lastViewed = json_decode($res['lasttime']);//$lasttime;//($DateTime->sub(date_interval_create_from_date_string("".strval($lasttime->fromNum)." ".strval($lasttime->fromLetter)."")))->format('Y/m/d H:m:s');//$res['lasttime'];
				
				
				// $query = pg_query($connection, "SELECT json FROM filters WHERE indexName = '".$indexKey."'");
				// $modules->$moduleKey->indexes->$indexKey->filter = json_decode(pg_fetch_assoc($query)['json']);

				$fields = $modules->$moduleKey->indexes->$indexKey->fields;
				// $arraydb['sasda'][$moduleKey][$indexKey] = pg_fetch_assoc($query);
				// $arraydb['!!!'] = array();
				foreach($fields as $fieldKey => $field) {
					//Получаем все поля
					// $arraydb['filter'][$moduleKey][$indexKey][$fieldKey] = $fieldKey;
					// $arraydb[$fieldKey]=$fieldKey;
					$fieldsListIsFull = true;
					$_POST['timeFilter']['from'] = '2000/01/01 00:00:00';
					$_POST['aggs'] = 15;//true;
					$_POST['aggsParam'] =  $fieldKey;
					// $fields = array();
					
					while($fieldsListIsFull){

						$querry  = getQuerryString($_POST);
						//service
						$arraydb['aggsStrings'][$moduleKey][$indexKey][$fieldKey] = $querry;
						$result = json_decode(getLogs($indexKey,$querry,'long'))->aggregations->termsfast->buckets;
						// $arraydb['querries'][$moduleKey][$indexKey][$fieldKey] = json_decode(getLogs($indexKey,$querry,true))->aggregations->termsfast->buckets;//->aggregations->timeAggs->buckets[0]->termsfast->buckets;
	
						$array = array();
						foreach($result as $resultKey => $resultObject) {
							$array[$resultKey]=$resultObject->key;
							// $arraydb['xxx'][$moduleKey][$indexKey][$fieldKey][$resultKey] = $resultObject->key ;
						}
						// $fields = array_merge($fields, $array);
						// $arraydb['!!!'][$fieldKey] = count($array)==100;
						if(count($array)<$_POST['aggs'] ) {
						// if($fieldKey=='event') {
							$fieldsListIsFull = false;
						}else{
							$_POST['aggs'] = $_POST['aggs']*10;
						}
						
						
					}
					$filters[$moduleKey][$indexKey][$fieldKey]  = $array;

					// $querry = '{"aggs" : {		"t_shirts" :{ "range" : {"field" : "time",   "ranges" : [{ "from": "2019/10/29 00:00:00",  "to": "2020/01/29 16:56:00"   }]     },	"aggs": { "termsfast": { "terms": { "field": "significance.keyword" } } } }}}';
					// $arraydb[$moduleKey][$indexName] = getLogs($indexName,$querry);
					// $modules->$moduleKey->indexes->$indexKey->filter = json_decode(getLogs($indexName,$querry))->aggregations->timeAggs->buckets[0]->termsfast->buckets;
					// $arraydb[$moduleKey][$indexKey][$fieldKey]=json_decode(getLogs($indexKey,$querry));


					// $result = json_decode(getLogs($indexKey,$querry,true))->aggregations->timeAggs->buckets[0]->termsfast->buckets;
					// $arraydb['aue'][$moduleKey][$indexKey][$fieldKey] = json_decode(getLogs($indexKey,$querry,true));//->aggregations->timeAggs->buckets[0]->termsfast->buckets;


					
					
					// $arraydb['!'][$moduleKey][$indexKey]=$filters[$moduleKey][$indexKey];//$filter;
					//записали фильтр в индекс
					
					// pg_query($connection, "UPDATE filters SET json='".$filter."' WHERE indexName='".$indexKey."'");
					
					// $arraydb['cookies']['filter'][$moduleKey][$indexKey][$fieldKey] = json_decode(getLogs($indexName,$querry))->aggregations->timeAggs->buckets[0]->termsfast->buckets;
					// $arraydb[$moduleKey][$indexName] = json_decode(getLogs($indexName,$querry))->aggregations->timeAggs->buckets[0]->termsfast->buckets;
				}
				// "{"significance":["1","2"],"object":["\u041a\u0440\u0435\u043c\u0430\u0442\u043e\u0440\u0438\u0439 ''\u0431\u0430\u0440\u0431\u0435\u043a\u044c\u044e''","\u0414\u0435\u0442\u0441\u043a\u0438\u0439 \u0441\u0430\u0434\u0438\u043a ''\u0432\u0438\u0448\u0435\u043d\u043a\u0430''","\u0410\u0434\u043c\u0438\u043d\u0438\u0441\u0442\u0440\u0430\u0446\u0438\u044f","\u043e\u0444\u0438\u0441 ARinteg"],"device":["\u041a\u043e\u0440\u0438\u0434\u043e\u0440","\u041f\u0435\u0447\u0438","\u0421\u0442\u043e\u043b\u043e\u0432\u0430\u044f","\u0411\u0443\u0445\u0433\u0430\u043b\u0442\u0435\u0440\u0438\u044f","\u041f\u0430\u0440\u0430\u0434\u043d\u044b\u0439 \u0432\u0445\u043e\u0434","\u0418\u0433\u0440\u043e\u0432\u0430\u044f \u043f\u043b\u043e\u0449\u0430\u0434\u043a\u0430","\u0412\u0435\u0449\u0435\u0432\u0430\u044f","\u041c\u043e\u0440\u043e\u0437\u0438\u043b\u044c\u043d\u0438\u043a","\u041a\u0443\u0445\u043d\u044f","\u0414\u0432\u0435\u0440\u044c \u0432 SAVA"],"ip_device":["192.168.1.12","192.168.1.20","192.168.1.32","192.168.1.30","192.168.1.29","192.168.1.15","192.168.1.22","192.168.1.21","192.168.1.13","192.168.3.111"],"event":["\u0417\u0430\u0440\u0435\u0433\u0438\u0441\u0442\u0440\u0438\u0440\u043e\u0432\u0430\u043d \u043f\u0440\u043e\u0445\u043e\u0434.","\u0414\u043e\u0441\u0442\u0443\u043f \u0437\u0430\u043f\u0440\u0435\u0449\u0435\u043d.","\u0414\u043e\u0441\u0442\u0443\u043f \u0437\u0430\u043f\u0440\u0435\u0449\u0435\u043d. \u041e\u0442\u0441\u0443\u0442\u0441\u0442\u0432\u0443\u0435\u0442 \u0440\u0430\u0437\u0440\u0435\u0448\u0435\u043d\u0438\u0435 \u043d\u0430 \u043f\u0440\u043e\u0445\u043e\u0434.","\u0417\u0430\u0440\u0435\u0433\u0438\u0441\u0442\u0440\u0438\u0440\u043e\u0432\u0430\u043d \u043f\u0440\u043e\u0445\u043e\u0434, \u0441\u0430\u043d\u043a\u0446\u0438\u043e\u043d\u0438\u0440\u043e\u0432\u0430\u043d\u043d\u044b\u0439 \u0441 \u043a\u043d\u043e\u043f\u043a\u0438."],"route":["\u0412\u044b\u0445\u043e\u0434","\u0412\u0445\u043e\u0434"],"person":["-","\u0412\u0438\u0442\u0430\u043b\u0438\u0439 \u041c\u0435\u0440\u0437\u043b\u044f\u043a\u043e\u0432","\u041c\u0430\u0442\u0432\u0435\u0439 \u0428\u0435\u0441\u0442\u0430\u043a\u043e\u0432\u0441\u043a\u0438\u0439","\u041e\u043b\u0435\u0433 \u0421\u0438\u043c\u043e\u043d\u043e\u0432","\u042d\u043b\u044c\u0434\u0430\u0440 \u0413\u043e\u043b\u0443\u0431\u0438\u043a\u0438\u043d","\u041a\u0441\u0435\u043d\u0438\u044f \u0412\u043e\u0440\u043e\u0431\u044c\u0435\u0432\u0430","\u041d\u0430\u0442\u0430\u043b\u044c\u044f \u041c\u043e\u0440\u043e\u0437\u043e\u0432\u0430","\u0412\u0438\u0442\u0430\u043b\u0438\u0439 \u0422\u0440\u0443\u0431\u043d\u043e\u0439","\u0420\u0438\u043d\u0430\u0442 \u0411\u043b\u0430\u0433\u043e\u0432\u0435\u0449\u0435\u043d\u0441\u043a\u0438\u0439","\u041d\u0438\u043a\u043e\u043b\u0430\u0439 \u0422\u0430\u0440\u0430\u0442\u044c\u0435\u0432"],"pass_number":["-","120.44417","109.31001","021.31544","024.12109","301.31412","210.21406","123.25200","047.20140","242.31844"]}"
				$filter=json_encode($filters[$moduleKey][$indexKey]);
				
				// $S = addcslashes($filter, "'");;
				$vowels = array("'");
				$S = str_replace($vowels, "''", "$filter");
				// $arraydb['sql'] = $onlyconsonants;

				// $querry=pg_fetch_assoc(pg_query($connection,"delete from filters where indexname='".$indexKey."'"));
				// $querry=pg_fetch_assoc(pg_query($connection,"INSERT INTO filters (json, indexName) VALUES ('".$S."','".$indexKey."')"));//"INSERT INTO filters (json, indexName) VALUES ('".$S."','".$indexKey."')"));
				
				pg_query($connection,"UPDATE filters SET json='".$S."' WHERE indexName='".$indexKey."'");
				// $arraydb['sql'][$indexKey.':)']=$filters[$moduleKey][$indexKey];//delete from filters whete indexname='loxpidr';
				// pg_query($connection,"INSERT INTO filters (json, indexName) VALUES ('".$filter."','".$indexKey."')");INSERT INTO filters (json, indexName) VALUES (':)','loxpidr');
				$modules->$moduleKey->indexes->$indexKey->filter = $filters[$moduleKey][$indexKey];//UPDATE filters SET json=':(' WHERE indexname='loxpidr';
				// $arraydb['aue'][$moduleKey][$indexKey] = pg_fetch_assoc($querry);
			}
			// $modules[$moduleKey]['lastViewed'] = pg_query($connection, "SELECT top FROM LastIndexLook WHERE login='".$_POST['auth']['login']."'")[0];
			// pg_close($connection);
		}
		// $arraydb['filters']=$filters;
		$arraydb['cookies']['modules'] = $modules;
		$arraydb['filters'] = $filters;
		// echo '!!!ddddddddddddddddd';
	//=======================================================LICENSE=======================================================
		$lines = file("/var/www/html/lic/licence.lic");
	
		$lic = $lines[count($lines)-2];
		
		$index = count($lines)-2;
		$key = "-----BEGIN PRIVATE KEY-----".PHP_EOL;
		for($i = 1; $i<$index; $i++)
			$key = $key.$lines[$i];
		$key = $key."-----END PRIVATE KEY-----".PHP_EOL;
	
		openssl_private_decrypt(base64_decode($lic), $result, $key);
		// echo $result;
		
		$times = explode("|", $result);    
		
		$today = time();    
		$day_last = ($times[1] - $today)/86400;                    
	

		// $arraydb['result'] = $result;	date('d.m.Y h:i:s'
		
		$licInfo = array();
		$licInfo['from'] = date('d-m-Y H:i:s', $times[0]);
		$licInfo['to'] = date('d-m-Y H:i:s', $times[1]);
		$licInfo['day_last'] = $day_last;
		$arraydb['cookies']['lic'] = 	$licInfo;
		
	//=======================================================LICENSE=======================================================
	
	//=======================================================NET_SETTINGS=======================================================
	$Iface = exec("route -n | awk 'NR==3 {print $8}'");
	
	$net_settings = array();
	$net_settings["ip"] = exec("ifconfig ".$Iface." | awk '{ print $2}' | grep -E -o \"([0-9]{1,3}[\.]){3}[0-9]{1,3}\"");
	// $mask = system("ifconfig enp2s0 | awk '/Mask:/{split($4,a,\":\"); print a[2]}'");
	$net_settings["mask"] = exec("ifconfig ".$Iface." | awk '/Mask:/{split($4,a,\":\"); print a[2]}'");
	//в 3 строке у нас шлюз по умолчанию
	$net_settings["gw"]  = exec("route -n | awk 'NR==3 {print $2}'");
	// $array["SERVER_ADDR"] = $_SERVER["SERVER_ADDR"];

	$arraydb['cookies']['net_settings'] = $net_settings;
	//=======================================================NET_SETTINGS=======================================================

	//=======================================================NTP_SETTINGS=======================================================
	$handle = fopen('/etc/ntp.conf', 'rb');
	$chunksize = 1*(1024*1024);
	
	// $arraydb["buffer"] = $handle;
	if ($handle === false) {
		// return false;
		$arraydb['cookies']["ntp_settings"] = false;
	}else{
		$ntp_servers = array();
		while (!feof($handle)) {
			$buffer = fread($handle, $chunksize);
			$string_array  = explode(PHP_EOL, $buffer);
			$ntp_serv_num=1;
			for ($i = 0;$i < count($string_array); $i++) {
				//нам нужны только строки содержащие "pool" и не нужна последняя дефолтная почта "pool ntp.ubuntu.com"
				if(strripos($string_array[$i], 'pool')!==false &&  $string_array[$i] !== 'pool ntp.ubuntu.com'){
					//вырезаем из файла название 
					$pushed = substr($string_array[$i], 5, -8); 
					$ntp_servers['NTP server '.$ntp_serv_num.':'] = $pushed;
					$ntp_serv_num++;
					// array_push($ntp_servers,$pushed);
				}
			}
		}
		$arraydb['cookies']["ntp_settings"] = $ntp_servers;
	}

	fclose($handle);
	//=======================================================NTP_SETTINGS=======================================================
		// делаем запрос на занимаемое место 
		exec("curl -XGET 'http://127.0.0.1:9200/_cat/indices?v' | awk '{print $3, $7, $8, $9}'",$diskInfo);
		$diskInfoResult = array();
		for ($i=1; $i < count($diskInfo); $i++) { 
			$exploded = explode(" ",$diskInfo[$i]);
			$diskInfoResult[$i - 1] = array(
				'Название таблицы elasticsearch' =>  $exploded[0],
				'Количество документов' =>  $exploded[1],
				'Документов удалено' =>  $exploded[2],
				'Занимаемый размер' =>  $exploded[3],
			);
		}
		$arraydb['diskInfo'] = $diskInfoResult;
		exec("df -h | awk 'NR==4 {print $4}'", $arraydb['dataAvail']);
	//=======================================================SMTP_SETTINGS=======================================================

	//=======================================================SMTP_SETTINGS=======================================================

	}
}

if($_POST['need']=='user'){
	$arraydb['usernames'] = getDB($conn,"SELECT id, login, rights, modules FROM users");
}

if($_POST['need']=='notification'){
	$arraydb['events'] = getDB($conn,"SELECT dashboard, low, mid, top FROM significance");
	
}

if(isset($_POST['delField']))
{
	$id = $_POST['delField']['id']['id'];
	$modules = $_POST['delField']['modules'];
	$login = pg_fetch_assoc(pg_query($conn,"SELECT login from users WHERE id='$id'"))['login'];//$_POST['delField']['login'];

	foreach($modules as $moduleKey => $index) {
		$connection = connectBD($moduleKey);
		pg_query($connection, "DELETE FROM LastIndexLook WHERE login = '$login'");
		pg_query($connection, "DELETE FROM dashboards WHERE master = '$login'");
	// 	pg_query($conn, "DELETE FROM LastIndexLook WHERE login = '$id'");
	// 	pg_query($connection,"INSERT INTO LastIndexLook (indexname,login,top,mid,low) VALUES ('$indexKey','$login', '$date', '$date', '$date')");
	// 	{
	// 		pg_query($connection,"INSERT INTO dashboards (id,name,type,master,json,indexname) VALUES ('".$k."','".$buffer['name']."', '".$buffer['type']."', '".$login."', '".$buffer['json']."','".$indexKey."')");
	// 	}
		pg_close($connection);
	}
	// теперь удаляем дашборды из sava_core
	$connection = connectBD('sava_core');
	pg_query($conn, "DELETE FROM dashboards WHERE master = '$login'");

	$result = pg_query($conn, "DELETE FROM users WHERE id = '$id'");
	$arraydb['post']=$_POST;
	if ($result) {
		$arraydb['result'] = 'done';
	}
	else{
		$arraydb['result'] = 'nope';
	}
}
// $arraydb['hash123']= hash('sha1' , '123');
// $arraydb['hash1']= hash('sha1' , '1');
if(isset($_POST['addField']))
{    

	$login = $_POST['_login'];
	$pass = $_POST['_password'];
	$rights = $_POST['_admin'];
	// $modules = $_POST['_modules'];//строка в json с перечнем доступных модулей
	$modules = $_POST['_modules'];//строка в json с перечнем доступных модулей
	$indexes = $_POST['_indexes'];
	// $arraydb['modules'] = $_POST['_modules'];
	$result = pg_query($conn, "SELECT * FROM users WHERE login = '".$login."'");
	if(pg_numrows($result)>0){
		$arraydb['result'] = 'nope';
	}
	else{
		//Раздраконили все дашборды
		$query = pg_query($conn, "SELECT * FROM allDashboards");
		$allModules = json_decode(pg_fetch_assoc($query)['json']);
		// $arraydb['allDashboards']
		$userModules;
		for ($i = 0;$i < count($modules) ; $i++) {
			$userModules->{$modules[$i]} = $allModules->{$modules[$i]};
			$connection = connectBD($modules[$i]);
			foreach($userModules->{$modules[$i]}->indexes as $indexKey => $index) {
				$date = date("Y/m/d H:i:s");
				//$json
				// $query = pg_query($connection, "SELECT top FROM LastIndexLook WHERE (login,indexName) = ('".$_POST['upload']['login']."','".$index."')");
				$querry = "SELECT * FROM LastIndexLook WHERE (login,indexName) = ('core','".$indexKey."')";
				$buffer = pg_fetch_assoc(pg_query($connection,$querry ));
				$json = $buffer['json'];
				$lastTime = $buffer["lasttime"];
				pg_query($connection,"INSERT INTO LastIndexLook (indexname, login, json, lastTime) VALUES ('$indexKey','$login', '$json','$lastTime')");
				//копируем стартовые дашборды по индексу
				for ($k = 0;$k < 4 ; $k++) {
					// $arraydb[':)'] = pg_fetch_assoc(pg_query($connection,"SELECT * FROM dashboards WHERE master='core'"));$arraydb[':)'][$k]
					$buffer = pg_fetch_assoc(pg_query($connection, "SELECT * FROM dashboards WHERE (master,id,indexname) = ('core','".$k."','".$indexKey."')"));
					if($buffer!=false){
						pg_query($connection,"INSERT INTO dashboards (id,name,type,master,json,indexname) VALUES ('".$k."','".$buffer['name']."', '".$buffer['type']."', '".$login."', '".$buffer['json']."','".$indexKey."')");
					}
					// $arraydb[':)'] = "INSERT INTO dashboards (id,name,type,master,json,indexname) VALUES ('".$k."','".$buffer['name']."', '".$buffer['type']."', '".$login."', '".$buffer['json']."','".$indexKey."')";
					
				}
				// $querry = pg_query($connection,"SELECT");
				//SELECT * FROM dashboards WHERE (master,indexname) = ('new','acs_castle_ep2_userlog');
				// SELECT * FROM dashboards WHERE (master,indexname) = ('1','acs_castle_ep2_event');
			}
			pg_close($connection);
			$arraydb['date'] = $date;
		}
		$modules=json_encode($userModules);
		$id=get_id($conn,'users');
		$coded_pass = hash('sha1' , $pass);
		pg_query($conn,"INSERT INTO users (id, login , pass, user_hash, rights, modules) VALUES ($id, '$login', '$coded_pass' ,'asdf', '$rights', '$modules')");
		$arraydb['result'] = 'done';
		
		
	}

	unset($_POST);


}



if(isset($_POST['changePass'])){
	$arraydb['post'] = $_POST; 
	$pass_query= pg_query($conn, "SELECT pass FROM users WHERE id=".$_POST['changePass']['id']."");
	$data =  pg_fetch_assoc($pass_query);
	

	$arraydb['result'] = ($data['pass'] == hash('sha1' , $_POST['changePass']['formData']['old_password']));
	if($arraydb['result']){
		$coded_pass = hash('sha1' ,$_POST['changePass']['formData']['password']);
		$pass_query = pg_query($conn,"UPDATE users SET pass='".$coded_pass."' WHERE id=".$_POST['changePass']['id']."");
	}

}

pg_close($conn);

echo json_encode($arraydb);

