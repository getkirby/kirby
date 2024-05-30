import ErrorView from "./ErrorView.vue";
import SearchView from "./SearchView.vue";

import FileView from "./Files/FileView.vue";
import FilePreview from "./Files/FilePreview.vue";
import FileFocusButton from "./Files/FileFocusButton.vue";

import LanguagesView from "./Languages/LanguagesView.vue";
import LanguageView from "./Languages/LanguageView.vue";

import Login from "./Login/index.js";

import PageView from "./Pages/PageView.vue";
import SiteView from "./Pages/SiteView.vue";

import SystemView from "./System/SystemView.vue";
import TableUpdateStatusCell from "./System/TableUpdateStatusCell.vue";

import AccountView from "./Users/AccountView.vue";
import UserAvatar from "./Users/UserAvatar.vue";
import UserProfile from "./Users/UserProfile.vue";
import UserView from "./Users/UserView.vue";
import UsersView from "./Users/UsersView.vue";

import LegacyPluginView from "./LegacyPluginView.vue";

export default {
	install(app) {
		app.component("k-error-view", ErrorView);
		app.component("k-search-view", SearchView);

		app.component("k-file-view", FileView);
		app.component("k-file-preview", FilePreview);
		app.component("k-file-focus-button", FileFocusButton);

		app.component("k-languages-view", LanguagesView);
		app.component("k-language-view", LanguageView);

		app.use(Login);

		app.component("k-page-view", PageView);
		app.component("k-site-view", SiteView);

		app.component("k-system-view", SystemView);
		app.component("k-table-update-status-cell", TableUpdateStatusCell);

		app.component("k-account-view", AccountView);
		app.component("k-user-avatar", UserAvatar);
		app.component("k-user-profile", UserProfile);
		app.component("k-user-view", UserView);
		app.component("k-users-view", UsersView);

		app.component("k-plugin-view", LegacyPluginView);
	}
};
