import { describe, expect, it } from "vitest";
import Drawer from "./drawer.js";
import Panel from "./panel.js";

describe.concurrent("panel.drawer", () => {
	it("should have a default state", async () => {
		const panel = Panel.create();
		const drawer = Drawer(panel);

		const state = {
			component: null,
			isLoading: false,
			isOpen: false,
			island: true,
			on: {},
			parent: null,
			path: null,
			props: {},
			query: {},
			ref: null,
			referrer: null,
			tabId: null,
			timestamp: null
		};

		expect(drawer.key()).toStrictEqual("drawer");
		expect(drawer.state()).toStrictEqual(state);
	});
});
