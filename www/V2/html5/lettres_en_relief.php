<?phpheader('Content-Type: text/html; charset=utf-8');session_start();if (isset($_SESSION['rapidpub']))    session_unset($_SESSION['rapidpub']);//require_once( 'includes/connexion.php' );function random_color_part() {    return str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT);}function random_color() {    return random_color_part() . random_color_part() . random_color_part();}/*$statement = $db->prepare("SELECT * FROM couleurs ORDER BY nom ASC");$statement->execute();$couleurs = $statement->fetchAll();*/$couleurs = $DB_site->query("SELECT * FROM couleurs ORDER BY nom ASC");//get TVA$idPays = 57;$pays = $DB_site->query_first("SELECT * FROM pays WHERE paysid = ".$idPays);$taxeTVANormal = $pays[TVAtauxnormal];$productID = md5(uniqid(rand(), true));$_SESSION['prodID'] = $productID;if (strstr($_SERVER['PHP_SELF'], "V2")) {	$racine = "/V2";	$racinePdf = "V2";} else {	$racine = "/";	$racinePdf = "";}$strDirRoot = $_SERVER['DOCUMENT_ROOT'];$strUrlModuleHTML5 = "$racine/html5/";if ($racine == "/V2") {	$strDirModuleHTML5 = $strDirRoot . "V2/html5/";    $srtRacineV2 = '/V2/';} else {	$strDirModuleHTML5 = $strDirRoot . "html5/";    $srtRacineV2 = '/';}/** ----- get module data feature ----- **/$articlemoduleid = 4;$artid = 27;$siteid = 1; $moduleFeatureData = $DB_site->query_first("SELECT * FROM articlemodule WHERE articlemoduleid=$articlemoduleid");$idmodule = $moduleFeatureData[articlemoduleid];$textemodule = $moduleFeatureData[textemodule];$libelle= $moduleFeatureData[libelle];$imagemoduleExtension= $moduleFeatureData[imagemodule];$imageModule  = "http://".$host."/admin/assets/img/modulehtml5/". $idmodule .".".$imagemoduleExtension;$imageArticleModule = "";if($imagemoduleExtension !== null){   $imageArticleModule =  '<img src="'.$imageModule.'" alt="Créer '.$libelle.' en ligne" title="Créer test lettres en relief en ligne" />';}else{   $imageArticleModule = '<img src="http://placehold.it/130x130" alt="Créer test lettres en relief en ligne" title="Produit préféré de nos clients" />';       }/** ----- get module data feature----- **//** ----- get image  ------ **///$sites = $DB_site->query("SELECT * FROM site WHERE siteid != '1'");//while ($site = $DB_site->fetch_array($sites)){    $articlesite = $DB_site->query_first("SELECT * FROM article INNER JOIN article_site USING(artid) WHERE artid = '$artid' AND articlemoduleid ='$articlemoduleid' AND  siteid = '$siteid'");    $i = 1;    $articlephotosites = $DB_site->query("SELECT * FROM articlephoto INNER JOIN articlephoto_site USING(articlephotoid) WHERE artid = '$artid' AND siteid = '$siteid' ORDER BY position");    $imageDataSlick = "";    if ($DB_site->num_rows($articlephotosites)){        $articleImageliste .= "";        while ( $articlephotosite = $DB_site->fetch_array($articlephotosites) ){        	$legendesite = 'legende' . $i .'_' . $site[siteid];            //http://".$host.            $articlephotoid = $articlephotosite[articlephotoid];			$extensionPhoto =  $articlephotosite[image];            $imgUrlPhoto= 'http://'.$host.$srtRacineV2.'ori-'.seoUrlhtml5($articlesite[libelle]).'-'.$artid.'_'.$articlephotoid.'.'.$extensionPhoto;            $articleImageliste .= '<div><img src="'.$imgUrlPhoto.'" data-altimg="'.$imgUrlPhoto.'" alt=""  width="130" height="130" /></div>';	        	++$i;        }        $imageDataSlick = $articleImageliste;    }else{        $imageDataSlick = '<div><img src="http://placehold.it/130x130/ff0000/ffffff" data-altimg="http://placehold.it/390x440/ff0000/ffffff" alt="" /></div>                    <div><img src="http://placehold.it/130x130/ff00ee/ffffff" data-altimg="http://placehold.it/390x440/ff00ee/ffffff" alt="" /></div>                    <div><img src="http://placehold.it/130x130/5900ff/ffffff" data-altimg="http://placehold.it/390x440/5900ff/ffffff" alt="" /></div>                    <div><img src="http://placehold.it/130x130/0037ff/ffffff" data-altimg="http://placehold.it/390x440/0037ff/ffffff" alt="" /></div>                    <div><img src="http://placehold.it/130x130/00c7ff/ffffff" data-altimg="http://placehold.it/390x440/00c7ff/ffffff" alt="" /></div>                    <div><img src="http://placehold.it/130x130/00ff9d/ffffff" data-altimg="http://placehold.it/390x440/00ff9d/ffffff" alt="" /></div>                    <div><img src="http://placehold.it/130x130/00ff00/ffffff" data-altimg="http://placehold.it/390x440/00ff00/ffffff" alt="" /></div>                    <div><img src="http://placehold.it/130x130/c3ff00/ffffff" data-altimg="http://placehold.it/390x440/c3ff00/ffffff" alt="" /></div>                    <div><img src="http://placehold.it/130x130/ff7200/ffffff" data-altimg="http://placehold.it/390x440/ff7200/ffffff" alt="" /></div>';    }//}/** ----- get image  ------ **/error_reporting(0);?><link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/blitzer/jquery-ui.css" /><link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'><link href='http://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet' type='text/css'><link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>css/ddlist.jquery.css" type="text/css" media="all" /><link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>css/reliefs.css?<?php echo mt_rand(); ?>" type="text/css" media="all" /><link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>css/jquery.mCustomScrollbar.min.css" type="text/css" media="all" /><link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>css/jquery.qtip.min.css" type="text/css" media="all" /><link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>css/hopscotch.min.css" type="text/css" media="all" /><link rel="stylesheet" type="text/css" href="<?= $strUrlModuleHTML5 ?>slick/slick.css"/><link rel="stylesheet" type="text/css" href="<?= $strUrlModuleHTML5 ?>slick/slick-theme.css"/><link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>fancybox/jquery.fancybox.css?v=2.1.5" type="text/css" media="screen" /><link href="<?= $strUrlModuleHTML5 ?>skins/flat/red.css" rel="stylesheet"><script src="<?= $strUrlModuleHTML5 ?>js/jquery.min.js"></script><script src="<?= $strUrlModuleHTML5 ?>js/jquery-ui.min.js"></script><div id="lettres_en_relief">    <div id="descriptif">        <div class="block_left">            <div class="images">                <div class="carouselImages">                    <?= $imageDataSlick ?>                 </div>            </div>            <div class="texte">                <p>                    <strong><?php echo $libelle ?></strong><br>                    <?= $textemodule  ?>                </p>            </div>        </div>        <div class="block_right">            <div class="image">                <?= $imageArticleModule ?>            </div>        </div>    </div>    <div id="configurateur">        <div class="block_left">            <div id="panel">                <div class="imgApercu">                    <img src="#" alt="">                </div>                <div id="productCfg" class="border">                    <div class="floatLeft"><span class="sectionTitre" style="margin:10px 4px 0 0;">Choix de la matière</span></div>                    <div class="floatLeft" style="margin-top:5px;width:210px;">                        <select name="matiere" id="matiere">                            <?php                            $row = $DB_site->query("SELECT * FROM lettres_matieres ORDER BY nom ASC");                            $i = 0;                            while ($matiere = $DB_site->fetch_array($row)) {                                if ($i == 0)                                    $idselect = $matiere['id'];                                echo '<option value="' . $matiere['id'] . '">' . $matiere['nom'] . '</option>';                                $i++;                            }                            ?>                        </select></div>                </div>                <div id="heightCfg" class="border">                    <div class="floatLeft"><span class="sectionTitre" style="margin:10px 4px 0 4px;">Hauteur de lettre</span></div>                    <div class="floatLeft" style="margin-top:5px"><select name="hauteur_lettres" id="hauteur_lettres">                            <?php							$row = $DB_site->query("SELECT * FROM tailles_lettres WHERE actif = 1 ORDER BY taille ASC");							while ($taille = $DB_site->fetch_array($row)) {                                echo '<option value="' . $taille['taille'] . '">' . $taille['nom'] . '</option>';                            }                            ?>                        </select></div>                    <div class="floatLeft" style="margin-top: 3px;">                        <button id="uploadFile" class="upload-button">À partir de votre fichier</button>                    </div>                    <div id="uploadFileContent">                        <div>                            <h1>À partir de votre fichier</h1>                            <div>Pour recevoir un devis détaillé basé sur votre fichier de travail, veuillez remplir le formulaire ci-dessous et télécharger votre fichier au format vectoriel (.pdf, .ai ou .eps).</div>                            <form action="/">                                <label for="nom">Nom </label>                                <input type="text" id="nom" name="nom" placeholder="Nom..."><br>                                <label for="prenom">Prénom </label>                                <input type="text" id="prenom" name="prenom" placeholder="Prénom..."><br>                                <label for="email">Email </label>                                <input type="text" id="email" name="email" placeholder="Email..."><br>                                <label for="telephone">Téléphone </label>                                <input type="text" id="telephone" name="telephone" placeholder="Téléphone..."><br>                                <label for="infos">Descriptif </label>                                <textarea style="resize: none;width:173px" name="infos" id="infos" cols="30" rows="10" placeholder="Décrivez en détaillant au possible votre projet (textes, couleurs, police, matière souhaitée, hauteur de lettre etc...)."></textarea><br><br>                                <label for="fichier">Fichier </label>                                <input type="file" name="fichier" id="fichier"><br>                                <em style="float:left;font-size:10px">Formats acceptés: pdf, jpg et png</em>                                <br><button class="upload-button" style="width:150px">Envoyer</button>                            </form>                        </div>                        <div>                            <div>Votre demande sera traîtée sous <strong>48 heures ouvrées</strong>*.</p>                                <p><em>*Sous réserve de faisabilité</em></p></div>                        </div>                    </div>					                </div>                <div id="thicknessCfg" class="border">                    <div class="epaisseur">                        <span class="sectionTitre" style="margin-bottom:3px">Épaisseur des lettres</span>                        <div class="radioContainer">                            <?php                            $row = $DB_site->query("SELECT * FROM epaisseurs_lettres WHERE id_matiere = ".$idselect." ORDER BY epaisseur ASC");                            $i = 0;                            while ($epaisseur = $DB_site->fetch_array($row)) {                                if ($i == 0) {                                    echo '<input class="epaisseur" name="epaisseur_lettres" ' . ( $epaisseur['couleurs'] == 0 ? 'data-color=0' : 'data-color=1' ) . ' data-epaisseur="' . $epaisseur['epaisseur'] . '" type="radio" id="' . $epaisseur['id'] . '" value="' . $epaisseur['id'] . '" checked><label for="' . $epaisseur['id'] . '">' . $epaisseur['nom'] . '</label>';                                    $epaisseur2 = $epaisseur['epaisseur'];                                    if ($epaisseur['couleurs'] == 0) {                                        echo '<script type="text/javascript">$(function(){ $( "#MsgColors" ).show(); $( ".minibloc_couleur" ).hide(); });</script>';                                    }                                } else                                    echo '<input class="epaisseur" name="epaisseur_lettres" ' . ( $epaisseur['couleurs'] == 0 ? 'data-color=0' : 'data-color=1' ) . ' data-epaisseur="' . $epaisseur['epaisseur'] . '" type="radio" id="' . $epaisseur['id'] . '" value="' . $epaisseur['id'] . '"><label for="' . $epaisseur['id'] . '">' . $epaisseur['nom'] . '</label>';                                $i++;                            }                            ?>                        </div>                    </div>                </div>                <div id="textCfg" class="border">                    <div class="texte">                        <input type="text" name="texte" id="texte" placeholder="Saisissez votre texte ici">                    </div>						                </div>                <div id="fontCfg">                    <div id="bloc_fonts" class="jcarousel-wrapper">                        <div id="bloc_subfonts" class="jcarousel">                            <ul style="overflow:hidden;width: 1200px;height:70px !important;position:relative;list-style:none;margin:0;padding:0;">                                <?php                                if ($handle = opendir('fonts')) {                                    while (false !== ( $entry = readdir($handle) )) {                                        if ($entry != "." && $entry != "..") {//                                                                                     echo  __DIR__.'/fonts/' .  $entry."<br/>";                                            $fontname = explode('.', $entry);                                            $im = imagecreatetruecolor(100, 30);                                            $white = imagecolorallocate($im, 255, 255, 255);                                            $grey = imagecolorallocate($im, 128, 128, 128);                                            $black = imagecolorallocate($im, 0, 0, 0);                                            imagefilledrectangle($im, 0, 0, 99, 29, $white);                                            imagettftext($im, 10, 0, 11, 21, $grey, __DIR__ . '/fonts/' . $entry, str_replace('_', ' ', $fontname[0]));                                            imagettftext($im, 10, 0, 10, 20, $black, __DIR__ . '/fonts/' . $entry, str_replace('_', ' ', $fontname[0]));                                            ob_start();                                            imagepng($im);                                            $img = ob_get_clean();                                            echo '<li class="minibloc_font" title="' . str_replace('_', ' ', $fontname[0]) . '" data-fontcode="' . $entry . '"><img src="data:image/png;base64,' . base64_encode($img) . '" /></li>';                                            imagedestroy($im);                                        }                                    }                                    closedir($handle);                                }                                ?>                            </ul>                        </div>                        <a href="#" class="jcarousel-control-prev"><img src="<?= $strUrlModuleHTML5 ?>img/arrow_left.png" alt="left" /></a>                        <a href="#" class="jcarousel-control-next"><img style="max-width: 32px;" src="<?= $strUrlModuleHTML5 ?>img/arrow_right.png" alt="right" /></a>                    </div>                </div>                <div id="colorCfg" class="border">                    <div id="bloc_couleurs">                        <span class="sectionTitre" style="margin-bottom:3px">Couleur de face</span>                        <span id="MsgColors">La matière sélectionnée ne permet pas d'appliquer une couleur de face.</span>                        <?php                        while ($color = $DB_site->fetch_array($couleurs)) {                            echo '<div data-hexcolor="' . $color['hexa'] . '" class="minibloc_couleur" title="' . $color['nom'] . '" style="background-color:' . $color['hexa'] . '"></div>';                        }                        ?>                    </div>                </div>                <div id="priceCfg">				<!--div id="prix_container" style="text-align:center; font-weight:bold">					<span id="prix_ht"><span>0.00</span> € HT</span>					<span id="prix_ttc">Soit <span>0.00</span> € TTC</span>				</div-->                <div id="block_right6">        			<div style="width:150px;position:relative;z-index:10;height:40px;margin:5px auto 0 auto;background:#70c007"><span style="display:block;padding-top:8px;color:#fff;font-size:18px">NOTRE PRIX</span></div>        			<div id="prix">        				<div id="prix_container">        					<span id="prix_ht"><span>0.00</span> € HT</span>        					<span id="prix_ttc">Soit <span>0.00</span> € TTC</span>        				</div>        			</div>        			<span style="text-transform:uppercase;font-size:14px;color:#70ad24;font-weight:bold">Expédition prévue le 27/10/2016</span>        		</div>                <a href="#" id="addpanier_button"><img src="template-add_to_cart.png" alt="Ajouter au panier"></a>                </div>                <div id="add2Cart" style="display:none">			 		<form id="formu_add_panier_lettre_relief" method="post" action="cde1.php">			 			<input type="hidden" name="action" id="action" value="addpanier">			 			<input type="hidden" name="artid" id="add_artid" value="">			 			<input type="hidden" name="articlemoduleid" id="add_articlemoduleid" value="4">			 			<input type="hidden" name="prix_ht" id="add_prix_ht" value="">			 			<input type="hidden" name="prix_ttc" id="add_prix_ttc" value="">			 			<input type="hidden" name="longueur" id="add_longueur" value="">			 			<input type="hidden" name="hauteur" id="add_hauteur" value="">						<input type="hidden" name="url_image" id="add_url_image" value="">						<input type="hidden" name="url_pdf" id="add_url_pdf" value="">			 			<input type="hidden" name="qte" id="qte" value="1"> 						 		</form>                 </div>            </div>        </div>        <div class="block_right" style="position:relative">            <div id="perspective">                <!--a id="pdf_button" href="#" >PDF</a-->                <label for="tperspective" style="margin-right: 0 !important;">Vue en relief</label> <input type="checkbox" name="tperspective" id="tperspective">            </div>            <div id="creation_fond">                <label for="fond_apercu" style="margin-right: 0 !important;">Fond:</label>&nbsp;<select name="fond_apercu" id="fond_apercu">                    <option value="blanc">Blanc</option>                    <option value="noir">Noir</option>                    <option value="mur">Mur</option>                    <option value="paysage">Paysage</option>                </select>            </div>            <div id="apercu">            </div>            <div id="dimensions">                <span id="slongueur"></span><span id="shauteur"></span>            </div>        </div>    </div></div><script type="text/javascript">    var matiereSel = <?php echo $idselect; ?>;    var thickness = <?php echo $epaisseur2; ?>;    var productID = "<?php echo $productID; ?>";	var tvaNormal = "<?php echo $taxeTVANormal ?>";	var host  = "<?php echo $host."/".$racinePdf ?>";//        $text = $_POST['text'];//$height = $_POST['height'];//$cFace = $_POST['couleurFace'];//$cFont = $_POST['font'];//$cChamp = $_POST['couleurChamp'];//$matiere = (int) $_POST['matiere'];//$epaisseur = $_POST['epaisseur'];//$perspective = $_POST['perspective'];</script><!-- <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script> --><script src="<?= $strUrlModuleHTML5 ?>js/jquery.mCustomScrollbar.concat.min.js"></script><script src="<?= $strUrlModuleHTML5 ?>js/jquery.jcarousel.min.js"></script><script src="<?= $strUrlModuleHTML5 ?>js/jquery.jcarousel-control.min.js"></script><script src="<?= $strUrlModuleHTML5 ?>js/jquery.qtip.min.js"></script><script src="<?= $strUrlModuleHTML5 ?>js/functions.js?<?php echo mt_rand(); ?>"></script><script src="<?= $strUrlModuleHTML5 ?>js/ddlist.jquery.min.js"></script><script src="<?= $strUrlModuleHTML5 ?>js/icheck.min.js"></script><script src="<?= $strUrlModuleHTML5 ?>js/hopscotch.min.js"></script><script type="text/javascript" src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script><script type="text/javascript" src="<?= $strUrlModuleHTML5 ?>slick/slick.min.js"></script><script src="<?= $strUrlModuleHTML5 ?>js/html2canvas.js"></script><script type="text/javascript" src="<?= $strUrlModuleHTML5 ?>fancybox/jquery.fancybox.pack.js?v=2.1.5"></script><script type="text/javascript" src="<?= $strUrlModuleHTML5 ?>fancybox/jquery.mousewheel-3.0.6.pack.js"></script><script src="<?= $strUrlModuleHTML5 ?>js/reliefs.js?<?php echo mt_rand(); ?>"></script>