import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import ObjectField from "./ObjectField.vue";

const TableCell = {
	name: "k-table-cell",
	props: ["disabled", "field", "value"],
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

	// props
	describe("disabled prop", () => {
		it("disables the cells", () => {
			const wrapper = mount({ disabled: true });
			expect(wrapper.findComponent(TableCell).props("disabled")).toBe(true);
		});
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

	// methods
	describe("cell()", () => {
		it("emits the object with the new cell value", () => {
			const wrapper = mount();
			wrapper.vm.cell("title", "B");
			expect(wrapper.emitted("input")).toStrictEqual([[{ title: "B" }]]);
		});

		it("does nothing when disabled", () => {
			const wrapper = mount({ disabled: true });
			wrapper.vm.cell("title", "B");
			expect(wrapper.emitted("input")).toBeUndefined();
		});
	});
});
