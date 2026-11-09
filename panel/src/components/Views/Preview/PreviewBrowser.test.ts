import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import PreviewBrowser from "./PreviewBrowser.vue";

function mount(site: string, props = {}, attrs = {}) {
	return vueMount(PreviewBrowser, {
		props: {
			src: "https://getkirby.com/test",
			...props
		},
		attrs,
		global: {
			mocks: {
				$panel: {
					urls: {
						panel: site + "/panel",
						site
					}
				}
			}
		}
	});
}

function anchor(href: string) {
	const link = document.createElement("a");
	link.href = href;
	return link;
}

/**
 * Simulates a click on a link inside the iframe
 */
function click(wrapper: ReturnType<typeof mount>, link: HTMLAnchorElement) {
	const event = {
		preventDefault: vi.fn(),
		target: { closest: () => link }
	};

	wrapper.vm.onClick(event);

	return event;
}

describe("k-preview-browser", () => {
	// $el
	describe("element", () => {
		const component = (attrs = {}) => mount("https://getkirby.com", {}, attrs);

		it.rendersAs(component, "DIV", "k-preview-browser");
		it.acceptsClass(component);
		it.acceptsStyle(component);
	});

	// methods
	describe("isPanelLink()", () => {
		it("accepts the Panel and paths inside it", () => {
			const { vm } = mount("https://getkirby.com/demo");

			expect(vm.isPanelLink(anchor("https://getkirby.com/demo/panel"))).toBe(
				true
			);
			expect(
				vm.isPanelLink(anchor("https://getkirby.com/demo/panel/pages/notes"))
			).toBe(true);
		});

		it("rejects paths that only start like the Panel path", () => {
			const { vm } = mount("https://getkirby.com/demo");

			expect(vm.isPanelLink(anchor("https://getkirby.com/demo/panel-2"))).toBe(
				false
			);
		});

		it("resolves a relative Panel URL", () => {
			const { vm } = mount("/demo");

			expect(
				vm.isPanelLink(anchor(location.origin + "/demo/panel/pages/notes"))
			).toBe(true);
			expect(vm.isPanelLink(anchor(location.origin + "/demo/notes"))).toBe(
				false
			);
		});
	});

	describe("isSiteLink()", () => {
		it("accepts all paths for a site in the root", () => {
			const { vm } = mount("https://getkirby.com");

			expect(vm.isSiteLink(anchor("https://getkirby.com"))).toBe(true);
			expect(vm.isSiteLink(anchor("https://getkirby.com/notes"))).toBe(true);
		});

		it("accepts paths inside the subfolder", () => {
			const { vm } = mount("https://getkirby.com/demo");

			expect(vm.isSiteLink(anchor("https://getkirby.com/demo"))).toBe(true);
			expect(vm.isSiteLink(anchor("https://getkirby.com/demo/"))).toBe(true);
			expect(vm.isSiteLink(anchor("https://getkirby.com/demo/notes"))).toBe(
				true
			);
		});

		it("rejects paths outside the subfolder", () => {
			const { vm } = mount("https://getkirby.com/demo");

			expect(vm.isSiteLink(anchor("https://getkirby.com"))).toBe(false);
			expect(vm.isSiteLink(anchor("https://getkirby.com/notes"))).toBe(false);
			expect(vm.isSiteLink(anchor("https://getkirby.com/demo-2"))).toBe(false);
		});

		it("rejects other origins", () => {
			const { vm } = mount("https://getkirby.com");

			expect(vm.isSiteLink(anchor("https://example.com"))).toBe(false);
			expect(vm.isSiteLink(anchor("https://getkirby.com.example.com"))).toBe(
				false
			);
		});

		it("resolves a relative site URL", () => {
			const { vm } = mount("/demo");

			expect(vm.isSiteLink(anchor(location.origin + "/demo/notes"))).toBe(true);
			expect(vm.isSiteLink(anchor(location.origin + "/notes"))).toBe(false);
		});
	});

	describe("onClick()", () => {
		it("navigates for links inside the subfolder", () => {
			const wrapper = mount("https://getkirby.com/demo");
			const link = anchor("https://getkirby.com/demo/notes");
			const event = click(wrapper, link);

			expect(event.preventDefault).toHaveBeenCalled();
			expect(wrapper.emitted("navigate")).toStrictEqual([
				[{ view: link.href }]
			]);
		});

		it("only navigates the browser when pinned", () => {
			const wrapper = mount("https://getkirby.com/demo", { isPinned: true });
			const link = anchor("https://getkirby.com/demo/notes");

			click(wrapper, link);

			expect(wrapper.emitted("navigate")).toStrictEqual([
				[{ browser: link.href }]
			]);
		});

		it("opens links outside the subfolder in a new tab", () => {
			const wrapper = mount("https://getkirby.com/demo");
			const link = anchor("https://getkirby.com/notes");
			const event = click(wrapper, link);

			expect(link.target).toBe("_blank");
			expect(event.preventDefault).not.toHaveBeenCalled();
			expect(wrapper.emitted("navigate")).toBeUndefined();
		});

		it("opens Panel links in a new tab", () => {
			const wrapper = mount("https://getkirby.com/demo");
			const link = anchor("https://getkirby.com/demo/panel/pages/notes");
			const event = click(wrapper, link);

			expect(link.target).toBe("_blank");
			expect(event.preventDefault).not.toHaveBeenCalled();
			expect(wrapper.emitted("navigate")).toBeUndefined();
		});

		it("opens Panel links in a new tab for a relative Panel URL", () => {
			const wrapper = mount("/demo");
			const link = anchor(location.origin + "/demo/panel/pages/notes");
			const event = click(wrapper, link);

			expect(link.target).toBe("_blank");
			expect(event.preventDefault).not.toHaveBeenCalled();
			expect(wrapper.emitted("navigate")).toBeUndefined();
		});

		it("navigates for pages that only start like the Panel path", () => {
			const wrapper = mount("https://getkirby.com/demo");
			const link = anchor("https://getkirby.com/demo/panel-2");
			const event = click(wrapper, link);

			expect(link.target).toBe("");
			expect(event.preventDefault).toHaveBeenCalled();
			expect(wrapper.emitted("navigate")).toStrictEqual([
				[{ view: link.href }]
			]);
		});
	});
});
