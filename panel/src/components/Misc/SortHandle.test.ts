import { describe, it, expect } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import SortHandle from "./SortHandle.vue";

function mount(attrs = {}) {
	return vueMount(SortHandle, {
		attrs,
		shallow: true,
		global: {
			mocks: { $t: (key: string) => key }
		}
	});
}

describe("SortHandle.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-BUTTON", "k-sort-handle");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);

		it("is aria-hidden", () => {
			const wrapper = mount();
			expect(wrapper.attributes("aria-hidden")).toBe("true");
		});
	});

	// button props
	describe("button", () => {
		it("uses the sort icon", () => {
			const wrapper = mount();
			expect(wrapper.attributes("icon")).toBe("sort");
		});

		it("uses sort.drag as title", () => {
			const wrapper = mount();
			expect(wrapper.attributes("title")).toBe("sort.drag");
		});
	});
});
