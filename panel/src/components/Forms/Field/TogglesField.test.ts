import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import TogglesField from "./TogglesField.vue";

const Input = {
	name: "k-input",
	props: ["options"],
	emits: ["input"],
	template: "<div />"
};
const options = [{ text: "A", value: "a" }];

function mount(props = {}, attrs = {}) {
	return vueMount(TogglesField, {
		props: { options, ...props },
		attrs,
		global: {
			stubs: {
				"k-input": Input
			}
		}
	});
}

describe("k-toggles-field", () => {
	// $el
	describe("element", () => {
		const component = (attrs = {}) => mount({}, attrs);

		it.rendersAs(component, "K-FIELD", "k-toggles-field");
		it.acceptsClass(component);
		it.acceptsStyle(component);
		it.inheritsNoAttrs(component);
	});

	// props
	describe("options prop", () => {
		it("passes the options to the input", () => {
			const wrapper = mount({ value: "a" });
			expect(wrapper.findComponent(Input).props("options")).toStrictEqual(
				options
			);
		});

		it("adds an option for a value without option", () => {
			const wrapper = mount({ value: "b" });
			expect(wrapper.findComponent(Input).props("options")).toStrictEqual([
				...options,
				{ text: "[unknown]", value: "b" }
			]);
		});

		it("renders an empty state without options", () => {
			const wrapper = mount({ options: [] });
			expect(wrapper.find("k-empty").exists()).toBe(true);
			expect(wrapper.findComponent(Input).exists()).toBe(false);
		});
	});

	// events
	describe("input event", () => {
		it("emits the value of the input", () => {
			const wrapper = mount();
			wrapper.findComponent(Input).vm.$emit("input", "a");
			expect(wrapper.emitted("input")).toStrictEqual([["a"]]);
		});
	});
});
