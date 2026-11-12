import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import RadioField from "./RadioField.vue";

const Input = {
	name: "k-radio-input",
	props: ["options"],
	emits: ["input"],
	template: "<div />"
};
const options = [{ text: "A", value: "a" }];

function mount(props = {}, attrs = {}) {
	return vueMount(RadioField, {
		props: { options, ...props },
		attrs,
		global: {
			stubs: {
				"k-radio-input": Input
			}
		}
	});
}

describe("k-radio-field", () => {
	// $el
	describe("element", () => {
		const component = (attrs = {}) => mount({}, attrs);

		it.rendersAs(component, "K-FIELD", "k-radio-field");
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
