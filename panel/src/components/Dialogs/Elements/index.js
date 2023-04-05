import Body from "./Body.vue";
import Buttons from "./Buttons.vue";
import Fields from "./Fields.vue";
import Footer from "./Footer.vue";
import Notification from "./Notification.vue";
import Search from "./Search.vue";
import Text from "./Text.vue";

export default {
	install(app) {
		app.component("k-dialog-body", Body);
		app.component("k-dialog-buttons", Buttons);
		app.component("k-dialog-fields", Fields);
		app.component("k-dialog-footer", Footer);
		app.component("k-dialog-notification", Notification);
		app.component("k-dialog-search", Search);
		app.component("k-dialog-text", Text);
	}
};
