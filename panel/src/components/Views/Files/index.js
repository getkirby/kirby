import FileView from "./FileView.vue";

/* File preview components */
import FilePreview from "./FilePreview.vue";
import FilePreviewDetails from "./FilePreviewDetails.vue";
import FilePreviewFrame from "./FilePreviewFrame.vue";

/* File previews */
import DefaultFilePreview from "./DefaultFilePreview.vue";
import AudioFilePreview from "./AudioFilePreview.vue";
import ImageFilePreview from "./ImageFilePreview.vue";
import PdfFilePreview from "./PdfFilePreview.vue";
import VideoFilePreview from "./VideoFilePreview.vue";

export default {
	install(app) {
		app.component("k-file-view", FileView);

		app.component("k-file-preview", FilePreview);
		app.component("k-file-preview-details", FilePreviewDetails);
		app.component("k-file-preview-frame", FilePreviewFrame);

		app.component("k-default-file-preview", DefaultFilePreview);
		app.component("k-audio-file-preview", AudioFilePreview);
		app.component("k-image-file-preview", ImageFilePreview);
		app.component("k-pdf-file-preview", PdfFilePreview);
		app.component("k-video-file-preview", VideoFilePreview);
	}
};
