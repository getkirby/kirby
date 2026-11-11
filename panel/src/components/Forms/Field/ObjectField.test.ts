import { describe, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import ObjectField from "./ObjectField.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(ObjectField, {
		props: {
			fields: {
				title: { label: "Title", name: "title", saveable: true, type: "text" }
			},
			value: { title: "A" },
			...props
		},
		attrs
	});
}

describe("k-object-field", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-FIELD", "k-object-field");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});
});
