import autosize from "autosize";
import colors from "./colors";
import dayjs from "./dayjs";

export default {
	install(app) {
		app.prototype.$library = {
			autosize,
			colors,
			dayjs
		};
	}
};
