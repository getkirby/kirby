import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import MultiselectField from "./MultiselectField.vue";

const Input = {
	name: "k-input",
	props: ["options"],
	emits: ["input"],
	template: "<div />"
};
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

		it("renders an empty state without options when accepting only options", () => {
			const wrapper = mount({ accept: "options", options: [] });
			expect(wrapper.find("k-empty").exists()).toBe(true);
			expect(wrapper.findComponent(Input).exists()).toBe(false);
		});

		it("renders the input without options when accepting all", () => {
			const wrapper = mount({ accept: "all", options: [] });
			expect(wrapper.find("k-empty").exists()).toBe(false);
			expect(wrapper.findComponent(Input).exists()).toBe(true);
		});
	});

	// events
	describe("input event", () => {
		it("emits the value of the input", () => {
			const wrapper = mount();
			wrapper.findComponent(Input).vm.$emit("input", ["a"]);
			expect(wrapper.emitted("input")).toStrictEqual([[["a"]]]);
		});
	});
});
