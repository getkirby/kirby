import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import ToggleField from "./ToggleField.vue";

const Input = {
	name: "k-toggle-input",
	props: ["disabled", "id", "text", "value"],
	emits: ["input"],
	methods: { focus: vi.fn() },
	template: "<label />"
};

function mount(props = {}, attrs = {}) {
	return vueMount(ToggleField, {
		props,
		attrs,
		global: {
			stubs: { "k-toggle-input": Input }
		}
	});
}

describe("k-toggle-field", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-FIELD", "k-toggle-field");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("renders a k-toggle-input", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Input).exists()).toBe(true);
		});
	});

	// props
	describe("disabled prop", () => {
		it("passes the disabled state to the k-toggle-input", () => {
			const wrapper = mount({ disabled: true });
			expect(wrapper.findComponent(Input).props("disabled")).toBe(true);
		});
	});

	describe("id prop", () => {
		it("links the k-field label to the k-toggle-input", () => {
			const wrapper = mount({ id: "visible" });
			expect(wrapper.attributes("input")).toBe("visible");
			expect(wrapper.findComponent(Input).props("id")).toBe("visible");
		});
	});

	describe("text prop", () => {
		it("passes the text to the k-toggle-input", () => {
			const wrapper = mount({ text: ["Hidden", "Visible"] });
			expect(wrapper.findComponent(Input).props("text")).toStrictEqual([
				"Hidden",
				"Visible"
			]);
		});
	});

	describe("value prop", () => {
		it("passes the value to the k-toggle-input", () => {
			const wrapper = mount({ value: true });
			expect(wrapper.findComponent(Input).props("value")).toBe(true);
		});
	});

	// methods
	describe("focus()", () => {
		it("focuses the k-toggle-input", () => {
			const wrapper = mount();
			wrapper.vm.focus();
			expect(Input.methods.focus).toHaveBeenCalledOnce();
		});
	});

	// events
	describe("input event", () => {
		it("emits the value of the k-toggle-input", () => {
			const wrapper = mount();
			wrapper.findComponent(Input).vm.$emit("input", true);
			expect(wrapper.emitted("input")).toStrictEqual([[true]]);
		});
	});
});
