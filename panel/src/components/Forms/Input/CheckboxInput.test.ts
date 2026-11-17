import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import CheckboxInput from "./CheckboxInput.vue";

function mount(props = {}, attrs = {}) {
	// clicks only check connected inputs
	return vueMount(CheckboxInput, { props, attrs, attachTo: document.body });
}

describe("k-checkbox-input", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "LABEL", "k-choice-input");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("renders a checkbox", () => {
			const wrapper = mount();
			expect(wrapper.find("input").attributes("type")).toBe("checkbox");
		});
	});

	// events
	describe("input event", () => {
		it("emits the checked state", () => {
			const wrapper = mount();
			wrapper.find("input").element.click();
			expect(wrapper.emitted("input")).toStrictEqual([[true]]);
		});
	});
});
