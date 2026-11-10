import { describe, it, expect, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Link from "@/components/Navigation/Link.vue";
import Stat from "./Stat.vue";

function mount(props = {}, mocks = {}) {
	return vueMount(Stat, {
		props,
		global: {
			components: { "k-link": Link },
			mocks
		}
	});
}

describe("k-stat", () => {
	// $el
	describe("element", () => {
		it.rendersAs(Stat, "DIV", "k-stat");
		it.acceptsClass(Stat);
		it.acceptsStyle(Stat);
	});

	// props
	describe("click prop", () => {
		it("is called when the stat is clicked", async () => {
			const click = vi.fn();
			const wrapper = mount({ click });
			await wrapper.trigger("click");
			expect(click).toHaveBeenCalledOnce();
		});
	});

	describe("dialog prop", () => {
		it("opens the dialog when the stat is clicked", async () => {
			const open = vi.fn();
			const wrapper = mount(
				{ dialog: "pages/create" },
				{ $panel: { dialog: { open } } }
			);
			await wrapper.trigger("click");
			expect(open).toHaveBeenCalledWith("pages/create");
		});
	});

	describe("drawer prop", () => {
		it("opens the drawer when the stat is clicked", async () => {
			const open = vi.fn();
			const wrapper = mount(
				{ drawer: "pages/preview" },
				{ $panel: { drawer: { open } } }
			);
			await wrapper.trigger("click");
			expect(open).toHaveBeenCalledWith("pages/preview");
		});
	});

	describe("icon prop", () => {
		it("renders a k-icon with the correct type", () => {
			const wrapper = mount({ label: "Pages", icon: "page" });
			expect(wrapper.find("k-icon").attributes("type")).toBe("page");
		});

		it("does not render a k-icon when not provided", () => {
			const wrapper = mount({ label: "Pages" });
			expect(wrapper.find("k-icon").exists()).toBe(false);
		});
	});

	describe("info prop", () => {
		it("renders info text", () => {
			const wrapper = mount({ info: "Last week" });
			expect(wrapper.find(".k-stat-info").text()).toBe("Last week");
		});

		it("does not render info element when not provided", () => {
			const wrapper = mount();
			expect(wrapper.find(".k-stat-info").exists()).toBe(false);
		});
	});

	describe("label prop", () => {
		it("renders label text", () => {
			const wrapper = mount({ label: "Pages" });
			expect(wrapper.find(".k-stat-label").text()).toBe("Pages");
		});

		it("does not render label element when not provided", () => {
			const wrapper = mount();
			expect(wrapper.find(".k-stat-label").exists()).toBe(false);
		});
	});

	describe("link prop", () => {
		it("links the stat to the URL", () => {
			const wrapper = mount({ link: "https://getkirby.com" });
			expect(wrapper.element.tagName).toBe("A");
			expect(wrapper.attributes("href")).toBe("https://getkirby.com");
		});
	});

	describe("theme prop", () => {
		it("renders value as attribute", () => {
			const wrapper = mount({ theme: "positive" });
			expect(wrapper.attributes("data-theme")).toBe("positive");
		});
	});

	describe("value prop", () => {
		it("renders value text", () => {
			const wrapper = mount({ value: "123" });
			expect(wrapper.find(".k-stat-value").text()).toBe("123");
		});

		it("does not render value element when not provided", () => {
			const wrapper = mount();
			expect(wrapper.find(".k-stat-value").exists()).toBe(false);
		});
	});
});
