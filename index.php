
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
  <!-- // comment some lines to unable styles
  <link rel="stylesheet" href="http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css" />
  <script src="http://code.jquery.com/jquery-1.11.1.min.js"></script>
  <script src="http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>
  -->
</head>
  <body style="position: relative; margin:0 auto; width:80%">
    <h1>Generador de nuevo reporte.</h1>
    <form action="" method="POST" enctype="multipart/form-data">
      <label for="filtrado">Filtrado por texto:</label><br>
      <input type="text" name="searchtext"/><br><br>
      <label for="files">Selecciona tu archivo html:</label><br>
      <input type="file" name="file" /><br><br>
      <label for="files">Selecciona tu archivo xls:</label><br>
      <input type="file" name="filereport"/><br><br>
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
  //error_reporting(0);
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

      //Create a new id for the object coming from the excel
      $new_id = $excel->sheets[0]['cells'][$x][2];
      $new_id = $new_id.$excel->sheets[0]['cells'][$x][3];
      $new_id = $new_id.$excel->sheets[0]['cells'][$x][4];
      $new_id = $new_id.$excel->sheets[0]['cells'][$x][5];
      $new_id = strtoupper($new_id.$excel->sheets[0]['cells'][$x][6]);
      $new_id = trim(preg_replace('/\s+/', '', $new_id));
      $new_id = str_replace(',', '', $new_id);
      $new_id = str_replace('"', '', $new_id);
      $new_id = str_replace('/', '', $new_id);
      $new_id = str_replace('-', '', $new_id);
      $new_id = str_replace('.', '', $new_id);
      $toObject->$new_id = array('sintesis'=>$new_sintesis);
    }
    //Object to array if needed
    $obj_array = (array)$toObject;
    
    //Parser string by tables
    foreach ( $data as $line ) {
      //Parser string by trs
      $line = str_replace('||||||||', '', $line);
      $line = str_replace('</th></tr></thead>', '</th></tr></thead>||||', $line);
      $line = str_replace('</td></tr>', '</td><td><span>R</span></td></tr>||||', $line);
      $trs = explode('||||', $line);
      $line = str_replace('||||', '', $line);

      $tr_upper = '';
      $search_upper = strtoupper($search);
      $filltered_rows = '';

      $i = 0;
      foreach ($trs as $tr) {
        $sintesis = '';
        $tr = str_replace('</span><span>', '</span></td><td><span>', $tr);
        if($tr == (string) "<span>R</span>"){
          $line = str_replace($tr, "", $line);
        }

        //Find every row by first column id, get data from the object and create new row
        //Create a new id for the object coming from the html
        if (preg_match_all("/<span>.*?<\/span>/is", $tr, $matches)) {
          $matches0 = isset($matches[0][0]) ? $matches[0][0] : null;
          $matches1 = isset($matches[0][1]) ? $matches[0][1] : null;
          $matches2 = isset($matches[0][2]) ? $matches[0][2] : null;
          $matches3 = isset($matches[0][3]) ? $matches[0][3] : null;
          $matches4 = isset($matches[0][4]) ? $matches[0][4] : null;
          $matches5 = isset($matches[0][5]) ? $matches[0][5] : null;
          $matches6 = isset($matches[0][6]) ? $matches[0][6] : null;
          $string_compare = $matches1.$matches2.$matches3.$matches4.$matches5;

          if($matches0 != "<span>No</span>" || $string_compare != "<span>R</span>"){
            $i++;
            $string_compare = strtoupper(preg_replace("#</?span[^>]*>#is", "", $string_compare));
            $string_compare = trim(preg_replace('/\s+/', "", $string_compare));
            $string_compare = str_replace(",", "", $string_compare);
            $string_compare = str_replace('"', "", $string_compare);
            $string_compare = str_replace("/", "", $string_compare);
            $string_compare = str_replace("-", "", $string_compare);
            $string_compare = str_replace(".", "", $string_compare);

            if(isset($toObject->{$string_compare})){
              $sintesis = $toObject->{$string_compare}['sintesis'];
            }
          }
          if(startsWith($tr, '<label>') == false
              //&& strpos((string) $sintesis, (string) $matches6) !== false
              //if( (int)$matches0 > 4520 && (int)$matches0 < 4557){
            ){

            $tr_format = str_replace("<span>R</span>", "<span>".$sintesis."</span>", $tr);

            if(strpos($line,$tr_format) === false  && $matches0 !== (string) "<span>R</span>"){
              if(strpos($line,"<span>4556</span>") !== false){
                //echo $matches0.' //// '.$tr."<br>\n".PHP_EOL;
              }
              $line = str_replace($tr, $tr_format, $line);
            }

            $tr_upper = strtoupper($tr_format);
            if(strpos($tr_upper,$search_upper) === false 
             && $search != 'empty_search'
             && substr($tr, -10) == '</td></tr>' 
             && startsWith($tr, '<label>') == false){
              $line = str_replace($tr_format, "", $line);
            }
          }
        }
      }
      if($i==0){
        $line = '';
      }
      if((strpos(strtoupper($line),$search_upper) !== false 
        && $search != 'empty_search')
        || $search == 'empty_search'
      ){
        $research .= $line;
      }
    }
    //Sending new string to export xls file
    csv_to_excel($research, $filename, $search);
    //echo "Actualizacion terminada.<br>\n".PHP_EOL;
  }
}

function csv_to_excel($data_excel, $filename){

  //header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
  header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
  header ("Pragma: public");
  header ("Expires: 0");
  
  header ("Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0");
  header ("Cache-Control: private",false);
  //header ("Pragma: no-cache");

  header ("Content-Type: application/force-download");
  header ("Content-Disposition: attachment; filename=\"" . basename($filename) . ".xls\"" );
  header ("Content-Description: PHP/INTERBASE Generated Data" );
  header ("Content-Type: application/xls; charset=utf-8");
  header ("Content-Transfer-Encoding: UTF-8");
  
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