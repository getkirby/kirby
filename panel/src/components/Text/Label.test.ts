import { describe, it, expect } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Label from "./Label.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(Label, {
		props,
		attrs,
		global: { mocks: { $t: (key: string) => key } }
	});
}

const component = (attrs = {}) => mount({ input: "field" }, attrs);

describe("Label.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(component, "LABEL", "k-label");
		it.acceptsClass(component);
		it.acceptsStyle(component);
	});

	// props
	describe("type prop", () => {
		it("defaults to field", () => {
			const wrapper = mount({ input: "field" });
			expect(wrapper.classes()).toContain("k-field-label");
		});

		it("renders type as class", () => {
			const wrapper = mount({ type: "section" });
			expect(wrapper.classes()).toContain("k-section-label");
		});

		it("renders as h2 when type is section", () => {
			const wrapper = mount({ type: "section" });
			expect(wrapper.element.tagName).toBe("H2");
		});
	});

	describe("input prop", () => {
		it("sets the for attribute", () => {
			const wrapper = mount({ input: "my-field" });
			expect(wrapper.attributes("for")).toBe("my-field");
		});

		it("renders as h2 when input is false", () => {
			const wrapper = mount({ input: false });
			expect(wrapper.element.tagName).toBe("H2");
		});
	});

	describe("link prop", () => {
		it("wraps content in a k-link", () => {
			const wrapper = mount({ link: "/foo" });
			expect(wrapper.find("k-link").attributes("to")).toBe("/foo");
		});

		it("renders slot without k-link when not provided", () => {
			const wrapper = mount();
			expect(wrapper.find("k-link").exists()).toBe(false);
		});
	});

	describe("required prop", () => {
		it("shows required marker when true", () => {
			const wrapper = mount({ required: true });
			expect(wrapper.find("abbr[title='field.required']").exists()).toBe(true);
		});

		it("hides required marker when false", () => {
			const wrapper = mount({ required: false });
			expect(wrapper.find("abbr[title='field.required']").exists()).toBe(false);
		});
	});

	describe("hasDiff prop", () => {
		it("sets data-has-diff attribute", () => {
			const wrapper = mount({ hasDiff: true });
			expect(wrapper.attributes("data-has-diff")).toBe("true");
		});
	});

	// slots
	describe("default slot", () => {
		it("renders slotted content", () => {
			const wrapper = vueMount(Label, {
				slots: { default: "My Label" },
				global: { mocks: { $t: (key: string) => key } }
			});
			expect(wrapper.text()).toContain("My Label");
		});
	});
});
