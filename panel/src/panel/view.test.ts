import { describe, expect, it, vi } from "vitest";
import View from "./view";

// dummy panel to avoid dependencies
const Panel = () => {
	return {
		dialog: {},
		drawer: {},
		title: undefined as string | undefined,
		open(url: string | URL, options?: Record<string, unknown>) {
			return { url, options };
		},
		url(path?: string) {
			return {
				toString() {
					return path;
				}
			};
		}
	};
};

describe("panel.view", () => {
	describe("state", () => {
		it("should have a default state", async () => {
			const view = View(Panel());

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
			const panel = Panel();
			// make panel.open hang so the first load doesn't finish before the second starts
			// @ts-expect-error overriding open to hang indefinitely
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
			const panel = Panel();
			const view = View(panel);

			view.set({ title: "Site" });

			expect(panel.title).toStrictEqual("Site");
		});

		it("should push the state", async () => {
			const pushState = vi.spyOn(window.history, "pushState");
			const panel = Panel();
			const view = View(panel);

			view.set({ path: "/site", title: "Site" });

			expect(pushState).toHaveBeenCalledWith(null, "", "/site");
			pushState.mockRestore();
		});

		it("should reset scroll position when path changes", async () => {
			const scrollTo = vi.spyOn(window, "scrollTo").mockImplementation(() => {});
			const panel = Panel();
			const view = View(panel);

			view.set({ path: "/site", title: "Site" });

			expect(scrollTo).toHaveBeenCalledWith(0, 0);
			scrollTo.mockRestore();
		});

		it("should not push state when URL has not changed", async () => {
			const pushState = vi.spyOn(window.history, "pushState");
			const panel = Panel();
			const view = View(panel);

			// set path to match the current location
			view.set({ path: window.location.toString(), title: "Site" });

			expect(pushState).not.toHaveBeenCalled();
			pushState.mockRestore();
		});

	});
});
