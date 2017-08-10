<?php
include "includes/header.php";

$scriptcourant = "newsletter.php";

if(isset($action) && $action == "listemailings"){
	$iDisplayLength = intval($length);
	$iDisplayStart = intval($start);
	
	$records = array();
	$records["aaData"] = array();
	
	if(!isset($iDisplayStart))
		$iDisplayStart = 0;
	if(!isset($iDisplayLength))
		$iDisplayLength = 10;
	
	/*switch($order[0][column]){
		case "1" :
			$orderby = "old_url";
			break;
		case "2" :
			$orderby = "new_url";
			break;
		case "3" :
			$orderby = "type";
			break;
		default:
			$orderby = "old_url";
			break;
	}*/
	
	$orderby = "mailingid";
	
	$sensorder = $order[0][dir];
	
	/*$search[value] = securiserSql($search[value]);
	$where = "";
	if($search[value]!=""){
		$where = "WHERE old_url LIKE '%$search[value]%'";
		$where .= " OR new_url LIKE '%$search[value]%'";
	
	}*/
	
	$mailings = $DB_site->query("SELECT * FROM mailing ORDER BY $orderby $sensorder") ;
	
	$mailings_count = $DB_site->num_rows($mailings);
	
	$limitlower = ($iDisplayLength != -1 ? $iDisplayStart : 0);
	$perpage = ($iDisplayLength != -1 ? $iDisplayLength : $mailings_count);
	
	if ($mailings_count > 0) {
		$mailings = $DB_site->query("SELECT * FROM mailing ORDER BY $orderby $sensorder LIMIT $limitlower, $perpage");
		
		while ($mailing = $DB_site->fetch_array($mailings)){
			
			////////////
			$det_newsletter=$DB_site->query_first("SELECT * FROM newsletter WHERE newsletterid = '".$mailing[newsletterid]."'");
				
	
			$date_debut = date("d/m/Y H:i:s", $mailing[date_debut]);
			$date_fin = date("d/m/Y H:i:s", $mailing[date_fin_estimee]);
							
			//nb mail total
			// début modif benoit
			$sql_nb_mail_total= "SELECT COUNT(*) FROM mailing_liste WHERE mailingid='".$mailing[mailingid]."'";
			$rq_nb_mail_total = $DB_site->query_first($sql_nb_mail_total);
			$nb_mail_total = $rq_nb_mail_total[0];
			$rq_envoyer=$DB_site->query_first("SELECT COUNT(*) FROM mailing_envoi WHERE mailingid='".$mailing[mailingid]."' AND envoye='1' ");
			$nb_envoyer=$rq_envoyer[0];
			// fin modif benoit
				
			$nb_lus= "SELECT COUNT(*) FROM mailing_envoi WHERE mailingid='".$mailing[mailingid]."' and lu!='0'";
			$nb_lus = $DB_site->query_first($nb_lus);
			$nb_lus = $nb_lus[0];
				
			$nb_clic= "SELECT COUNT(*) FROM mailing_envoi WHERE mailingid='".$mailing[mailingid]."' and clic!='0'";
			$nb_clic = $DB_site->query_first($nb_clic);
			$nb_clic = $nb_clic[0];
			
			//variable à remplir			
			$nb_cde= "SELECT COUNT(factureid) FROM facture WHERE mailingid='".$mailing[mailingid]."' and etatid IN (1,5) AND deleted != '1'";
			$nb_cde = $DB_site->query_first($nb_cde);
			$nb_cde = $nb_cde[0];
				
			
			// Total des factures générées grâce au mailing
			$total_cdes = 0;
			
			$factures = $DB_site->query("SELECT factureid, deviseid FROM facture WHERE etatid IN (1,5) AND mailingid='".$mailing[mailingid]."' AND deleted != '1'");
			while ($facture = $DB_site->fetch_array($factures)) {
				$totaux = calculerTotalFacture($DB_site, $facture[factureid]);
				if($facture[deviseid] != 1){
					$devise = $DB_site->query_first("SELECT * FROM devise WHERE deviseid='$facture[deviseid]'");
					$totaux[totalTTC] = number_format($totaux[totalTTC]/$devise[taux],2,".","");
				}
				$total_cdes += $totaux[totalTTC];
			}	
			$mt_cde = formaterPrix($total_cdes)." €";
			///////////

			if($admin_droit[$scriptcourant][suppression]){	
	  			$actions = '<a href="#myModalSupprM'.$mailing[mailingid].'" data-original-title="'.$multilangue[supprimer].'" data-placement="top" class="tooltips" data-toggle="modal" role="button"><i class="fa fa-trash-o fs-18 font-red"></i></a>
			  					<div aria-hidden="true" aria-labelledby="myModalSupprM'.$mailing[mailingid].'" role="dialog" tabindex="-1" class="modal fade" id="myModalSupprM'.$mailing[mailingid].'" style="display: none;">
									<div class="modal-dialog">
										<div class="modal-content">
											<div class="modal-header">
												<button aria-hidden="true" data-dismiss="modal" class="close" type="button"></button>
												<h4 class="modal-title" id="modalSupprTitle">'.$multilangue[suppr_mailing].' : <b>"'.$det_newsletter[libelleadmin].'"</b> ?</h4>
											</div>
											<div class="modal-body">
												'.$multilangue[suppression_mailing_infos].'
											</div>
											<div class="modal-footer">
												<button aria-hidden="true" data-dismiss="modal" class="btn default">'.$multilangue[non].'</button>
												<a href="newsletter.php?action=supprimerM&mailingidsuppr='.$mailing[mailingid].'" class="btn blue">'.$multilangue[oui_supprimer].'</a>
											</div>
										</div>
									</div>
								</div>';
			}else{
				$actions="";
			}
  	  			
			$records["aaData"][] = array(
					"mail_type" => $det_newsletter[libelleadmin],
					"date_debut_envoi" => $date_debut,
					"date_fin_envoi" => $date_fin,
					"nb_envoyes" => $nb_envoyer,
					"nb_lus" => $nb_lus,
					"nb_clics" => $nb_clic,
					"nb_cdes" => $nb_cde,
					"montant_cdes" => $mt_cde,
					"actions" => $actions
			);
		}
	}
	
	$records["iTotalRecords"] = $mailings_count;
	$records["iTotalDisplayRecords"] = $mailings_count;
	
	echo json_encode($records);
}


if(isset($action) && $action == "listenewsletters"){
	$iDisplayLength = intval($length);
	$iDisplayStart = intval($start);

	$records = array();
	$records["aaData"] = array();

	if(!isset($iDisplayStart))
		$iDisplayStart = 0;
	if(!isset($iDisplayLength))
		$iDisplayLength = 10;

	switch($order[0][column]){
		case "1" :
		$orderby = "libelleadmin";
		break;		
		default:
		$orderby = "newsletterid";
		break;
	}

	$orderby = "newsletterid";

	$sensorder = $order[0][dir];

	$search[value] = securiserSql($search[value]);
	 $where = "";
	 if($search[value]!=""){
		$where = "WHERE old_url LIKE '%$search[value]%'";
		$where .= " OR new_url LIKE '%$search[value]%'";

	}

	$newsletters = $DB_site->query("SELECT * FROM newsletter ORDER BY $orderby $sensorder") ;

	$newsletters_count = $DB_site->num_rows($newsletters);

	$limitlower = ($iDisplayLength != -1 ? $iDisplayStart : 0);
	$perpage = ($iDisplayLength != -1 ? $iDisplayLength : $newsletters_count);

	if ($newsletters_count > 0) {
		$newsletters = $DB_site->query("SELECT * FROM newsletter ORDER BY $orderby $sensorder LIMIT $limitlower, $perpage");

		while ($newsletter = $DB_site->fetch_array($newsletters)){
		
			if($admin_droit[$scriptcourant][suppression]){
				$actions = '<a href="newsletter.php?action=modifier&newsletterid=' . $newsletter[newsletterid] . '" data-original-title="' . $multilangue[modifier] . '" data-placement="top" class="btn tooltips">';
	  			$actions .= '<i class="fa fa-edit fs-18 font-blue"></i>';
	  			$actions .= '</a>';
				$actions .= '<a href="#myModalSupprN'.$newsletter[newsletterid].'" data-original-title="'.$multilangue[supprimer].'" data-placement="top" class="tooltips" data-toggle="modal" role="button"><i class="fa fa-trash-o fs-18 font-red"></i></a>
			  					<div aria-hidden="true" aria-labelledby="myModalSupprN'.$newsletter[newsletterid].'" role="dialog" tabindex="-1" class="modal fade" id="myModalSupprN'.$newsletter[newsletterid].'" style="display: none;">
									<div class="modal-dialog">
										<div class="modal-content">
											<div class="modal-header">
												<button aria-hidden="true" data-dismiss="modal" class="close" type="button"></button>
												<h4 class="modal-title" id="modalSupprTitle">'.$multilangue[suppr_newsletter].' : <b>"'.$newsletter[libelleadmin].'"</b> ?</h4>
											</div>
											<div class="modal-body">
												'.$multilangue[suppression_newsletter_infos].'
											</div>
											<div class="modal-footer">
												<button aria-hidden="true" data-dismiss="modal" class="btn default">'.$multilangue[non].'</button>
												<a href="newsletter.php?action=supprimerN&newsletteridsuppr='.$newsletter[newsletterid].'" class="btn blue">'.$multilangue[oui_supprimer].'</a>
											</div>
										</div>
									</div>
								</div>';
			}
			
			$lien_test = '<a href="#myModalTest'.$newsletter[newsletterid].'" data-original-title="'.$multilangue[supprimer].'" data-placement="top" class="tooltips" data-toggle="modal" role="button">'.$multilangue[faire_un_test].'</a>
		  					<div aria-hidden="true" aria-labelledby="myModalTest'.$newsletter[newsletterid].'" role="dialog" tabindex="-1" class="modal fade" id="myModalTest'.$newsletter[newsletterid].'" style="display: none;">
								<div class="modal-dialog">
									<div class="modal-content">
		  								<form action="newsletter.php" method="post">
											<div class="modal-header">
												<button aria-hidden="true" data-dismiss="modal" class="close" type="button"></button>
												<h4 class="modal-title" id="modalSupprTitle">'.$multilangue[envoyer_email_test].' : <b>"'.$newsletter[libelleadmin].'"</b> ?</h4>
											</div>
											<div class="modal-body">
												'.$multilangue[envoyer_email_test_infos].'
														
													<input type="hidden" name="action" value="envoyertest2">
													<input type="hidden" name="newsletterid" value="'.$newsletter[newsletterid].'">
													
															
													<div class="form-group">
														<label class="control-label col-md-2">'.$multilangue[email_destination].' :</label>
														<div class="col-md-10">																														
															<input type="text" name="monmail" value="">
														</div>
													</div>		
															
														
													
											</div>
											<div class="modal-footer">
												<button aria-hidden="true" data-dismiss="modal" class="btn default">'.$multilangue[non].'</button>
												<button class="btn blue" type="submit">'.$multilangue[envoyer].'</a>
											</div>
										</form>
									</div>
								</div>
							</div>';
			
			$lien_voir="";
			$sites=$DB_site->query("SELECT * FROM site ORDER BY siteid");
			while($site = $DB_site->fetch_array($sites)){
				$lien_voir .= '<a target="_blank" href="newsletter.php?action=voir'.$site[siteid].'&newsletterid='.$newsletter[newsletterid].'" ><i class="fa fa-eye"></i> &nbsp; '.$site[libelle].'</a><br>';
			}
			
			
			
			$records["aaData"][] = array(
					"titre" => $newsletter[libelleadmin],
					"faire_un_test" => $lien_test,
					"voir" => $lien_voir,
					"actions" => $actions
			);
		}
	}

	$records["iTotalRecords"] = $newsletters_count;
	$records["iTotalDisplayRecords"] = $newsletters_count;

	echo json_encode($records);
}

if(isset($action) && $action == "listeinscritsN"){
	$iDisplayLength = intval($length);
	$iDisplayStart = intval($start);

	$records = array();
	$records["aaData"] = array();

	if(!isset($iDisplayStart))
		$iDisplayStart = 0;
	if(!isset($iDisplayLength))
		$iDisplayLength = 10;
	
	
	switch($order[0][column]){
		case "0" :
		$orderby = "adresse_mail";
		break;
		case "1" :
		$orderby = "siteid";
		break;
		case "2" :
		$orderby = "import_newsletterid";
		break;
		default:
		$orderby = "adresse_mail";
		break;
	}	

	$sensorder = $order[0][dir];

	$search[value] = securiserSql($search[value]);
	$where = "";
	if($search[value]!=""){
		$where = " AND adresse_mail  LIKE '%$search[value]%'";
	}

	$mails_N = $DB_site->query("SELECT * FROM mails_newsletter WHERE allow_email='1' $where ORDER BY $orderby $sensorder") ;

	$mails_N_count = $DB_site->num_rows($mails_N);

	$limitlower = ($iDisplayLength != -1 ? $iDisplayStart : 0);
	$perpage = ($iDisplayLength != -1 ? $iDisplayLength : $mails_N_count);

	
	
	if ($mails_N_count > 0) {
		$mails_N = $DB_site->query("SELECT * FROM mails_newsletter WHERE allow_email='1' $where ORDER BY $orderby $sensorder LIMIT $limitlower, $perpage");
		$increment_news = 0;
		while ($mail_N = $DB_site->fetch_array($mails_N)){
			$increment_news++;
			////////////
			$det_import=$DB_site->query_first("SELECT * FROM import_newsletter WHERE import_newsletterid = '".$mail_N[import_newsletterid]."'");
			$det_site=$DB_site->query_first("SELECT * FROM site WHERE siteid = '".$mail_N[siteid]."'");
			
			if($admin_droit[$scriptcourant][suppression]){
				$actions = '<a href="#myModalSupprMN'.$increment_news.'" data-original-title="'.$multilangue[supprimer].'" data-placement="top" class="tooltips" data-toggle="modal" role="button"><i class="fa fa-times" style="color : red;"></i></a>
			  					<div aria-hidden="true" aria-labelledby="myModalSupprMN'.$increment_news.'" role="dialog" tabindex="-1" class="modal fade" id="myModalSupprMN'.$increment_news.'" style="display: none;">
									<div class="modal-dialog">
										<div class="modal-content">
											<div class="modal-header">
												<button aria-hidden="true" data-dismiss="modal" class="close" type="button"></button>
												<h4 class="modal-title" id="modalSupprTitle">'.$multilangue[suppr_email].' : '.$mail_N[adresse_mail].'" ?</h4>
											</div>
											<div class="modal-body">
												'.$multilangue[suppression_mail_newsletter_infos].'
											</div>
											<div class="modal-footer">
												<button aria-hidden="true" data-dismiss="modal" class="btn default">'.$multilangue[non].'</button>
												<a href="newsletter.php?action=supprimerMN&emailsuppr='.$mail_N[adresse_mail].'" class="btn blue">'.$multilangue[oui_supprimer].'</a>
											</div>
										</div>
									</div>
								</div>';
			}

			$records["aaData"][] = array(
					"adresse_mail" => $mail_N[adresse_mail],
					"site" => $det_site[libelle],
					"import" => $det_import[import_newsletter_lib],
					"actions" => $actions
			);
		}
	}

	$records["iTotalRecords"] = $mails_N_count;
	$records["iTotalDisplayRecords"] = $mails_N_count;

	echo json_encode($records);
}



if(isset($action) && $action == "listeinscritsC"){
	$iDisplayLength = intval($length);
	$iDisplayStart = intval($start);

	$records = array();
	$records["aaData"] = array();

	if(!isset($iDisplayStart))
		$iDisplayStart = 0;
	if(!isset($iDisplayLength))
		$iDisplayLength = 10;
	
	
	switch($order[0][column]){
		case "0" :
			$orderby = "mail";
		break;
		case "1" :
			$orderby = "nom, prenom";
		break;
		case "2" :
			$orderby = "dateinscription";
		break;
		case "3" :
			$orderby = "siteid";
		break;
		default:
			$orderby = "mail";
		break;
	}	

	$sensorder = $order[0][dir];

	$search[value] = securiserSql($search[value]);
	$where = "";
	if($search[value]!=""){
		$where = " AND (mail LIKE '%$search[value]%'";
		$where .= " OR nom LIKE '%$search[value]%'";
		$where .= " OR prenom LIKE '%$search[value]%')";
	}

	$mails_C = $DB_site->query("SELECT * FROM utilisateur WHERE recevoir='1' $where ORDER BY $orderby $sensorder") ;

	$mails_C_count = $DB_site->num_rows($mails_C);

	$limitlower = ($iDisplayLength != -1 ? $iDisplayStart : 0);
	$perpage = ($iDisplayLength != -1 ? $iDisplayLength : $mails_C_count);

	if ($mails_C_count > 0) {
		$mails_C = $DB_site->query("SELECT * FROM utilisateur WHERE recevoir='1' $where ORDER BY $orderby $sensorder LIMIT $limitlower, $perpage");

		while ($mail_C = $DB_site->fetch_array($mails_C)){				
			////////////
			$det_import=$DB_site->query_first("SELECT * FROM import_newsletter WHERE import_newsletterid = '".$mail_C[import_newsletterid]."'");
			$det_site=$DB_site->query_first("SELECT * FROM site WHERE siteid = '".$mail_C[siteid]."'");
			
			$actions = '<a target="_blank" href="clients.php?action=editer&user='.$mail_C[userid].'" data-original-title="'.$multilangue[modifier].'" data-placement="top" class="tooltips"><i class="fa fa-pencil-square-o"></i></a>';
			
			
			if($admin_droit[$scriptcourant][suppression]){
					$actions .= '&nbsp;<a href="#myModalSupprC'.$mail_C[userid].'" data-original-title="'.$multilangue[supprimer].'" data-placement="top" class="tooltips" data-toggle="modal" role="button"><i class="fa fa-times" style="color : red;"></i></a>
		  					<div aria-hidden="true" aria-labelledby="myModalSupprC'.$mail_C[userid].'" role="dialog" tabindex="-1" class="modal fade" id="myModalSupprC'.$mail_C[userid].'" style="display: none;">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<button aria-hidden="true" data-dismiss="modal" class="close" type="button"></button>
											<h4 class="modal-title" id="modalSupprTitle">'.$multilangue[suppr_email].' : '.$mail_C[mail].'" ?</h4>
										</div>
										<div class="modal-body">
											'.$multilangue[suppression_mail_newsletter_infos].'
										</div>
										<div class="modal-footer">
											<button aria-hidden="true" data-dismiss="modal" class="btn default">'.$multilangue[non].'</button>
											<a href="newsletter.php?action=supprimerMC&useridsuppr='.$mail_C[userid].'" class="btn blue">'.$multilangue[oui_supprimer].'</a>
										</div>
									</div>
								</div>
							</div>';
			}

			list($annee, $mois, $jour) = explode( "-", $mail_C[dateinscription]);			
			$date_inscription="$jour/$mois/$annee";
			
			$records["aaData"][] = array(
					"mail" => $mail_C[mail],
					"nom_prenom" => $mail_C[nom]." ".$mail_C[prenom],
					"dateinscription" => $date_inscription,
					"site" => $det_site[libelle],
					"actions" => $actions
			);
		}
	}

	$records["iTotalRecords"] = $mails_C_count;
	$records["iTotalDisplayRecords"] = $mails_C_count;

	echo json_encode($records);
}


if(isset($action) && $action == "org"){	
	$DB_site->query("TRUNCATE TABLE newsletter_article");
	if($ordre != ""){
		$ordre = explode("|", $ordre);
		for($i=0;$i<sizeof($ordre);$i++){
			$position = $i+1;
			$DB_site->query("INSERT INTO newsletter_article (artid, position) VALUES ('$ordre[$i]','$position')");
		}
	}	
}




?>