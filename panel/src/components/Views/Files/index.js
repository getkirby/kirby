import FileView from "./FileView.vue";

/* File preview components */
import FilePreview from "./FilePreview.vue";
import FilePreviewDetails from "./FilePreviewDetails.vue";
import FilePreviewThumb from "./FilePreviewThumb.vue";

/* File previews */
import DefaultFilePreview from "./DefaultFilePreview.vue";
import ImageFilePreview from "./ImageFilePreview.vue";

export default {
	install(app) {
		app.component("k-file-view", FileView);

		app.component("k-file-preview", FilePreview);
		app.component("k-file-preview-details", FilePreviewDetails);
		app.component("k-file-preview-thumb", FilePreviewThumb);

		app.component("k-default-file-preview", DefaultFilePreview);
		app.component("k-image-file-preview", ImageFilePreview);
	}
};
