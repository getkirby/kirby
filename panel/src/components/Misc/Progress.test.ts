import { describe, it, expect } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Progress from "./Progress.vue";

function mount(props: Record<string, unknown> = {}) {
	return vueMount(Progress, { props: props as never });
}

function bar(wrapper: ReturnType<typeof mount>) {
	return wrapper.find("progress");
}

describe("k-progress", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "LABEL", "k-progress");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);

		it("max attribute is always 100", () => {
			const wrapper = mount();
			expect(bar(wrapper).attributes("max")).toBe("100");
		});
	});

	// props
	describe("label prop", () => {
		it("names the bar for screen readers", () => {
			const wrapper = mount({ label: "Upload" });
			expect(wrapper.find(".sr-only").text()).toBe("Upload");
		});

		it("falls back to a generic label", () => {
			const wrapper = mount();
			expect(wrapper.find(".sr-only").text()).toBe("progress");
		});
	});

	describe("value prop", () => {
		it("defaults to 0", () => {
			const wrapper = mount();
			expect(bar(wrapper).attributes("value")).toBe("0");
		});

		it("reflects the prop as attribute", () => {
			const wrapper = mount({ value: 42 });
			expect(bar(wrapper).attributes("value")).toBe("42");
		});

		it("renders value as percentage text", () => {
			const wrapper = mount({ value: 75 });
			expect(bar(wrapper).text()).toBe("75%");
		});
	});
});
