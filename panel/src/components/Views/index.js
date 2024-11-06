import ErrorView from "./ErrorView.vue";
import PreviewView from "./PreviewView.vue";
import SearchView from "./SearchView.vue";

import Files from "./Files/index.js";
import Installation from "./Installation/index.js";
import Languages from "./Languages/index.js";
import Login from "./Login/index.js";
import Pages from "./Pages/index.js";
import Users from "./Users/index.js";
import System from "./System/index.js";

export default {
	install(app) {
		app.component("k-error-view", ErrorView);
		app.component("k-preview-view", PreviewView);
		app.component("k-search-view", SearchView);

		app.use(Files);
		app.use(Installation);
		app.use(Languages);
		app.use(Login);
		app.use(Pages);
		app.use(System);
		app.use(Users);
	}
};
