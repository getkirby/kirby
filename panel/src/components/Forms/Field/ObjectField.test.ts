import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import ObjectField from "./ObjectField.vue";

const TableCell = {
	name: "k-table-cell",
	props: ["field", "value"],
	template: "<td />"
};

function mount(props = {}, attrs = {}) {
	return vueMount(ObjectField, {
		props: {
			fields: {
				title: { label: "Title", name: "title", saveable: true, type: "text" }
			},
			value: { title: "A" },
			...props
		},
		attrs,
		global: {
			stubs: { "k-table-cell": TableCell }
		}
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

	// computed
	describe("subfields computed", () => {
		it("passes the fields with their own endpoints to the cells", () => {
			const wrapper = mount({
				endpoints: { field: "pages/x/fields/meta", model: "pages/x" }
			});

			expect(wrapper.findComponent(TableCell).props("field")).toStrictEqual({
				label: "Title",
				name: "title",
				saveable: true,
				type: "text",
				endpoints: { field: "pages/x/fields/meta+title", model: "pages/x" }
			});
		});
	});
});
