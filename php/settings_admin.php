<?php
include 'db.php';
include 'socket.php';
$_POST = json_decode(file_get_contents('php://input'), true);


if(isset($_POST['giveMeData'])) {
    //enp2s0
	//узнаем Iface поле 
	$Iface = system("route -n | awk 'NR==3 {print $8}'");
	$arraydb["ip"] = system("ifconfig ".$Iface." | awk '{ print $2}' | grep -E -o \"([0-9]{1,3}[\.]){3}[0-9]{1,3}\"");
	// $mask = system("ifconfig enp2s0 | awk '/Mask:/{split($4,a,\":\"); print a[2]}'");
	$arraydb["mask"] = system("ifconfig ".$Iface." | awk '/Mask:/{split($4,a,\":\"); print a[2]}'");
	//в 3 строке у нас шлюз по умолчанию
	$arraydb["gw"]  = system("route -n | awk 'NR==3 {print $2}'");
	$arraydb["SERVER_ADDR"] = $_SERVER["SERVER_ADDR"];
	
}


//Изменение настройки NTP сервера	
// не забудь sudo chmod 777 .
if (isset($_POST['ntp'])) {
	$arraydb["post"] = $_POST;
	system('cat ../sh/ntp_header.txt>/etc/ntp.conf');
	//вывод на стандартный вывод	
	foreach($_POST['ntp'] as $moduleKey => $module) {
		system("echo 'pool ".$module." iburst '>>/etc/ntp.conf");
	}
	system('cat ../sh/ntp_footer.txt>>/etc/ntp.conf');
	exec('echo "Sava123!" | /usr/bin/sudo -S /var/www/sava/html/sh/ntp.sh');
}	
//Изменение настроек отправки уведомлений	
if (isset($_POST['smtp'])) {
	// $result = system("cat > /home/administrator/Desktop/SAVAv0.0.3/mail.txt <<TXT
	$result = system("cat > /var/NN_SAVA/mail.txt <<TXT
".$_POST['from']."
".$_POST['to']."");
// echo system("echo 'pool ".$_POST['ntp_server4']." iburst '>>/var/NN_SAVA/mail.txt");
	$arraydb['result'] = 'email_success';
	echo json_encode($arraydb);
}
//Изменение сетевых настроек
// не забудь прописать %www-data    ALL=(ALL) NOPASSWD: /sbin/ifconfig
// проверка sudo -u www-data sudo ifconfig ens32 192.168.3.35 netmask 255.255.255.0;
if (isset($_POST['network'])){
	$ip = $_POST['network']['ip'];
	$mask = $_POST['network']['mask'];
	$gw = $_POST['network']['gw'];
	// echo "sss";
	//if(isset($_POST['ntp_server1']))
	// $str = "sudo ifconfig ens32 ".$_POST['ip']." netmask ".$_POST['mask']."; sudo route add default gw ".$_POST['gw'];
	
	$Iface = exec("route -n | awk 'NR==3 {print $8}'");

	$str = "sudo ifconfig ".$Iface." ".$ip." netmask ".$mask."";//; sudo ip route change default via ".$gw;
	$arraydb['str'] = $str;
	// sudo ifconfig ens32 192.168.3.35 netmask 255.255.255.0; sudo ip route change default via 192.168.3.1
	//  ifconfig ens32 192.168.3.35 netmask 255.255.255.0; sudo ip route change default via 192.168.3.1

	$arraydb['aaa'] = system($str);


	
}
// SIOCSIFADDR: Operation not permitted

// SIOCSIFADDR: Operation not permitted
// SIOCSIFFLAGS: Operation not permitted
// SIOCSIFNETMASK: Operation not permitted


// $arraydb['files'] = ($_FILES);
// $arraydb['post'] = ($_POST);
// $arraydb['get'] = ($_GET);
// echo json_encode($arraydb);

//Добавление лицензии  не забудь в каталоге lic выполнить sudo chmod -R 777 .
if (isset($_GET['lic'])){ 
	$uploaddir = '/var/www/html/lic/';
	$uploadfile = $uploaddir.basename($_FILES['licFile']['name']);
	//Перемещает загруженный файл в новое место
	$res = is_uploaded_file($_FILES['licFile']['tmp_name']);
	$arraydb['files'] = ($_FILES);
	$arraydb['res'] = ($res);

	if (move_uploaded_file($_FILES['licFile']['tmp_name'], $uploadfile)) {
		$arraydb['result'] = "done";
	} else {
		$arraydb['result'] = "nope";
	}
	echo json_encode($arraydb);
}

if (isset($_GET['licСheck'])){
	$lines = file("../lic/licence.lic");
	
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
	$arraydb['from'] = date('d-m-Y H:i:s', $times[0]);
	$arraydb['to'] = date('d-m-Y H:i:s', $times[1]+$_GET['time']*86400);
	$arraydb['day_left'] = $day_last;
	
	echo json_encode($arraydb);
}

if ($_POST['need']=='notific'){
	$login = $_COOKIE['login'];
	
	$conn = connectBD('sava_core');

	// $query = pg_query($conn, "SELECT * FROM emailNotificationEvents");// WHERE login = '".$login."'
	// $arraydb['events'] =  pg_fetch_row($query);

	$result = pg_query($conn, "SELECT * FROM emailNotificationEvents");
	if (!$result) {
		echo "Waring!\n";
		exit;
	}
	$r = 0;
	while ($row = pg_fetch_assoc($result)) {
		$arrayid[$r] = ($row);
		$r++;
	}
	$arraydb['events'] = $arrayid;


	// $arraydb['events2'] = $query;
	$result = pg_query($conn, "SELECT * FROM emailAddressees");// WHERE login = '".$login."'
	if (!$result) {
		echo "Waring!\n";
		exit;
	}
	$r = 0;
	while ($row = pg_fetch_assoc($result)) {
		$arrayid2[$r] = ($row);
		$r++;
	}
	$arraydb['addresses'] = $arrayid2;
	$arraydb['smtp'] = pg_fetch_assoc(pg_query($conn, "SELECT * FROM smtpSettings"));
	// $arraydb['addressees'] =  pg_fetch_assoc($query);
	
	pg_close($conn);

}
if(isset($_POST['notific'])){
	$conn = connectBD('sava_core');

	// $arraydb['post'] = $_POST;
	if($_POST['notific']['purpose']=='changeFilter'){
		$arraydb['id'] = $id;
		$filter = $_POST['notific']['filter'];
		$usr = $_POST['notific']['login'];
		$id = $_POST['notific']['id'];
		
		$query = pg_query($conn, "UPDATE emailNotificationEvents SET filter='".$filter."' WHERE (login, id) = ('$usr',$id)");
		$arraydb['post'] = $_POST;
		$arraydb['str'] = "UPDATE emailNotificationEvents SET filter='".$filter."' WHERE (login, id) = ('$usr',$id)";
		// UPDATE emailNotificationEvents SET filter='{"tdn":["Предотвращение вторжений"]}' WHERE (login, id) = ('admin1',3)
	}
	if($_POST['notific']['purpose']=='addEvent'){
		$arraydb['post'] = $_POST;

		$name = $_POST['notific']['event']['name'];
		$moduleName = $_POST['notific']['event']['modulename'];
		$indexName = $_POST['notific']['event']['indexname'];
		$filter = $_POST['notific']['event']['filter'];
		if (count($filter)==0){
			$filter = json_encode(array());
		}
		$login = $_COOKIE['login'];
		$id = $_POST['notific']['event']['id'];
		$arraydb['name'] = $name;
		$arraydb['modulename'] = $moduleName;
		$arraydb['indexname'] = $indexName;
		$arraydb['filter'] = $filter;
		$arraydb['id'] = $id;

		// $query = pg_query($conn, "UPDATE emailNotificationEvents SET filter='".$filter."' WHERE (login, id) = ('$usr',$id)");
		// id, login, name , events
		$str="INSERT INTO emailNotificationEvents (id, login, name, moduleName, indexName, filter) VALUES ($id,'".$login."','".$name."', '".$moduleName."', '".$indexName."', '".$filter."')";
		$arraydb['str'] = $str;
		$arraydb['python'] = socketConnect($_POST['notific']['socketJSON']) ;
		$result = pg_query($conn, $str);
		
	}
	if($_POST['notific']['purpose']=='addAdress'){
		$login = $_COOKIE['login'];
		$arraydb['post'] = $_POST;
		$name = $_POST['notific']['adress']['name'];
		$theme = $_POST['notific']['adress']['theme'];
		$events = json_encode($_POST['notific']['adress']['events']);
		$id  = $_POST['notific']['adress']['id'];
		$str="INSERT INTO emailAddressees (id, login, name, events, theme) VALUES ($id,'".$login."','".$name."', '".$events."', '".$theme."')";
		$arraydb['str'] = $str;
		$result = pg_query($conn, $str);
		$arraydb['python'] = socketConnect($_POST['notific']['socketJSON']) ;
		$arraydb['result'] = pg_fetch_assoc($result);
	}
	
	if($_POST['notific']['purpose']=='dellAdress'){
		$id = $_POST['notific']['id'];
		$arraydb['post'] = $_POST;
		$str = "DELETE FROM emailAddressees WHERE id = $id";
		$result = pg_query($conn, $str);
		$arraydb['result'] = pg_fetch_assoc($result);
	}
	if($_POST['notific']['purpose']=='dellEvent'){
		$id = $_POST['notific']['id'];
		$arraydb['post'] = $_POST;
		


		$adresses = (getDB($conn, 'select * from emailAddressees'));; 
		$arraydb['adresses'] = $adresses;
		foreach ($adresses as $key => $value) {
			$array = json_decode($adresses[$key][4]);
			$adressId = $adresses[$key][0];
			//получение списка событий у адресата
			// for ($i=0; $i <count($array) ; $i++) { 
			// 	$array
			// }
			//  = $array;;
			$search = array_search($id,$array);
			if($search!==null&&$search!==false){
				unset($array[array_search($id,$array)]);
				sort($array);
				$json_array = json_encode($array);
				$str = "UPDATE emailAddressees set (events) = ('".$json_array."') where id=$adressId";
				pg_query($conn, $str);
				// $newAdress =  $adresses[$key];
				// $newAdress[4] = $json_array;
				// $arraydb[':('][$key] = $newAdress;
			}
			// else{
			// 	$arraydb[':('][$key] = $adresses[$key];
			// }
			$str = "DELETE FROM emailNotificationEvents WHERE id = $id";
			$result = pg_query($conn, $str);
			
			$arraydb['result'] = pg_fetch_assoc($result);//pg_fetch_assoc($result);
			
			// $arraydb[':)'][$key] = $search;//unset($adresses[array_search($id,$array)]);//array_search($id,$array);//unset($array[array_search($id,$adresses[$key][4])]);;
			// (getDB($conn, 'select UPDATE emailAddressees set (events) = ('.json_encode($array).') where id='$key'));; 
			
		}
	}
	if($_POST['notific']['purpose']=='changeAdress'){
		$id = $_POST['notific']['id'];
		$theme = $_POST['notific']['newTheme'];
		$events = $_POST['notific']['eventsList'];
		$arraydb['post'] = $_POST;
		
		$str = "UPDATE emailAddressees SET (events, theme) = ('".$events."','".$theme."') WHERE id = $id";
		// $str = "UPDATE emailAddressees SET events='".$events."' WHERE id = $id";
		$arraydb['str'] = $str;
		$result = pg_query($conn, $str);
		$arraydb['result'] = pg_fetch_assoc($result);
	}
	if($_POST['notific']['purpose']=='setSMTP') {
		$smtp = $_POST['notific']['event'];
		$arraydb['post'] = $_POST;
		$useAuth = $smtp['useAuth']?'t':'f';
		$arraydb['result'] = pg_fetch_assoc(pg_query($conn,"UPDATE smtpSettings SET (name ,port ,adress ,useAuth ,login, username ,pass) = ('".$smtp['name']."','".$smtp['port']."','".$smtp['adress']."','".$useAuth."','".$smtp['login']."','".$smtp['username']."','".$smtp['pass']."')"));
		// pg_query($conn, );
	}
	if($_POST['notific']['purpose']=='testSMTP') {
		$arraydb['post'] = $_POST['notific']['data'];
		$msg = $_POST['notific']['data'];
		// $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		// socket_connect($socket, 'localhost', 9310);

		// $len = strlen($msg);
		// //envio informacion a socket
		// $sendMsg = socket_send($socket, $msg, $len, MSG_DONTROUTE);
		// //now you can read from...
		// $python = trim(socket_read($socket, 100));
		// socket_close($socket);
		/////////////////////////////////////////////////////////////////////////////////

		// echo $python;
		$arraydb['python'] = socketConnect($msg) ;
		//echo json_encode($arraydb);
	}
	// "UPDATE smtpSettings SET (name ,port ,adress ,useAuth ,login, username ,pass) = ('SMTPserver.damain.com','27','wsee@domain.com','1','wsee@domain.com','admin1','123')
	pg_close($conn);
}
// $arraydb['post'] = $_POST;


echo json_encode($arraydb);