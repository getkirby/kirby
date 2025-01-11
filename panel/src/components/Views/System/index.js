import SystemView from "./SystemView.vue";
import TableLicenseCell from "./TableLicenseCell.vue";
import TableLicenseStatusCell from "./TableLicenseStatusCell.vue";
import TableUpdateStatusCell from "./TableUpdateStatusCell.vue";

export default {
	install(app) {
		app.component("k-system-view", SystemView);
		app.component("k-table-license-cell", TableLicenseCell);
		app.component("k-table-license-status-cell", TableLicenseStatusCell);
		app.component("k-table-update-status-cell", TableUpdateStatusCell);
	}
};
