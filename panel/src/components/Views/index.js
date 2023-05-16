import AccountView from "./AccountView.vue";
import ErrorView from "./ErrorView.vue";
import FileView from "./FileView.vue";
import InstallationView from "./InstallationView.vue";
import LanguageView from "./LanguageView.vue";
import LanguagesView from "./LanguagesView.vue";
import LoginView from "./LoginView.vue";
import PageView from "./PageView.vue";
import PluginView from "./PluginView.vue";
import ResetPasswordView from "./ResetPasswordView.vue";
import SiteView from "./SiteView.vue";
import SystemView from "./SystemView.vue";
import UsersView from "./UsersView.vue";
import UserView from "./UserView.vue";

export default {
	install(app) {
		app.component("k-account-view", AccountView);
		app.component("k-error-view", ErrorView);
		app.component("k-file-view", FileView);
		app.component("k-installation-view", InstallationView);
		app.component("k-language-view", LanguageView);
		app.component("k-languages-view", LanguagesView);
		app.component("k-login-view", LoginView);
		app.component("k-page-view", PageView);
		app.component("k-plugin-view", PluginView);
		app.component("k-reset-password-view", ResetPasswordView);
		app.component("k-site-view", SiteView);
		app.component("k-system-view", SystemView);
		app.component("k-users-view", UsersView);
		app.component("k-user-view", UserView);
	}
};
