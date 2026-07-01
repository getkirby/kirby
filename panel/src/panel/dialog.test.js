/**
 * @vitest-environment jsdom
 */

import { describe, expect, it, vi } from "vitest";
import Dialog from "./dialog.js";
import Panel from "./panel.js";

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
		const portal = document.createElement("div");
		const form = document.createElement("form");
		const input = document.createElement("input");
		const preventDefault = vi.fn();
		const requestSubmit = vi.fn();

		portal.classList.add("k-dialog-portal");
		form.classList.add("k-dialog");

		Object.defineProperty(form, "requestSubmit", {
			configurable: true,
			value: requestSubmit
		});

		form.append(input);
		portal.append(form);
		document.body.append(portal);

		panel.events.emit("dialog.save", { preventDefault, target: input });

		expect(preventDefault).toHaveBeenCalledOnce();
		expect(requestSubmit).toHaveBeenCalledOnce();

		portal.remove();
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
