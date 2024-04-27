import FileView from "./FileView.vue";

import FilePreview from "./FilePreview.vue";
import FileAudioPreview from "./FileAudioPreview.vue";
import FileDefaultPreview from "./FileDefaultPreview.vue";
import FileImagePreview from "./FileImagePreview.vue";

import FileFocusButton from "./FileFocusButton.vue";

export default {
	install(app) {
		app.component("k-file-view", FileView);

		app.component("k-file-preview", FilePreview);
		app.component("k-file-audio-preview", FileAudioPreview);
		app.component("k-file-default-preview", FileDefaultPreview);
		app.component("k-file-image-preview", FileImagePreview);

		app.component("k-file-focus-button", FileFocusButton);
	}
};
