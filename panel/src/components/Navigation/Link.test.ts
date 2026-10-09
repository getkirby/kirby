import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount, type VueWrapper } from "@vue/test-utils";
import { buildUrl } from "@/helpers/url";
import Link from "./Link.vue";

/**
 * Clicks the link and returns whether the link stopped the browser
 * from following it; the click itself never gets followed
 */
function click(wrapper: VueWrapper, init: MouseEventInit = {}) {
	let prevented = false;

	wrapper.element.addEventListener(
		"click",
		(event: Event) => {
			prevented = event.defaultPrevented;
			event.preventDefault();
		},
		{ once: true }
	);

	wrapper.element.dispatchEvent(
		new MouseEvent("click", { bubbles: true, cancelable: true, ...init })
	);

	return prevented;
}

function mount(props = {}, attrs = {}, slots = {}) {
	return vueMount(Link, {
		props: { to: "https://getkirby.com", ...props },
		attrs,
		slots,
		global: {
			mocks: {
				$go: vi.fn(),
				$url: (path: string) => buildUrl(path, {}, "https://getkirby.com")
			}
		}
	});
}

describe("Link.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "A", "k-link");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
	});

	// props
	describe("disabled prop", () => {
		it("renders a span", () => {
			const wrapper = mount({ disabled: true });
			expect(wrapper.element.tagName).toBe("SPAN");
			expect(wrapper.classes()).toContain("k-link");
			expect(wrapper.attributes("aria-disabled")).toBeDefined();
		});
	});

	describe("tabindex prop", () => {
		it("renders as tabindex attribute", () => {
			const wrapper = mount({ tabindex: -1 });
			expect(wrapper.attributes("tabindex")).toBe("-1");
		});
	});

	describe("target prop", () => {
		it("renders as target attribute", () => {
			const wrapper = mount({ target: "_blank" });
			expect(wrapper.attributes("target")).toBe("_blank");
		});
	});

	describe("title prop", () => {
		it("renders as title attribute", () => {
			const wrapper = mount({ title: "Kirby" });
			expect(wrapper.attributes("title")).toBe("Kirby");
		});

		it("is kept without a link", () => {
			const wrapper = mount({ title: "Kirby", to: undefined });
			expect(wrapper.attributes("title")).toBe("Kirby");
		});
	});

	describe("to prop", () => {
		it("renders a span without a link", () => {
			const wrapper = mount({ to: undefined });
			expect(wrapper.element.tagName).toBe("SPAN");
			expect(wrapper.attributes("aria-disabled")).toBeDefined();
		});
	});

	// computed
	describe("downloadAttr computed", () => {
		it("is not set without download", () => {
			expect(mount().attributes("download")).toBeUndefined();
		});

		it("names the download after an absolute URL's file", () => {
			const wrapper = mount({
				download: true,
				to: "https://getkirby.com/a/b.pdf"
			});
			expect(wrapper.attributes("download")).toBe("b.pdf");
		});

		it("names the download after a relative path's file", () => {
			const wrapper = mount({ download: true, to: "/a/b.pdf" });
			expect(wrapper.attributes("download")).toBe("b.pdf");
		});
	});

	describe("href computed", () => {
		it("passes an absolute URL on", () => {
			const wrapper = mount({ to: "https://getkirby.com/a" });
			expect(wrapper.attributes("href")).toBe("https://getkirby.com/a");
		});

		it("builds a Panel URL for a path", () => {
			const wrapper = mount({ to: "/pages/a" });
			expect(wrapper.attributes("href")).toBe("https://getkirby.com/pages/a");
		});

		it("keeps a path with a target", () => {
			const wrapper = mount({ to: "/pages/a", target: "_blank" });
			expect(wrapper.attributes("href")).toBe("/pages/a");
		});

		it("turns an email address into a mailto link", () => {
			const wrapper = mount({ to: "mail@getkirby.com" });
			expect(wrapper.attributes("href")).toBe("mailto:mail@getkirby.com");
		});

		it("keeps a mailto link", () => {
			const wrapper = mount({ to: "mailto:mail@getkirby.com" });
			expect(wrapper.attributes("href")).toBe("mailto:mail@getkirby.com");
		});

		it("keeps a URL with an @", () => {
			const wrapper = mount({ to: "https://getkirby.com/@/page/a" });
			expect(wrapper.attributes("href")).toBe("https://getkirby.com/@/page/a");
		});

		it("is empty for a dangerous scheme", () => {
			const wrapper = mount({ to: "javascript:alert(1)" });
			expect(wrapper.attributes("href")).toBe("");
		});

		it("is empty for a callback", () => {
			const wrapper = mount({ to: () => {} });
			expect(wrapper.attributes("href")).toBe("");
		});
	});

	describe("relAttr computed", () => {
		it("uses the rel prop", () => {
			const wrapper = mount({ rel: "nofollow" });
			expect(wrapper.attributes("rel")).toBe("nofollow");
		});

		it("protects a blank target", () => {
			const wrapper = mount({ rel: "nofollow", target: "_blank" });
			expect(wrapper.attributes("rel")).toBe("noreferrer noopener");
		});
	});

	// methods
	describe("isRoutable()", () => {
		it("routes a plain click on a Panel path", () => {
			const wrapper = mount({ to: "/pages/a" });
			expect(wrapper.vm.isRoutable({ button: 0 })).toBe(true);
		});

		it.each(["altKey", "ctrlKey", "metaKey", "shiftKey"])(
			"does not route a click with the %s",
			(key) => {
				const wrapper = mount({ to: "/pages/a" });
				expect(wrapper.vm.isRoutable({ [key]: true })).toBe(false);
			}
		);

		it("does not route a prevented click", () => {
			const wrapper = mount({ to: "/pages/a" });
			expect(wrapper.vm.isRoutable({ defaultPrevented: true })).toBe(false);
		});

		it("does not route other mouse buttons", () => {
			const wrapper = mount({ to: "/pages/a" });
			expect(wrapper.vm.isRoutable({ button: 1 })).toBe(false);
		});

		it("does not route a link with a target", () => {
			const wrapper = mount({ to: "/pages/a", target: "_blank" });
			expect(wrapper.vm.isRoutable({})).toBe(false);
		});

		it("does not route a download", () => {
			const wrapper = mount({ download: true, to: "/a/b.pdf" });
			expect(wrapper.vm.isRoutable({})).toBe(false);
		});

		it("does not route an absolute URL", () => {
			const wrapper = mount({ to: "https://getkirby.com/a" });
			expect(wrapper.vm.isRoutable({})).toBe(false);
		});

		it("does not route an email link", () => {
			const wrapper = mount({ to: "mail@getkirby.com" });
			expect(wrapper.vm.isRoutable({})).toBe(false);
		});
	});

	// events
	describe("click event", () => {
		it("is emitted with the click", () => {
			const wrapper = mount();
			click(wrapper);
			expect(wrapper.emitted("click")?.[0][0]).toBeInstanceOf(MouseEvent);
		});

		it("opens a Panel path in the Panel", () => {
			const wrapper = mount({ to: "/pages/a" });
			expect(click(wrapper)).toBe(true);
			expect(wrapper.vm.$go).toHaveBeenCalledWith("/pages/a");
		});

		it("leaves an absolute URL to the browser", () => {
			const wrapper = mount({ to: "https://getkirby.com/a" });
			expect(click(wrapper)).toBe(false);
			expect(wrapper.vm.$go).not.toHaveBeenCalled();
		});

		it("leaves a click with a modifier key to the browser", () => {
			const wrapper = mount({ to: "/pages/a" });
			expect(click(wrapper, { metaKey: true })).toBe(false);
			expect(wrapper.vm.$go).not.toHaveBeenCalled();
		});

		it("leaves a download to the browser", () => {
			const wrapper = mount({ download: true, to: "/a/b.pdf" });
			expect(click(wrapper)).toBe(false);
			expect(wrapper.vm.$go).not.toHaveBeenCalled();
		});

		it("calls a callback instead", () => {
			const to = vi.fn();
			const wrapper = mount({ to });
			expect(click(wrapper)).toBe(true);
			expect(to).toHaveBeenCalledOnce();
			expect(wrapper.vm.$go).not.toHaveBeenCalled();
		});

		it("is blocked for a dangerous scheme", () => {
			const wrapper = mount({ to: "javascript:alert(1)" });
			expect(click(wrapper)).toBe(true);
			expect(wrapper.emitted("click")).toBeUndefined();
			expect(wrapper.vm.$go).not.toHaveBeenCalled();
		});

		it("is not emitted while disabled", () => {
			const wrapper = mount({ disabled: true });
			click(wrapper);
			expect(wrapper.emitted("click")).toBeUndefined();
		});
	});

	// slots
	describe("default slot", () => {
		it("renders the link text", () => {
			const wrapper = mount({}, {}, { default: "Kirby" });
			expect(wrapper.text()).toBe("Kirby");
		});

		it("renders the text without a link", () => {
			const wrapper = mount({ to: undefined }, {}, { default: "Kirby" });
			expect(wrapper.text()).toBe("Kirby");
		});
	});
});
