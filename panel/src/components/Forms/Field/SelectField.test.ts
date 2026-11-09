import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import field from "@/helpers/field";
import SelectField from "./SelectField.vue";

const Input = { name: "k-input", props: ["options"], template: "<div />" };
const options = [{ text: "A", value: "a" }];

function mount(props = {}, attrs = {}) {
	return vueMount(SelectField, {
		props: { options, ...props },
		attrs,
		global: {
			mocks: { $helper: { field }, $t: (key: string) => key },
			stubs: {
				"k-field": { template: "<div><slot /></div>" },
				"k-input": Input
			}
		}
	});
}

describe("SelectField.vue", () => {
	// $el
	describe("element", () => {
		const component = (attrs = {}) => mount({}, attrs);

		it.rendersAs(component, "DIV", "k-select-field");
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

		it("adds a placeholder for a value without option", () => {
			const wrapper = mount({ value: "b" });
			expect(wrapper.findComponent(Input).props("options")).toStrictEqual([
				...options,
				{ text: "–", value: "b" }
			]);
		});
	});
});
