import "./array.js";
import clipboard from "./clipboard.js";
import color from "./color.js";
import debounce from "./debounce.js";
import embed from "./embed.js";
import field from "./field.js";
import focus from "./focus.js";
import isComponent from "./isComponent.js";
import isUploadEvent from "./isUploadEvent.js";
import keyboard from "./keyboard.js";
import object from "./object.js";
import ratio from "./ratio.js";
import sort from "./sort.js";
import string from "./string.js";
import upload from "./upload.js";
import url from "./url.js";

import "./regex.js";

export default {
	install(app) {
		app.prototype.$helper = {
			clipboard,
			clone: object.clone,
			color,
			embed,
			focus,
			isComponent,
			isUploadEvent,
			debounce,
			field,
			keyboard,
			object,
			pad: string.pad,
			ratio,
			slug: string.slug,
			sort,
			string,
			upload,
			url,
			uuid: string.uuid
		};

		app.prototype.$esc = string.escapeHTML;
	}
};
