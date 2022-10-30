import { Controller } from '@hotwired/stimulus';
import { DataTable } from "simple-datatables";

export default class extends Controller {
	static targets = ['table'];
	static values = {labels: Object};
	
	// static values = {url: String, order: Array};
	
	initialize() {
		this.$table = this.hasTableTarget ? this.tableTarget : this.element;
		this.connectTable();
	}
	
	connectTable() {
		const options = this.generateOptions();
		this.datatable = new DataTable(this.$table, options);
	}
	
	reload() {
		this.datatable.ajax.reload();
	}
	
	generateOptions() {
		return {
			labels: this.generateOptionLabels(),
		};
	}
	
	generateOptionLabels() {
		return this.hasLabelsValue ? this.labelsValue : null;
	}
}
