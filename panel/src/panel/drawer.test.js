/**
 * @vitest-environment jsdom
 */

import { describe, expect, it } from "vitest";
import Drawer from "./drawer.js";
import Panel from "./panel.js";

describe.concurrent("panel.drawer", () => {
	it("should have a default state", async () => {
		const panel = Panel.create();
		const drawer = Drawer(panel);

		const state = {
			abortController: null,
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
});
