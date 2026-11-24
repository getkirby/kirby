import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import GapField from "./GapField.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(GapField, { props, attrs });
}

describe("k-gap-field", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-gap-field");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("is styled as a field", () => {
			const wrapper = mount();
			expect(wrapper.classes()).toContain("k-field");
		});

		it("renders no content", () => {
			const wrapper = mount();
			expect(wrapper.element.childNodes).toHaveLength(0);
		});
	});
});
