
<?php
class Enc {
	
	// Процесс изменения лицензии:
	// устанавливаемый срок лицензии лежит в $licenseTime
	// чтобы обновить лицензию нужно загрузить в интерфейсе файл private.txt который лежит в папке R:\Технический департамент\ИБ\SAVA NEW\пара
	// перед этим в машине нужно удалить старый файл лицензии license.lic в /var/www/html/lic и /var/www/html/LicenseGen  
 
 static public function get_keys() {
  
	// Процесс генерации ключа
	  $config = array(
		 "private_key_type"=>OPENSSL_KEYTYPE_RSA,
		 "private_key_bits"=>512
		 );
	
		$res = openssl_pkey_new($config);

	 $privKey = '';
	 openssl_pkey_export($res,$privKey);
	
	 $fpr = fopen("private.txt","w");
	 fwrite($fpr,$privKey);
	 fclose($fpr);
 
 $arr = array(
 "countryName" => "RU",
     "stateOrProvinceName" => "Moscow oblast",
   "localityName" => "Moscow",
     "organizationName" => "Arinteg",
     "organizationalUnitName" => "SIEM",
   "commonName" => "Arinteg123!",
     "emailAddress" => "secretP@rtF0rEm@1l"
 );
 $csr = openssl_csr_new($arr,$privKey);
 
 $cert = openssl_csr_sign($csr,NULL, $privKey,10);
 openssl_x509_export($cert,$str_cert);
 
 $public_key = openssl_pkey_get_public($str_cert);
 $public_key_details = openssl_pkey_get_details($public_key);
 
 $public_key_string = $public_key_details['key'];
 
 $fpr1 = fopen("public.txt","w");
 fwrite($fpr1,$public_key_string);
 fclose($fpr1);
 
 return array('private'=>$privKey,'public'=>$public_key_string);
 }


public function my_enc($str) {
 
 $path = "public.txt";
 $fpr = fopen($path,"r");
 $pub_key = fread($fpr,1024);
 fclose($fpr);
 
 openssl_public_encrypt($str,$result,$pub_key);
 
 return $result;
 }
 
 public function my_dec($str) {
  $path = "private.txt";
  $fpr = fopen($path,"r");
  $pr_key = fread($fpr,1024);
  fclose($fpr);
//   echo "</br></br></br></br>";
//   echo $pr_key;
  openssl_private_decrypt($str,$result,$pr_key);
 
  return $result;
 }
 
}
 
if (isset($_GET['time_gen'])){

	// $keys = Enc::get_keys();

	if($_FILES['licFile']['name']!='public.txt'){
		$new_file = $_FILES['licFile']['name'];

		// копирование файла
		if (copy($_FILES['licFile']['tmp_name'], $new_file)) {
			$arraydb['result']= "Файл загружен на сервер";
		} else {
			$arraydb['result']= "Ошибка при загрузке файла";
		}
	
	}else{
		// копирование файла
		if (copy($_FILES['licFile']['tmp_name'], 'public.txt')) {
			$arraydb['result']= "Файл загружен на сервер";
		} else {
			$arraydb['result']= "Ошибка при загрузке файла";
		}
	}



	
	$ob = new Enc();
	//Формирование дат
	$date = date_create('15-05-2020 00:00:00'); //версия arinteg
	//$date = date_create();  //версия нормального человека
	
	$licenseTime = 365;
	$arraydb['from'] = date('d-m-Y H:i:s', date_timestamp_get($date));
	$arraydb['to'] = date('d-m-Y H:i:s', date_timestamp_get($date)+$_GET['time']*86400);
	$day_last = (date_timestamp_get($date) - date_timestamp_get($date)+$_GET['time']*86400)/86400;                    
	$arraydb['day_last'] = $day_last;

	$str = date_timestamp_get($date)."|".(date_timestamp_get($date)+$licenseTime*86400)."|"."ALL";
	//echo "<h2>".$str."</h2>";
	 
	$cipher = $ob->my_enc($str);
	$arraydb['шифр']= base64_encode($cipher);
	
	$path = "private.txt";
	$fpr = fopen($path,"r");
	$pr_key = fread($fpr,1024);
	fclose($fpr);
	
	
	$fpr1 = fopen("licence.lic","w");
	$pr_key = substr($pr_key, 27, -26);
	
	$pr_key ="-----BEGIN LICENCE KEY-----".$pr_key.base64_encode($cipher).PHP_EOL."-----END LICENCE KEY-----".PHP_EOL;
	// print_r($pr_key);
	fwrite($fpr1, $pr_key);
	fclose($fpr1);
	
	$lines = file("licence.lic");
	
	$lic = $lines[count($lines)-2];
	
	// $index = count($lines)-2;
	// $key = "-----BEGIN PRIVATE KEY-----".PHP_EOL;
	// for($i = 1; $i<$index; $i++)
	// 	$key = $key.$lines[$i];
	// $key = $key."-----END PRIVATE KEY-----".PHP_EOL;
	// $fpr1 = fopen("private.txt","w");
	// fwrite($fpr1, $key);
	
	// fclose($fpr1);
	
	// echo copy('/var/www/html/LicenceGen/licence.lic', '/var/www/html/lic/licence.lic');
	
	$arraydb['lic']=  $lic;
	
	$str_d = $ob->my_dec(base64_decode($lic));
	if($str_d!=null){
		$result = copy('/var/www/html/php/LicenceGen/licence.lic', '/var/www/html/lic/licence.lic');
		$arraydb['result']='done';
	}
	else $arraydb['result']='nope';

// /////////////////////////////////////////////////////////////////////////
// 	// $lines = file("/var/www/html/php/LicenceGen/licence.lic");
	
// 	// // $lic = $lines[count($lines)-2];
	
	// $index = count($lines)-2;
	// $key = "-----BEGIN PRIVATE KEY-----".PHP_EOL;
	// for($i = 1; $i<$index; $i++)
	// 	$key = $key.$lines[$i];
	// $key = $key."-----END PRIVATE KEY-----".PHP_EOL;
	// openssl_private_decrypt(base64_decode($lic), $result, $key);
	// $times = explode("|", $result);    
	
	// $today = time();    
	// $day_last = ($times[1] - $today)/86400;                    
	// // // $arraydb['result'] = $result;	date('d.m.Y h:i:s'
	
	// $licInfo = array();
	// $licInfo['from'] = (date('d-m-Y H:i:s', $times[0]));
	// $licInfo['to'] = (date('d-m-Y H:i:s', $times[1]));
	// $licInfo['day_last'] = $day_last;
	// $arraydb['cookies']['lic'] = 	$licInfo;



	unlink($new_file);
	echo json_encode($arraydb);
}

// if (isset($_GET['time_gen']))
// {
// 	$keys = Enc::get_keys();

// 	$ob = new Enc();
// 	$arraydb['files']= $_FILES;

//   $filename='private.txt';

// // // указание директории и имени нового файла на сервере
// // $new_file = $_FILES['licFile']['name'];

// // // копирование файла
// // if (copy($_FILES['licFile']['tmp_name'], $new_file)) {
// // 	$arraydb['result']= "Файл загружен на сервер";
// // } else {
// // 	$arraydb['result']= "Ошибка при загрузке файла";
// // }
// //   fwrite($fpr1, $pr_key);
// // $ctype="application/pdf";

 
// 	//Формирование дат
// 	$date = date_create(); 
// 	//$date = date_create('13-02-2013');
// 	// $date = date_create(); 
// 	$arraydb['from'] = date('d-m-Y H:i:s', date_timestamp_get($date));
// 	// $arraydb['2'] = time(date_timestamp_get($date)+$_GET['time']*86400);
// 	$arraydb['to'] = date('d-m-Y H:i:s', date_timestamp_get($date)+$_GET['time']*86400);
// 	$day_last = (date_timestamp_get($date) - date_timestamp_get($date)+$_GET['time']*86400)/86400;                    
// 	$arraydb['day_last'] = $day_last;
// 	$str = date_timestamp_get($date)."|".(date_timestamp_get($date)+$_GET['time']*86400)."|"."ALL";
	
// // 	$arraydb['get'] = $str;
	
	
// // 	// считывание публ ключа
// // 	$cipher = $ob->my_enc($str);
	
// 	$path = $new_file;
// 	$fpr = fopen($path,"r");
// 	$pr_key = fread($fpr,1024);
// 	fclose($fpr);
// 	$arraydb['$'] = $pr_key;
	
// 	$fpr1 = fopen("licence.lic","w");
// 	$pr_key = substr($pr_key, 27, -26);
	
// 	$pr_key ="-----BEGIN LICENCE KEY-----".$pr_key.base64_encode($cipher).PHP_EOL."-----END LICENCE KEY-----".PHP_EOL;
// 	// print_r($pr_key);
// 	fwrite($fpr1, $pr_key);
// 	fclose($fpr1);
	
// 	$lines = file("licence.lic");
	
// 	$lic = $lines[count($lines)-2];
	
// 	$index = count($lines)-2;
// 	$key = "-----BEGIN PRIVATE KEY-----".PHP_EOL;
// 	for($i = 1; $i<$index; $i++)
// 		$key = $key.$lines[$i];
// 	$key = $key."-----END PRIVATE KEY-----".PHP_EOL;
// 	$fpr1 = fopen($new_file,"w");
// 	fwrite($fpr1, $key);


// 	fclose($fpr1);

// // //
// 	$lines = file("/var/www/html/php/LicenceGen/licence.lic");
	
// 	// $lic = $lines[count($lines)-2];
	
// 	$index = count($lines)-2;
// 	$key = "-----BEGIN PRIVATE KEY-----".PHP_EOL;
// 	for($i = 1; $i<$index; $i++)
// 		$key = $key.$lines[$i];
// 	$key = $key."-----END PRIVATE KEY-----".PHP_EOL;

// 	openssl_private_decrypt(base64_decode($lic), $result, $key);
// 	$arraydb['ссс'] =  $result;
	
// 	$times = explode("|", $result);    
	
// 	$today = time();    
// 	$day_last = ($times[1] - $today)/86400;                    
// 	// // $arraydb['result'] = $result;	date('d.m.Y h:i:s'
	
// 	$licInfo = array();
// 	$licInfo['from'] = (date('d-m-Y H:i:s', $times[0]));
// 	$licInfo['to'] = (date('d-m-Y H:i:s', $times[1]));
// 	$licInfo['day_last'] = $day_last;
// 	$arraydb['cookies']['lic'] = 	$licInfo;
// // //	
// // 	// unlink('/var/www/html/lic/licence.lic');
// // 	//удаление остатков
// 	// unlink('private.txt');
// 	// unlink('licence.lic');
	
// // 	if($licInfo['from']){
// // 		$result = copy('/var/www/html/php/LicenceGen/licence.lic', '/var/www/html/lic/licence.lic');
// // 		$arraydb['result']='done';
// // 	}
// // 	else $arraydb['result']='nope';
// // 	// echo "</br></br></br></br>";
// // 	// $arraydb['lic'] = $lic;
	
// // 	$str_d = $ob->my_dec(base64_decode($lic));

	
// 	echo json_encode($arraydb);

// }



