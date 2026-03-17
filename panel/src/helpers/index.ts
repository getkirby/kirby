import type { App } from "vue";
import array from "./array";
import clipboard from "./clipboard";
import color from "./color";
import debounce from "./debounce";
import embed from "./embed";
import field from "./field";
import file from "./file.js";
import focus from "./focus";
import isComponent from "./isComponent";
import isUploadEvent from "./isUploadEvent";
import keyboard from "./keyboard";
import link from "./link";
import object from "./object";
import page from "./page";
import ratio from "./ratio";
import sort from "./sort";
import string from "./string";
import throttle from "./throttle";
import upload from "./upload";
import url from "./url";
import writer from "./writer";

import "./regex";

export const helper = {
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
	uuid: string.uuid,
	writer
};

export default {
	install(app: App) {
		app.config.globalProperties.$helper = helper;
		app.config.globalProperties.$esc = helper.string.escapeHTML;
	}
};
