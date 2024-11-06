import PageView from "./PageView.vue";
import SiteView from "./SiteView.vue";

export default {
	install(app) {
		app.component("k-page-view", PageView);
		app.component("k-site-view", SiteView);
	}
};
