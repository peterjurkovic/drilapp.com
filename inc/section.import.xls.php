<div id="article">
    <article class="fullscreen">
    
<?php 
 
  
$strings['sk']['bookname'] = "Názov učebnice musí mať min. <b>8 znakov</b>. Zvoľte, prosím, plnohodnotný názov, odpovedajúci obsahu učebnice.";
$strings['en']['bookname'] = "Name of textbook must have at least <b>8 characters</b>. Please, choose appropriate name,  describe content of given book.";  
$strings['en']['captcha'] = "Not readable? Change text.";
$strings['sk']['captcha'] = "Nečitateľné? Vygenerovať iný.";
$strings['sk']['submit'] = "Nahrať súbor";
$strings['en']['submit'] = "Save file";
$strings["sk"]['captchalabel'] = "Opíšte text obrázka";
$strings["en"]['captchalabel'] = "Rewrite text";
$strings["sk"]['email'] = '<span class="einfo">Ak zadáte Váš email a nazdielate slovíčka, v budúcnosti budete mať možnosť spätnej editácie.</span> Odoslať identifikátor na e-mail <em>(nepovinné)</em>';
$strings["en"]['email'] = 'Send ID to email address. <em>(optional)</em>';
$strings["sk"]['book'] = '<em style="color:red">*</em> <strong>Názov učebnice:</strong> ';
$strings["en"]['book'] = '<em style="color:red">*</em> <strong>Book name</strong>:';
$strings["sk"]['author'] = 'Vaše meno/nick:';
$strings["en"]['author'] = 'Your name (optional):';
$strings["sk"]['lang'] = 'Jazyk otázka:';
$strings["en"]['lang'] = 'Language of question:';
$strings["sk"]['lang2'] = 'Jazyk odpoveď:';
$strings["en"]['lang2'] = 'Language of answer:';
$strings["sk"]['level'] = 'Jazyková úroveň:';
$strings["en"]['level'] = 'Choose level:';
$strings["sk"]['share'] = 'Zdielať slovíčka:';
$strings["en"]['share'] = 'Share words:';
$strings["sk"]['note'] = 'Poznámka k slovíčkam:';
$strings["en"]['note'] = 'Your note:';
$strings["sk"]['share_btn'] = 'Chcete umožniť stiahnuť slovíčka aj ostatným užívateľom?';
$strings["en"]['share_btn'] = 'Do you want share your words to other?';
$strings["sk"]['answer'] = 'Odpoveď';
$strings["en"]['answer'] = 'Answer';
$strings["sk"]['question'] = 'Otázka';
$strings["en"]['question'] = 'Question';

$logged = false;
$article = getArticle("fullHidden", $meta['id_article'], $lang);  
if(isset($_SESSION["id"])){
    $userService = new UserService($conn);
    $user = $userService->getUserById($_SESSION["id"]);
    if(count($user) == 1){
        $logged = true;
    }
}

if(!$logged){ 
    die('Pre zobrazenie stránky, musíte byť prihlásený.');
}
?>
    <div class="user-nav gradientGray">
        <?php include 'section.user.nav.php'; ?>
    </div>
<?php
echo '<h1>'.$article[0]["title_${lang}"].'</h1>'.$article[0]["content_${lang}"];
    
    if(!empty($_SESSION['error'])){
        echo '<p class="err">'.$_SESSION['error'].'</p>';
        unset($_SESSION['error']);
    }

?>     
        <form class="ajax" action="/inc/process.import.php" method="post" name="xlsimport" enctype="multipart/form-data">
            <div id="share">                     
                <div id="share-form" class="showen">
                    
                    <span class="row">
                        <span><em style="color:red">*</em> <strong><?php echo getMessage("chooseFile"); ?></strong></span><input type="file" name="xlsFile"  class="w250 required" />
                        <span class="form-note"></span>
                        <div class="clear"></div>
                    </span>

                    <span class="row">
                        <span><?php echo $strings[$lang]['book']; ?></span><input type="text" name="name" maxlength="50" class="w250" />
                        <span class="form-note"><?php echo $strings[$lang]['bookname']; ?></span>
                        <div class="clear"></div>
                    </span>
                    <span class="row">
                        <span><?php echo $strings[$lang]['lang']; ?></span><select name="lang2" class="w250"><?php echo getOptions( $conn, "lang", "name_${lang}" ); ?></select>
                        <div class="clear"></div>
                    </span>
                    <span class="row">
                        <span><?php echo $strings[$lang]['lang2']; ?></span><select name="lang_a" class="w250"><?php echo getOptions( $conn, "lang", "name_${lang}" ); ?></select>
                        <div class="clear"></div>
                    </span>
                     <span class="row">
                        <span><?php echo $strings[$lang]['level']; ?></span><select name="level" class="w250"><?php echo getOptions( $conn, "level", "name_${lang}" ); ?></select>
                        <div class="clear"></div>
                    </span>
                    <span class="row">
                        <span><?php echo $strings[$lang]['note']; ?></span><textarea name="descr" rows="10" cols="50"></textarea>
                        <div class="clear"></div>
                    </span>
                    <span class="row">
                        <span><?php echo $strings[$lang]['share']; ?> </span><input type="checkbox" name="share" <?php echo  ($logged ? 'checked' : ''); ?> />
                        <div class="clear"></div>
                    </span>
                </div>
                
            </div>
         
                <input type="submit" value="<?php echo $strings[$lang]['submit'] ?>" class="btn" />
                <input type="hidden" name="userId" value="<?php echo $_SESSION["id"]; ?>" />
                <div class="clear"></div>
            </div>
            <input type="hidden" value="<?php echo $lang; ?>" name="lang" />
            <div class="clear"></div>
        </form>
        <img id="infoImport" src="/img/import.png" alt="Dril - angličtina / nemčina efektívne" />
</article>
</div>    
