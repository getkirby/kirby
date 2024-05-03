import UploadItem from "./UploadItem.vue";
import UploadItemPreview from "./UploadItemPreview.vue";
import UploadItems from "./UploadItems.vue";

export default {
	install(app) {
		app.component("k-upload-item", UploadItem);
		app.component("k-upload-item-preview", UploadItemPreview);
		app.component("k-upload-items", UploadItems);
	}
};
