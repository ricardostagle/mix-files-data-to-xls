
<?php
/*
 *  Save the html from this URL
 *  Same day
 *  http://sentencias.tfjfa.gob.mx:8082/SICSEJL/faces/content/public/BoletinJurisdiccional.xhtml?fbclid=IwAR1-DULB5RE23oFs-up02AtwVzPUS9mFHbo8x39y9iYKZiZXnUAkBn4FalQ
 *  
 */

if (!isset($_FILES['file']) && !isset($_FILES['filereport'])) {
  // Print html if there are no files data from web form submit
?>
<html>

<head>
  <title>Generador de nuevo reporte</title>
  <meta charset="UTF-8">
  <meta http-equiv="Content-Type" content="text/html;" charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css" />
  <script src="http://code.jquery.com/jquery-1.11.1.min.js"></script>
  <script src="http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>
</head>
  <body style="position: relative; margin:0 auto; width:80%">
    <h1>Generador de nuevo reporte.</h1>
    <form action="" method="POST" enctype="multipart/form-data">
      <label for="filtrado">Filtrado por texto:</label><br>
      <input type="text" name="searchtext" value="Escribe tu busqueda..." /><br><br>
      <label for="files">Selecciona tu archivo html:</label><br>
      <input type="file" name="file" class="hidden" /><br><br>
      <label for="files">Selecciona tu archivo xls:</label><br>
      <input type="file" name="filereport" value="Selecciona tu archivo xls" /><br><br>
      <input type="submit" value="Enviar"/>
    </form>
   </body>
</html>

<?php
  exit;
}

// Receive variables from web form 

try{ 
  if ($_FILES["file"]["error"] > 0){
    echo "Error: " . $_FILES["file"]["error"];
    exit;
  }
  if ($_FILES["filereport"]["error"] > 0){
    echo "Error: " . $_FILES["filereport"]["error"];
    exit;
  }

  if(isset($_FILES['file']) && isset($_FILES['filereport'])){
    $errors= array();
    $file_name = $_FILES['file']['name'];
    $file_size =$_FILES['file']['size'];
    $file_tmp =$_FILES['file']['tmp_name'];
    $file_type=$_FILES['file']['type'];

    $file_name_r = $_FILES['filereport']['name'];
    $file_size_r =$_FILES['filereport']['size'];
    $file_tmp_r =$_FILES['filereport']['tmp_name'];
    $file_type_r=$_FILES['filereport']['type'];


    if(isset($_POST['searchtext'])){
      $search = $_POST['searchtext'];
    }
    if($search == ''){
      $search = 'empty_search';
    }

    $fileNameCmps = explode(".", $file_name);
    $fileExtension = strtolower(end($fileNameCmps));

    $fileNameCmpsR = explode(".", $file_name_r);
    $fileExtensionR = strtolower(end($fileNameCmpsR));
        
    $extensions= array("html", "htm", "xls");
        
    if(in_array($fileExtension,$extensions)=== false || in_array($fileExtensionR,$extensions)=== false){
      $errors[]="Extension not allowed, please choose a html, htm or xls file.";
    }
        
    if($file_size > 100000000 || $file_size_r > 100000000){
      $errors[]='File size must be excately 100 MB';
    }

    $path0 = "uploads/";
    $path = $path0 . basename($file_name);
    $path_r = $path0 . basename($file_name_r);

    /*if(!empty(basename($file_name)) && basename($file_name) == "uploads/"){
      unlink($path);
    }*/
        
    if(empty($errors)==true){
      
      if(move_uploaded_file($file_tmp, $path) && move_uploaded_file($file_tmp_r, $path_r)) {
        //echo "<br>El archivo '". $file_name ."' ha sido cargado.<br>".PHP_EOL;
        if(in_array($fileExtension,$extensions) !== false){
          //echo "<br>Comienza a ejecutar php...".PHP_EOL;
          run_request($path, $search, $path_r ); 
        }else{
          echo "<br>La extencion del archivo debe de ser html.<br>".PHP_EOL;
        }
      } else{
        echo "<br>Hubo un error cargando el archivo, por favor vuelva a intentar.<br>\n".PHP_EOL;
      }
    }else{
      print_r($errors);
    }
  }

} catch(Exception $e) {
  echo $e->getMessage();
}


function run_request($path, $search, $path_r){
  error_reporting(0);
  include 'reader.php';
  $excel = new Spreadsheet_Excel_Reader();
  //echo 'Abriendo archivo ' . $path .".<br>\n".PHP_EOL;
  if (($gestor = fopen($path, "r")) === FALSE) {
    exit;
  }

  if (($gestor_r = fopen($path_r, "r")) === FALSE) {
    exit;
  }

  if (($gestor = fopen($path, "r")) !== FALSE && ($gestor_r = fopen($path_r, "r")) !== FALSE) {
    //echo "Abro archivo...".PHP_EOL;

    //Read html
    $page = file_get_contents($path, FILE_USE_INCLUDE_PATH);
    fclose($gestor);

    // Removing before and after html tables in html string
    $string_before='<input type="hidden" name="frmTablas" value="frmTablas">';
    $page = strstr($page, $string_before);
    $string_after='<button id="frmTablas:j_idt43" name="frmTablas:j_idt43"';
    $str = explode($string_after, $page);

    // Removing before and after html tables
    $filename = str_replace('uploads/','', $path);
    $fileNameCmps = explode(".", $filename);
    $fileExtension = strtolower(end($fileNameCmps));
    $filename = str_replace('.'.$fileExtension,'', $filename);
    $page = utf8_decode($str[0]);
    
    //Regex regular expressions to clean the main string
    $page = preg_replace('#<span class="ui-column-title">(.*?)</span>#', '', $page);
    $page = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i",'<$1$2>', $page);
    $page = preg_replace( "/\r|\n/", "", $page);
    $page = preg_replace('#</?a[^>]*>#is', '',$page);

    //Remove and replace some strings
    $thead_empty = '<thead><tr><th></th><th></th><th></th><th></th><th></th><th></th><th></th></tr></thead>';
    $thead = utf8_decode('<thead><tr><th><span>No</span></th><th><span>No. Expediente</span></th><th><span>Parte Actora</span></th><th><span>Parte Demandada</span></th><th><span>Parte Notificada</span></th><th><span>Fecha de la actuación</span></th><th><span>Síntesis</span></th><th><span>Síntesis</span></th></tr></thead>');
    $page = str_replace($thead_empty, $thead, $page);
    $page = str_replace('<div>','', $page);
    $page = str_replace('</div>','', $page);
    $page = str_replace('<input><img>','', $page);
    $page = str_replace('....Seguir leyendo', '', $page);
    $page = str_replace('<!-- Aquí comienza el botón de exportar datos de la búsqueda general-->','', $page);
    $page = str_replace('<!-- Aqu&iacute; comienza el bot&oacute;n de exportar datos de la b&uacute;squeda general-->','', $page);
    $page = str_replace('  ','', $page);
    $page = str_replace('</label>', '</label><br>', $page);
    $page = str_replace('</table>', '</table><br><br>||||||||', $page);
    $data = explode('||||||||', $page);
    $page = str_replace('||||||||', '', $page);

    $research = '';

    //Read xls file
    $excel->read($path_r);
    $toObject = new stdClass;
    $rows = $excel->sheets[0]['numRows'];
    for($x=1; $x<=$rows; $x++) {
      $fileid = $excel->sheets[0]['cells'][$x][1];
      $new_sintesis = $excel->sheets[0]['cells'][$x][7];
      $toObject->$fileid = array('sintesis'=>$new_sintesis);
    }
    $obj_array = (array)$toObject;

    //Parser string by tables
    foreach ( $data as $line ) {
      //Parser string by trs
      $line = str_replace('||||||||', '', $line);
      $line = str_replace('</th></tr></thead>', '</th></tr></thead>||||', $line);
      $line = str_replace('</td></tr>', '</td><td><span>R</span></td></tr>||||', $line);
      $trs = explode('||||', $line);
      $line = str_replace('||||', '', $line);

      foreach ($trs as $tr) {
        $tr_new = '';
        //Find every row by first column id, get data from the object and create new row
        if (preg_match('#<span[^<>]*>([\d,]+).*?</span>#', $tr, $matches)) {
          $sintesis = $toObject->{$matches[1]}['sintesis'];
          $tr_new= '<span>'.$sintesis.'</span>';
          if(strpos($tr,'<span>'.$matches[1].'</span>') !== false && startsWith($tr, '<label>') == false){
            $tr_new = str_replace('<span>R</span>', '<span>'.$sintesis.'</span>', $tr);
            $line = str_replace($tr, $tr_new, $line);
          }
          $tr = $tr_new;
        }
        if(isset($tr) && strpos(strtoupper ($tr),strtoupper ($search)) === false && substr($tr, -10) == '</td></tr>' && startsWith($tr, '<label>') == false){
          //Remove not wanted row when filter text
          $line = str_replace($tr, '', $line);
        }
        if(strpos($tr,$search) !== false && $search != 'empty_search'){
          //echo for deg research
          //echo $tr.'<br>';
        }
      }
      if(strpos($line,$search) !== false && $search != 'empty_search'){
        // Create new string for send info to xls 
        $research += $line;
        echo $line;
      }
      if($search == 'empty_search'){
        $research += $line;
        echo $line;
      }
    }
    
    //Sending new string to export xls file
    $page = $research;
    csv_to_excel($page, $filename, $search);
    //echo "Actualizacion terminada.<br>\n".PHP_EOL;
  }
}

function csv_to_excel($data_excel, $filename){
  
  header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
  header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
  header ("Cache-Control: no-cache, must-revalidate");
  header ("Pragma: no-cache");
  header ("Content-Disposition: attachment; filename=\"" . basename($filename) . ".xls\"" );
  header ("Content-Description: PHP/INTERBASE Generated Data" );
  header("Content-Type: application/xls");    
  header("Expires: 0");
  
  echo $data_excel;
  exit;
}

function startsWith($haystack, $needle) {
  return substr_compare($haystack, $needle, 0, strlen($needle)) === 0;
}
function endsWith($haystack, $needle) {
  return substr_compare($haystack, $needle, -strlen($needle)) === 0;
}

?>