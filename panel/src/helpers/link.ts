type LinkType = {
	detect: (value: string) => boolean;
	icon: string;
	id: string;
	input?: string;
	label: string;
	link: (value: string) => string;
	pattern?: string;
	placeholder?: string;
	value: (value: string) => string;
};

// TODO: better type once we have Vue components as types
type LinkPreview = { label: string; image?: unknown } | null;

/**
 * Detects the type of a link
 * @param _types - Custom types, otherwise default types are used
 */
export function detect(
	value: string,
	_types?: Record<string, LinkType>
): { type: string; link: string } | undefined {
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
 */
export function getFileUUID(value: string): string {
	return value.replace("/@/file/", "file://");
}

/**
 * Converts page permalink to page UUID
 */
export function getPageUUID(value: string): string {
	return value.replace(/^\/(.*\/)?@\/page\//, "page://");
}

/**
 * Checks if string is a file UUID or permalink
 */
export function isFileUUID(value: string): boolean {
	return (
		value.startsWith("file://") === true ||
		value.startsWith("/@/file/") === true
	);
}

/**
 * Checks if string is a file UUID or permalink
 */
export function isPageUUID(value: string): boolean {
	return (
		value === "site://" ||
		value.startsWith("page://") === true ||
		value.match(/^\/(.*\/)?@\/page\//) !== null
	);
}

/**
 * Returns preview data for the link
 */
export async function preview(
	{ type, link }: { type: string; link: string },
	fields: string[] = []
): Promise<LinkPreview> {
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

async function previewForFile(
	id: string,
	fields = ["filename", "panelImage"]
): Promise<LinkPreview> {
	try {
		// @ts-expect-error - window.panel has no type yet
		const file = await window.panel.api.files.get(null, id, {
			select: fields.join(",")
		});

		return {
			label: file.filename,
			image: file.panelImage
		};
	} catch {
		return null;
	}
}

async function previewForPage(
	id: string,
	fields = ["title", "panelImage"]
): Promise<LinkPreview> {
	if (id === "site://") {
		// @ts-expect-error - window.panel has no type yet
		return { label: window.panel.$t("view.site") };
	}

	try {
		// @ts-expect-error - window.panel has no type yet
		const page = await window.panel.api.pages.get(id, {
			select: fields.join(",")
		});

		return {
			label: page.title,
			image: page.panelImage
		};
	} catch {
		return null;
	}
}

export function types(keys: string[] = []): Record<string, LinkType> {
	// @ts-expect-error - window.panel has no type yet
	const panel = window.panel;

	const types: Record<string, LinkType> = {
		url: {
			detect: (value) => /^(http|https):\/\//.test(value),
			icon: "url",
			id: "url",
			label: panel.$t("url"),
			link: (value) => value,
			placeholder: panel.$t("url.placeholder"),
			input: "url",
			value: (value) => value
		},
		page: {
			detect: (value) => isPageUUID(value) === true,
			icon: "page",
			id: "page",
			label: panel.$t("page"),
			link: (value) => value,
			placeholder: panel.$t("select") + " …",
			input: "text",
			value: (value) => value
		},
		file: {
			detect: (value) => isFileUUID(value) === true,
			icon: "file",
			id: "file",
			label: panel.$t("file"),
			link: (value) => value,
			placeholder: panel.$t("select") + " …",
			value: (value) => value
		},
		email: {
			detect: (value) => value.startsWith("mailto:"),
			icon: "email",
			id: "email",
			label: panel.$t("email"),
			link: (value) => value.replace(/^mailto:/, ""),
			placeholder: panel.$t("email.placeholder"),
			input: "email",
			value: (value) => "mailto:" + value
		},
		tel: {
			detect: (value) => value.startsWith("tel:"),
			icon: "phone",
			id: "tel",
			label: panel.$t("tel"),
			link: (value) => value.replace(/^tel:/, ""),
			pattern: "[+]{0,1}[0-9]+",
			placeholder: panel.$t("tel.placeholder"),
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
			label: panel.$t("custom"),
			link: (value) => value,
			input: "text",
			value: (value) => value
		}
	};

	if (!keys.length) {
		return types;
	}

	const active: Record<string, LinkType> = {};

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
