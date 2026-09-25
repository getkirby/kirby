import { describe, expect, it, vi } from "vitest";
import Dialog from "./dialog.js";
import Panel from "./panel.js";

/**
 * Renders the markup of an open modal in its portal
 */
function render(key) {
	const portal = document.createElement("div");
	const form = document.createElement("form");
	const input = document.createElement("input");
	const requestSubmit = vi.fn();

	portal.classList.add(`k-${key}-portal`);
	form.classList.add(`k-${key}`);

	Object.defineProperty(form, "requestSubmit", {
		configurable: true,
		value: requestSubmit
	});

	form.append(input);
	portal.append(form);
	document.body.append(portal);

	return { form, input, portal, requestSubmit };
}

describe("panel.dialog", () => {
	it("should have a default state", async () => {
		const panel = Panel.create();
		const dialog = Dialog(panel);
		const state = {
			component: null,
			id: null,
			isLoading: false,
			legacy: false,
			on: {},
			path: null,
			props: {},
			query: {},
			ref: null,
			referrer: null,
			timestamp: null
		};

		expect(dialog.key()).toStrictEqual("dialog");
		expect(dialog.state()).toStrictEqual(state);
	});

	it("should submit the active dialog form on save shortcut", () => {
		document.body.innerHTML = "";

		const panel = Panel.create();
		const { input, requestSubmit } = render("dialog");
		const preventDefault = vi.fn();

		panel.events.emit("dialog.save", { preventDefault, target: input });

		expect(preventDefault).toHaveBeenCalledOnce();
		expect(requestSubmit).toHaveBeenCalledOnce();
	});

	it("should submit the active dialog form when the focus is outside of it", () => {
		document.body.innerHTML = "";

		const panel = Panel.create();
		const { requestSubmit } = render("dialog");
		const submit = vi.spyOn(panel.dialog, "submit");

		// clicking a collection item moves the focus out of the form
		panel.events.emit("dialog.save", { target: document.body });

		expect(requestSubmit).toHaveBeenCalledOnce();
		expect(submit).not.toHaveBeenCalled();
	});

	it("should submit the topmost dialog form", () => {
		document.body.innerHTML = "";

		const panel = Panel.create();
		const first = render("dialog");
		const last = render("dialog");

		panel.events.emit("dialog.save", { target: first.input });

		expect(first.requestSubmit).not.toHaveBeenCalled();
		expect(last.requestSubmit).toHaveBeenCalledOnce();
	});

	it("should fall back to direct submit without a dialog form", () => {
		document.body.innerHTML = "";

		const panel = Panel.create();
		const preventDefault = vi.fn();
		const submit = vi.spyOn(panel.dialog, "submit");

		panel.events.emit("dialog.save", { preventDefault });

		expect(preventDefault).toHaveBeenCalledOnce();
		expect(submit).toHaveBeenCalledOnce();
	});
});
