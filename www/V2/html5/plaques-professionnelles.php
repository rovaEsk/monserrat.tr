<?phpheader('Content-Type: text/html; charset=utf-8');session_start();if ( isset( $_SESSION['rapidpub']) )	session_unset( $_SESSION['rapidpub'] );/** --- includ global  ---*/   if( !isset($DB_site) ){        require_once("../global.php");}/** --- includ global  ---*/ function random_color_part() {    return str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT);}function random_color() {    return random_color_part() . random_color_part() . random_color_part();}$couleurs = $DB_site->query("SELECT * FROM couleurs ORDER BY nom ASC");$couleurs2 = $DB_site->query("SELECT * FROM couleurs ORDER BY nom ASC LIMIT 20");$couleurs3 = $DB_site->query("SELECT * FROM couleurs ORDER BY nom ASC LIMIT 20");$couleurs4 = $DB_site->query("SELECT * FROM couleurs ORDER BY nom ASC LIMIT 20");$pays = $DB_site->query_first("SELECT * FROM pays WHERE paysid = '57'");$taxeTVANormal = $pays[TVAtauxnormal];/*$statement = $db->prepare("SELECT * FROM couleurs ORDER BY nom ASC");$statement->execute();$couleurs = $statement->fetchAll();$statement2 = $db->prepare("SELECT * FROM couleurs ORDER BY nom ASC LIMIT 20");$statement2->execute();$couleurs2 = $statement2->fetchAll();*/$productID = md5( uniqid( rand(), true ) );$_SESSION['prodID'] = $productID;$_SESSION['prodEpaisseur'] = 3;if (strstr($_SERVER['PHP_SELF'], "V2")) {    $racine = "/V2";    $racinePdf = "V2";} else {    $racine = "/";    $racinePdf = "";}$strDirRoot = $_SERVER['DOCUMENT_ROOT'];$strUrlModuleHTML5 = "$racine/html5/";if ($racine == "/V2") {	$strDirModuleHTML5 = $strDirRoot . "V2/html5/";} else {	$strDirModuleHTML5 = $strDirRoot . "html5/";}   /** ----- get module data feature ----- **/   $articlemoduleid = 7;   $moduleFeatureData = $DB_site->query_first("SELECT * FROM articlemodule WHERE articlemoduleid=$articlemoduleid");   $idmodule = $moduleFeatureData[articlemoduleid];   $textemodule = $moduleFeatureData[textemodule];   $libelle= $moduleFeatureData[libelle];   $imagemoduleExtension= $moduleFeatureData[imagemodule];   $imageModule  = "http://".$host."/admin/assets/img/modulehtml5/". $idmodule .".".$imagemoduleExtension;   $imageArticleModule = "";   if($imagemoduleExtension !== null){       $imageArticleModule =  '<img src="'.$imageModule.'" alt="Créer '.$libelle.' en ligne" title="Créer $libelle en ligne" />';    }else{       $imageArticleModule = '<img src="http://placehold.it/130x130" alt="Créer test banderole en ligne" title="Produit préféré de nos clients" />';          }       /** ----- get module data feature----- **/error_reporting(0);?> <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/blitzer/jquery-ui.css" /> <!--link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>css/jquery-ui.css" /--> <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'><link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>css/ddlist.jquery.css" type="text/css" media="all" /><link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>css/plaquespro.css?<?php echo mt_rand(); ?>" type="text/css" media="all" /><link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>css/jquery.mCustomScrollbar.min.css" type="text/css" media="all" /><link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>css/jquery.qtip.min.css" type="text/css" media="all" /><link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>css/spectrum.css" type="text/css" media="all" /><link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>css/hopscotch.min.css" type="text/css" media="all" /><link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>css/sweet-alert.css" type="text/css" media="all" /><link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>css/colpick.css" type="text/css" media="all" /><link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>css/lobibox.css" type="text/css" media="all" /><link href="<?= $strUrlModuleHTML5 ?>skins/flat/red.css" rel="stylesheet"><link rel="stylesheet" href="<?= $strUrlModuleHTML5 ?>fancybox/jquery.fancybox.css?v=2.1.5" type="text/css" media="screen" /><div id="plaques_config">	<div class="container-left">		<div id="block_left">    		<div id="text_content">                <div class="img">                   <?php echo $imageArticleModule ?>                </div>                <div class="text">                   <h1><?php echo $libelle ?></h1>                   <p class="mCustomScrollbar" data-mcs-theme="dark">                      <?php echo $textemodule ?>                   </p>                </div>             </div>			<div id="apercu_content">				<div id="plaque_bg">					<div id="fixations_plaque" >						<div class="fixation fixation_hg"></div><div class="fixation fixation_hd"></div><div class="fixation fixation_bg"></div><div class="fixation fixation_bd"></div>					</div>					<div id="layout_tsprt_ppcf" style="display:none"></div>					<div id="plaque_content">											</div>				</div>			</div>		</div>	</div>	<div class="container-right">		<div id="block_right">			<div id="config_right">				<div id="dimensions">					<div class="step_container" style="margin-left:5px"><span class="step">1</span></div>					<span class="dimensionstext">Dimensions <span class="small">en cm</span></span>					<select id="dimensionssel" name="dimensionssel">						<?php							/*                            $statement = $db->prepare("select * from tarifs_plaques order by id");							$statement->execute();							$row = $statement->fetchAll();							echo '<option value="">Dimensions</option>';							foreach ( $row AS $dim ) {								$dimensions = explode('x', $dim['dimensions']);								echo '<option value="' . $dim['id'] . '">' . $dimensions[0]. ' x ' . $dimensions[1] .' cm</option>';							}                            */                            $statement = $DB_site->query("select * from  categorie_dimension_module order by categoriedimensionid  LIMIT 0, 12");                            echo '<option value="0">Dimensions</option>';                            while ($dim=$DB_site->fetch_array($statement)) {								echo '<option value="' . $dim['categoriedimensionid'] . '">' . $dim['dimension_prix'] .' cm</option>';							}                            /*                            $statement = $DB_site->query("select * from tarifs_plaques order by id");							echo '<option value="">Dimensions</option>';							while ($dim=$DB_site->fetch_array($statement)) {								$dimensions = explode('x', $dim['dimensions']);								echo '<option value="' . $dim['id'] . '">' . $dimensions[0]. ' x ' . $dimensions[1] .' cm</option>';							}*/						?>					</select>				</div>				<div id="matierecontainer">					<div class="step_container" style="width:160px"><span class="step">2</span><span class="step_title">Choisir une matière</span></div>					<select id="matiere" name="matiere">						<?php							/*                            $statement = $db->prepare("select * from matieres where actif = 1 order by tri");							$statement->execute();							$row = $statement->fetchAll();							foreach ( $row AS $matiere ) {								echo '<option value="' . $matiere['id'] . '">' . $matiere['nom']. '</option>';							}                            */                            /*                            $statement = $DB_site->query("select * from  categorie_regleprix_module order by categorieregleprixid  LIMIT 0, 8");							echo '<option value="0">Matière</option>';                            while ($matiere=$DB_site->fetch_array($statement)) {								echo '<option value="' . $matiere['categorieregleprixid'] . '">' . $matiere['categorieregleprix']. '</option>';							}*/                            $statement = $DB_site->query("select * from matieres where actif = 1 order by tri");							echo '<option value="0">Matière</option>';                            if ($DB_site->num_rows($statement) > 0){                                while ($matiere=$DB_site->fetch_array($statement)) {								    echo '<option value="' . $matiere['id'] . '" ' . ($matiere['id'] == "1" ? "selected" : "")  . '  >' . $matiere['nom']. '</option>';                                }                            } 						?>					</select>					<div class="epaisseur" style="clear:both;display:none;">						<span class="sectionTitre" style="margin-bottom:3px;color: #c70077;font-weight: bold;font-size: 12px;display: block;margin-left: 5px;">Épaisseur de la plaque</span>						<div class="radioContainer" style="padding-left:55px">							<?php                                /*								$statement = $db->prepare("SELECT * FROM epaisseurs_plaques WHERE id_matiere = :id_matiere ORDER BY epaisseur ASC");								$statement->execute(array(':id_matiere' => 3));								$row = $statement->fetchAll();								$i = 0;								foreach ( $row AS $epaisseur ) {									if ( $i == 0)										echo '<input class="epaisseur" name="epaisseur_lettres" data-epaisseur="' . $epaisseur['epaisseur'] . '" type="radio" id="' . $epaisseur['id'] . '" value="' . $epaisseur['id'] . '" checked><label for="' . $epaisseur['id'] . '" style="margin:0 10px 0 5px;font-weight: bold;font-size: 12px;cursor:pointer">' . $epaisseur['nom'] . '</label>';									else										echo '<input class="epaisseur" name="epaisseur_lettres" data-epaisseur="' . $epaisseur['epaisseur'] . '" type="radio" id="' . $epaisseur['id'] . '" value="' . $epaisseur['id'] . '"><label for="' . $epaisseur['id'] . '" style="margin:0 10px 0 5px;font-weight: bold;font-size: 12px;cursor:pointer">' . $epaisseur['nom'] . '</label>';									$i++;								}                                */                                $statement = $DB_site->query("SELECT * FROM epaisseurs_plaques WHERE id_matiere = 3 ORDER BY epaisseur ASC");								$i = 0;								while ($epaisseur=$DB_site->fetch_array($statement)) {									if ( $i == 0)										echo '<input class="epaisseur" name="epaisseur_lettres" data-epaisseur="' . $epaisseur['epaisseur'] . '" type="radio" id="' . $epaisseur['id'] . '" value="' . $epaisseur['id'] . '" checked><label for="' . $epaisseur['id'] . '" style="margin:0 10px 0 5px;font-weight: bold;font-size: 12px;cursor:pointer">' . $epaisseur['nom'] . '</label>';									else										echo '<input class="epaisseur" name="epaisseur_lettres" data-epaisseur="' . $epaisseur['epaisseur'] . '" type="radio" id="' . $epaisseur['id'] . '" value="' . $epaisseur['id'] . '"><label for="' . $epaisseur['id'] . '" style="margin:0 10px 0 5px;font-weight: bold;font-size: 12px;cursor:pointer">' . $epaisseur['nom'] . '</label>';									$i++;								}							?>						</div>					</div>				</div>			</div>		</div>		<div id="block_right2">			<div id="text_right">				<div class="text_left"></div><input type="text" name="textecfg" id="textecfg" value="" placeholder="Insérer un texte" />                                <!--label style="float: left; font-size: 12px;line-height: 30px;">Selectionnez la taille</label-->                                <input type="hidden" min="1" max="1000" name="textecfgsize" id="textecfgsize" value="12" placeholder="" />                                <button id="add_text" title="Ajouter le texte saisi">ok</button>			</div>			<div id="policecontainer">				<select id="police" name="police">					<?php						$polices = array();						if ( $handle = opendir( './fonts' ) ) {							while ( false !== ( $entry = readdir( $handle ) ) ) {								if ( $entry != "." && $entry != ".." ) {									$polices[] = $entry;								}							}							closedir( $handle );							sort( $polices );							foreach ( $polices as $police ) {								$fontname = explode( '.', $police );								$im = imagecreatetruecolor(100, 30);								$white = imagecolorallocate( $im, 255, 255, 255);								$grey = imagecolorallocate( $im, 128, 128, 128);								$black = imagecolorallocate($im, 0, 0, 0);								imagefilledrectangle($im, 0, 0, 99, 29, $white);								imagettftext($im, 10, 0, 11, 21, $grey, 'fonts/' . $police, str_replace( '_', ' ', $fontname[0] ) );								imagettftext($im, 10, 0, 10, 20, $black, 'fonts/' . $police, str_replace( '_', ' ', $fontname[0] ) );								ob_start();								imagepng( $im );								$img = ob_get_clean();								echo '<option value="' .$police . '">' . str_replace( '_', ' ', $fontname[0] ) . '</option>';								imagedestroy( $im );							}						}					?>				</select>			</div>			<style type="text/css">				<?php foreach ($polices as $police): ?>				<?php $fontname = explode( '.', $police ); ?>				@font-face {					font-family: '<?php echo $fontname[0]; ?>';					src: url('./fonts/<?php echo $police ?>');				}				<?php endforeach; ?>			</style>			<div class="step_container" style="width:145px"><span class="step">3</span><span class="step_title">Choisir une police</span></div>		</div>		<div id="block_right3" style="background-color:#F4F4F4">			<div style="margin-bottom:10px;padding-top:5px"> 				<div class="step_container" style="width:190px;margin-right: 15px;"><span class="step">4</span><span class="step_title">Insérer un pictogramme</span></div> 				<select id="themes" name="themes"> 					<option value="divers" selected="selected">Divers</option> 					<option value="vehicules">Véhicules</option> 					<option value="personnages">Personnages</option> 					<option value="signes">Signes</option> 					<option value="outils">Outils</option>                                        <option value="svg">svg</option> 				</select> 			</div> 			<div id="motifs_contain"> 				<div id="divers_container" class="motifs_container"> 					<?php 						$files = glob( $strDirModuleHTML5.'img/motifs/divers/*.{png}', GLOB_BRACE ); 						foreach( $files AS $file ) { 							echo '<div title="Ajouter un motif sur votre plaque" class="motif" id="' . basename( $file ) . '" style="background-size:100%;background-image: url(' . str_replace($strDirRoot, '/', $file) . ');"></div>'; 						} 					?> 				</div> 				<div id="vehicules_container" class="motifs_container" style="display:none;"> 					<?php 						$files = glob( $strDirModuleHTML5.'img/motifs/vehicules/*.{png}', GLOB_BRACE ); 						foreach( $files AS $file ) { 							echo '<div title="Ajouter un motif sur votre plaque" class="motif" id="' . basename( $file ) . '" style="background-size:100%;background-image: url(' . str_replace($strDirRoot, '/', $file) . ');"></div>'; 						} 					?> 				</div> 				<div id="personnages_container" class="motifs_container" style="display:none;"> 					<?php 						$files = glob( $strDirModuleHTML5.'img/motifs/personnages/*.{png}', GLOB_BRACE ); 						foreach( $files AS $file ) { 							echo '<div title="Ajouter un motif sur votre plaque" class="motif" id="' . basename( $file ) . '" style="background-size:100%;background-image: url(' . str_replace($strDirRoot, '/', $file) . ');"></div>'; 						} 					?> 				</div> 				<div id="signes_container" class="motifs_container" style="display:none;"> 					<?php 						$files = glob( $strDirModuleHTML5.'img/motifs/signes/*.{png}', GLOB_BRACE ); 						foreach( $files AS $file ) { 							echo '<div title="Ajouter un motif sur votre plaque" class="motif" id="' . basename( $file ) . '" style="background-size:100%;background-image: url(' . str_replace($strDirRoot, '/', $file) . ');"></div>'; 						} 					?> 				</div> 				<div id="outils_container" class="motifs_container" style="display:none;"> 					<?php 						$files = glob( $strDirModuleHTML5.'img/motifs/outils/*.{png}', GLOB_BRACE ); 						foreach( $files AS $file ) { 							echo '<div title="Ajouter un motif sur votre plaque" class="motif" id="' . basename( $file ) . '" style="background-size:100%;background-image: url(' . str_replace($strDirRoot, '/', $file) . ');"></div>'; 						} 					?> 				</div>                            <div id="svg_container" class="motifs_container" style="display:none;"> 					<?php 						$files = glob( $strDirModuleHTML5.'img/motifs/svg/*.{svg}', GLOB_BRACE ); 						foreach( $files AS $file ) { 							echo '<div title="Ajouter un motif sur votre plaque" class="motif" id="' . basename( $file ) . '" style="background-size:100%;background-image: url(' . str_replace($strDirRoot, '/', $file) . ');"></div>'; 						} 					?> 				</div> 			</div>			<div id="motif_custom">                            <button style="margin-right: 10px;" id="uploadFile" title="Ajouter une image">Télécharger une image</button>                                <label style="font-size: 12px;">Quliquer sur shift pour redimensionner le pictogramme</label>			</div>			<div id="uploadFileContent">				<div>					<h1>Télécharger une image</h1>					<p>Choisissez une image au format <strong>.gif</strong> ou <strong>.png</strong> sur fond transparent et sans bordure de préférence.<br>Si votre image dispose d'un fond (blanc ou de couleur) et d'une bordure, ceux-ci seront imprimés*.</p>					<p>						<div style="float:left;width:auto;margin-left:40px;">							<span style="font-size:12px;font-weight:bold">Image sur fond transparent</span><br>							<img style="border: 1px solid #ccc;" src="<?= $strUrlModuleHTML5 ?>img/fond_transparent_small.jpg" alt="">						</div>						<div style="float:left;width:auto;margin-left:40px;">							<span style="font-size:12px;font-weight:bold">Image sur fond plein</span><br>							<img style="border: 1px solid #ccc;" src="<?= $strUrlModuleHTML5 ?>img/fond_couleur_small.jpg" alt="">						</div>						<div style="clear:both"></div>						<div id="progressbar" style="display:none" max="100" value="0"></div>						<button class="dlbutton download" style="float:none;margin-top:10px">Télécharger</button>					</p>					<p><em style="font-size:10px">*Sous réserve de faisabilité</em></p>					<div class="hiddenfile">						<input name="imageupload" type="file" id="fileinput"/>					</div>				</div>			</div>		</div>		<div id="block_right4">			<div id="conftabs" style="overflow: hidden;">				<ul>					<li><a href="#element">Couleurs</a></li>					<li><a href="#fond">Fond</a></li>					<!--<li><a href="#bordure">Bordure</a></li>-->					<li><a href="#calques">Calques</a></li>				</ul>				<div id="element">					<div id="hidecolorpicker1" style="margin-top:10px;display:none;font-weight:bold;color:#c70077 !important;font-size:12px">						<div id="black-col" data-hexcolor="#000" class="colorblock" style="background-color:#000" title="#000"></div>						<div id="gold-col" data-hexcolor="#d5b36a" class="colorblock" style="background-color:#d5b36a;border:1px solid #ccc" title="Or"></div>						<div id="white-col" data-hexcolor="#fff" class="colorblock" style="background-color:#fff;border:1px solid #ccc" title="Blanc"></div>					</div>					<div id="showcolorpicker1">						<p style="font-weight:bold;color:#c70077 !important">Couleur de l'élément sélectionné <em style="font-size:10px">(entouré en rouge sur l'aperçu)</em></p>						<div id="colorpicker-element">							<div id="color40" style="width:134px;padding:4px 0 0 7px;float:left">								<?php									while ($color = $DB_site->fetch_array($couleurs)) {										echo '<div data-hexcolor="' . $color['hexa'] . '" class="colorblock" title="' . $color['nom'] . '" style="background-color:' . $color['hexa'] . '"></div>';									}								?>							</div>							<div id="color60" style="width:350px;padding:4px 0 0 7px;float:left">								<?php									while ($color = $DB_site->fetch_array($couleurs2)) {										echo '<div data-hexcolor="' . $color['hexa'] . '" class="colorblock" title="' . $color['nom'] . '" style="background-color:' . $color['hexa'] . '"></div>';									}								?>							</div>							<div id="elemColor"></div>						</div>						<div id="colorpicker-element2">							<div style="width:134px;padding:4px 0 0 7px;float:left">								<?php									while ($color = $DB_site->fetch_array($couleurs3)) {										echo '<div data-hexcolor="' . $color['hexa'] . '" class="colorblock" title="' . $color['nom'] . '" style="background-color:' . $color['hexa'] . '"></div>';									}								?>							</div>						</div>					</div>				</div>				<div id="fond">					<div id="hidecolorpicker2" style="margin-top:10px;font-weight:bold;color:#c70077 !important;font-size:12px">La matière sélectionnée ne permet pas de choisir de couleur de fond</div>					<div id="showcolorpicker2" style="display:none;">						<p style="font-weight:bold;color:#c70077 !important;font-size:12px">Couleur de fond</p>						<div id="colorpicker-fond">							<div style="width:134px;padding:4px 0 0 7px;float:left">								<?php									while ($color = $DB_site->fetch_array($couleurs4)) {										echo '<div data-hexcolor="' . $color['hexa'] . '" class="colorfondblock" style="background-color:' . $color['hexa'] . '" title="' . $color['nom'] . '"></div>';									}								?>							</div>							<div id="fondColor"></div>						</div>					</div>				</div>				<!--<div id="bordure">					<p style="font-weight:bold;color:#c70077 !important">Bordure de plaque</p>					<div id="bordure_cfg">						<label for="epaisseur">Épaisseur de la bordure (en mm)</label><input id="epaisseur" type="number" value="0" style="1px solid #CCC;"/>						<div class="spacer10"></div>						<label for="distance">Distance du bord (en mm)</label><input id="distance" type="number" value="0" style="1px solid #CCC;"/><br>											</div>				</div>-->				<div id="calques">					<ul id="calques">					</ul>				</div>			</div>			<div id="block_right4_2" style="position:relative;background-color:#F4F4F4">				<div class="step_container" style="width:230px"><span class="step">5</span><span class="step_title">Dupliquer / Aligner / Inverser</span></div>				<div id="align_container">					<div id="duplicate" class="alignmove" title="Dupliquer l'élément"></div>					<div id="center_h" class="alignmove" title="Centrer horizontalement"></div>					<div id="center_v" class="alignmove" title="Centrer verticalement"></div>					<div id="center" class="alignmove" title="Centrer horizontalement et verticalement"></div>					<div id="move_left" class="alignmove" title="Déplacer à gauche"></div>					<div id="move_right" class="alignmove" title="Déplacer à droite"></div>					<div id="move_top" class="alignmove" title="Déplacer vers le haut"></div>					<div id="move_bottom" class="alignmove" title="Déplacer vers le bas"></div>					<div id="flip_vertical" class="alignmove" title="Rotation verticale"></div>					<div id="flip_horizontal" class="alignmove" title="Rotation horizontale"></div>				</div>				<!--<div class="sizes" style="position: absolute;right: 0;top: 13px;">					<label for="sizes" style="font-size:12px;cursor:pointer">Afficher les dimensions</label><input id="sizes" type="checkbox" checked style="cursor:pointer">				</div>-->			</div>			<div id="block_right5">				<div class="step_container" style="width:160px"><span class="step">6</span><span class="step_title">Choisir les fixations</span></div>				<div id="fixations">					<a href="#" id="aucune" class="selectfixation" style="margin-left: 50px;"><span style="display:block;margin-top:13px">Aucune fixation</span></a>					<a href="#" id="fix1" class="selectfixation selected"><img src="<?= $strUrlModuleHTML5 ?>img/fix1.png" alt="" style="margin-top:10px;width: 40px;" /></a>					<a href="#" id="fix2" class="selectfixation"><img src="<?= $strUrlModuleHTML5 ?>img/fix2.png" alt="" style="margin-top:10px;width: 40px;" /></a>					<a href="#" id="adhesifs" class="selectfixation"><span style="display:block;margin-top:23px">Adhésifs</span></a>				</div>			</div>			<div id="block_right6">				<div style="width:150px;position:relative;z-index:10;height:40px;margin:5px auto 0 auto;background:#70c007"><span style="display:block;padding-top:8px;color:#fff;font-size:18px">NOTRE PRIX</span></div>				<div id="prix">					<div id="prix_container">						<span id="prix_ht"><span>0.00</span> € HT</span>						<span id="prix_ttc">Soit <span>0.00</span> € TTC</span>					</div>				</div>				<span style="text-transform:uppercase;font-size:14px;color:#70ad24;font-weight:bold">Expédition prévue le <?php echo date( 'd/m/Y', strtotime( "+2 days" ) ); ?></span>			</div>			<div id="block_right7">	            <!--a href="#" id="pdf_button" style="display:block">pdf</a-->	 			<a href="#" id="addpanier_button">	 				<img src="template-add_to_cart.png" alt="Ajouter au panier" />	 			</a>	 			<div id="div_export"> </div> 				 			<form id="formu_add_panier_plaquespro" method="post" action="cde1.php">	 				<input type="hidden" name="action" id="action" value="addpanier">	 				<input type="hidden" name="artid" id="add_artid" value="">	 				<input type="hidden" name="articlemoduleid" id="add_articlemoduleid" value="7">	 				<input type="hidden" name="prix_ht" id="add_prix_ht" value="">	 				<input type="hidden" name="prix_ttc" id="add_prix_ttc" value="">	 				<input type="hidden" name="longueur" id="add_longueur" value="">	 				<input type="hidden" name="hauteur" id="add_hauteur" value="">	 				<input type="hidden" name="url_image" id="add_url_image" value="">                	                <input type="hidden" name="url_pdf" id="add_url_pdf" value="">	                <input type="hidden" name="url_image_uploaded" id="add_url_image_uploaded" value="">	 				<input type="hidden" name="qte" id="qte" value="1"> 				 			</form> 				 		</div>		</div>			</div></div><div style="display: none" id="svgblock"></div><script type="text/javascript">var productID = "<?php echo $productID; ?>";var strUrlModuleHTML5 = "<?= $strUrlModuleHTML5 ?>";var tvaNormal = "<?php echo $taxeTVANormal ?>";var host  = "<?php echo $host."/".$racinePdf ?>";</script> <script src="<?= $strUrlModuleHTML5 ?>js/jquery.min.js"></script> <script src="<?= $strUrlModuleHTML5 ?>js/jquery-ui.min.js"></script><script src="<?= $strUrlModuleHTML5 ?>js/spectrum.js"></script><script src="<?= $strUrlModuleHTML5 ?>js/jquery.ui.rotatable.min.js"></script><script src="<?= $strUrlModuleHTML5 ?>js/jquery.mCustomScrollbar.concat.min.js"></script><script src="<?= $strUrlModuleHTML5 ?>js/jquery.qtip.min.js"></script><script src="<?= $strUrlModuleHTML5 ?>js/ddlist.jquery.min.js"></script><script src="<?= $strUrlModuleHTML5 ?>js/hopscotch.min.js"></script> <script src="<?= $strUrlModuleHTML5 ?>js/ajaxq.js"></script><script type="text/javascript" src="<?= $strUrlModuleHTML5 ?>fancybox/jquery.fancybox.pack.js?v=2.1.5"></script><script type="text/javascript" src="<?= $strUrlModuleHTML5 ?>fancybox/jquery.mousewheel-3.0.6.pack.js"></script><script src="<?= $strUrlModuleHTML5 ?>js/sweet-alert.min.js"></script><script src="<?= $strUrlModuleHTML5 ?>js/jquery-collision.min.js"></script><script src="<?= $strUrlModuleHTML5 ?>js/html2canvas.js"></script><script src="<?= $strUrlModuleHTML5 ?>js/colpick.js"></script><script src="<?= $strUrlModuleHTML5 ?>js/lobibox.js"></script><script src="<?= $strUrlModuleHTML5 ?>js/icheck.min.js"></script><!-- <script src="<?= $strUrlModuleHTML5 ?>js/plaquesfunctions.js"></script> --><script src="<?= $strUrlModuleHTML5 ?>js/plaquespro.js?<?php echo mt_rand();?>"></script><!-- <script src="<?= $strUrlModuleHTML5 ?>js/saveSvgAsPng.js"></script><script src="<?= $strUrlModuleHTML5 ?>js/jspdf.debug.js"></script><script src="<?= $strUrlModuleHTML5 ?>js/svg_to_pdf.js"></script> --><style type="text/css">    div.elementContainer img {        max-width: 100%;        /*height: 100% !important;*/    }    #font-demo-list{visibility: hidden;position:fixed;top:0px;left:0px;}	.textDimensionCalculation {		position: fixed;		visibility: hidden;		height: auto;		width: auto;		white-space: nowrap;		position: absolute;		bottom: 50px;		background: red;		color: #fff;		font-size: 63px;		z-index: -50;	}</style><div id="font-demo-list">    <?php foreach ($polices as $police): ?>        <?php $fontname = explode( '.', $police ); ?>        <div style="font-family:<?php echo $fontname[0]; ?>" class="font-demo-test"><?php echo $fontname[0]; ?></div>    <?php endforeach; ?></div><div class="textDimensionCalculation"></div>