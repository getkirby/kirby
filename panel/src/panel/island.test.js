import { describe, expect, it, vi } from "vitest";
import Island, { defaults } from "./island.js";
import Panel from "./panel.js";

describe.concurrent("panel/island.js", () => {
	it("should have a default state", async () => {
		const panel = Panel.create();
		const island = Island(panel, "test", defaults());

		const state = {
			component: null,
			isLoading: false,
			isOpen: false,
			island: true,
			on: {},
			path: null,
			props: {},
			ref: null,
			referrer: null,
			timestamp: null
		};

		expect(island.key()).toStrictEqual("test");
		expect(island.state()).toStrictEqual(state);
	});

	it("should have a cancel method", async () => {
		const panel = Panel.create();
		const island = Island(panel, "test", defaults());
		let cancelled = false;

		island.set({
			component: "k-test",
			isOpen: true,
			on: {
				cancel() {
					cancelled = true;
				}
			}
		});

		expect(island.isOpen).toStrictEqual(true);
		expect(island.component).toStrictEqual("k-test");
		expect(cancelled).toStrictEqual(false);

		island.cancel();

		expect(cancelled).toStrictEqual(true);
	});

	it("should close a previous notification", async () => {
		const panel = Panel.create();
		const island = Island(panel, "test", defaults());

		// open a notification first
		panel.notification.error("Foo");

		expect(island.isOpen).toStrictEqual(false);
		expect(panel.notification.isOpen).toStrictEqual(true);

		island.open({
			component: "k-test"
		});

		expect(panel.notification.isOpen).toStrictEqual(false);
		expect(island.isOpen).toStrictEqual(true);

		// expect(panel.notification.isOpen).toStrictEqual(false);
	});
});
