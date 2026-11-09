import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import StringInput from "./StringInput.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(StringInput, { props, attrs });
}

describe("StringInput.vue", () => {
	// $el
	describe("element", () => {
		const component = (attrs = {}) => mount({}, attrs);

		it.rendersAs(component, "INPUT", "k-string-input");
		it.acceptsClass(component);
		it.acceptsStyle(component);
		it.inheritsNoAttrs(component);
	});

	// props
	describe("dir prop", () => {
		it("sets the reading direction of the input", () => {
			const wrapper = mount({ dir: "rtl" });
			expect(wrapper.attributes("dir")).toBe("rtl");
		});

		it("inherits the reading direction without dir", () => {
			const wrapper = mount();
			expect(wrapper.attributes("dir")).toBeUndefined();
		});
	});

	describe("lang prop", () => {
		it("sets the language of the input", () => {
			const wrapper = mount({ lang: "de" });
			expect(wrapper.attributes("lang")).toBe("de");
		});

		it("inherits the language without lang", () => {
			const wrapper = mount();
			expect(wrapper.attributes("lang")).toBeUndefined();
		});
	});
});
