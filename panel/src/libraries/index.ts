import type Vue from "vue";
import autosize from "autosize";
import colors from "./colors";
import dayjs from "./dayjs";

export const library = {
	autosize,
	colors,
	dayjs
};

export default {
	install(app: typeof Vue) {
		app.prototype.$library = library;
	}
};
