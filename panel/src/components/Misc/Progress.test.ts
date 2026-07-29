import { describe, it, expect } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Progress from "./Progress.vue";

function mount(props: Record<string, unknown> = {}) {
	return vueMount(Progress, {
		props: props as never,
		global: {
			mocks: {
				$t: (key: string) => key
			}
		}
	});
}

function bar(wrapper: ReturnType<typeof mount>) {
	return wrapper.find("progress");
}

describe("Progress.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "LABEL", "k-progress");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);

		it("max attribute is always 100", () => {
			expect(bar(mount()).attributes("max")).toBe("100");
		});
	});

	// props
	describe("value prop", () => {
		it("defaults to 0", () => {
			expect(bar(mount()).attributes("value")).toBe("0");
		});

		it("reflects the prop as attribute", () => {
			expect(bar(mount({ value: 42 })).attributes("value")).toBe("42");
		});

		it("renders value as percentage text", () => {
			expect(bar(mount({ value: 75 })).text()).toBe("75%");
		});
	});

	describe("label prop", () => {
		it("names the bar for screen readers", () => {
			const wrapper = mount({ label: "Upload" });
			expect(wrapper.find(".sr-only").text()).toBe("Upload");
		});

		it("falls back to a generic label", () => {
			expect(mount().find(".sr-only").text()).toBe("progress");
		});
	});
});
