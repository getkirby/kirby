import {
	afterEach,
	describe,
	expect,
	it,
	onTestFinished,
	vi
} from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import mitt from "mitt";
import type Api from "@/api";
import Pages from "@/api/pages";
import PreviewView from "./PreviewView.vue";

const browser = {
	emits: ["open"],
	methods: { reload: vi.fn() },
	render: () => null
};
const dropdown = { methods: { close: vi.fn() }, render: () => null };
const events = mitt();
const panel = {
	content: {
		diff: () => ({}),
		hasDiff: () => false,
		isProcessing: false
	},
	isLoading: false,
	language: { code: "en" },
	overlays: vi.fn((): string[] => []),
	view: {
		open: vi.fn(),
		path: "/pages/photography/preview/changes",
		refresh: vi.fn()
	}
};

function mount(props = {}, attrs = {}) {
	return vueMount(PreviewView, {
		props: {
			api: "pages/photography",
			id: "photography",
			link: "/pages/photography",
			lock: { isLocked: false, user: {} },
			mode: "changes",
			src: {},
			versions: { changes: {} },
			...props
		},
		attrs,
		global: {
			components: {
				"k-dropdown": dropdown,
				"k-preview-browser": browser
			},
			mocks: {
				$api: { pages: Pages({} as Api) },
				$events: events,
				$panel: panel
			}
		}
	});
}

/**
 * Opens the remote preview and collects the messages it receives
 */
function remote(wrapper: ReturnType<typeof mount>) {
	const messages: unknown[] = [];
	const channel = new BroadcastChannel("preview$photography");
	channel.addEventListener("message", (e) => messages.push(e.data));

	wrapper.findComponent(browser).vm.$emit("open");

	// the view closes its channel when the remote exits
	onTestFinished(async () => {
		channel.postMessage({ on: "remote:exit" });
		await vi.waitFor(() =>
			expect(wrapper.findComponent(browser).exists()).toBe(true)
		);
		channel.close();
	});

	return messages;
}

describe("k-preview-view", () => {
	afterEach(() => {
		events.all.clear();
	});

	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-PANEL", "k-preview-view");
		it.inheritsNoAttrs(mount);
	});

	// lifecycle
	describe("mounted hook", () => {
		const changes = [
			["content.discard", { api: "pages/photography", language: "en" }],
			["content.publish", { api: "pages/photography", language: "en" }],
			["content.save", { api: "pages/photography", language: "en" }],
			["model.update", { path: "pages/photography" }]
		] as const;

		it.each(changes)("reloads the browser on %s", (event, data) => {
			mount({ mode: "form" });
			events.emit(event, data);

			expect(browser.methods.reload).toHaveBeenCalledOnce();
		});

		it("announces changes to the remote preview", async () => {
			const wrapper = mount({ mode: "form" });
			const messages = remote(wrapper);
			events.emit("content.save", { api: "pages/photography", language: "en" });

			await vi.waitFor(() =>
				expect(messages).toStrictEqual([{ on: "host:changes" }])
			);
		});
	});

	// methods
	describe("exit()", () => {
		it("opens the model view on back", async () => {
			const wrapper = mount();
			await wrapper.find("k-button").trigger("click");

			expect(panel.view.open).toHaveBeenCalledWith("/pages/photography");
		});

		it("opens the model view on Esc", () => {
			mount();
			events.emit("keydown.esc");

			expect(panel.view.open).toHaveBeenCalledWith("/pages/photography");
		});

		it("ignores back while overlays are open", async () => {
			panel.overlays.mockReturnValueOnce(["dialog"]);

			const wrapper = mount();
			await wrapper.find("k-button").trigger("click");

			expect(panel.view.open).not.toHaveBeenCalled();
		});

		it("ignores Esc while overlays are open", () => {
			panel.overlays.mockReturnValueOnce(["drawer"]);

			mount();
			events.emit("keydown.esc");

			expect(panel.view.open).not.toHaveBeenCalled();
		});
	});

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
