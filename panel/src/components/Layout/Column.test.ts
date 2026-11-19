import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Column from "./Column.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(Column, {
		props,
		attrs,
		slots: { default: "<p>Content</p>" }
	});
}

function style(wrapper: ReturnType<typeof mount>) {
	return (wrapper.element as HTMLElement).style;
}

describe("k-column", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-column");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);

		it("keeps the width next to a custom style", () => {
			const wrapper = mount({ width: "1/2" }, { style: "--foo: bar" });
			expect(style(wrapper).getPropertyValue("--width")).toBe("1/2");
			expect(style(wrapper).getPropertyValue("--foo")).toBe("bar");
		});
	});

	// props
	describe("sticky prop", () => {
		it("renders the content directly by default", () => {
			const wrapper = mount();
			expect(wrapper.attributes("data-sticky")).toBe("false");
			expect(wrapper.element.firstElementChild?.tagName).toBe("P");
		});

		it("wraps the content for sticky columns", () => {
			const wrapper = mount({ sticky: true });
			expect(wrapper.attributes("data-sticky")).toBe("true");
			expect(wrapper.element.firstElementChild?.tagName).toBe("DIV");
			expect(wrapper.find(":scope > div > p").text()).toBe("Content");
		});
	});

	describe("width prop", () => {
		it("spans the full width by default", () => {
			expect(style(mount()).getPropertyValue("--width")).toBe("1/1");
		});

		it("sets the width", () => {
			const wrapper = mount({ width: "2/3" });
			expect(style(wrapper).getPropertyValue("--width")).toBe("2/3");
		});
	});

	// slots
	describe("default slot", () => {
		it("renders the content", () => {
			expect(mount().text()).toBe("Content");
		});
	});
});
