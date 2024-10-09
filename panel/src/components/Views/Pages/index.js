import PageView from "./PageView.vue";
import PageComparisonView from "./PageComparisonView.vue";
import SiteView from "./SiteView.vue";

export default {
	install(app) {
		app.component("k-page-view", PageView);
		app.component("k-page-comparison-view", PageComparisonView);
		app.component("k-site-view", SiteView);
	}
};
