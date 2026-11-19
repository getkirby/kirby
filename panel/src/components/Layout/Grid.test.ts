import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Grid from "./Grid.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(Grid, {
		props,
		attrs,
		slots: { default: "<p>A</p><p>B</p>" }
	});
}

describe("k-grid", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-grid");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
	});

	// props
	describe("variant prop", () => {
		it("has no variant by default", () => {
			expect(mount().attributes("data-variant")).toBeUndefined();
		});

		it("sets the variant", () => {
			const wrapper = mount({ variant: "fields" });
			expect(wrapper.attributes("data-variant")).toBe("fields");
		});
	});

	// slots
	describe("default slot", () => {
		it("renders the items", () => {
			const items = mount().findAll(":scope > p");
			expect(items.map((item) => item.text())).toStrictEqual(["A", "B"]);
		});
	});
});
