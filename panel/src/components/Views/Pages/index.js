import PageView from "./PageView.vue";
import PageChangesView from "./PageChangesView.vue";
import SiteView from "./SiteView.vue";

export default {
	install(app) {
		app.component("k-page-view", PageView);
		app.component("k-page-changes-view", PageChangesView);
		app.component("k-site-view", SiteView);
	}
};
