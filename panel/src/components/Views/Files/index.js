import FileView from "./FileView.vue";

import FilePreview from "./FilePreview.vue";
import FilePreviewDetails from "./FilePreviewDetails.vue";
import FilePreviewThumb from "./FilePreviewThumb.vue";

import FileAudioPreview from "./FileAudioPreview.vue";
import FileDefaultPreview from "./FileDefaultPreview.vue";
import FileImagePreview from "./FileImagePreview.vue";
import FileVideoPreview from "./FileVideoPreview.vue";

export default {
	install(app) {
		app.component("k-file-view", FileView);

		app.component("k-file-preview", FilePreview);
		app.component("k-file-preview-details", FilePreviewDetails);
		app.component("k-file-preview-thumb", FilePreviewThumb);

		app.component("k-file-audio-preview", FileAudioPreview);
		app.component("k-file-default-preview", FileDefaultPreview);
		app.component("k-file-image-preview", FileImagePreview);
		app.component("k-file-video-preview", FileVideoPreview);
	}
};
