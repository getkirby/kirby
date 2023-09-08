import Inputbox from "./Inputbox.vue";

import Elements from "./Elements/index.js";
import Types from "./Types/index.js";

export default {
	install(app) {
		app.use(Elements);
		app.use(Types);

		app.component("k-inputbox", Inputbox);
	}
};
