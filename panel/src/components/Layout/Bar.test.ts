import { describe, it, expect } from "@test/unit";
import { mount } from "@vue/test-utils";
import Bar from "./Bar.vue";

describe("Bar.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(Bar, "DIV", "k-bar");
		it.acceptsClass(Bar);
		it.acceptsStyle(Bar);
	});

	// props
	describe("align prop", () => {
		it("defaults to start", () => {
			const wrapper = mount(Bar);
			expect(wrapper.attributes("data-align")).toBe("start");
		});

		it("reflects the prop as data-align attribute", () => {
			const wrapper = mount(Bar, { props: { align: "center" } });
			expect(wrapper.attributes("data-align")).toBe("center");
		});
	});

	// slots
	describe("default slot", () => {
		it("renders slotted content", () => {
			const wrapper = mount(Bar, {
				slots: { default: "<button>Foo</button>" }
			});
			expect(wrapper.find("button").text()).toBe("Foo");
		});
	});
});
