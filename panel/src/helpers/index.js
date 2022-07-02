import clipboard from "./clipboard";
import color from "./color";
import debounce from "./debounce";
import embed from "./embed";
import isComponent from "./isComponent";
import isUploadEvent from "./isUploadEvent";
import keyboard from "./keyboard";
import object from "./object";
import ratio from "./ratio";
import sort from "./sort";
import string from "./string";
import upload from "./upload.js";

import "./regex";

export default {
	install(Vue) {
		/**
		 * Array.sortBy()
		 */
		Array.prototype.sortBy = function (sortBy) {
			const sort = Vue.prototype.$helper.sort();
			const options = sortBy.split(" ");
			const field = options[0];
			const direction = options[1] || "asc";

			return this.sort((a, b) => {
				const valueA = String(a[field]).toLowerCase();
				const valueB = String(b[field]).toLowerCase();

				if (direction === "desc") {
					return sort(valueB, valueA);
				} else {
					return sort(valueA, valueB);
				}
			});
		};

		Vue.prototype.$helper = {
			clipboard,
			clone: object.clone,
			color,
			embed,
			isComponent,
			isUploadEvent,
			debounce,
			keyboard,
			object,
			pad: string.pad,
			ratio,
			slug: string.slug,
			sort,
			string,
			upload,
			uuid: string.uuid
		};

		Vue.prototype.$esc = string.escapeHTML;
	}
};
