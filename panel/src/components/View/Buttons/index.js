import Add from "./AddButton.vue";
import AddLanguages from "./AddLanguagesButton.vue";
import AddUsers from "./AddUsersButton.vue";
import Languages from "./LanguagesButton.vue";
import Preview from "./PreviewButton.vue";
import Remove from "./RemoveButton.vue";
import RemoveLanguage from "./RemoveLanguageButton.vue";
import Settings from "./SettingsButton.vue";
import Status from "./StatusButton.vue";
import Theme from "./ThemeButton.vue";

import Button from "./Button.vue";
import Buttons from "./Buttons.vue";

export default {
	install(app) {
		app.component("k-view-add-button", Add);
		app.component("k-view-add-languages-button", AddLanguages);
		app.component("k-view-add-users-button", AddUsers);
		app.component("k-view-languages-button", Languages);
		app.component("k-view-preview-button", Preview);
		app.component("k-view-remove-button", Remove);
		app.component("k-view-remove-language-button", RemoveLanguage);
		app.component("k-view-settings-button", Settings);
		app.component("k-view-status-button", Status);
		app.component("k-view-theme-button", Theme);

		app.component("k-view-button", Button);
		app.component("k-view-buttons", Buttons);

		// @deprecated
		app.component("k-languages-dropdown", Languages);
	}
};
