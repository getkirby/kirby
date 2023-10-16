import Toolbar from "./Toolbar.vue";
import TextareaToolbar from "./TextareaToolbar.vue";
import ToolbarEmailDialog from "./EmailDialog.vue";
import ToolbarLinkDialog from "./LinkDialog.vue";

export default {
	install(app) {
		app.component("k-toolbar", Toolbar);
		app.component("k-textarea-toolbar", TextareaToolbar);
		app.component("k-toolbar-email-dialog", ToolbarEmailDialog);
		app.component("k-toolbar-link-dialog", ToolbarLinkDialog);
	}
};
