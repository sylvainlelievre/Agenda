<?php if($this->getData(['core','dataVersion']) > 10092){ 
	echo '<link rel="stylesheet" href="./site/data/admin.css">';
}
else{ 
	echo '<link rel="stylesheet" href="./core/layout/admin.css">';
} ?>

<!--Chargement des librairies tinymce si nécessaire et adaptation de l'init en fonction des droits des utilisateurs -->
<?php 	if( $this->getUser('group') >= $module::$evenement['groupe_mod'] ){?>
<script src="./core/vendor/tinymce/tinymce.min.js"></script>
<script src="./core/vendor/tinymce/jquery.tinymce.min.js"></script>
<?php if( $this->getUser('group') >= 2){echo '<script src="./module/agenda/vendor/js/init23.js"></script>';}
else{echo '<script src="./module/agenda/vendor/js/init01.js"></script>';}?>

<link rel="stylesheet" href="./core/vendor/tinymce/init.css">
<?php
}
?>

<?php echo template::formOpen('edition_events'); ?>

<div class="row">
	<div class="col2">
		<?php echo template::button('edition_retour', [
			'class' => 'buttonGrey',
			'href' => helper::baseUrl() . $this->getUrl(0),
			'ico' => 'left',
			'value' => 'Retour'
		]); ?>
	</div>
<?php 	if( $this->getUser('group') >= $module::$evenement['groupe_mod'] ){?>	
	<div class="col2 offset8">
		<?php echo template::submit('edition_enregistrer',[
			'ico' => 'check',
			'name' => 'enregistrer'
		]); ?>
	</div>
<?php 
		$readonly = false;
		}
		else{
			$readonly = true;
		}?>
</div>

<div class="block">
	<h4><?php if ($readonly){echo'Lire un évènement'; }else{echo'Lire, modifier, supprimer un évènement';}?></h4>
	
	<?php if($readonly){echo 'Evénement<br/><div class="block">'.$module::$evenement['texte'].'</div>';}
	else{
	?>
	<div class="row">
		<div class="col12">
			<?php echo template::textarea('edition_text', [
				'label' => 'Evènement',
				'class' => 'editorWysiwyg',
				'value' => $module::$evenement['texte']
			]); ?>
		</div>
	</div>
	<?php
	}
	?>

	<div class="row">
		<div class="col4">
			<?php echo template::date('edition_date_debut', [
				'help' => 'Date de début',
				'label' => 'Date de début',
				'disabled' => $readonly,
				'value' => $module::$evenement['datedebut'],
				'vendor' => 'flatpickr'
			]); ?>
		</div>

		<div class="col4">
			<?php echo template::date('edition_date_fin', [
				'help' => 'Date de fin',
				'label' => 'Date de fin',
				'disabled' => $readonly,
				'value' => $module::$evenement['datefin'],
				'vendor' => 'flatpickr'
			]); ?>
		</div>
	</div>
	
	<div class="row">
		<div class="col4">
			<?php echo template::select('edition_couleur_fond', $module::$couleur,[
				'help' => 'Choix de la couleur du bandeau dans lequel le texte apparaît.',
				'label' => 'Couleur de fond',
				'disabled' => $readonly,
				'selected' => $module::$evenement['couleurfond']
			]); ?>	
		</div>
		<div class="col4">
			<?php echo template::select('edition_couleur_texte', $module::$couleur,[
				'help' => 'Choix de la couleur du texte.',
				'label' => 'Couleur du texte',
				'disabled' => $readonly,
				'selected' => $module::$evenement['couleurtexte']
			]); ?>	
		</div>
	</div>

	<div class="row">
		<div class="col4">
			<?php echo template::select('edition_groupe_lire', $module::$groupe,[
				'help' => 'Choix du groupe minimal qui pourra voir et lire cet évènement',
				'label' => 'Accès en lecture',
				'disabled' => $readonly,
				'selected' => $module::$evenement['groupe_lire']
			]); ?>	
		</div>
		<div class="col4">
			<?php echo template::select('edition_groupe_mod', $module::$groupe,[
				'help' => 'Choix du groupe minimal qui pourra modifier ou supprimer cet évènement',
				'label' => 'Accès en modification',
				'disabled' => $readonly,
				'selected' => $module::$evenement['groupe_mod']
			]); ?>	
		</div>
	</div>
<?php if( $this->getUser('group') >= $module::$evenement['groupe_mod'] ){?>
	<div class="row">
		<div class="col2">
			<?php  echo template::submit('edition_supprimer',[
				'ico' => 'cancel',
				'name' => 'supprimer',
				'value' => 'Supprimer'
			]);?>
		</div>                                                                                         
	</div>
<?php } ?>
</div>
	
<?php echo template::formClose(); ?>
<div class="moduleVersion">Version n°
	<?php echo $module::AGENDA_VERSION; ?>
</div>