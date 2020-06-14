<?php if($this->getData(['core','dataVersion']) > 10092){ 
	echo '<link rel="stylesheet" href="./site/data/admin.css">';
}
else{ 
	echo '<link rel="stylesheet" href="./core/layout/admin.css">';
} ?>

<!-- Agenda dans une div pour contrôler la taille-->
<div id="index_wrapper" style=" margin:0 auto;">
	<!--Affiche l'agenda-->
	<div id='calendar' style='font-family:arial'></div>
	</br>

	<?php echo template::formOpen('index_events'); ?>
	<div class="row">
		<?php 
		if ($this->getUser('group') >= 2){
		?>

			<div class="col2">
				<?php
					echo template::button('index_config', [
						'class' => 'buttonGrey',
						'href' => helper::baseUrl() . $this->getUrl(0).'/config',
						'ico' => 'check',
						'value' => 'Gérer'
					]); 
				?>
			</div>
		<?php
		}
		?>	
	</div>
	<?php echo template::formClose();?>
</div>
<!--Pour liaison entre variables php et javascript dans index.js.php-->
<script>
	// Integer: largeur MAXI du diaporama, en pixels. Par exemple : 800, 920, 500
	var maxwidth=<?php echo $this->getData(['module', $this->getUrl(0),'config','maxiWidth']); ?>;
</script>
