<?php
   header('Content-Type: text/html; charset=utf-8');
   session_start();
   if (isset( $_SESSION['rapidpub']))
   	session_unset( $_SESSION['rapidpub'] );

   function random_color_part() {
   	return str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT);
   }
   function random_color() {
   	return random_color_part() . random_color_part() . random_color_part();
   }
   /** --- includ global  ---*/   
   if( !isset($DB_site) ){
        require_once("../global.php");
   }
   /** --- includ global  ---*/       
   $couleursData = $DB_site->query("SELECT * FROM couleurs ORDER BY nom ASC LIMIT 20");
   $couleursData2 = $DB_site->query("SELECT * FROM couleurs ORDER BY nom ASC LIMIT 20");
   $pays = $DB_site->query_first("SELECT * FROM pays WHERE paysid = '57'");
   $taxeTVANormal = $pays[TVAtauxnormal];
   $productID = md5( uniqid( rand(), true ) );
   
   if (strstr($_SERVER['PHP_SELF'], "V2")) {
    $racine = "/V2";
    $racinePdf = "V2";
   } else {
    $racine = "/";
    $racinePdf = "";
   }
   
   $strDirRoot = $_SERVER['DOCUMENT_ROOT'];
   
   $strUrlModuleHTML5 = "$racine/html5/";
   
   if ($racine == "/V2") {
   	$strDirModuleHTML5 = $strDirRoot . "V2/html5/";
   } else {
   	$strDirModuleHTML5 = $strDirRoot . "html5/";
   }
    if (isset($_POST['action'])) {
       $action = $_POST['action'];
    } else if(isset($_GET['action'])) {
       $action = $_GET['action'];
    } 
   
   /** ----- get module data feature ----- **/
   $articlemoduleid = 1;
   $moduleFeatureData = $DB_site->query_first("SELECT * FROM articlemodule WHERE articlemoduleid=$articlemoduleid");
   $idmodule = $moduleFeatureData[articlemoduleid];
   $textemodule = $moduleFeatureData[textemodule];
   $libelle= $moduleFeatureData[libelle];
   $imagemoduleExtension= $moduleFeatureData[imagemodule];
   $imageModule  = "http://".$host."/admin/assets/img/modulehtml5/". $idmodule .".".$imagemoduleExtension;
   $imageArticleModule = "";
   if($imagemoduleExtension !== null){
       $imageArticleModule =  '<img src="'.$imageModule.'" alt="Créer '.$libelle.' en ligne" title="Créer $libelle en ligne" />';
    }else{
       $imageArticleModule = '<img src="http://placehold.it/130x130" alt="Créer test banderole en ligne" title="Produit préféré de nos clients" />';       
   }    

   /** ----- get module data feature----- **/
   error_reporting(0);
?>
<div id="result"></div>
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/blitzer/jquery-ui.css" />
<link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
<link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>css/ddlist.jquery.css" type="text/css" media="all" />
<link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>css/banderoles.css?<?php echo mt_rand(); ?>" type="text/css" media="all" />
<link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>css/jquery.mCustomScrollbar.min.css" type="text/css" media="all" />
<link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>css/jquery.qtip.min.css" type="text/css" media="all" />
<link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>css/spectrum.css" type="text/css" media="all" />
<link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>css/hopscotch.min.css" type="text/css" media="all" />
<link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>css/sweet-alert.css" type="text/css" media="all" />
<link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>css/colpick.css" type="text/css" media="all" />
<link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>css/lobibox.css" type="text/css" media="all" />
<link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>fancybox/jquery.fancybox.css?v=2.1.5" type="text/css" media="screen" />

<div id="banderoles_config">
   <a name="focus_banderole" id="focus_banderole"></a>
   <div class="container-left">
      <div id="block_left">
         <div id="text_content">
            <div class="img">
               <?php echo $imageArticleModule ?>
            </div>
            <div class="text">
               <h1><?php echo $libelle ?></h1>
               <p class="mCustomScrollbar" data-mcs-theme="dark">
                  <?php echo $textemodule ?>
               </p>
            </div>
         </div>
         <div id="apercu_content">
            <div id="banderole_bg">
               <div id="banderole_content">
                  <div id="oeillets">
                     <div class="oeillet" id="oeillet_tl"></div>
                     <div class="oeillet" id="oeillet_tr"></div>
                     <div class="oeillet" id="oeillet_bl"></div>
                     <div class="oeillet" id="oeillet_br"></div>
                  </div>
                  <div id="banderole_width"></div>
                  <div id="banderole_height"></div>
               </div>
            </div>
         </div>
      </div>
   </div>
   <div class="container-right">
      <div id="block_right">
         <div id="config_right">
            <div id="dimensions">
               <div class="step_container" style="margin-left:5px"><span class="step">1</span></div>
               <span class="dimensionstext">Dimensions <span class="small">(en cm)</span></span>
               <div id="longueur_cfg" style="position:relative">
                  <img src="<?=$strUrlModuleHTML5?>img/longueur.png" class="dim_img" alt="" style="left:0">
                  <input type="text" maxlength="5" id="longueur" placeholder="longueur" class="dimensions_banderole" />
               </div>
               <div id="hauteur_cfg" style="position:relative;margin-left: 10px;">
                  <img src="<?=$strUrlModuleHTML5?>img/hauteur.png" class="dim_img" alt="" style="left:0">
                  <input type="text" maxlength="5" id="hauteur" placeholder="hauteur" class="dimensions_banderole" />
               </div>
            </div>
         </div>
      </div>
      <div id="block_right2">
         <div id="text_right">
            <!--               sosso             ajout svg-->
            <div id="text_right">
               <div class="text_left"></div>
               <input type="text" placeholder="Insérer un texte" value="" id="textecfg" name="textecfg">
               <label style="float: left; font-size: 12px;line-height: 30px;">Selectionnez la taille</label>
               <input type="number" placeholder="" value="12" id="textecfgsize" name="textecfgsize" max="1000" min="1">
               <button title="Ajouter le texte saisi" id="add_text">ok</button>
            </div>
         </div>
         <div id="policecontainer">
            <select id="police" name="police">
            <?php
               $polices = array();
               if ( $handle = opendir( './fonts' ) ) {
               	while ( false !== ( $entry = readdir( $handle ) ) ) {
               		if ( $entry != "." && $entry != ".." && substr($entry, 0, 1) != "_") {
               			$polices[] = $entry;
               		}
               	}
               	closedir( $handle );
               	sort( $polices );
               	foreach ( $polices as $police ) {
               		$fontname = explode( '.', $police );
               		$im = imagecreatetruecolor(100, 30);
               		$white = imagecolorallocate( $im, 255, 255, 255);
               		$grey = imagecolorallocate( $im, 128, 128, 128);
               		$black = imagecolorallocate($im, 0, 0, 0);
               		imagefilledrectangle($im, 0, 0, 99, 29, $white);
               		imagettftext($im, 10, 0, 11, 21, $grey, 'fonts/' . $police, str_replace( '_', ' ', $fontname[0] ) );
               		imagettftext($im, 10, 0, 10, 20, $black, 'fonts/' . $police, str_replace( '_', ' ', $fontname[0] ) );
               		ob_start();
               		imagepng( $im );
               		$img = ob_get_clean();
               		echo '<option value="' .$police . '">' . str_replace( '_', ' ', $fontname[0] ) . '</option>';
               		imagedestroy( $im );
               	}
               }
               ?>
            </select>
         </div>
         <div class="step_container" style="width:145px"><span class="step">2</span><span class="step_title">Choisir une police</span></div>
      </div>
      <div id="block_right3" style="background-color:#F4F4F4">
         <div style="margin-bottom:10px;padding-top:5px">
            <div class="step_container" style="width:165px;margin-right: 45px;"><span class="step">3</span><span class="step_title">Insérer un pictogramme</span></div>
            <select id="themes" name="themes">
               <option value="divers" selected="selected">Divers</option>
               <option value="vehicules">Véhicules</option>
               <option value="personnages">Personnages</option>
               <option value="signes">Signes</option>
               <option value="outils">Outils</option>
               <option value="svg">svg</option>
            </select>
         </div>
         <div id="motifs_contain">
            <div id="divers_container" class="motifs_container">
               <?php
                  $files = glob( $strDirModuleHTML5.'img/motifs/divers/*.{png}', GLOB_BRACE );
                  
                  foreach( $files AS $file ) {
                  	echo '<div title="Ajouter un motif sur votre plaque" class="motif" id="' . basename( $file ) . '" style="background-size:100%;background-image: url(' . str_replace($strDirRoot, '/', $file) . ');"></div>';
                  }
                  ?>
            </div>
            <div id="vehicules_container" class="motifs_container" style="display:none;">
               <?php
                  $files = glob( $strDirModuleHTML5.'img/motifs/vehicules/*.{png}', GLOB_BRACE );
                  foreach( $files AS $file ) {
                  	echo '<div title="Ajouter un motif sur votre plaque" class="motif" id="' . basename( $file ) . '" style="background-size:100%;background-image: url(' . str_replace($strDirRoot, '/', $file) . ');"></div>';
                  }
                  ?>
            </div>
            <div id="personnages_container" class="motifs_container" style="display:none;">
               <?php
                  $files = glob( $strDirModuleHTML5.'img/motifs/personnages/*.{png}', GLOB_BRACE );
                  foreach( $files AS $file ) {
                  	echo '<div title="Ajouter un motif sur votre plaque" class="motif" id="' . basename( $file ) . '" style="background-size:100%;background-image: url(' . str_replace($strDirRoot, '/', $file) . ');"></div>';
                  }
                  ?>
            </div>
            <div id="signes_container" class="motifs_container" style="display:none;">
               <?php
                  $files = glob( $strDirModuleHTML5.'img/motifs/signes/*.{png}', GLOB_BRACE );
                  foreach( $files AS $file ) {
                  	echo '<div title="Ajouter un motif sur votre plaque" class="motif" id="' . basename( $file ) . '" style="background-size:100%;background-image: url(' . str_replace($strDirRoot, '/', $file) . ');"></div>';
                  }
                  ?>
            </div>
            <div id="outils_container" class="motifs_container" style="display:none;">
               <?php
                  $files = glob( $strDirModuleHTML5.'img/motifs/outils/*.{png}', GLOB_BRACE );
                  foreach( $files AS $file ) {
                  	echo '<div title="Ajouter un motif sur votre plaque" class="motif" id="' . basename( $file ) . '" style="background-size:100%;background-image: url(' . str_replace($strDirRoot, '/', $file) . ');"></div>';
                  }
                  ?>
            </div>
            <div id="svg_container" class="motifs_container" style="display:none;">
               <?php
                  $files = glob( $strDirModuleHTML5.'img/motifs/svg/*.{svg}', GLOB_BRACE );
                  foreach( $files AS $file ) {
                  	echo '<div title="Ajouter un motif sur votre plaque" class="motif" id="' . basename( $file ) . '" style="background-size:100%;background-image: url(' . str_replace($strDirRoot, '/', $file) . ');"></div>';
                  }
                  ?>
            </div>
         </div>
         <div id="motif_custom">
            <button style="margin-right: 10px;" id="uploadFile" title="Ajouter une image">Télécharger une image</button>
            <label style="font-size: 12px;">Quliquer sur shift pour redimensionner le pictogramme</label>
         </div>
         <div id="uploadFileContent">
            <div>
               <h1>Télécharger une image</h1>
               <p>Choisissez une image au format <strong>.gif</strong> ou <strong>.png</strong> sur fond transparent et sans bordure de préférence.<br>Si votre image dispose d'un fond (blanc ou de couleur) et d'une bordure, ceux-ci seront imprimés*.</p>
               <p>
               <div style="float:left;width:auto;margin-left:40px;">
                  <span style="font-size:12px;font-weight:bold">Image sur fond transparent</span><br>
                  <img style="border: 1px solid #ccc;" src="<?=$strUrlModuleHTML5?>img/fond_transparent_small.jpg" alt="">
               </div>
               <div style="float:left;width:auto;margin-left:40px;">
                  <span style="font-size:12px;font-weight:bold">Image sur fond plein</span><br>
                  <img style="border: 1px solid #ccc;" src="<?=$strUrlModuleHTML5?>img/fond_couleur_small.jpg" alt="">
               </div>
               <div style="clear:both"></div>
               <div id="progressbar" style="display:none" max="100" value="0"></div>
               <button class="dlbutton download" style="float:none;margin-top:10px">Télécharger</button>
               </p>
               <p><em style="font-size:10px">*Sous réserve de faisabilité</em></p>
               <div class="hiddenfile">
                  <input name="imageupload" type="file" id="fileinput"/>
               </div>
            </div>
         </div>
      </div>
      <div id="block_right4">
         <div id="conftabs" style="overflow: hidden;">
            <ul>
               <li><a href="#element">Couleurs</a></li>
               <li><a href="#fond">Fond</a></li>
               <li><a href="#calques">Calques</a></li>
            </ul>
            <div id="element">
               <p style="font-weight:bold;color:#c70077 !important">Couleur de l'élément sélectionné <em style="font-size:10px">(entouré en rouge sur l'aperçu)</em></p>
               <div id="colorpicker-element">
                  <div style="width:134px;padding:4px 0 0 7px;float:left">
                     <?php
                        while ($color=$DB_site->fetch_array($couleursData)) {
                        	echo '<div data-hexcolor="' . $color['hexa'] . '" class="colorblock" title="' . $color['nom'] . '" style="background-color:' . $color['hexa'] . '"></div>';
                        }
                        ?>
                  </div>
                  <div id="elemColor"></div>
               </div>
            </div>
            <div id="fond">
               <p style="font-weight:bold;color:#c70077 !important;font-size:12px">Couleur de fond</p>
               <div id="colorpicker-fond">
                  <div style="width:134px;padding:4px 0 0 7px;float:left">
                     <?php
                        while ($color=$DB_site->fetch_array($couleursData2)) {
                        	echo '<div data-hexcolor="' . $color['hexa'] . '" class="colorblock" title="' . $color['nom'] . '" style="background-color:' . $color['hexa'] . '"></div>';
                        }
                        ?>
                  </div>
                  <div id="fondColor"></div>
               </div>
            </div>
            <div id="calques">
               <ul id="calques">
               </ul>
            </div>
         </div>
      </div>
      <div id="block_right4_2" style="position:relative;background-color:#F4F4F4">
         <div class="step_container" style="width:190px"><span class="step">4</span><span class="step_title">Dupliquer / Aligner / Inverser</span></div>
         <div id="align_container">
            <div id="duplicate" class="alignmove" title="Dupliquer l'élément"></div>
            <div id="center_h" class="alignmove" title="Centrer horizontalement"></div>
            <div id="center_v" class="alignmove" title="Centrer verticalement"></div>
            <div id="center" class="alignmove" title="Centrer horizontalement et verticalement"></div>
            <div id="move_left" class="alignmove" title="Déplacer à gauche"></div>
            <div id="move_right" class="alignmove" title="Déplacer à droite"></div>
            <div id="move_top" class="alignmove" title="Déplacer vers le haut"></div>
            <div id="move_bottom" class="alignmove" title="Déplacer vers le bas"></div>
            <div id="flip_vertical" class="alignmove" title="Rotation verticale"></div>
            <div id="flip_horizontal" class="alignmove" title="Rotation horizontale"></div>
         </div>
         <!--<div class="sizes" style="position: absolute;right: 0;top: 13px;">
            <label for="sizes" style="font-size:12px;cursor:pointer">Afficher les dimensions</label><input id="sizes" type="checkbox" checked style="cursor:pointer">
            </div>-->
      </div>
      <div id="block_right6">
         <div style="width:150px;position:relative;z-index:10;height:40px;margin:5px auto 0 auto;background:#70c007"><span style="display:block;padding-top:8px;color:#fff;font-size:18px">NOTRE PRIX</span></div>
         <div id="prix">
            <div id="prix_container">
               <span id="prix_ht"><span>0.00</span> € HT</span>
               <span id="prix_ttc">Soit <span>0.00</span> € TTC</span>
            </div>
         </div>
         <span style="text-transform:uppercase;font-size:14px;color:#70ad24;font-weight:bold">Expédition prévue le <?php echo date( 'd/m/Y', strtotime( "+2 days" ) ); ?></span>
      </div>
      <div id="block_right7">
         
         <!-- <a href="#" id="pdf_button" style="display:block">pdf</a> -->
         <a href="#" id="addpanier_button">
         	<img src="template-add_to_cart.png" alt="Ajouter au panier" />
         </a>
         <div id="div_export"> </div>
         <form id="formu_add_panier_banderole" method="post" action="cde1.php">
            <input type="hidden" name="action" id="action" value="addpanier">
            <input type="hidden" name="artid" id="add_artid" value="">
            <input type="hidden" name="articlemoduleid" id="add_articlemoduleid" value="1">
            <input type="hidden" name="prix_ht" id="add_prix_ht" value="">
            <input type="hidden" name="prix_ttc" id="add_prix_ttc" value="">
            <input type="hidden" name="longueur" id="add_longueur" value="">
            <input type="hidden" name="hauteur" id="add_hauteur" value="">
            <input type="hidden" name="url_image" id="add_url_image" value="">
            <input type="hidden" name="url_pdf" id="add_url_pdf" value="">
            <input type="hidden" name="url_image_uploaded" id="add_url_image_uploaded" value="">
            <input type="hidden" name="qte" id="qte" value="1"> 			
         </form>
      </div>
   </div>
</div>
<script type="text/javascript">
   var productID = "<?php echo $productID; $_SESSION['prodID'] = $productID; ?>";
   var tvaNormal = "<?php echo $taxeTVANormal ?>";
   var strUrlModuleHTML5 = "<?= $strUrlModuleHTML5 ?>";
   var moduleID = "<?php echo  $idmodule ?>";
   var host  = "<?php echo $host."/".$racinePdf ?>";
</script>
<!-- <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script> -->
<script src="<?= $strUrlModuleHTML5 ?>js/jquery.min.js"></script>
<script src="<?= $strUrlModuleHTML5 ?>js/jquery-ui.min.js"></script>
<script src="<?= $strUrlModuleHTML5 ?>js/spectrum.js"></script>
<script src="<?= $strUrlModuleHTML5 ?>js/jquery.ui.rotatable.min.js"></script>
<script src="<?= $strUrlModuleHTML5 ?>js/jquery.mCustomScrollbar.concat.min.js"></script>
<script src="<?= $strUrlModuleHTML5 ?>js/jquery.qtip.min.js"></script>
<script src="<?= $strUrlModuleHTML5 ?>js/ddlist.jquery.min.js"></script>
<script src="<?= $strUrlModuleHTML5 ?>js/hopscotch.min.js"></script>
<script src="<?= $strUrlModuleHTML5 ?>js/ajaxq.js"></script>
<script type="text/javascript" src="<?= $strUrlModuleHTML5 ?>fancybox/jquery.fancybox.pack.js?v=2.1.5"></script>
<script type="text/javascript" src="<?= $strUrlModuleHTML5 ?>fancybox/jquery.mousewheel-3.0.6.pack.js"></script>
<script src="<?= $strUrlModuleHTML5 ?>js/sweet-alert.min.js"></script>
<script src="<?= $strUrlModuleHTML5 ?>js/jquery-collision.min.js"></script>
<script src="<?= $strUrlModuleHTML5 ?>js/fabric.js"></script>
<script src="<?= $strUrlModuleHTML5 ?>js/html2canvas.js"></script>
<script src="<?= $strUrlModuleHTML5 ?>js/colpick.js"></script>
<script src="<?= $strUrlModuleHTML5 ?>js/lobibox.js"></script>
<script src="<?= $strUrlModuleHTML5 ?>js/banderoles.js?<?php echo mt_rand(); ?>"></script>
<?php /** <script src="<?= $strUrlModuleHTML5 ?>js/html2canvas.svg.js"></script> **/ ?> 