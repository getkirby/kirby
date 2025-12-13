import PreviewBrowser from "./PreviewBrowser.vue";
import PreviewForm from "./PreviewForm.vue";
import PreviewView from "./PreviewView.vue";

export default {
	install(app) {
		app.component("k-preview-browser", PreviewBrowser);
		app.component("k-preview-form", PreviewForm);
		app.component("k-preview-view", PreviewView);
	}
};
