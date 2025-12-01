import BlueprintView from "./BlueprintView.vue";
import BlueprintFieldView from "./BlueprintFieldView.vue";
import BlueprintFieldsView from "./BlueprintFieldsView.vue";
import BlueprintTabView from "./BlueprintTabView.vue";

export default {
	install(app) {
		app.component("k-blueprint-view", BlueprintView);
		app.component("k-blueprint-field-view", BlueprintFieldView);
		app.component("k-blueprint-fields-view", BlueprintFieldsView);
		app.component("k-blueprint-tab-view", BlueprintTabView);
	}
};
