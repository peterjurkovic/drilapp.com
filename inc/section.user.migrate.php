<?php

	// require_once '/api/services/TagService.php';

	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		try{
			if(intval($_POST['existing']) == 1){
				copyToExisting($_POST);
			}else{
				copyToNewBook($_POST);
			}
			header("Location: ".linker(16, 1, $lang)."?success=1");
			exit;
		}catch(InvalidArgumentException $e){
			$errorMessage2 = $e->getMessage();
		}

	}

	$userService = new UserService($conn);
	$userStats = $userService->getWebDrilStats($_SESSION['id']);
	$oldBook = $userService->getById($_GET['id']);

	$langList = $conn->select("SELECT id_lang as id, name_$lang as name FROM `lang`");
	$levelList =$conn->select("SELECT id_level as id, name_$lang as name FROM `level`");
	$bookList = $conn->select("SELECT * FROM dril_book WHERE user_id = ? order by name", array($_SESSION['id']));
	$wordList = $conn->select("SELECT * FROM `import_word` WHERE `token`=? ", array($oldBook[0]['import_id']));	
	$wordCount = count($wordList);
	

	$errorMessage = validate($oldBook, $userStats, $wordCount);

	
function langsAreSame($oldBook, $newbook){
	return 
	(
		$oldBook['lang'] == $newbook['question_lang_id'] &&
		$oldBook['lang_a'] == $newbook['answer_lang_id']
	) || (
		$oldBook['lang_a'] == $newbook['question_lang_id'] &&
		$oldBook['lang'] == $newbook['answer_lang_id']
	);
}



?>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.4.2/chosen.jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.12/jquery-ui.min.js" type="text/javascript" charset="utf-8"></script>
<script src="/js/tag-it.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/flick/jquery-ui.css">

<link href='https://cdnjs.cloudflare.com/ajax/libs/chosen/1.4.2/chosen.min.css' rel='stylesheet' type='text/css'>
<link href='/css/bootstrap.css' rel='stylesheet' type='text/css'>


<script type="text/javascript">
$(function(){
    $("select").chosen({width: "97%"});
    $('#accordion h3').click(function(){
    	$('.pj-active').removeClass('pj-active');
    	
    	$(this).addClass('pj-active').next().addClass('pj-active');
    });
     $("#myTags").tagit({
     	fieldName: "tags",
     	allowSpaces :true,
     	autocomplete: {
     		delay: 100, 
     		minLength: 2,
     		source : '<?php echo API_URL; ?>tags'
     	}
     });

     $(document).on('submit', 'form', function(){
     	if($(this).hasClass('disabled')){
     		return false;
     	}
     	var valid = true;
     	$(this).find('select').each(function(){
     		var $select = $(this);
     		if($select.val() === ""){
     			valid = false;
     		}
     	})	

     	if(!valid || !validate($(this))){
     		showStatus({msg: 'Skontrolute zadané data.', err: 1});
     		return false;
     	}
     	$('input[name=tagList]').val($("#myTags").tagit("assignedTags"));
     	$(this).addClass('disabled');
     });
});

</script>

<div id="article">
	<article class="user-section fullscreen">
		


		<h1>Presun slovíčok do nového WebDrilu</h1>
		<?php 
		if(isset($errorMessage2)){
			echo '<p class="err">'.$errorMessage2.'</p>';
		}
		if($errorMessage){
			echo '<p class="err">'.$errorMessage.'</p>';
		}else{  ?>

		<div class="user-content">

			<?php
				if(isset($success) && !$success){
					echo '<p class="alert alert-danger">Skontrolujte zadané dáta.</p>';
				}
				if($wordCount > LECTURE_WORD_LIMIT){
					echo '<p class="alert alert-warning">Jedna lekcia v novom Drile môže obsahovať max. 300 slovíčok. '.
					'Preto bude '.($wordCount - LECTURE_WORD_LIMIT).' slovíčok vynechaných.</p>';
				}
			?>	

			<div id="accordion">
			  <h3><?php printMessage("migrateAddToExisting"); ?></h3>
			  <div class="pj-bx content-f1">
			  	<p>
			  		<?php printMessage("migrateAddToExistingDesc"); ?>
			  	</p> 

			  	<form method="POST">
			  		 <div class="form-group">
			  		 		<span for="bookId"><em>**</em><?php printMessage("migrateExisting"); ?></span>
			  		 		<select class="required existing" name="bookId">
							<option value=""><?php printMessage("migrateExisting2"); ?></option>		
							<?php 
								$html = '';
								foreach ($bookList as $i => $book) {
									$html .= '<option '.(langsAreSame($oldBook[0], $book) ? '' : 'disabled="disabled"').' value="'.$book['id'].'">'.$book['name'].'</option>';
								}
								echo $html;
							?>
						</select>
			  		</div>
			  		 <div class="form-group">
			  		 	<span for="lectureName"><em>**</em><?php printMessage("migrateLectureName"); ?></span>
			  		 	<input type="text" name="lectureName" class="required form-control" required value="<?php echo $oldBook[0]['book_name'] ?>" />
			  		 </div>
			  		<div class="form-group">
			  			<button class="btn btn-primary"><?php printMessage("migrate"); ?></button>
			  		</div>
					<div class="clear"></div>   
					<input type="hidden" name="existing" value="1" />
					<input type="hidden" name="id" value="<?php echo $_GET['id']; ?>" />
					
			  	</form>
			  	
					
				
			  </div>
			  <h3><?php printMessage("migrateCreateNew") ?></h3>
			  <div class="pj-bx content-f">
			  		<form method="POST">
			  			<div class="form-group">
				  		 	<span for="bookName"><em>**</em><?php printMessage("migrateBookName"); ?>:</span>
				  		 	<input type="text" name="bookName" class="required form-control" required value="<?php echo $oldBook[0]['book_name'] ?>" />
			  		 	</div>
			  		 	<div class="form-group">
				  		 	<span for="lectureName"><em>**</em><?php printMessage("migrateLectureName"); ?>:</span>
				  		 	<input type="text" name="lectureName" class="required form-control" required value="<?php echo $oldBook[0]['book_name'] ?>" />
			  		 	</div>
			  		 	<div class="form-group">
				  		 	<span for="langQuestion"><em>**</em><?php printMessage("migrateLangQuestion"); ?>:</span>
				  		 	<select class="required" name="langQuestion"><?php echo getBookOptions($oldBook[0]['lang'], $langList) ?></select>
			  		 	</div>
			  		 	<div class="form-group">
				  		 	<span for="langAnswer"><em>**</em><?php printMessage("migrateLangAnswer"); ?>:</span>
				  		 	<select class="required" name="langAnswer"><?php echo getBookOptions($oldBook[0]['lang_a'], $langList) ?></select>
			  		 	</div>
			  		 	<div class="form-group">
				  		 	<span for="level"><em>**</em><?php printMessage("migrateLevel"); ?>:</span>
				  		 	<select class="required" name="level"><?php echo getBookOptions($oldBook[0]["level"], $levelList) ?></select>
			  		 	</div>
			  		 	<div class="form-group">
				  		 	<span for="category"><em>**</em><?php printMessage("migrateCateg"); ?>: <a style="padding-left:20px;" href="mailto:info@drilapp.com">navrhnúť novú kategóriu</a></span>
				  		 	<select class="required" name="category"><?php printCategories() ?></select>
			  		 	</div>
			  		 	<div class="form-group">
			  		 		<span for="bookName"><?php printMessage("migrateTags"); ?>:</span>
			  		 		<ul id="myTags"></ul>
							<span>
								<br />
								<?php printMessage("migrageTagsDesr"); ?>
							</span>
			  		 	</div>
			  		 	<div class="form-group">
				  			<button class="btn btn-primary"><?php printMessage("migrate"); ?></button>
				  		</div>
				  		<div class="clear"></div>   
				  		<input type="hidden" name="tagList" value="" />
			  		 	<input type="hidden" name="existing" value="0" />
						<input type="hidden" name="id" value="<?php echo $_GET['id']; ?>" />
			  		</form>	

			  
			  </div>
			</div>

			<em>**</em> - <?php printMessage("migrateReq"); ?>

			<?php } ?>

				<?php
				$html = '';
				if($wordCount > 0){
		                $html .= '<h2 class="cst">Nasledujúce slovíčka budú prenesené:</h2>'.
		                            '<table id="words" data-lang="'.$lang.'">';
		                for($i = 0; $i < $wordCount && $i < LECTURE_WORD_LIMIT; $i++){
		                    $html .= '<tr id="id'.$wordList[$i]['_id'].'">
		                                 <td>'.$wordList[$i]['question'].'</td>
		                                 <td>'.$wordList[$i]['answer'].'</td>
		                              </tr>';
		                }
		                $html .= '</table>';
		           }else{
		               $html .= '<table id="words" data-lang="'.$lang.'"><p class="alert">Učebnica neobsahuje žiadne kartičky.</p></table>';
		           }
		           echo $html;
				?>
		</div>	
		<div class="clear"></div>

	
	</article>
</div>


<?php

// printCategories();

function getBookOptions($id , $list){
    $html = '';
    foreach ($list as $val) {
        $html .= '<option value="'.$val["id"].'" '.($val["id"] == $id ? 'selected="selected"' : '').'>'.$val["name"].'</option>';
    }
    return $html;
}

function printCategories(){
	$list = getTreeCategories();
	$html = '<option value="">-- Vyberte z možností --</option>';
	foreach($list as $c){
		$html .= '<optgroup label="'.$c['name'].'">'.
					getBookOptions(-1, $c['subCategories']).
					'</optgroup>';
	}	
	echo $html;
}

function getTreeCategories(){
	$categories = getCategoryLevel(null);
    for($i = 0; $i < count($categories); $i++){
    	$categories[$i]['subCategories'] = getCategoryLevel($categories[$i]['id']);
    }
    return $categories;
}

	
function getCategoryLevel($id){
	global $lang, $conn;
	return $conn->select(
        "SELECT id , name_$lang as name ".
        "FROM `dril_category` ".
        "WHERE parent_id ".($id == null ? " IS NULL " : " = ".$id." ").
        "ORDER BY ordering"
    );
}


function copyToNewBook($form){
	global $conn;
	$oldBookId = (int)$form['id'];
	$userService = new UserService($conn);
	$userStats = $userService->getWebDrilStats($_SESSION['id']);
	$oldBook = $userService->getById( $oldBookId );
	$words = $conn->select( "SELECT * FROM `import_word` WHERE `token`=? ", array($oldBook[0]['import_id']));
	$errorMessage = validate($oldBook, $userStats, count($words));
	if($errorMessage){
		throw new InvalidArgumentException($errorMessage);
	}
	isBookNameUniqe($form['bookName']);
	createNewBook($form, $oldBook);
}

function createNewBook($form, $oldBook){
	global $conn;
	$tagService = new TagService($conn);
	$sql = 
          "INSERT INTO `dril_book` ( ".
            "`name`, ".
            "`question_lang_id`, ".
            "`answer_lang_id`, ".
            "`level_id`, ".
            "`dril_category_id`, ".
            "`is_shared`, ".
            "`user_id`, ".
            "`description`, ".
            "`changed`, ".
            "`created`) ".
          "VALUES (?,?,?,?,?,?,?,?, NOW(), NOW())";
        $conn->insert($sql,  array(
            $form['bookName'], 
            $form['langQuestion'], 
            $form['langAnswer'],
            $form['level'],
            $form['category'], 
            $oldBook[0]['shared'],
            getUserId($form['id']),
            $oldBook[0]['descr']
          ));
    $bookId = $conn->getInsertId();
    if(isset($form['tagList'])){
    	$tagService->createTags(explode(",", $form['tagList']), $bookId, $_SESSION['id']);
    }
    createLecture($bookId, $form['lectureName'], $oldBook);    
}

function getUserId($bookId){
	global $conn;
	$r = $conn->select("SELECT id_user FROM  import_book WHERE _id = " . intval($bookId));
	return count($r) == 1 && $r[0]['id_user'] != null ? $r[0]['id_user'] : $_SESSION['id'];
}

function copyToExisting($form){
	global $conn;

	$lectureName = trim($form['lectureName']);
	$webDrilBookId = (int)$form['bookId'];
	$oldBookId = (int)$form['id'];

	$userService = new UserService($conn);
	$userStats = $userService->getWebDrilStats($_SESSION['id']);
	$oldBook = $userService->getById( $oldBookId );
	$words = $conn->select( "SELECT * FROM `import_word` WHERE `token`=? ", array($oldBook[0]['import_id']));
	$errorMessage = validate($oldBook, $userStats, count($words));
	if($errorMessage){
		throw new InvalidArgumentException($errorMessage);
	}
	createLecture($webDrilBookId, $lectureName,  $oldBook);
	return true;
}


function createLecture($webDrilBookId, $lectureName,  $oldBook){
	global $conn;
	isLectureNameUniqe($lectureName, $webDrilBookId);
	$sql = "INSERT INTO `dril_book_has_lecture` (`name`,`dril_book_id`,`changed`,`created`) ".
            "VALUES (?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
    $conn->insert($sql,  array($lectureName, $webDrilBookId) );
    $lectureId = $conn->getInsertId();
    importWordsIntoLecture($lectureId, $oldBook);
}



function importWordsIntoLecture($lectureId, $oldBook){
	 	global $conn;
	 	$wordList = $conn->select( "SELECT * FROM `import_word` WHERE `token`=? ", array($oldBook[0]['import_id']));	
		$wordCount = count($wordList);
	
	 	$count = count($wordList);
        $webDrilBook = getBookByLecture( $lectureId );	

        $swap = $webDrilBook['question_lang_id'] !=  $oldBook[0]['lang'];

        $sqlRows = array();
        for( $i = 0; $i < $count && $i < LECTURE_WORD_LIMIT; $i++ ){
          $sqlRows[] = "(
                        '".$conn->clean(($swap ? $wordList[$i]['answer'] : $wordList[$i]['question']))."', 
                        '".$conn->clean((!$swap ? $wordList[$i]['answer'] : $wordList[$i]['question']))."',
                        ".$lectureId.",".
                        "NOW() ".
                      ")";
        } 
        $sql = "INSERT INTO `dril_lecture_has_word` (`question`, `answer`, `dril_lecture_id`, `created`) VALUES ".
                implode(",", $sqlRows);
        $conn->insert($sql);
        $sql = "UPDATE `dril_book_has_lecture` " .
             "SET `no_of_words`= (SELECT count(*) FROM dril_lecture_has_word WHERE dril_lecture_id = $lectureId) ".
             "WHERE id = $lectureId";
        $conn->update( $sql );
        $conn->update( "UPDATE import_book SET transmitted=1 WHERE import_id=".$oldBook[0]['import_id']." LIMIT 1");    
}

function getBookByLecture($lectureId){
	global $conn;
	 $sql =  "SELECT b.*, bhl.`no_of_words` as no_of_words, bhl.`id` as dril_lecture_id ".
              "FROM `dril_book` b ".
              "INNER JOIN dril_book_has_lecture bhl ON bhl.dril_book_id = b.`id` ".
              "WHERE bhl.id = ? LIMIT 1";
      $result =  $conn->select( $sql, array($lectureId) ); 
       if(count($result) == 1){
          return $result[0];
      }
      return null;
}


function validate($oldBook, $userStats, $wordCount){
	$totalWordsAfterTransmision = $userStats['wordCount'] + ($wordCount > LECTURE_WORD_LIMIT ? LECTURE_WORD_LIMIT : $wordCount);
	$errorMessage = false;
	if(count($oldBook) == 0){
		$errorMessage = "The book was not found";
	}else if($_SESSION['id'] != 1 && $oldBook[0]['id_user'] != (int)$_SESSION['id']){
		$errorMessage = "Access denied";
	}else if($oldBook[0]['transmitted'] == 1){
		$errorMessage = "The book was already transmitted.";
	}else if($userStats['wordLimit'] != -1 && $userStats['wordLimit'] < $totalWordsAfterTransmision){
		$errorMessage = "Užívateľ môže mať maximálne evidovaných <b>".$userStats['wordLimit']."</b> slovíčok.".
						"Na Vašom účte je momentálne uložených ".$userStats['wordCount'];
	}
	return $errorMessage;
}


function isBookNameUniqe( $name ){
   global $conn;
   $sql =  "SELECT count(*) as book_count FROM  `dril_book` ".
           "WHERE name = ? AND user_id = ? ";
    $result =  $conn->select( $sql, array( $name, $_SESSION['id'] ));
    if($result[0]["book_count"] > 0){
    	throw new InvalidArgumentException("The book named \"$name\" already exists");
    }
}

function isLectureNameUniqe( $name, $bookId){
	global $conn;
    $sql =  "SELECT count(*) as lecture_count FROM  `dril_book_has_lecture` l ".
            "WHERE l.name = ? AND l.dril_book_id = ? ";  

    $result =  $conn->select( $sql, array( $name, $bookId ));
    return $result[0]["lecture_count"] == 0;
    if($result[0]["lecture_count"] > 0){
    	throw new InvalidArgumentException("The lecture named \"$name\" already exists");
    }
}

include 'loacle-dialog.php';
?>