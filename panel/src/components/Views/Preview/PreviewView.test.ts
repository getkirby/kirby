import { afterEach, describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import type Api from "@/api";
import Pages from "@/api/pages";
import PreviewView from "./PreviewView.vue";

const dropdown = { methods: { close: vi.fn() }, render: () => null };
const events = { off: vi.fn(), on: vi.fn() };
const panel = {
	content: {
		diff: () => ({}),
		hasDiff: () => false,
		isProcessing: false
	},
	isLoading: false,
	view: {
		open: vi.fn(),
		path: "/pages/photography/preview/changes"
	}
};

function mount(props = {}, attrs = {}) {
	return vueMount(PreviewView, {
		props: {
			id: "photography",
			lock: { isLocked: false, user: {} },
			mode: "changes",
			src: {},
			...props
		},
		attrs,
		global: {
			components: { "k-dropdown": dropdown },
			mocks: {
				$api: { pages: Pages({} as Api) },
				$events: events,
				$panel: panel
			}
		}
	});
}

describe("k-preview-view", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-PANEL", "k-preview-view");
		it.inheritsNoAttrs(mount);
	});

	// methods
	describe("onScrollSyncing()", () => {
		afterEach(() => {
			localStorage.removeItem("kirby$preview$scroll");
		});

		it("starts with syncing on without a stored choice", () => {
			const wrapper = mount({ mode: "compare" });
			const toggle = wrapper.find(".k-preview-scroll-sync");
			expect(toggle.attributes("aria-checked")).toBe("true");
		});

		it("restores a stored choice", () => {
			localStorage.setItem("kirby$preview$scroll", "false");

			const wrapper = mount({ mode: "compare" });
			const toggle = wrapper.find(".k-preview-scroll-sync");
			expect(toggle.attributes("aria-checked")).toBe("false");
		});

		it("toggles syncing and remembers the choice", async () => {
			const wrapper = mount({ mode: "compare" });
			const toggle = wrapper.find(".k-preview-scroll-sync");
			await toggle.trigger("click");

			expect(toggle.attributes("aria-checked")).toBe("false");
			expect(localStorage.getItem("kirby$preview$scroll")).toBe("false");
		});
	});

	describe("onTreeNavigate()", () => {
		it("closes the tree dropdown", () => {
			const wrapper = mount();
			wrapper.vm.onTreeNavigate({ id: "photography/trees" });

			expect(dropdown.methods.close).toHaveBeenCalledOnce();
		});

		it("opens the preview of the selected page", () => {
			const wrapper = mount();
			wrapper.vm.onTreeNavigate({ id: "photography/trees" });

			expect(panel.view.open).toHaveBeenCalledWith(
				"pages/photography+trees/preview/changes"
			);
		});

		it("opens the preview of the site", () => {
			const wrapper = mount();
			wrapper.vm.onTreeNavigate({ id: "/" });

			expect(panel.view.open).toHaveBeenCalledWith("site/preview/changes");
		});

		it("ignores the current page", () => {
			const wrapper = mount();
			wrapper.vm.onTreeNavigate({ id: "photography" });

			expect(panel.view.open).not.toHaveBeenCalled();
		});

		it("reopens the current page when forced", () => {
			const wrapper = mount();
			wrapper.vm.onTreeNavigate({ id: "photography" }, true);

			expect(panel.view.open).toHaveBeenCalledWith(
				"pages/photography/preview/changes"
			);
		});
	});
});
