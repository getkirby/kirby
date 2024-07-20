import Languages from "./LanguagesButton.vue";
import Settings from "./SettingsButton.vue";
import Status from "./StatusButton.vue";
import Theme from "./ThemeButton.vue";

import Button from "./Button.vue";
import Buttons from "./Buttons.vue";

export default {
	install(app) {
		app.component("k-view-languages-button", Languages);
		app.component("k-view-settings-button", Settings);
		app.component("k-view-status-button", Status);
		app.component("k-view-theme-button", Theme);

		app.component("k-view-button", Button);
		app.component("k-view-buttons", Buttons);

		// @deprecated
		app.component("k-languages-dropdown", Languages);
	}
};
