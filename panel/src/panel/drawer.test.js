import { describe, expect, it, vi } from "vitest";
import Drawer from "./drawer.js";
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

describe("panel.drawer", () => {
	it("should have a default state", async () => {
		const panel = Panel.create();
		const drawer = Drawer(panel);

		const state = {
			component: null,
			id: null,
			isLoading: false,
			on: {},
			path: null,
			props: {},
			query: {},
			referrer: null,
			timestamp: null
		};

		expect(drawer.key()).toStrictEqual("drawer");
		expect(drawer.state()).toStrictEqual(state);
	});

	it("should submit the active drawer form on save shortcut", () => {
		document.body.innerHTML = "";

		const panel = Panel.create();
		const { requestSubmit } = render("drawer");
		const preventDefault = vi.fn();
		const submit = vi.spyOn(panel.drawer, "submit");

		// the focus does not have to be inside the form
		panel.events.emit("drawer.save", { preventDefault, target: document.body });

		expect(preventDefault).toHaveBeenCalledOnce();
		expect(requestSubmit).toHaveBeenCalledOnce();
		expect(submit).not.toHaveBeenCalled();
	});

	it("should fall back to direct submit without a drawer form", () => {
		document.body.innerHTML = "";

		const panel = Panel.create();
		const preventDefault = vi.fn();
		const submit = vi.spyOn(panel.drawer, "submit");

		panel.events.emit("drawer.save", { preventDefault });

		expect(preventDefault).toHaveBeenCalledOnce();
		expect(submit).toHaveBeenCalledOnce();
	});
});
