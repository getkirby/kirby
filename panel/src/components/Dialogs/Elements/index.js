import Body from "./Body.vue";
import Box from "./Box.vue";
import Buttons from "./Buttons.vue";
import Fields from "./Fields.vue";
import Form from "./Form.vue";
import Footer from "./Footer.vue";
import Notification from "./Notification.vue";
import Pagination from "./Pagination.vue";
import Search from "./Search.vue";
import Text from "./Text.vue";

export default {
	install(app) {
		app.component("k-dialog-body", Body);
		app.component("k-dialog-box", Box);
		app.component("k-dialog-buttons", Buttons);
		app.component("k-dialog-fields", Fields);
		app.component("k-dialog-form", Form);
		app.component("k-dialog-footer", Footer);
		app.component("k-dialog-notification", Notification);
		app.component("k-dialog-pagination", Pagination);
		app.component("k-dialog-search", Search);
		app.component("k-dialog-text", Text);
	}
};
