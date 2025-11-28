/**
 * Detects the type of a link
 * @param {String} value
 * @param {Object} _types Custom types, otherwise default types are used
 * @returns {Object}
 */
export function detect(value, _types) {
	value ??= "";
	_types ??= types();

	if (value.length === 0) {
		return {
			type: Object.keys(_types)[0] ?? "url",
			link: ""
		};
	}

	for (const type in _types) {
		if (_types[type].detect(value) === true) {
			return {
				type: type,
				link: _types[type].link(value)
			};
		}
	}
}

/**
 * Converts file permalink to file UUID
 * @param {String} value
 * @returns {String}
 */
export function getFileUUID(value) {
	return value.replace("/@/file/", "file://");
}

/**
 * Converts page permalink to page UUID
 * @param {String} value
 * @returns {String}
 */
export function getPageUUID(value) {
	return value.replace(/^\/(.*\/)?@\/page\//, "page://");
}

/**
 * Checks if string is a file UUID or permalink
 * @param {String} value
 * @returns {Boolean}
 */
export function isFileUUID(value) {
	return (
		value.startsWith("file://") === true ||
		value.startsWith("/@/file/") === true
	);
}

/**
 * Checks if string is a file UUID or permalink
 * @param {String} value
 * @returns {Boolean}
 */
export function isPageUUID(value) {
	return (
		value === "site://" ||
		value.startsWith("page://") === true ||
		value.match(/^\/(.*\/)?@\/page\//) !== null
	);
}

export function types(keys = []) {
	const types = {
		url: {
			detect: (value) => /^(http|https):\/\//.test(value),
			icon: "url",
			id: "url",
			label: window.panel.$t("url"),
			link: (value) => value,
			placeholder: window.panel.$t("url.placeholder"),
			input: "url",
			value: (value) => value
		},
		page: {
			detect: (value) => isPageUUID(value) === true,
			icon: "page",
			id: "page",
			label: window.panel.$t("page"),
			link: (value) => value,
			placeholder: window.panel.$t("select") + " …",
			input: "text",
			value: (value) => value
		},
		file: {
			detect: (value) => isFileUUID(value) === true,
			icon: "file",
			id: "file",
			label: window.panel.$t("file"),
			link: (value) => value,
			placeholder: window.panel.$t("select") + " …",
			value: (value) => value
		},
		email: {
			detect: (value) => value.startsWith("mailto:"),
			icon: "email",
			id: "email",
			label: window.panel.$t("email"),
			link: (value) => value.replace(/^mailto:/, ""),
			placeholder: window.panel.$t("email.placeholder"),
			input: "email",
			value: (value) => "mailto:" + value
		},
		tel: {
			detect: (value) => value.startsWith("tel:"),
			icon: "phone",
			id: "tel",
			label: window.panel.$t("tel"),
			link: (value) => value.replace(/^tel:/, ""),
			pattern: "[+]{0,1}[0-9]+",
			placeholder: window.panel.$t("tel.placeholder"),
			input: "tel",
			value: (value) => "tel:" + value
		},
		anchor: {
			detect: (value) => value.startsWith("#"),
			icon: "anchor",
			id: "anchor",
			label: "Anchor",
			link: (value) => value,
			pattern: "^#.+",
			placeholder: "#element",
			input: "text",
			value: (value) => value
		},
		custom: {
			detect: () => true,
			icon: "title",
			id: "custom",
			label: window.panel.$t("custom"),
			link: (value) => value,
			input: "text",
			value: (value) => value
		}
	};

	if (!keys.length) {
		return types;
	}

	const active = {};

	for (const type of keys) {
		if (types[type]) {
			active[type] = types[type];
		}
	}

	return active;
}

export default {
	detect,
	getFileUUID,
	getPageUUID,
	isFileUUID,
	isPageUUID,
	types
};
