import Languages from "./LanguagesButton.vue";
import Preview from "./PreviewButton.vue";
import Settings from "./SettingsButton.vue";
import Status from "./StatusButton.vue";
import Theme from "./ThemeButton.vue";

import Buttons from "./Buttons.vue";

export default {
	install(app) {
		app.component("k-header-languages-button", Languages);
		app.component("k-header-preview-button", Preview);
		app.component("k-header-settings-button", Settings);
		app.component("k-header-status-button", Status);
		app.component("k-header-theme-button", Theme);

		app.component("k-header-buttons", Buttons);

		// @deprecated
		app.component("k-languages-dropdown", Languages);
	}
};
