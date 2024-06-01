import LanguagesView from "./LanguagesView.vue";
import LanguageView from "./LanguageView.vue";

export default {
	install(app) {
		app.component("k-languages-view", LanguagesView);
		app.component("k-language-view", LanguageView);
	}
};
