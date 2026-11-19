import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Definitions from "./Definitions.vue";

function mount(attrs = {}) {
	return vueMount(Definitions, {
		attrs,
		slots: { default: "<div><dt>Version</dt><dd>5.3.0</dd></div>" }
	});
}

describe("k-definitions", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DL", "k-definitions");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
	});

	// slots
	describe("default slot", () => {
		it("renders the definitions", () => {
			const wrapper = mount();
			expect(wrapper.find("dt").text()).toBe("Version");
			expect(wrapper.find("dd").text()).toBe("5.3.0");
		});
	});
});
