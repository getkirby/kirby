import Fiber from "./index";
import dialog from "./dialog";
import dropdown from "./dropdown";
import search from "./search";

export default {
	install(app) {
		const fiber = new Fiber();

		app.prototype.$dialog = window.panel.$dialog = dialog;
		app.prototype.$dropdown = window.panel.$dropdown = dropdown;
		app.prototype.$search = window.panel.$search = search;
	}
};
