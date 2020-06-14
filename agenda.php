<?php

/**
 * This file is part of Zwii.
 *
 * For full copyright and license information, please see the LICENSE
 * file that was distributed with this source code.
 *
 * @author Rémi Jean <remi.jean@outlook.com>
 * @copyright Copyright (C) 2008-2018, Rémi Jean
 * @license GNU General Public License, version 3
 * @link http://zwiicms.com/
 */
 /** Module Zwii développé par Sylvain Lelièvre
 * Utilise le package Fullcalendar 
 * FullCalendar Core Package v4.3.1
 * Docs & License: https://fullcalendar.io/
 * (c) 2019 Adam Shaw
 */

class agenda extends common {

	public static $actions = [
		'creation' => self::GROUP_VISITOR,
		'edition' => self::GROUP_VISITOR,
		'config' => self::GROUP_MODERATOR,
		'delete' => self::GROUP_VISITOR,
		'deleteall' => self::GROUP_VISITOR,
		'index' => self::GROUP_VISITOR
	];

	const AGENDA_VERSION = '2.7';
	
	//Couleur du bandeau et du texte
	public static $couleur = [
		'black' => 'noir',
		'grey' => 'gris',
		'blue' => 'bleu',
		'red' => 'rouge',
		'yellow' => 'jaune',
		'orange' => 'orange',
		'green' => 'vert',
		'white' => 'blanc'
	];
	
	public static $groupe = [
		'0' => 'Visiteur',
		'1' => 'Membre',
		'2' => 'Editeur',
		'3' => 'Administrateur'
	];
	
	//Evenement
	public static $evenement = [
		'id' => 0,
		'datedebut' => '',
		'datefin' => '',
		'texte' => 'texte déclaration public static',
		'couleurfond' => 'black',
		'couleurtexte' => 'white',
		'groupe_lire' => 0,
		'groupe_mod' => 2
	];
	
	//Largeur maximale de l'agenda
	public static $maxwidth = [
		'400' => '400 pixels',
		'500' => '500 pixels',
		'600' => '600 pixels',
		'710' => '710 pixels',
		'800' => '800 pixels',
		'920' => '920 pixels',
		'1130' => '1130 pixels',
		'10000' => '100%'
	];
	
	public static $datecreation = '';
	
	//Pour choix de l'affichage mois / semaine dans configuration de l'agenda
	public static $vue_agenda = [
		'dayGridMonth' => 'Vue par mois',
		'dayGridWeek' => 'Vue par semaine'
	];

	
	/**
	 * Configuration Paramètrage
	 */
	public function config() {
		
		// Soumission du formulaire
		if($this->isPost()) {
			$fichier_restaure = $this->getInput('config_restaure');
			$fichier_sauve = $this->getInput('config_sauve');
			$droit_creation = $this->getInput('config_droit_creation');
			$droit_limite = $this->getInput('config_droit_limite', helper::FILTER_BOOLEAN);
			$fichier_ics = $this->getInput('config_fichier_ics');
			$largeur_maxi = $this->getInput('config_MaxiWidth'); 
				
			//Sauvegarder l'agenda
			if ($fichier_sauve !=''){
				$json_sauve = file_get_contents('module/agenda/data/'.$this->getUrl(0).'/events.json');
				file_put_contents('module/agenda/data/'.$this->getUrl(0).'_sauve/'.$fichier_sauve.'.json', $json_sauve);
			}
			
			//Charger un agenda sauvegardé
			if (strpos($fichier_restaure,'.json') !== false){
								
				//Remplacement par le fichier de restauration
				$json_restaure = file_get_contents('module/agenda/data/'.$this->getUrl(0).'_sauve/'. $fichier_restaure);
				file_put_contents('module/agenda/data/'.$this->getUrl(0).'/events.json', $json_restaure);
				
				//Sauvegarde dans data_sauve de l'agenda chargé
				$this->sauve($json_restaure);
				
				//Valeurs en sortie après prise en compte du formulaire 
				$this->addOutput([
					'notification' => 'Agenda chargé',
					'redirect' => helper::baseUrl() . $this->getUrl(0),
					'state' => true
				]);
			}
			
			//Ajouter des évènements contenus dans le fichier ics
			if (strpos($fichier_ics,'.ics') !== false){
				$tableau = $this->getIcsEventsAsArray('./site/file/source/agenda/ics/'.$fichier_ics);
				foreach($tableau as $key=>$value){
					$evenement_texte = '';
					$date_debut = '';
					$date_fin = '';
					$begin = '';
					$end = '';
					$clef_fin ='';
					foreach($value as $key2=>$value2){
						if($key2 == "BEGIN"){
							$begin = $value2;
						}
						if($key2 == "SUMMARY"){
							$evenement_texte = $value2;
						}
						if(strpos($key2,"DTSTART") !== false){
							$date_debut = $value2;
							$clef_debut = $key2;
						}
						if(strpos($key2,"DTEND") !== false){
							$date_fin = $value2;
							$clef_fin = $key2;
						}
						if($key2 == "END"){
							$end = $value2;
						}
					}
					
					//Si un évènement VEVENT est trouvé, avec summary et dtstart présents, on ajoute cet évènement à l'agenda
					if ($evenement_texte != '' && strpos($begin,'VEVENT')!==false && $date_debut!=='' ){
						if($date_fin == '') {
							$date_fin = $date_debut; 
							$clef_fin = $clef_debut;
						}
						$evenement_texte = $this->modif_texte($evenement_texte);
						//Modifier date format ics yyyymmddThhmm... ou yyyymmdd vers format fullcalendar yyyy-mm-ddThh:mm
						$date_debut = $this->modif_date($date_debut, $clef_debut);
						$date_fin = $this->modif_date($date_fin, $clef_fin);
					
						//Valeurs par défaut pour l'import ics fond blanc, texte noir, lecture visiteur, modification éditeur
						$this->nouvel_evenement($evenement_texte,$date_debut,$date_fin,'white','black','0','2','');			
					}
				}
			}
			
			//Mise à jour des données de configuration liées aux droits et à l'affichage
			$this->setData(['module', $this->getUrl(0), 'config', [
				'droit_creation' => intval($droit_creation),
				'droit_limite' => $droit_limite,
				'maxiWidth' => $largeur_maxi
			]]);
		
			//Valeurs en sortie
			$this->addOutput([
				'notification' => 'Opérations enregistrées',
				'redirect' => helper::baseUrl() . $this->getUrl(0),
				'state' => true
			]);
		}
		else{
		// Valeurs en sortie hors soumission du formulaire
			$this->addOutput([
				'showBarEditButton' => true,
				'showPageContent' => false,
				'view' => 'config'
			]);
		}
	}
	
	
	
	/**
	 * Suppression d'un évènement
	 */
	public function delete($lid, $sauve, $json) {
	
		//$pos1 et $pos2 sont les délimiteurs de la partie à supprimer
		$pos1 = strpos($json, '{"id":'.$lid);
		$pos2 = strpos($json, '}', $pos1);
		//Premier évènement ?
		if ($pos1 < 2) {
			//Premier ! et dernier évènement ?
			if (strlen($json) < $pos2 + 4){
				$json ='[]';
			}
			else{
				$json = substr_replace($json,'{},',$pos1, $pos2-$pos1+2);
			}
		}
		else{
			$json = substr_replace($json,',{}',$pos1-1, $pos2-$pos1+2);
		}
		
		//Enregistrer le nouveau fichier json
		//file_put_contents('module/agenda/data/'.$this->getUrl(0).'/events.json', $json);
		
		//Enregistrer le json et sauvegarder dans data_sauve si suppression de l'évènement et non modification
		if ($sauve == true){
			file_put_contents('module/agenda/data/'.$this->getUrl(0).'/events.json', $json);
			$this->sauve($json);
				
			//Valeurs en sortie si suppression demandée et réalisée
			$this->addOutput([
					'notification' => 'Evènement supprimé',
					'redirect' => helper::baseUrl() . $this->getUrl(0),
					'state' => true
			]);
		}
		else{
			return $json;
		}
	}
	
	
	/**
	 * Suppression de tous les évènements
	 */
	public function deleteall() {
	
		//Sauvegarde dans data de l'agenda actuel bien qu'il soit déjà sauvegardé dans data_sauve
		$json = file_get_contents('module/agenda/data/'.$this->getUrl(0).'/events.json');
		file_put_contents('module/agenda/data/'.$this->getUrl(0).'/events_'.date('YmdHis').'.json', $json);
		
		//Enregistrer le nouveau fichier json vide
		$json='[]';	
		file_put_contents('module/agenda/data/'.$this->getUrl(0).'/events.json', $json);
		
		//Valeurs en sortie
		$this->addOutput([
				'notification' => 'Suppression de tous les évènements',
				'redirect' => helper::baseUrl() . $this->getUrl(0),
				'state' => true
		]);
	
	}
	
	/**
	 * Création
	 */
	public function creation() {
	
		// Soumission du formulaire
		if($this->isPost()) {
		
			//lecture du formulaire
			$evenement_texte = $this->getInput('creation_text',null);
			$date_debut = $this->getInput('creation_date_debut');
			$date_fin = $this->getInput('creation_date_fin');
			$couleur_fond = $this->getInput('creation_couleur_fond');
			$couleur_texte = $this->getInput('creation_couleur_texte');
			$groupe_visible = $this->getInput('creation_groupe_lire');
			$groupe_mod = $this->getInput('creation_groupe_mod');

			//Modification de CR LF " { } dans le texte de l'évènement
			$evenement_texte = $this->modif_texte($evenement_texte);
		
			//Vérification que date fin > date debut			
			if ($this->verif_date($date_debut,$date_fin)){ 
			
				//Ajout et enregistrement de l'évènement
				$json = file_get_contents('module/agenda/data/'.$this->getUrl(0).'/events.json');
				$this->nouvel_evenement($evenement_texte,$date_debut,$date_fin,$couleur_fond,$couleur_texte,$groupe_visible,$groupe_mod,$json);
		
				//Valeurs en sortie après prise en compte du formulaire
				$this->addOutput([
					'notification' => 'Evènement enregistré',
					'state' => true,
					'redirect' => helper::baseUrl() . $this->getUrl(0)
				]);			
			}
			//Valeurs saisies non correctes
			else{
				$this->addOutput([
					'notification' => 'La date de fin précède la date de début !',
					'view' => 'creation',
					'state' => false
				]);		
			}
		}
		else{
			$this->limite_groupes();
			// Valeurs en sortie hors soumission du formulaire
			$this->addOutput([
				'showBarEditButton' => true,
				'showPageContent' => false,
				'view' => 'creation'
			]);
		}
	}
	
	/**
	 * Edition, modification, suppression
	 */
	public function edition($lid) {
	
		//Préparation avant l'édition de l'évènement
		self::$evenement['id'] = $lid;
		$json = file_get_contents('module/agenda/data/'.$this->getUrl(0).'/events.json');
		$tableau = json_decode($json, true);
		self::$evenement['groupe_lire'] = $tableau[$lid]['groupe_lire'];
		self::$evenement['groupe_mod'] = $tableau[$lid]['groupe_mod'];
		self::$evenement['texte'] = $this->restaure_texte($tableau[$lid]['title']);
		self::$evenement['couleurfond'] = $tableau[$lid]['backgroundColor'];
		self::$evenement['couleurtexte'] = $tableau[$lid]['textColor'];	
		$dateclic = $tableau[$lid]['start'];
		self::$evenement['datedebut'] = $this->conversion_date($dateclic);	
		$dateclic = $tableau[$lid]['end'];
		self::$evenement['datefin'] = $this->conversion_date($dateclic);
		
		//Soumission du formulaire
		if($this->isPost()) {
		
			//Si bouton submit enregistrer
			if (isset($_POST['enregistrer'])) {
				//lecture du formulaire
				$evenement_texte = $this->getInput('edition_text', null);
				$date_debut = $this->getInput('edition_date_debut');
				$date_fin = $this->getInput('edition_date_fin');
				$couleur_fond = $this->getInput('edition_couleur_fond');
				$couleur_texte = $this->getInput('edition_couleur_texte');
				$groupe_visible = $this->getInput('edition_groupe_lire');
				$groupe_mod = $this->getInput('edition_groupe_mod');

				//Modification de CR LF " { } dans le texte de l'évènement
				$evenement_texte = $this->modif_texte($evenement_texte);
			
				//Vérification que date fin > date debut			
				if ($this->verif_date($date_debut,$date_fin)){ 
				
					//Effacer l'évènement sans sauvegarde dans data_sauve
					$sauve = false;
					$json = $this->delete($lid, $sauve, $json);
					
					//Ajout, enregistrement et sauvegarde de l'évènement
					$this->nouvel_evenement($evenement_texte,$date_debut,$date_fin,$couleur_fond,$couleur_texte,$groupe_visible,$groupe_mod,$json);

					//Valeurs en sortie après prise en compte du formulaire
					$this->addOutput([
						'notification' => 'Modification de l\'évènement enregistrée',
						'state' => true,
						'redirect' => helper::baseUrl() . $this->getUrl(0)
					]);

				}
				//Valeurs saisies non correctes
				else{
					$this->addOutput([
						'notification' => 'La date de fin précède la date de début !',
						'view' => 'edition',
						'state' => false
					]);				
				}
  
			// sinon si bouton submit supprimer
			} elseif (isset($_POST['supprimer'])) {
				$sauve = true;
				$this->delete($lid, $sauve, $json); 
			}
		}
		else{
			$this->limite_groupes();
			// Affichage de la page édition d'un évènement avec valeurs actuelles
			$this->addOutput([
				'showBarEditButton' => true,
				'showPageContent' => false,
				'view' => 'edition'
			]);
		}
	}

	
	/**
	 * Newname utilisé par la version 9 pour inscrire le nouveau nom de page dans le json du module
	 */
	 public function newname() {
			$this->setData(['module',$this->getUrl(0),'name',$this->getUrl(0)]);
	 }
	 
	/**
	 * Accueil
	 */
	public function index() {

		//Pour récupération des données ajax jquery date ou id 
		$url = $_SERVER['REQUEST_URI'];
		if (strpos($url,'/da:') !== false){
			//Extraction des données de la chaîne url et détection de changement de vue
			$dateclic = $this->vue_debut($url,'/da:');
			self::$datecreation = $dateclic;
			//Vers la création d'un évènement
			$this->creation();
		}
		else{
			if (strpos($url,'/id:') !== false){	
				//Extraction des données de la chaîne url et détection de changement de vue
				$idclic = $this->vue_debut($url,'/id:');
				//Vers l'édition d'un évènement
				$this->edition($idclic);
			}
			else{
				//Initialisations des paramètres de configuration du module et création des dossiers de sauvegarde
				if( null === $this->getData(['module', $this->getUrl(0), 'vue'])) {
					// name est utilisé pour détecter un changement de nom de la page contenant le module
					$this->setData(['module',$this->getUrl(0),[
						'name' => $this->getUrl(0),
						'vue' => [
						'vueagenda' => 'dayGridMonth',
						'debagenda' => date('Y-m-d')
						],
						'config' => [
						'droit_creation' => 2,
						'droit_limite' => true,
						'maxiWidth' => '800'
						]
					]]);
				
					//Création des dossiers de sauvegarde de l'agenda
					if(! is_dir('./module/agenda/data')){mkdir('./module/agenda/data');}
					if(! is_dir('./module/agenda/data/'.$this->getUrl(0).'_sauve')){mkdir('./module/agenda/data/'.$this->getUrl(0).'_sauve');}
					if(! is_dir('./module/agenda/data/'.$this->getUrl(0).'_visible')){mkdir('./module/agenda/data/'.$this->getUrl(0).'_visible');}
					if(! is_dir('./module/agenda/data/'.$this->getUrl(0))){mkdir('./module/agenda/data/'.$this->getUrl(0));}
					if(! is_dir('./site/file/source/agenda')){mkdir('./site/file/source/agenda');}
					if(! is_dir('./site/file/source/agenda/ics')){mkdir('./site/file/source/agenda/ics');}

				
					$this->addOutput([
							'notification' => 'Initialisations effectuées',
							'state' => true
					]);	
				}
				else{
					//le module existe dans le json, détection du changement de nom de la page pour renommer les dossiers
					if(! is_dir('./module/agenda/data/'.$this->getUrl(0))){
						$oldname = $this->getData(['module', $this->getUrl(0), 'name']);
						$newname = $this->getUrl(0);
						rename( './module/agenda/data/'.$oldname, './module/agenda/data/'.$newname);
						rename( './module/agenda/data/'.$oldname.'_visible' , './module/agenda/data/'.$newname.'_visible');
						rename( './module/agenda/data/'.$oldname.'_sauve' , './module/agenda/data/'.$newname.'_sauve');	
						$this->addOutput([
								'notification' => 'Modification des dossiers de sauvegarde',
								'state' => true
						]);	
						//Fonctionne avec Zwii 10.0.044 mais sans effet avec version 9.2.27, pourquoi ?
						//$this->setData(['module',$newname,'name',$newname]);	
						//avec une version 9 on passe par une fonction pour réaliser cette mise à jour
						$this->newname();
			
					}
				}	
				//Si le fichier events.json n'existe pas ou si sa taille est inférieure à 2 on le crée vide
				if( is_file('module/agenda/data/'.$this->getUrl(0).'/events.json') === false || 
				( is_file('module/agenda/data/'.$this->getUrl(0).'/events.json') === true && filesize('module/agenda/data/'.$this->getUrl(0).'/events.json')<2)){
					file_put_contents('module/agenda/data/'.$this->getUrl(0).'/events.json', '[]');
				}
				
				//Création d'une copie d'events.json visible en fonction des droits
				$json = file_get_contents('module/agenda/data/'.$this->getUrl(0).'/events.json');
				$tableau = json_decode($json, true);
				foreach($tableau as $key=>$value){
					if( isset($value['groupe_lire'])){
						if($value['groupe_lire'] > $this->getUser('group')){
								$json = $this->delete_visible($json,$key);
						}
						else{
							if( isset ($value['title'])){
								$newvalues = html_entity_decode($value['title']);
								$newvalue = strip_tags($newvalues);
								//Modification de CR LF " { } dans le texte de l'évènement
								$newvalue = $this->modif_texte($newvalue);
								$json = str_replace($value['title'], $newvalue, $json); 
							}
						}
					}
				}
				file_put_contents('module/agenda/data/'.$this->getUrl(0).'_visible/events.json',$json);
				
				// Affichage de la page agenda
				$this->addOutput([
					'showBarEditButton' => true,
					'showPageContent' => true,
					'vendor' => [
						'js'
					],
					'view' => 'index'
				]);
			
			}
		}

	}
	

	/* 
	/*Fonctions privées
	*/
	
	/* Conversion date au format unix (valeur 0 au 1/1/1970 00:00)
	*/
	private function conversion_date($dateclic){
		$annee = intval(substr($dateclic, 0, 4));
		$mois = intval(substr($dateclic, 5, 2));
		$jour= intval(substr($dateclic, 8, 2));
		$heure = intval(substr($dateclic, 11, 2));
		$minute = intval(substr($dateclic, 14, 2));
		$date = new DateTime();
		$date->setDate($annee, $mois, $jour);
		$date->setTime($heure, $minute);
		return $date->getTimestamp();
	}
	
	
	/* Vérification que $datedebut précède $datefin
	*/
	private function verif_date($datedebut, $datefin){
		$result = false;
		$date[0] = $datedebut;
		$date[1] = $datefin;
		for($key = 0; $key <2; $key++){
			$annee = substr($date[$key],0,4);
			$mois = substr($date[$key],5,2);
			$jour = substr($date[$key],8,2);
			$heure = substr($date[$key],11,2);
			$minute = substr($date[$key],14,2);
			$valdate[$key] = intval($annee.$mois.$jour.$heure.$minute);
		}
		if ($valdate[0] <= $valdate[1]){ $result = true;}
		return $result;
	}
	
	/*Modifier date format ics yyyymmddThhmm...  ou yyyymmdd vers format fullcalendar yyyy-mm-ddThh:mm ou yyyy-mm-dd
	*/
	private function modif_date($datein, $clef){
		if (strpos($clef, 'VALUE=DATE') !== false){
			$dateout = substr($datein, 0, 4).'-'.substr($datein, 4, 2).'-'.substr($datein, 6, 2);
		}
		else{
			$dateout = substr($datein, 0, 4).'-'.substr($datein, 4, 2).'-'.substr($datein, 6, 5).':'.substr($datein, 11, 2);
		}
		return $dateout;
	}
						
		
	
	/* Modification de CR LF " ' { } dans le texte de l'évènement
	*/
	private function modif_texte($evenement_texte){
		$evenement_texte = str_replace(CHR(13),"&#13;",$evenement_texte);
		$evenement_texte = str_replace(CHR(10),"&#10;",$evenement_texte);
		$evenement_texte = str_replace('"','&#34;',$evenement_texte);
		$evenement_texte = str_replace("&#39;","'",$evenement_texte);
		$evenement_texte = str_replace('}','&#125;',$evenement_texte);
		$evenement_texte = str_replace('{','&#123;',$evenement_texte);
		return $evenement_texte;
	}
	
	/* Restauration des CR LF " ' { } dans le texte de l'évènement
	*/
	private function restaure_texte($evenement_texte){
		$evenement_texte = str_replace("&#13;",CHR(13),$evenement_texte);
		$evenement_texte = str_replace("&#10;",CHR(10),$evenement_texte);
		$evenement_texte = str_replace('&#34;','"',$evenement_texte);
		$evenement_texte = str_replace("&#39;","'",$evenement_texte);
		$evenement_texte = str_replace('&#125;','}',$evenement_texte);
		$evenement_texte = str_replace('&#123;','{',$evenement_texte);
		return $evenement_texte;
	}
	
	/* Ajout et enregistrement d'un évènement sur création ou édition
	*/
	private function nouvel_evenement($evenement_texte,$date_debut,$date_fin,$couleur_fond,$couleur_texte,$groupe_visible,$groupe_mod,$json){
		//Changement du format des dates yyyy-mm-dd hh:mm:0  vers format fullcalendar yyyy-mm-ddThh:mm
		$date_debut = str_replace(' ','T',$date_debut);
		$date_fin = str_replace(' ','T',$date_fin);
		
		//Limitation à 16 caractères
		$date_debut = substr($date_debut,0,16);
		$date_fin = substr($date_fin,0,16);
		
		//Ouverture et décodage du fichier json
		if($json == ''){$json = file_get_contents('module/agenda/data/'.$this->getUrl(0).'/events.json');}
		$tableau = json_decode($json);
		$keynew = count($tableau);
		
		//Chaîne à ajouter de type ,{"id":"2","title":"...","start":"...","end":"...","backgroundColor":"...","textColor":"...","groupe":"..."}]
		//Sans la virgule initiale si c'est le premier évènement
		if (strlen($json) > 2){
			$new = ',{"id":'.$keynew.',"title":"'.$evenement_texte.'","start":"'.$date_debut.'","end":"'
			.$date_fin.'","backgroundColor":"'.$couleur_fond.'","textColor":"'.$couleur_texte.'","groupe_lire":"'.$groupe_visible.'","groupe_mod":"'.$groupe_mod.'"}]';
		}
		else{
			$new = '{"id":'.$keynew.',"title":"'.$evenement_texte.'","start":"'.$date_debut.'","end":"'
			.$date_fin.'","backgroundColor":"'.$couleur_fond.'","textColor":"'.$couleur_texte.'","groupe_lire":"'.$groupe_visible.'","groupe_mod":"'.$groupe_mod.'"}]';
		}
		$json = str_replace(']',$new,$json);

		//Enregistrement dans le fichier json et sauvegarde pour restauration par "Agenda précédent"
		file_put_contents('module/agenda/data/'.$this->getUrl(0).'/events.json', $json);
		$this->sauve($json);
	}
	
	/* Sauvegarde automatique de l'agenda sous une forme datée après chaque création, modification, suppression d'un évènement
	* ou chargement d'un nouvel agenda, seuls les 10 derniers agendas sont sauvegardés
	*/
	private function sauve($sauve_json) {

		//Sauvegarde du fichier json actuel
		file_put_contents('module/agenda/data/'.$this->getUrl(0).'_sauve/events_'.date('YmdHis').'.json', $sauve_json);
			
		//Effacement du plus ancien fichier de sauvegarde auto si le nombre de fichiers dépasse 10
		$dir='./module/agenda/data/'.$this->getUrl(0).'_sauve';
		$nom_fichier = scandir($dir);
		//Comptage du nombre de fichiers de sauvegarde auto
		$nb_sauve_auto = 0;
		$plus_ancien_clef = 0;
		foreach($nom_fichier as $key=>$value){
			if(strpos($value,'events_') !== false && strlen($value) == 26){
				if ($nb_sauve_auto == 0) { $plus_ancien_clef = $key;}
				$nb_sauve_auto++;
			}
		}
		if ($nb_sauve_auto > 10){
			$handle = opendir('./module/agenda/data/'.$this->getUrl(0).'_sauve');
			unlink('module/agenda/data/'.$this->getUrl(0).'_sauve/'.$nom_fichier[$plus_ancien_clef]);
			closedir($handle);
		}
	}
	
	/* Suppression d'évènements dans le json public ( visible) en fonction des droits
	*/ 
	private function delete_visible($json,$lid) {
		//$pos1 et $pos2 sont les délimiteurs de la partie à supprimer
		$pos1 = strpos($json, '{"id":'.$lid);
		$pos2 = strpos($json, '}', $pos1);
		//Premier évènement ?
		if ($pos1 < 2) {
			//Premier ! et dernier évènement ?
			if (strlen($json) < $pos2 + 4){
				$json ='[]';
			}
			else{
				$json = substr_replace($json,'{},',$pos1, $pos2-$pos1+2);
			}
		}
		else{
			$json = substr_replace($json,',{}',$pos1-1, $pos2-$pos1+2);
		}
		return $json;
	}

	/* Limitation des choix pour les groupes lecture et modification avant création ou édition
	*/ 
	private function limite_groupes() {
		//Modification du tableau self::$groupe si case cochée en configuration
		if ($this->getData(['module', $this->getUrl(0), 'config', 'droit_limite'])
			&& $this->getUser('group') >= self::$evenement['groupe_mod']){
			 switch ($this->getUser('group')) {
				case 0 :
					array_splice(self::$groupe,1);
					break;
				case 1 :
					array_splice(self::$groupe,2);
					break;
				case 2 :
					array_splice(self::$groupe,3);
					break;
			 }
		}
	}
	
	/*
	* Extraction des données de la chaîne url et détection de changement de vue
	*/
	private function vue_debut($url,$idda) {
		$pos1 = strpos($url,$idda);
		$pos2 = strpos($url,'vue:');
		$pos3 = strpos($url,'deb:');
		$iddaclic = substr($url,$pos1 + 4, $pos2-($pos1+4));
		$grid = substr($url,$pos2 + 4, $pos3-($pos2+4));
		$deb = substr($url,$pos3 + 4, 10);
		$gridold = $this->getData(['module', $this->getUrl(0), 'vue','vueagenda']);
		$debold = $this->getData(['module', $this->getUrl(0), 'vue','debagenda']);
		if($grid != $gridold || $deb != $debold){
			$this->setData(['module', $this->getUrl(0), 'vue', [
				'vueagenda' => $grid,
				'debagenda' => $deb
				]]);		
			$this->addOutput([
				'notification' => 'Modification de vue enregistrée',
				'state' => true
			]);	
		}
		return $iddaclic;
	}
	
	/* Function is to get all the contents from ics and explode all the datas according to the events and its sections */
	/* de https://www.apptha.com/blog/import-google-calendar-events-in-php/ */
    function getIcsEventsAsArray($file) {
        $icalString = file_get_contents ( $file );
        $icsDates = array ();
        /* Explode the ICs Data to get datas as array according to string ‘BEGIN:’ */
        $icsData = explode ( "BEGIN:", $icalString );
        /* Iterating the icsData value to make all the start end dates as sub array */
        foreach ( $icsData as $key => $value ) {
            $icsDatesMeta [$key] = explode ( "\n", $value );
        }
        /* Itearting the Ics Meta Value */
        foreach ( $icsDatesMeta as $key => $value ) {
            foreach ( $value as $subKey => $subValue ) {
                /* to get ics events in proper order */
                $icsDates = $this->getICSDates ( $key, $subKey, $subValue, $icsDates );
            }
        }
        return $icsDates;
    }
	
    /* funcion is to avaid the elements wich is not having the proper start, end  and summary informations */
	/* de https://www.apptha.com/blog/import-google-calendar-events-in-php/ */	
    function getICSDates($key, $subKey, $subValue, $icsDates) {
        if ($key != 0 && $subKey == 0) {
            $icsDates [$key] ["BEGIN"] = $subValue;
        } else {
            $subValueArr = explode ( ":", $subValue, 2 );
            if (isset ( $subValueArr [1] )) {
                $icsDates [$key] [$subValueArr [0]] = $subValueArr [1];
            }
        }
        return $icsDates;
    }

}