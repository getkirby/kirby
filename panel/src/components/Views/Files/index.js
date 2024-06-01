import FileView from "./FileView.vue";
import FileFocusButton from "./FileFocusButton.vue";
import FilePreview from "./FilePreview.vue";

export default {
	install(app) {
		app.component("k-file-view", FileView);
		app.component("k-file-preview", FilePreview);
		app.component("k-file-focus-button", FileFocusButton);
	}
};
