import array from "./array.js";
import clipboard from "./clipboard.js";
import color from "./color.js";
import debounce from "./debounce.js";
import embed from "./embed.js";
import field from "./field.js";
import file from "./file.js";
import focus from "./focus.js";
import isComponent from "./isComponent.js";
import isUploadEvent from "./isUploadEvent.js";
import keyboard from "./keyboard.js";
import link from "./link.js";
import object from "./object.js";
import page from "./page.js";
import ratio from "./ratio.js";
import sort from "./sort.js";
import string from "./string.js";
import throttle from "./throttle.js";
import upload from "./upload.js";
import url from "./url.js";

import "./regex.js";

export default {
	install(app) {
		app.config.globalProperties.$helper = {
			array,
			clipboard,
			clone: object.clone,
			color,
			embed,
			focus,
			isComponent,
			isUploadEvent,
			debounce,
			field,
			file,
			keyboard,
			link,
			object,
			page,
			pad: string.pad,
			ratio,
			slug: string.slug,
			sort,
			string,
			throttle,
			upload,
			url,
			uuid: string.uuid
		};

		app.config.globalProperties.$esc = string.escapeHTML;
	}
};
