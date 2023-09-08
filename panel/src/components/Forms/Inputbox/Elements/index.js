import Description from "./Description.vue";
import Element from "./Element.vue";
import Icon from "./Icon.vue";

export default {
	install(app) {
		app.component("k-inputbox-description", Description);
		app.component("k-inputbox-element", Element);
		app.component("k-inputbox-icon", Icon);
	}
};
