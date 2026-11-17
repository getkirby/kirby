import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import AlphaInput from "./AlphaInput.vue";
import RangeInput from "./RangeInput.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(AlphaInput, {
		props,
		attrs,
		// focus only works on connected elements
		attachTo: document.body,
		global: {
			// registered globally in the Panel
			components: { "k-range-input": RangeInput }
		}
	});
}

function range(wrapper: ReturnType<typeof mount>) {
	return wrapper.find("input").element;
}

describe("k-alpha-input", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-alpha-input");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("renders a k-range-input from 0 to 1", () => {
			const wrapper = mount({ max: 10, min: 5 });
			expect(wrapper.classes()).toContain("k-range-input");
			expect(range(wrapper).min).toBe("0");
			expect(range(wrapper).max).toBe("1");
		});
	});

	// props
	describe("step prop", () => {
		it("defaults to 0.01", () => {
			const wrapper = mount();
			expect(range(wrapper).step).toBe("0.01");
		});
	});

	describe("tooltip prop", () => {
		it("shows no tooltip by default", () => {
			const wrapper = mount();
			expect(wrapper.find("output").exists()).toBe(false);
		});

		it("shows a tooltip", () => {
			const wrapper = mount({ tooltip: true, value: 0.5 });
			expect(wrapper.find("output").exists()).toBe(true);
		});
	});

	describe("value prop", () => {
		it("positions the slider", () => {
			const wrapper = mount({ value: 0.5 });
			expect(range(wrapper).value).toBe("0.5");
		});
	});

	// methods
	describe("focus()", () => {
		it("focuses the slider", () => {
			const wrapper = mount();
			wrapper.vm.focus();
			expect(document.activeElement).toBe(range(wrapper));
		});
	});

	// events
	describe("input event", () => {
		it("emits the number of the slider", async () => {
			const wrapper = mount();
			await wrapper.find("input").setValue("0.5");
			expect(wrapper.emitted("input")).toStrictEqual([[0.5]]);
		});
	});
});
