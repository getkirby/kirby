import colors from "./colors";
import dayjs from "./dayjs";

export default {
	install(app) {
		app.prototype.$library = {
			colors,
			dayjs
		};
	}
};
