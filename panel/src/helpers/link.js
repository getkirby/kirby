export function detect(value, _types) {
	value = value ?? "";
	_types = _types ?? types();

	if (value.length === 0) {
		return {
			type: "url",
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

export function getFileUUID(value) {
	return value.replace("/@/file/", "file://");
}

export function getPageUUID(value) {
	return value.replace("/@/page/", "page://");
}

export function isFileUUID(value) {
	return (
		value.startsWith("file://") === true ||
		value.startsWith("/@/file/") === true
	);
}

export function isPageUUID(value) {
	return (
		value === "site://" ||
		value.startsWith("page://") === true ||
		value.startsWith("/@/page/") === true
	);
}

export async function preview({ type, link }, fields) {
	if (type === "page" && link) {
		return await previewForPage(link, fields);
	}

	if (type === "file" && link) {
		return await previewForFile(link, fields);
	}

	if (link) {
		return {
			label: link
		};
	}

	return null;
}

async function previewForFile(id, fields = ["filename", "panelImage"]) {
	try {
		const file = await window.panel.api.files.get(null, id, {
			select: fields.join(",")
		});

		return {
			label: file.filename,
			image: file.panelImage
		};
	} catch (e) {
		return null;
	}
}

async function previewForPage(id, fields = ["title"]) {
	if (id === "site://") {
		return {
			label: window.panel.$t("view.site")
		};
	}

	try {
		const page = await window.panel.api.pages.get(id, {
			select: fields.join(",")
		});

		return {
			label: page.title
		};
	} catch (e) {
		return null;
	}
}

export function types(keys = []) {
	const types = {
		url: {
			detect: (value) => /^(http|https):\/\//.test(value),
			icon: "url",
			label: window.panel.$t("url"),
			link: (value) => value,
			placeholder: window.panel.$t("url.placeholder"),
			input: "url",
			value: (value) => value
		},
		page: {
			detect: (value) => isPageUUID(value) === true,
			icon: "page",
			label: window.panel.$t("page"),
			link: (value) => value,
			placeholder: window.panel.$t("select") + " …",
			input: "text",
			value: (value) => value
		},
		file: {
			detect: (value) => isFileUUID(value) === true,
			icon: "file",
			label: window.panel.$t("file"),
			link: (value) => value,
			placeholder: window.panel.$t("select") + " …",
			value: (value) => value
		},
		email: {
			detect: (value) => value.startsWith("mailto:"),
			icon: "email",
			label: window.panel.$t("email"),
			link: (value) => value.replace(/^mailto:/, ""),
			placeholder: window.panel.$t("email.placeholder"),
			input: "email",
			value: (value) => "mailto:" + value
		},
		tel: {
			detect: (value) => value.startsWith("tel:"),
			icon: "phone",
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
		active[type] = types[type];
	}

	return active;
}

export default {
	detect,
	getFileUUID,
	getPageUUID,
	isFileUUID,
	isPageUUID,
	preview,
	types
};
