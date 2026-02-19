import type { App } from "vue";

import array from "./array.js";
import clipboard from "./clipboard.js";
import color from "./color";
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
import ratio from "./ratio";
import sort from "./sort.js";
import string from "./string.js";
import throttle from "./throttle.js";
import upload from "./upload.js";
import url from "./url.js";
import writer from "./writer.js";

import "./regex.js";

const helper = {
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
		app.config.globalProperties.$esc = string.escapeHTML;
	}
};

declare module "@vue/runtime-core" {
	interface ComponentCustomProperties {
		$helper: typeof helper;
		$esc: typeof string.escapeHTML;
	}
}
