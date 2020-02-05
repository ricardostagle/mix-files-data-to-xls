
<?php
/*
 *  Save the html from this URL
 *  Same day
 *  http://sentencias.tfjfa.gob.mx:8082/SICSEJL/faces/content/public/BoletinJurisdiccional.xhtml?fbclid=IwAR1-DULB5RE23oFs-up02AtwVzPUS9mFHbo8x39y9iYKZiZXnUAkBn4FalQ
 *  
 */

  if (!isset($_FILES["file"])) {

?>
<html>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15">
  <body>
    <form action="" method="POST" enctype="multipart/form-data">
      <input type="text" name="searchtext" />
      <input type="file" name="file" />
      <input type="file" name="filereport" />
      <input type="submit"/>
    </form>
   </body>
</html>

<?php
  exit;
}

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
    $errors[]="Extension not allowed, please choose a JPEG or PNG file.";
  }
      
  if($file_size > 100000000 || $file_size_r > 100000000){
    $errors[]='File size must be excately 100 MB';
  }

  $path = "uploads/";
  $path = $path . basename($file_name);
  $path_r = $path . basename($file_name_r);

  echo $path_r;

  /*if(!empty(basename($file_name)) && basename($file_name) == "uploads/"){
    unlink($path);
  }*/
      
  if(empty($errors)==true){
    if(move_uploaded_file($file_tmp, $path) || move_uploaded_file($file_tmp_r, $path_r)) {
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

try{ 


} catch(Exception $e) {
  echo $e->getMessage();
}


function run_request($path, $search, $path_r){
  //error_reporting(E_ERROR | E_PARSE);
  //error_reporting(0);
  include 'reader.php';
  $excel = new Spreadsheet_Excel_Reader();
  //require_once(dirname(__FILE__)."/xlsxwriter.class.php");
  //echo "<br>\n".PHP_EOL;
  //echo 'Abriendo archivo ' . $path .".<br>\n".PHP_EOL;
  if (($gestor = fopen($path, "r")) === FALSE) {
    exit;
  }

  if (($gestor = fopen($path, "r")) !== FALSE) {
    //echo "Abro archivo...".PHP_EOL;
    $page = file_get_contents($path, FILE_USE_INCLUDE_PATH);

    fclose($gestor);
    $csv = array();

    //Remove all before this string
    $string_before='<input type="hidden" name="frmTablas" value="frmTablas">';
    $page = strstr($page, $string_before);
    $string_after='<button id="frmTablas:j_idt43" name="frmTablas:j_idt43"';
    $str = explode($string_after, $page);

    $filename = str_replace('uploads/','', $path);
    $fileNameCmps = explode(".", $filename);
    $fileExtension = strtolower(end($fileNameCmps));
    $filename = str_replace('.'.$fileExtension,'', $filename);
    $page = utf8_decode($str[0]);
    
    $page = preg_replace('#<span class="ui-column-title">(.*?)</span>#', '', $page);
    $page = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i",'<$1$2>', $page);
    $page = preg_replace( "/\r|\n/", "", $page);
    $page = preg_replace('#</?a[^>]*>#is', '',$page);
   
    $thead_empty = '<thead><tr><th></th><th></th><th></th><th></th><th></th><th></th><th></th></tr></thead>';
    $thead = utf8_decode('<thead><tr><th><span>No</span></th><th><span>No. Expediente</span></th><th><span>Parte Actora</span></th><th><span>Parte Demandada</span></th><th><span>Parte Notificada</span></th><th><span>Fecha de la actuación</span></th><th><span>Síntesis</span></th><th><span>Síntesis</span></th></tr></thead>');
    $page = str_replace($thead_empty, $thead, $page);
    $page = str_replace('<div>','', $page);
    $page = str_replace('</div>','', $page);
    $page = str_replace('<input><img>','', $page);
    $page = str_replace('<!-- Aquí comienza el botón de exportar datos de la búsqueda general-->','', $page);
    $page = str_replace('<!-- Aqu&iacute; comienza el bot&oacute;n de exportar datos de la b&uacute;squeda general-->','', $page);
    //echo $page;
    //exit;
    $page = str_replace('  ','', $page);
    $page = str_replace('</label>', '</label><br>', $page);
    $page = str_replace('</table>', '</table><br><br>||||||||', $page);
    $data = explode('||||||||', $page);
    $page = str_replace('||||||||', '', $page);

    $research = '';
    //add_column($xls, $gestor_r);

    /*
    echo '<table>';
    $excel->read($path_r);    
    $x=1;
    $rows = $excel->sheets[0]['numRows'];
    for($x=1; $x<= 10; $x++) {
      echo "\t<tr>\n";
      $y=1;
      while($y<=$excel->sheets[0]['numCols']) {
        $cell = isset($excel->sheets[0]['cells'][$x][$y]) ? $excel->sheets[0]['cells'][$x][$y] : '';
        echo "\t\t<td>$cell</td>\n";  
        $y++;
      }  
      echo "\t</tr>\n";
      $x++;
    }
      
    echo '</table><br/>';
    */

    
    //$data = array();
    foreach ( $data as $line ) {
      $line = str_replace('||||||||', '', $line);
      if(strpos($line,$search) !== false && $search != 'empty_search'){
        $html = str_replace('</th></tr></thead>', '</th></tr></thead>||||', $line);
        $html = str_replace('</td></tr>', '</td></tr>||||', $html);
        $trs = explode('||||', $html);
        $line = str_replace('||||', '', $line);
        
        preg_match_all('#<tr[^>]*>(.*?)</tr>#is', $line, $lines);
        $result = array();

        foreach ($lines[1] as $k => $line) {
          preg_match_all('#<td[^>]*>(.*?)</td>#is', $line, $cell);
          foreach ($cell[1] as $cell) {
            $result[$k][] = trim($cell);
          }
        }
        $excel->read($path_r);
        foreach ($trs as $tr) {
          foreach ($result as $k => $value) {
            if(isset($tr) && strpos($tr,'<td>'.$value[0].'</td>') !== false && substr($tr, -10) == '</td></tr>' && startsWith($tr, '<label>') == false){
              $rows = $excel->sheets[0]['numRows'];
              $htmlid = $value[0];
              for($x=1; $x<=$rows; $x++) {
                $fileid = $excel->sheets[0]['cells'][$x][1];
                //echo $htmlid.'/'.$fileid.'<br>'.'<br>'.PHP_EOL;
                $new_sintesis = $excel->sheets[0]['cells'][$x][7];
                //echo $new_sintesis.'<br>'.'<br>'.PHP_EOL;
                $tr_new = str_replace('</td></tr>', '</td><td>'.$new_sintesis.'</td></tr>', $tr);
                $line = str_replace($tr, $tr_new, $line);
              }
            }
          }
        
          if(isset($tr) && strpos($tr,$search) === false && substr($tr, -10) == '</td></tr>' && startsWith($tr, '<label>') == false){
            $line = str_replace($tr, '', $line);
          }
        }
        $research += $line;
        echo $line;
      }
      if($search == 'empty_search'){
        $research += $line;
        echo $line;
      }
    }
    
    $page = $research;
    //csv_to_excel($page, $filename, $search);
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

function add_column($tr, $gestor_r){
  while (($datos = fgetcsv($gestor_r, 100, ",")) !== FALSE) {
    $number = count($datos);
    for ($c=0; $c < $number; $c++) {
      echo $datos[0] .'<br>'.PHP_EOL;
      if($datos[0] == ''){
        $html = str_replace('</td></tr>', '</td><td>'.$datos[3].'</td></tr>', $table );
        return $html;
      }
    }
  }
}

?>