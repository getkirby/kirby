import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import CheckboxesField from "./CheckboxesField.vue";

const Input = {
	name: "k-checkboxes-input",
	props: ["options"],
	emits: ["input"],
	methods: { deselectAll: vi.fn(), selectAll: vi.fn() },
	template: "<div />"
};
const options = [{ text: "A", value: "a" }];

// a literal <k-field> tag only renders the default slot
const Field = { template: '<div><slot name="counter" /><slot /></div>' };

function mount(props = {}, attrs = {}, stubs = {}) {
	return vueMount(CheckboxesField, {
		props: { options, ...props },
		attrs,
		global: {
			stubs: {
				"k-checkboxes-input": Input,
				...stubs
			}
		}
	});
}

describe("k-checkboxes-field", () => {
	// $el
	describe("element", () => {
		const component = (attrs = {}) => mount({}, attrs);

		it.rendersAs(component, "K-FIELD", "k-checkboxes-field");
		it.acceptsClass(component);
		it.acceptsStyle(component);
		it.inheritsNoAttrs(component);
	});

	// props
	describe("batch prop", () => {
		const field = (props = {}) => mount(props, {}, { "k-field": Field });

		it("renders no batch buttons by default", () => {
			const wrapper = field();
			expect(wrapper.find("k-button-group").exists()).toBe(false);
		});

		it("deselects all options", async () => {
			const wrapper = field({ batch: true, value: ["a"] });
			await wrapper.find('k-button[icon="deselect-all"]').trigger("click");
			expect(Input.methods.deselectAll).toHaveBeenCalledOnce();
			expect(Input.methods.selectAll).not.toHaveBeenCalled();
		});

		it("selects all options", async () => {
			const wrapper = field({ batch: true });
			await wrapper.find('k-button[icon="select-all"]').trigger("click");
			expect(Input.methods.selectAll).toHaveBeenCalledOnce();
			expect(Input.methods.deselectAll).not.toHaveBeenCalled();
		});

		it("disables deselect all without a value", () => {
			const wrapper = field({ batch: true });
			const deselect = wrapper.find('k-button[icon="deselect-all"]');
			const select = wrapper.find('k-button[icon="select-all"]');
			expect(deselect.attributes("disabled")).toBe("true");
			expect(select.attributes("disabled")).toBe("false");
		});

		it("disables select all when all options are selected", () => {
			const wrapper = field({ batch: true, value: ["a"] });
			const deselect = wrapper.find('k-button[icon="deselect-all"]');
			const select = wrapper.find('k-button[icon="select-all"]');
			expect(deselect.attributes("disabled")).toBe("false");
			expect(select.attributes("disabled")).toBe("true");
		});
	});

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
			wrapper.findComponent(Input).vm.$emit("input", ["a"]);
			expect(wrapper.emitted("input")).toStrictEqual([[["a"]]]);
		});
	});
});
