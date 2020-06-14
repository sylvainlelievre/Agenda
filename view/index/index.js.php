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
 *
 * Module Zwii agenda développé par Sylvain Lelièvre
 * Utilise le package Fullcalendar 
 * FullCalendar Core Package v4.3.1
 * Docs & License: https://fullcalendar.io/
 * (c) 2019 Adam Shaw
 **/
 
 $(document).ready(function() {

	//Fullcalendar : instanciation, initialisations
	var calendarEl = document.getElementById('calendar');
	var calendar = new FullCalendar.Calendar(calendarEl, {
		header: {
		  left:   'dayGridMonth,dayGridWeek',
		  center: 'title',
		  right:  'today,prev,next'
		},
		titleFormat: {
			month: 'short',
			year: 'numeric'
	   },
		plugins: [ 'dayGrid', 'interaction' ],
		locale : 'fr',
		defaultView: '<?php echo $this->getData(['module', $this->getUrl(0), 'vue', 'vueagenda']) ;?>',
		defaultDate: '<?php echo $this->getData(['module', $this->getUrl(0), 'vue', 'debagenda']) ;?>',
		selectable: true,
		editable: true,
		//afficher les évènements à partir d'un fichier JSON
		events : 'module/agenda/data/'+'<?php echo $this->getUrl(0); ?>'+'_visible/events.json',
		//créer un évènement
		dateClick: function(info) {
			  window.open('<?php echo helper::baseUrl() . $this->getUrl(0); ?>'+ '/da:'+ info.dateStr + 'vue:' + info.view.type + 'deb:' + calendar.formatIso(info.view.currentStart),'_self');			  
		},
		//Lire, modifier, supprimer un évènement
		eventClick: function(info) {
			  window.open('<?php echo helper::baseUrl() . $this->getUrl(0); ?>'+'/id:'+ info.event.id + 'vue:' + info.view.type + 'deb:' + calendar.formatIso(info.view.currentStart),'_self');
		}
	});

	//Déclaration de la fonction wrapper pour déterminer la largeur du div qui contient l'agenda et le bouton gérer : index_wrapper
	$.wrapper = function(){
		// Adaptation de la largeur du wrapper en fonction de la largeur de la page client et de la largeur du site
		// 10000 pour la sélection 100%
		if(maxwidth != 10000){
			var wclient = document.body.clientWidth,
				largeur_pour_cent,
				largeur,
				largeur_section,
				wsection = getComputedStyle(site).width,
				wcalcul;
			switch (wsection)
			{
				case '750px':
					largeur_section = 750;
					break;
				case '960px':
					largeur_section = 960;
					break;
				case '1170px':
					largeur_section = 1170;
					break;
				default:
					largeur_section = wclient;
			}
						
			// 20 pour les margin du body / html, 40 pour le padding intérieur dans section	
			if(wclient > largeur_section + 20) {wcalcul = largeur_section-40} else {wcalcul = wclient-40};
			largeur_pour_cent = Math.floor(100*(maxwidth/wcalcul));
			if(largeur_pour_cent > 100) { largeur_pour_cent=100;}
			largeur=largeur_pour_cent.toString() + "%";
			
			console.log(largeur);
			
			$("#index_wrapper").css('width',largeur);
		}
		else
		{
			$("#index_wrapper").css('width',"100%");
		}
		//La taille du wrapper étant défini on peut l'afficher
		$("#index_wrapper").css('visibility', "visible");
	};
 
	$.wrapper();	
	calendar.render();
	
	$(window).resize(function(){
		$.wrapper();
		calendar.render();
	});
});




		