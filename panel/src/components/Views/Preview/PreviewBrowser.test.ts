import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import PreviewBrowser from "./PreviewBrowser.vue";

function mount(site: string, props = {}) {
	return vueMount(PreviewBrowser, {
		props: {
			src: "https://getkirby.com/test",
			...props
		},
		shallow: true,
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

describe("PreviewBrowser.vue", () => {
	describe("methods", () => {
		describe("isSiteLink", () => {
			it("should accept all paths for a site in the root", () => {
				const { vm } = mount("https://getkirby.com");

				expect(vm.isSiteLink(anchor("https://getkirby.com"))).toBe(true);
				expect(vm.isSiteLink(anchor("https://getkirby.com/notes"))).toBe(true);
			});

			it("should accept paths inside the subfolder", () => {
				const { vm } = mount("https://getkirby.com/demo");

				expect(vm.isSiteLink(anchor("https://getkirby.com/demo"))).toBe(true);
				expect(vm.isSiteLink(anchor("https://getkirby.com/demo/"))).toBe(true);
				expect(vm.isSiteLink(anchor("https://getkirby.com/demo/notes"))).toBe(
					true
				);
			});

			it("should reject paths outside the subfolder", () => {
				const { vm } = mount("https://getkirby.com/demo");

				expect(vm.isSiteLink(anchor("https://getkirby.com"))).toBe(false);
				expect(vm.isSiteLink(anchor("https://getkirby.com/notes"))).toBe(false);
				expect(vm.isSiteLink(anchor("https://getkirby.com/demo-2"))).toBe(
					false
				);
			});

			it("should reject other origins", () => {
				const { vm } = mount("https://getkirby.com");

				expect(vm.isSiteLink(anchor("https://example.com"))).toBe(false);
				expect(vm.isSiteLink(anchor("https://getkirby.com.example.com"))).toBe(
					false
				);
			});

			it("should resolve a relative site URL", () => {
				const { vm } = mount("/demo");

				expect(vm.isSiteLink(anchor(location.origin + "/demo/notes"))).toBe(
					true
				);
				expect(vm.isSiteLink(anchor(location.origin + "/notes"))).toBe(false);
			});
		});

		describe("onClick", () => {
			it("should navigate for links inside the subfolder", () => {
				const wrapper = mount("https://getkirby.com/demo");
				const link = anchor("https://getkirby.com/demo/notes");
				const event = click(wrapper, link);

				expect(event.preventDefault).toHaveBeenCalled();
				expect(wrapper.emitted("navigate")).toStrictEqual([
					[{ view: link.href }]
				]);
			});

			it("should only navigate the browser when pinned", () => {
				const wrapper = mount("https://getkirby.com/demo", { isPinned: true });
				const link = anchor("https://getkirby.com/demo/notes");

				click(wrapper, link);

				expect(wrapper.emitted("navigate")).toStrictEqual([
					[{ browser: link.href }]
				]);
			});

			it("should open links outside the subfolder in a new tab", () => {
				const wrapper = mount("https://getkirby.com/demo");
				const link = anchor("https://getkirby.com/notes");
				const event = click(wrapper, link);

				expect(link.target).toBe("_blank");
				expect(event.preventDefault).not.toHaveBeenCalled();
				expect(wrapper.emitted("navigate")).toBeUndefined();
			});

			it("should open Panel links in a new tab", () => {
				const wrapper = mount("https://getkirby.com/demo");
				const link = anchor("https://getkirby.com/demo/panel/pages/notes");
				const event = click(wrapper, link);

				expect(link.target).toBe("_blank");
				expect(event.preventDefault).not.toHaveBeenCalled();
				expect(wrapper.emitted("navigate")).toBeUndefined();
			});
		});
	});
});
