import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import RangeField from "./RangeField.vue";

const Input = {
	name: "k-input",
	props: ["id", "max", "min", "step", "tooltip", "type", "value"],
	emits: ["input"],
	methods: { focus: vi.fn() },
	template: "<input />"
};

function mount(props = {}, attrs = {}) {
	return vueMount(RangeField, {
		props,
		attrs,
		global: {
			stubs: { "k-input": Input }
		}
	});
}

describe("k-range-field", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-FIELD", "k-range-field");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("renders a range k-input", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Input).props("type")).toBe("range");
		});
	});

	// props
	describe("id prop", () => {
		it("links the k-field label to the k-input", () => {
			const wrapper = mount({ id: "amount" });
			expect(wrapper.attributes("input")).toBe("amount");
			expect(wrapper.findComponent(Input).props("id")).toBe("amount");
		});
	});

	describe("max prop", () => {
		it("passes the maximum to the k-input", () => {
			const wrapper = mount({ max: 50 });
			expect(wrapper.findComponent(Input).props("max")).toBe(50);
		});
	});

	describe("min prop", () => {
		it("passes the minimum to the k-input", () => {
			const wrapper = mount({ min: 5 });
			expect(wrapper.findComponent(Input).props("min")).toBe(5);
		});
	});

	describe("tooltip prop", () => {
		it("passes the tooltip to the k-input", () => {
			const wrapper = mount({ tooltip: { after: "%" } });
			expect(wrapper.findComponent(Input).props("tooltip")).toStrictEqual({
				after: "%"
			});
		});
	});

	describe("value prop", () => {
		it("passes the value to the k-input", () => {
			const wrapper = mount({ value: 40 });
			expect(wrapper.findComponent(Input).props("value")).toBe(40);
		});
	});

	// methods
	describe("focus()", () => {
		it("focuses the k-input", () => {
			const wrapper = mount();
			wrapper.vm.focus();
			expect(Input.methods.focus).toHaveBeenCalledOnce();
		});
	});

	// events
	describe("input event", () => {
		it("emits the value of the k-input", () => {
			const wrapper = mount();
			wrapper.findComponent(Input).vm.$emit("input", 40);
			expect(wrapper.emitted("input")).toStrictEqual([[40]]);
		});
	});
});
