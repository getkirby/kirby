import { describe, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Line from "./Line.vue";

function mount(attrs = {}) {
	return vueMount(Line, { attrs });
}

describe("k-block-type-line", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "HR");
		it.inheritsNoAttrs(mount);
	});
});
