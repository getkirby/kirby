import LanguagesDropdown from "./LanguagesDropdown.vue";
import Settings from "./SettingsButton.vue";
import Status from "./StatusButton.vue";
import Theme from "./ThemeButton.vue";

import Button from "./Button.vue";
import Buttons from "./Buttons.vue";

export default {
	install(app) {
		app.component("k-languages-dropdown", LanguagesDropdown);
		app.component("k-settings-view-button", Settings);
		app.component("k-status-view-button", Status);
		app.component("k-theme-view-button", Theme);

		app.component("k-view-button", Button);
		app.component("k-view-buttons", Buttons);
	}
};
