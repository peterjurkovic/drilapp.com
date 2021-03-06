<?php
header('Content-type: application/json');
	
  define("ACTION_CHECK", 1);
  define("ACTION_UPDATE", 2);
  define("LANG_EN", 1);
  define("LANG_DE", 2); 

ini_set("display_errors", 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__).'/admin/logs/php_errors.txt');
	

  require_once "admin/config.php";
  //   include_once  BASE_DIR."/inc/functions.php";
  function __autoload($class) {
    require_once 'admin/inc/class.'.$class.'.php';
  }
  

  
    try{

    $JSONarray = array( );

    $conn = Database::getInstance($config['db_server'], $config['db_user'], $config['db_pass'], $config['db_name']);
    //if(!isAuthorized($conn)){
    //  sendUnauthorizedResponse();
    //}


    if(isset($_GET['importId'])){
      // DRILAPP.COM ------------------------------------------------------------
      if(startsWith($_GET['importId'], "0")){
         $array = loadFromNewDril();
         if($array == null){
            $array = loadFromOldDril();
         } 
      }else{
          $array = loadFromOldDril();
           if($array == null){
             $array = loadFromNewDril();
           } 
      }

       if($array == null){
          header('HTTP/1.0 400 Bad Request');
          echo json_encode( array( "message" => "Not found") ) ;
       }else{
          $array = stripslashes_deep( $array );       
          echo json_encode( $array ) ;
       }   
	
    }else{
      $action = intval($_GET['act']);      
      $lang = intval($_GET['lang']);
      $version = intval($_GET['ver']);      
    // UPDATING ------------------------------------------------------------
      $books = $conn->select("SELECT * FROM `book` WHERE `version`>? AND `lang`=? AND enabled=1", array( $version, $lang ));        

      if(ACTION_CHECK == $action){
        echo json_encode(array('count' => count($books) )) ;
        exit;
      }

      for($i = 0; $i < count($books); $i++){
        $JSONarray[$i] = array( 
          "name" =>  $books[$i]['name'], 
          "version" =>  $books[$i]['version'],
          "lang_question" =>  $books[$i]['lang_question'],
          "lang_answer" =>  $books[$i]['lang_answer'],
          "sync" =>  $books[$i]['sync']
        );
        

        $lectures  = $conn->select("SELECT * FROM `lecture` WHERE `book_id`=?", array( $books[$i]['_id'] )); 
        // words
        for($j = 0; $j < count($lectures); $j++){
          $words  = $conn->select("SELECT `question` as q ,`answer` as a FROM `word` WHERE `lecture_id`=?", array( $lectures[$j]['_id'] ));

          $JSONarray[$i]["lectures"][$j] = array( 
                        "lecture_name" =>  $lectures[$j]['lecture_name'],
                        "words" =>  $words
                      );
          
        }
      }        
    
    echo json_encode(array('books'=> stripslashes_deep( $JSONarray))) ;
    }
  }catch(MysqlException $ex){
    header('HTTP/1.0 400 Bad Request');
    exit();
  }

 function loadFromOldDril(){
    global $conn;
    $book  = $conn->select("SELECT `name` FROM `import_book` WHERE `import_id`=? LIMIT 1", array( intval($_GET['importId']) ));
    if(count($book) == 0){
      return null;
    }
    $words  = $conn->select("SELECT `question` as a,`answer` as q FROM `import_word` WHERE `token`=?", array( intval($_GET['importId']) ));
    $conn->update("UPDATE `import_book` SET `downloads`= `downloads`+1 WHERE `import_id`=? LIMIT 1", array( intval($_GET['importId'])));
    return array('words'=> $words, "name" => $book[0]["name"] );
 }

 function loadFromNewDril(){
    global $conn;
    $id = intval(removeZeros($_GET['importId']));
    $lecture  = $conn->select("SELECT `name` FROM `dril_book_has_lecture` WHERE `id`=? LIMIT 1", array( $id  ));
    if(count($lecture) == 0){
      return null;
    }
    $words  = $conn->select("SELECT `question` as a,`answer` as q FROM `dril_lecture_has_word` WHERE `dril_lecture_id`=?", array( $id ));
    $conn->update("UPDATE `dril_book_has_lecture` SET `downloaded`= `downloaded`+1 WHERE `id`=? LIMIT 1", array( $id  ));
    return array('words'=> $words, "name" => $lecture[0]["name"] );
 }


function isAuthorized($conn){
  $headers = apache_request_headers();
  if(isset($headers['Authorization'])){
    $authToken = $conn->select("select val from config where `key`='dril_auth' LIMIT 1");
    return $headers['Authorization'] == $authToken[0]["val"];
  } 
  return false;
}


function sendUnauthorizedResponse(){
  header('WWW-Authenticate: Basic realm="My Realm"');
  header('HTTP/1.0 401 Unauthorized');
  exit();
}

function stripslashes_deep($value)
{
    $value = is_array($value) ?
                array_map('stripslashes_deep', $value) :
                stripslashes($value);

    return $value;
}



function startsWith($haystack, $needle) {
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}


function removeZeros($val){
  for($i = 0;$i < strlen($val) || $i < 7; $i++){
    if(startsWith($val, "0")){
        $val = substr($val, 1);
    }else{
      return $val;
    }
  }
  return null;
}