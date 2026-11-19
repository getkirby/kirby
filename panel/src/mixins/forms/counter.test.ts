import { describe, expect, it } from "vitest";
import { mount as vueMount } from "@vue/test-utils";
import counter from "./counter.js";

const Component = {
	mixins: [counter],
	props: {
		disabled: Boolean,
		max: Number,
		maxlength: Number,
		min: Number,
		minlength: Number,
		value: [Array, String]
	},
	template: "<div />"
};

function mount(props = {}) {
	return vueMount(Component, { props: { value: "abc", ...props } });
}

describe("counter", () => {
	// props
	describe("counter prop", () => {
		it("shows the counter by default", () => {
			const wrapper = mount();
			expect(wrapper.vm.counter).toBe(true);
		});

		it("hides the counter", () => {
			const wrapper = mount({ counter: false });
			expect(wrapper.vm.counterOptions).toBe(false);
		});
	});

	// computed
	describe("counterOptions computed", () => {
		it("counts the characters of the value", () => {
			const wrapper = mount();
			expect(wrapper.vm.counterOptions).toStrictEqual({
				count: 3,
				max: undefined,
				min: undefined
			});
		});

		it("counts the items of an array value", () => {
			const wrapper = mount({ value: ["a", "b"] });
			expect(wrapper.vm.counterOptions).toMatchObject({ count: 2 });
		});

		it("prefers min and max over minlength and maxlength", () => {
			const wrapper = mount({ max: 10, maxlength: 20, min: 2, minlength: 1 });
			expect(wrapper.vm.counterOptions).toMatchObject({ max: 10, min: 2 });
		});

		it("falls back to minlength and maxlength", () => {
			const wrapper = mount({ maxlength: 10, minlength: 2 });
			expect(wrapper.vm.counterOptions).toMatchObject({ max: 10, min: 2 });
		});

		it("returns false without value", () => {
			const wrapper = mount({ value: "" });
			expect(wrapper.vm.counterOptions).toBe(false);
		});

		it("returns false when disabled", () => {
			const wrapper = mount({ disabled: true });
			expect(wrapper.vm.counterOptions).toBe(false);
		});

		it("counts the counterValue instead of the value", () => {
			const wrapper = vueMount(
				{ ...Component, computed: { counterValue: () => "abcdef" } },
				{ props: { value: "abc" } }
			);
			expect(wrapper.vm.counterOptions).toMatchObject({ count: 6 });
		});
	});

	describe("counterValue computed", () => {
		it("is null by default", () => {
			const wrapper = mount();
			expect(wrapper.vm.counterValue).toBe(null);
		});
	});
});
