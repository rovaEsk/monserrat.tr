<?php
	include "includes/header.php";

	/*
	 * Parameters sent to the server

	The following information is sent to the server for each draw request. Your server-side script must use this information to obtain the data required for the draw.
	Type 	Name 				Info
	int 	iDisplayStart 		Display start point in the current data set.
	int 	iDisplayLength 		Number of records that the table can display in the current draw. It is expected that the number of records returned will be equal to this number, unless the server has fewer records to return.
	int 	iColumns 			Number of columns being displayed (useful for getting individual column search info)
	string 	sSearch 			Global search field
	bool 	bRegex 				True if the global filter should be treated as a regular expression for advanced filtering, false if not.
	bool 	bSearchable_(int) 	Indicator for if a column is flagged as searchable or not on the client-side
	string 	sSearch_(int) 		Individual column filter
	bool 	bRegex_(int) 		True if the individual column filter should be treated as a regular expression for advanced filtering, false if not
	bool 	bSortable_(int) 	Indicator for if a column is flagged as sortable or not on the client-side
	int 	iSortingCols 		Number of columns to sort on
	int 	iSortCol_(int) 		Column being sorted on (you will need to decode this number for your database)
	string 	sSortDir_(int) 		Direction to be sorted - "desc" or "asc".
	string 	mDataProp_(int) 	The value specified by mDataProp for each column. This can be useful for ensuring that the processing of data is independent from the order of the columns.
	string 	sEcho 				Information for DataTables to use for rendering.
	*/
	
	
	
	while (list($key, $val) = each($_POST)) {
		${$key}=$val;
	}
	
	if($iSortingCols){
		//print_r($_POST);
	}

	$iDisplayLength = intval($_POST['iDisplayLength']);
	$iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
	$iDisplayStart = intval($_POST['iDisplayStart']);
	$sEcho = $_POST['sEcho'];
  
	$iDisplayStart = intval($_POST['iDisplayStart']);
	
	
  	$records = array();
  	$records["aaData"] = array(); 

  	

  	$status_list = array(
	    array("success" => "Pending"),
	    array("info" => "Closed"),
	    array("danger" => "On Hold"),
	    array("warning" => "Fraud")
  	);

  	if(!isset($iDisplayStart)){
  		$iDisplayStart=0;
  	}
  	
  	if(!isset($iDisplayLength)){
  		$iDisplayLength=20;
  	}
  	
  	
  	$limitlower=$iDisplayStart;
  	$perpage=$iDisplayLength;
  	
  	$sorting_col_name = "mDataProp_".$iSortCol_0;
  	
  	switch(${$sorting_col_name}){
  		case "userid" :
  			// userid
  			$orderby = "userid";
  		break;
  		case "dateins" :
  			// dateinscription
  			$orderby = "dateinscription";
  		break;
  		case "mail" :
  			// mail
  			$orderby = "mail";
  		break;
  		case "raisonsociale" :
  			// raisonsociale
  			$orderby = "raisonsociale";
  		break;
  		case "nom" :
  			// userid
  			$orderby = "nom";
  		break;
  		case "prenom" :
  			// userid
  			$orderby = "prenom";
  		break;
  		case "pays" :
  			// paysid
  			$orderby = "paysid";
  		break;
  		default:
  			$orderby = "userid";
  		break;
  	}
  	
  	if($sSortDir_0 != ""){
  		$sensorder=$sSortDir_0;
  	}else{
  		$sensorder="ASC";
  	}
  	
	/*for ($i = 0; $i <= $iColumns - 1; $i++) {
	    $nom_colonneTemp = "mDataProp_".$i;
	    $nom_colonne=${$nom_colonneTemp};
		
	    $varSearchTemp = "search_$nom_colonne";
	    $varTempSsearch = "sSearch_".$i;
	    ${$varSearchTemp} = ${$varTempSsearch};
	    
	    //echo "$nom_colonneTemp // $nom_colonne // $varSearchTemp // $varTempSsearch //".${$varSearchTemp}." = ".${$varTempSsearch};
	}*/
  	
  	
  	$where="";
  	if($search_userid != ""){
  		$where.=" AND userid like '%".securiserSql($search_userid)."%'";
  	}
  	
	if($_GET[action] == "utilisateursactifs"){
		$where .= " AND datedernieraction > '".(time()-1200)."'";
	}
  	
  	if($search_date_from != ""){  		
  		list($jour,$mois,$annee) = explode('/',$search_date_from);
  		$date_debut_search="$annee-$mois-$jour";
  		$where.=" AND dateinscription > '".securiserSql($date_debut_search)."'";
  	}
  	
  	if($search_date_to != ""){
  		list($jour,$mois,$annee) = explode('/',$search_date_to);
  		$date_fin_search="$annee-$mois-$jour";
  		$where.=" AND dateinscription < '".securiserSql($date_fin_search)."'";
  	}
  	
  	if($search_mail != ""){
  		$where.=" AND mail LIKE '%".securiserSql($search_mail)."%'";
  	}
  	
  	if($search_raisonsociale != ""){
  		$where.=" AND raisonsociale LIKE '%".securiserSql($search_raisonsociale)."%'";
  	}
  	
  	if($search_nom != ""){
  		$where.=" AND nom LIKE '%".securiserSql($search_nom)."%'";
  	}
  	if($search_prenom != ""){
  		$where.=" AND prenom LIKE '%".securiserSql($search_prenom)."%'";
  	}
  	
  	if($search_telephone != ""){
  		$where.=" AND telephone LIKE '%".securiserSql($search_telephone)."%'";
  	}
  	
  	if($search_telephone2 != ""){
  		$where.=" AND telephone2 LIKE '%".securiserSql($search_telephone2)."%'";
  	}
  	
  	if($search_siret != ""){
  		$where.=" AND siret LIKE '%".securiserSql($search_siret)."%'";
  	}
  	
  	if($search_tva != ""){
  		$where.=" AND tva LIKE '%".securiserSql($search_tva)."%'";
  	}
  	
  	if($search_adresse != ""){
  		$where.=" AND adresse LIKE '%".securiserSql($search_adresse)."%'";
  	}
  	
  	if($search_adresse2 != ""){
  		$where.=" AND adresse2 LIKE '%".securiserSql($search_adresse2)."%'";
  	}
  	
  	if($search_codepostal != ""){
  		$where.=" AND codepostal LIKE '%".securiserSql($search_codepostal)."%'";
  	}
  	
  	if($search_ville != ""){
  		$where.=" AND ville LIKE '%".securiserSql($search_ville)."%'";
  	}
  	
  	if($search_paysid != 0){
  		$where.=" AND paysid ='".securiserSql($search_paysid)."'";
  	}
  	  		
  	$rq_client=$DB_site->query("SELECT * FROM utilisateur WHERE userid>0 $where AND deleted='0' ORDER BY $orderby $sensorder LIMIT $limitlower, $perpage");
	//echo "SELECT * FROM utilisateur WHERE userid>0 $where ORDER BY $orderby $sensorder LIMIT $limitlower, $perpage";
  	
  	
  	$rq_client_all=$DB_site->query("SELECT * FROM utilisateur WHERE userid>0 $where AND deleted='0' ORDER BY $orderby $sensorder");
  	$utilisateurcount = $DB_site->num_rows($rq_client_all);
  	
  	$iTotalRecords = $utilisateurcount;
  	$end = $iDisplayStart + $iDisplayLength;
  	$end = $end > $iTotalRecords ? $iTotalRecords : $end;
  	
  	if ($utilisateurcount > 0) {
  		$i = 0 ;
  		$k=1;
  		while ($rs_client=$DB_site->fetch_array($rq_client)) {
  			$rowalt = "td_users".getrowbg();
  			$i++;
  			list($annee, $mois, $jour) = explode( "-", $rs_client[dateinscription]);

  			$date_inscription="$jour/$mois/$annee";
  			
  			$pays_user = retournerLibellePays($DB_site, $rs_client[paysid]);
  			
  			$lien_connexion = "http://$host/V2/client.htm?action=logging&cryptage=no&mail_logging=$rs_client[mail]&pass_logging=$rs_client[password]";
  			
  			$nb_commandes_client=$DB_site->query_first("SELECT COUNT(*) FROM facture WHERE userid='$rs_client[userid]'");
  			
  			if($nb_commandes_client[0] > 0){
  				$actions='
  						<a href="'.$lien_connexion.'" target="_blank" data-original-title="'.$multilangue[connexion_compte].'" data-placement="top" class="tooltips"><i class="fa fa-key" style="color : #D1B110;"></i></a>
		                 &nbsp;<a href="clients.php?action=editer&user='.$rs_client[userid].'" data-original-title="'.$multilangue[modifier].'" data-placement="top" class="tooltips"><i class="fa fa-pencil-square-o"></i></a>
		               ';
  			}else{
  				$actions='
  						<a href="'.$lien_connexion.'" target="_blank" data-original-title="'.$multilangue[connexion_compte].'" data-placement="top" class="tooltips"><i class="fa fa-key" style="color : #D1B110;"></i></a>
		                 &nbsp;<a href="clients.php?action=editer&user='.$rs_client[userid].'" data-original-title="'.$multilangue[modifier].'" data-placement="top" class="tooltips"><i class="fa fa-pencil-square-o"></i></a>
		                 &nbsp;<a href="#myModalSuppr'.$rs_client[userid].'" data-original-title="'.$multilangue[supprimer].'" data-placement="top" class="tooltips" data-toggle="modal" role="button"><i class="fa fa-times" style="color : red;"></i></a>
		  					<div aria-hidden="true" aria-labelledby="myModalSuppr'.$rs_client[userid].'" role="dialog" tabindex="-1" class="modal fade" id="myModalSuppr'.$rs_client[userid].'" style="display: none;">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<button aria-hidden="true" data-dismiss="modal" class="close" type="button"></button>
											<h4 class="modal-title" id="modalSupprTitle">'.$multilangue[suppression_client].' "'.$rs_client[prenom].' '.$rs_client[nom].'" ?</h4>
										</div>
										<div class="modal-body">
											'.$multilangue[suppression_client_infos].'
										</div>
										<div class="modal-footer">
											<button aria-hidden="true" data-dismiss="modal" class="btn default">'.$multilangue[non].'</button>
											<a href="clients.php?action=supprimer2&useridsuppr='.$rs_client[userid].'" class="btn blue">'.$multilangue[oui_supprimer].'</a>
										</div>
									</div>
								</div>
							</div>
  						';
  			}
  			
  			
  			$records["aaData"][] = array(
		      	"userid" => $rs_client[userid],
		      	"dateins" => $date_inscription,
		      	"mail" => $rs_client[mail],
		      	"raisonsociale" => $rs_client[raisonsociale],
		      	"nom" => $rs_client[nom],
		      	"prenom" => $rs_client[prenom],
		      	"pays" => $pays_user,
  				"telephone" => $rs_client[telephone],
  				"telephone2" => $rs_client[telephone2],
  				"siret" => $rs_client[siret],
  				"tva" => $rs_client[tva],
  				"adresse" => $rs_client[adresse],
  				"adresse2" => $rs_client[adresse2],
  				"codepostal" => $rs_client[codepostal],
  				"ville" => $rs_client[ville],
		      	"actions" => $actions  					
	   		);
  		}
  	}

  	if (isset($_POST["sAction"]) && $_POST["sAction"] == "group_action") {
    	$records["sStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
    	$records["sMessage"] = "Group action successfully has been completed. Well done!"; // pass custom message(useful for getting status of group actions)
  	}

  	//$records["sEcho"] = "Test sEcho";
  	$records["iTotalRecords"] = $iTotalRecords;
  	$records["iTotalDisplayRecords"] = $iTotalRecords;
  	
  	echo json_encode($records);
?>