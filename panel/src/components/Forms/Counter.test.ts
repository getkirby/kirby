import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Counter from "./Counter.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(Counter, {
		props: { count: 3, ...props },
		attrs
	});
}

describe("k-counter", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "SPAN", "k-counter");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
	});

	// props
	describe("count prop", () => {
		it("renders the count", () => {
			const wrapper = mount({ count: 5 });
			expect(wrapper.text()).toBe("5");
		});
	});

	describe("max prop", () => {
		it("renders no rules by default", () => {
			const wrapper = mount();
			expect(wrapper.find(".k-counter-rules").exists()).toBe(false);
		});

		it("renders the maximum", () => {
			const wrapper = mount({ max: 10 });
			expect(wrapper.find(".k-counter-rules").text()).toBe("≤ 10");
		});

		it("renders the range with a minimum", () => {
			const wrapper = mount({ max: 10, min: 2 });
			expect(wrapper.find(".k-counter-rules").text()).toBe("2–10");
		});
	});

	describe("min prop", () => {
		it("renders the minimum", () => {
			const wrapper = mount({ min: 2 });
			expect(wrapper.find(".k-counter-rules").text()).toBe("≥ 2");
		});
	});

	// computed
	describe("valid computed", () => {
		it("accepts an empty count when not required", () => {
			const wrapper = mount({ count: 0, min: 2 });
			expect(wrapper.vm.valid).toBe(true);
		});

		it("rejects an empty count when required", () => {
			const wrapper = mount({ count: 0, required: true });
			expect(wrapper.vm.valid).toBe(false);
		});

		it("rejects a count below the minimum", () => {
			const wrapper = mount({ count: 1, min: 2 });
			expect(wrapper.vm.valid).toBe(false);
		});

		it("rejects a count above the maximum", () => {
			const wrapper = mount({ count: 11, max: 10 });
			expect(wrapper.vm.valid).toBe(false);
		});

		it("accepts a count at the minimum", () => {
			const wrapper = mount({ count: 2, min: 2 });
			expect(wrapper.vm.valid).toBe(true);
		});

		it("accepts a count at the maximum", () => {
			const wrapper = mount({ count: 10, max: 10 });
			expect(wrapper.vm.valid).toBe(true);
		});

		it("marks a valid count as positive", () => {
			const wrapper = mount();
			expect(wrapper.attributes("data-invalid")).toBe("false");
			expect(wrapper.attributes("data-theme")).toBe("positive");
		});

		it("marks an invalid count as negative", () => {
			const wrapper = mount({ max: 2 });
			expect(wrapper.attributes("data-invalid")).toBe("true");
			expect(wrapper.attributes("data-theme")).toBe("negative");
		});
	});
});
