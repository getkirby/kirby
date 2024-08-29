import ContentTranslations from "./ContentTranslationsButton.vue";
import Settings from "./SettingsButton.vue";
import Status from "./StatusButton.vue";
import Theme from "./ThemeButton.vue";

import Button from "./Button.vue";
import Buttons from "./Buttons.vue";

export default {
	install(app) {
		app.component("k-content-translations-view-button", ContentTranslations);
		app.component("k-settings-view-button", Settings);
		app.component("k-status-view-button", Status);
		app.component("k-theme-view-button", Theme);

		app.component("k-view-button", Button);
		app.component("k-view-buttons", Buttons);

		// @deprecated
		app.component("k-languages-dropdown", ContentTranslations);
	}
};
