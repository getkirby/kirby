import { describe, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import LineField from "./LineField.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(LineField, { props, attrs });
}

describe("k-line-field", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "HR", "k-line-field");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});
});
