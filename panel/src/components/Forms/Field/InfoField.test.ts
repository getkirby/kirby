import { describe, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import InfoField from "./InfoField.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(InfoField, {
		props: { text: "Some info", ...props },
		attrs,
		global: {
			stubs: {
				"k-box": true,
				"k-headline": true,
				"k-text": true
			}
		}
	});
}

describe("k-info-field", () => {
	// $el
	describe("element", () => {
		const component = (attrs = {}) => mount({}, attrs);

		it.rendersAs(component, "DIV", "k-info-field");
		it.acceptsClass(component);
		it.acceptsStyle(component);
		it.inheritsNoAttrs(component);
	});
});
