import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import MultiselectField from "./MultiselectField.vue";

const Input = { name: "k-input", props: ["options"], template: "<div />" };
const options = [{ text: "A", value: "a" }];

function mount(props = {}, attrs = {}) {
	return vueMount(MultiselectField, {
		props: { options, ...props },
		attrs,
		global: {
			stubs: {
				"k-input": Input
			}
		}
	});
}

describe("k-multiselect-field", () => {
	// $el
	describe("element", () => {
		const component = (attrs = {}) => mount({}, attrs);

		it.rendersAs(component, "K-FIELD", "k-multiselect-field");
		it.acceptsClass(component);
		it.acceptsStyle(component);
		it.inheritsNoAttrs(component);
	});

	// props
	describe("options prop", () => {
		it("passes the options to the input", () => {
			const wrapper = mount({ value: ["a"] });
			expect(wrapper.findComponent(Input).props("options")).toStrictEqual(
				options
			);
		});

		it("adds a placeholder for a value without option", () => {
			const wrapper = mount({ value: ["a", "b"] });
			expect(wrapper.findComponent(Input).props("options")).toStrictEqual([
				...options,
				{ text: "–", value: "b" }
			]);
		});
	});
});
