import { describe, expect, it, vi } from "vitest";
import Panel from "./panel";
import View from "./view";

vi.stubGlobal("location", new URL("http://localhost:3000/"));

describe("panel.view", () => {
	describe("state", () => {
		it("should have a default state", async () => {
			const panel = Panel.create(app);
			const view = View(panel);

			const state = {
				breadcrumb: [],
				breadcrumbLabel: null,
				component: null,
				icon: null,
				id: null,
				isLoading: false,
				link: null,
				on: {},
				path: null,
				props: {},
				query: {},
				referrer: null,
				search: "pages",
				timestamp: null,
				title: null
			};

			expect(view.key()).toStrictEqual("view");
			expect(view.state()).toStrictEqual(state);
		});
	});

	describe("load()", () => {
		it("should abort a previous request when a new one starts", async () => {
			const panel = Panel.create(app);
			// make panel.open hang so the first load doesn't finish before the second starts
			panel.open = () => new Promise(() => {});

			const view = View(panel);

			view.load("/first");
			const firstController = view.abortController;

			view.load("/second");

			expect(firstController?.signal.aborted).toBe(true);
		});
	});

	describe("set()", () => {
		it("should set the panel title", async () => {
			const panel = Panel.create(app);
			const view = View(panel);

			view.set({ title: "Site" });

			expect(panel.title).toStrictEqual("Site");
		});

		it("should push the state", async () => {
			const pushState = vi.spyOn(window.history, "pushState");
			const panel = Panel.create(app);
			const view = View(panel);

			view.set({ path: "/site", title: "Site" });

			expect(pushState).toHaveBeenCalledWith(
				null,
				"",
				"http://localhost:3000/site"
			);
			pushState.mockRestore();
		});

		it("should reset scroll position when path changes", async () => {
			const scrollTo = vi
				.spyOn(window, "scrollTo")
				.mockImplementation(() => {});
			const panel = Panel.create(app);
			const view = View(panel);

			view.set({ path: "/site", title: "Site" });

			expect(scrollTo).toHaveBeenCalledWith(0, 0);
			scrollTo.mockRestore();
		});

		it("should not push state when URL has not changed", async () => {
			const pushState = vi.spyOn(window.history, "pushState");
			const panel = Panel.create(app);
			const view = View(panel);

			// set path to match the current location
			view.set({ path: window.location.toString(), title: "Site" });

			expect(pushState).not.toHaveBeenCalled();
			pushState.mockRestore();
		});
	});
});
