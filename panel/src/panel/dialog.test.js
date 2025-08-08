/**
 * @vitest-environment jsdom
 */

import { describe, expect, it } from "vitest";
import Dialog from "./dialog.js";
import Panel from "./panel.js";

describe.concurrent("panel.dialog", () => {
	it("should have a default state", async () => {
		const panel = Panel.create();
		const dialog = Dialog(panel);
		const state = {
			abortController: null,
			component: null,
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
});
