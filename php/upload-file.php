<?php
 // иначе Internet Explorer будет игнорировать Content-Disposition 
if(ini_get('zlib.output_compression'))
  ini_set('zlib.output_compression', 'Off');
  
  if($_GET['resolution']=='pdf'){
    $filename='report.pdf';
    header("Content-Type: application/pdf");
  }else{
    $filename=$_GET['filename'].'.xlsx';
    
    // $filename='Отчет с 20.01.2020.xlsx';
    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
  }


// $ctype="application/pdf";
header("Pragma: public"); 
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false); // нужен для Explorer
header("Content-Disposition: attachment; filename=\"".basename($filename)."\";" );
header("Content-Transfer-Encoding: binary");
// нужно сделать подсчет размера файла его пути
header("Content-Length: ".filesize($filename));

// Переименование файла, чтобы потом можно было удалить
if($_GET['resolution'] == 'excel') {
  rename("./".$filename, "./old.xlsx");
  readfile("./old.xlsx");
}else{
  readfile("$filename");
}
// $arraydb['get'] = $_GET;
// echo json_encode($arraydb);
exit();
?>