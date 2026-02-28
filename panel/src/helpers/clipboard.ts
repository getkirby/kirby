export function read(
	e: Event | string | undefined,
	plain = false
): string | null {
	if (!e) {
		return null;
	}

	if (typeof e === "string") {
		return e;
	}

	if (e instanceof ClipboardEvent) {
		e.preventDefault();

		// getData() returns "" for absent types,
		// use || not ?? to treat empty strings as missing
		const text = e.clipboardData?.getData("text/plain") || null;

		if (plain === true) {
			return text;
		}

		const html = e.clipboardData?.getData("text/html") || text || null;

		if (html) {
			return html.replace(/\u00a0/g, " ");
		}
	}

	return null;
}

export function write(value: unknown, e?: Event) {
	// create pretty json of objects and arrays
	if (typeof value !== "string") {
		value = JSON.stringify(value, null, 2);
	}

	const string = value as string;

	// use the optional native clipboard event to copy
	if (e && e instanceof ClipboardEvent && e.clipboardData) {
		e.preventDefault();
		e.clipboardData.setData("text/plain", string);

		return true;
	}

	// fall back to little execCommand hack with a temporary textarea
	const input = document.createElement("textarea");
	input.value = string;
	document.body.append(input);

	// iOS
	if (navigator.userAgent.match(/ipad|ipod|iphone/i)) {
		input.contentEditable = "true";
		input.readOnly = true;

		const range = document.createRange();
		range.selectNodeContents(input);

		const selection = window.getSelection();
		selection?.removeAllRanges();
		selection?.addRange(range);
		input.setSelectionRange(0, 999999);

		// everything else
	} else {
		input.select();
	}

	document.execCommand("copy");
	input.remove();

	return true;
}

export default {
	read,
	write
};
