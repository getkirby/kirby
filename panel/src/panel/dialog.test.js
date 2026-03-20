import { describe, expect, it } from "vitest";
import Dialog from "./dialog.js";
import Panel from "./panel.js";

describe("panel.dialog", () => {
	it("should have a default state", async () => {
		const panel = Panel.create(app);
		const dialog = Dialog(panel);
		const state = {
			component: null,
			id: null,
			isLoading: false,
			on: {},
			path: null,
			props: { value: {} },
			query: {},
			referrer: null,
			timestamp: null
		};

		expect(dialog.key()).toStrictEqual("dialog");
		expect(dialog.state()).toStrictEqual(state);
	});
});
