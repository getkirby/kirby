import PreviewBrowser from "./PreviewBrowser.vue";
import PreviewForm from "./PreviewForm.vue";
import PreviewSizes from "./PreviewSizes.vue";
import PreviewView from "./PreviewView.vue";
import RemotePreviewView from "./RemotePreviewView.vue";

export default {
	install(app) {
		app.component("k-preview-browser", PreviewBrowser);
		app.component("k-preview-form", PreviewForm);
		app.component("k-preview-sizes", PreviewSizes);
		app.component("k-preview-view", PreviewView);
		app.component("k-remote-preview-view", RemotePreviewView);
	}
};
