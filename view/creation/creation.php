<?php if($this->getData(['core','dataVersion']) > 10092){ 
	echo '<link rel="stylesheet" href="./site/data/admin.css">';
}
else{ 
	echo '<link rel="stylesheet" href="./core/layout/admin.css">';
} ?>

<?php echo template::formOpen('creation_events'); ?>
	<div class="row">
		<div class="col2">
			<?php echo template::button('creation_retour', [
				'class' => 'buttonGrey',
				'href' => helper::baseUrl() . $this->getUrl(0),
				'ico' => 'left',
				'value' => 'Retour'
			]); ?>
		</div>
<?php 	if( $this->getUser('group') >= $this->getData(['module', $this->getUrl(0), 'config', 'droit_creation']) ){?>	

<script src="./core/vendor/tinymce/tinymce.min.js"></script>
<script src="./core/vendor/tinymce/jquery.tinymce.min.js"></script>
<?php if( $this->getUser('group') >= 2){echo '<script src="./module/agenda/vendor/js/init23.js"></script>';}
else{echo '<script src="./module/agenda/vendor/js/init01.js"></script>';}?>

<link rel="stylesheet" href="./core/vendor/tinymce/init.css">

<?php 
	//Récupérer la date cliquée
	$dateclic = $module::$datecreation;
	$annee = intval(substr($dateclic, 0, 4));
	$mois = intval(substr($dateclic, 5, 2));
	$jour= intval(substr($dateclic, 8, 2));
	//Conversion date au format unix (valeur 0 au 1/1/1970 00:00)
	$date = new DateTime();
	//setDate(année, mois, jour) setTime(heure, minute)
	$date->setDate($annee, $mois, $jour);
	$date->setTime(8, 00);
	$time_unix_deb = $date->getTimestamp();
	$date->setTime(18, 00);
	$time_unix_fin = $date->getTimestamp();

?>
<!--Suite de la row Retour Enregistrer -->
		<div class="col2 offset8">
			<?php echo template::submit('creation_enregistrer'); ?>
		</div>
	</div>

	<div class="block">
		<h4>Créer un évènement</h4>	
		<div class="row">
			<div class="col12">
				<?php echo template::textarea('creation_text', [
					'label' => 'Evènement',
					'class' => 'editorWysiwyg',
					'value' => 'Votre évènement du '.$jour.'/'.$mois.'/'.$annee
				]); ?>
			</div>
		</div>

		<div class="row">
			<div class="col4">
				<?php echo template::date('creation_date_debut', [
					'help' => 'Choix de la date et de l\'heure de début de l\'évènement',
					'label' => 'Date de début',
					'value' => $time_unix_deb,
					'vendor' => 'flatpickr'
				]); ?>
			</div>

			<div class="col4">
				<?php echo template::date('creation_date_fin', [
					'help' => 'Choix de la date et de l\'heure de fin de l\'évènement',
					'label' => 'Date de fin',
					'value' => $time_unix_fin,
					'vendor' => 'flatpickr'
				]); ?>
			</div>
		</div>
		
		<div class="row">
			<div class="col4">
			<?php echo template::select('creation_couleur_fond', $module::$couleur,[
					'help' => 'Choix de la couleur du bandeau dans lequel le texte apparaît.',
					'label' => 'Couleur de fond',
					'selected' => 'black'
				]); ?>	
			</div>
			<div class="col4">
			<?php echo template::select('creation_couleur_texte', $module::$couleur,[
					'help' => 'Choix de la couleur du texte.',
					'label' => 'Couleur du texte',
					'selected' => 'white'
				]); ?>	
			</div>
		</div>
		<div class="row">
			<div class="col4">
				<?php echo template::select('creation_groupe_lire', $module::$groupe,[
					'help' => 'Choix du groupe minimal qui pourra voir et lire cet évènement',
					'label' => 'Accès en lecture',
					'selected' => '0'
				]); ?>	
			</div>
			<div class="col4">
				<?php	
					$groupe_mini = $this->getUser('group');
					if ($groupe_mini == 3){ $groupe_mini = 2;}
				?>
				<?php echo template::select('creation_groupe_mod', $module::$groupe,[
					'help' => 'Choix du groupe minimal qui pourra modifier ou supprimer cet évènement',
					'label' => 'Accès en modification',
					'selected' => $groupe_mini
				]); ?>	
			</div>
		</div>	
			
	</div>
	


<?php 	}
		else{?>
			<!--Fermeture de la row Retour -->
			</div>
			<div class="block">
				<h4>Vous n'avez pas accès à la création d'évènements, connectez-vous.</h4>
			</div>
		<?php ;}	?>

<?php echo template::formClose(); ?>
<div class="moduleVersion">Version n°
	<?php echo $module::AGENDA_VERSION; ?>
</div>