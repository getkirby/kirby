import SystemView from "./SystemView.vue";
import TableUpdateStatusCell from "./TableUpdateStatusCell.vue";

export default {
	install(app) {
		app.component("k-system-view", SystemView);
		app.component("k-table-update-status-cell", TableUpdateStatusCell);
	}
};
