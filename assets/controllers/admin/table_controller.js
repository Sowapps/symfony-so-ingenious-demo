import { Controller } from 'stimulus';

export default class extends Controller {
	static targets = ['table'];
	static values = {url: String, order: Array};
	
	initialize() {
		this.table = this.hasTableTarget ? this.tableTarget : this.element;
		this.connectTable();
	}
	
	connectTable() {
		const options = this.generateOptions();
		this.datatable = $(this.table).DataTable(options);
	}
	
	reload() {
		this.datatable.ajax.reload();
	}
	
	generateOptions() {
		const options = {
			responsive: true,
			autoWidth: false,
			language: this.getDatatableTranslations(),
			lengthMenu: [10, 25, 100, 500],
		};
		if( this.orderValue.length ) {
			options.order = this.orderValue;
		}
		return options;
	}
	
	getDatatableTranslations() {
		return {
			"lengthMenu": "Afficher _MENU_ entrées",
			"info": "Page _PAGE_ sur _PAGES_",
			"infoEmpty": "Aucune entrée",
			"infoFiltered": "(résultats sur _MAX_ entrées au total)",
			"search": "Rechercher :",
			"zeroRecords": "Aucun résultat",
			"loadingRecords": "Chargement...",
			"processing": "Calculs...",
			"thousands": " ",
			"paginate": {
				"first": "Début",
				"last": "Fin",
				"next": "Suivante",
				"previous": "Précédente"
			},
			"aria": {
				"sortAscending": ": Tri ascendant",
				"sortDescending": ": Tri descendant"
			}
		};
	}
	
}
