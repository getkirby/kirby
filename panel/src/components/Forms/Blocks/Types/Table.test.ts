import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import preserveListeners from "@/mixins/preserveListeners";
import Table from "./Table.vue";

const KTable = {
	name: "k-table",
	props: ["columns", "empty", "rows"],
	template: "<table />"
};

const fields = { name: { type: "text" }, role: { type: "text" } };
const rows = [{ name: "Ada", role: "Engineer" }];

function mount(props = {}, attrs = {}) {
	return vueMount(Table, {
		props: {
			content: { rows },
			fieldset: { tabs: { content: { fields: { rows: { fields } } } } },
			...props
		},
		attrs,
		global: {
			mixins: [preserveListeners],
			stubs: { "k-table": KTable }
		}
	});
}

function table(wrapper: ReturnType<typeof mount>) {
	return wrapper.findComponent(KTable);
}

describe("k-block-type-table", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "TABLE", "k-block-type-table-preview");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("renders the rows with the fields as columns", () => {
			expect(table(mount()).props()).toStrictEqual({
				columns: fields,
				empty: "field.structure.empty",
				rows
			});
		});
	});

	// computed
	describe("columns computed", () => {
		it("prefers the columns of the rows field", () => {
			const columns = { name: { label: "Name" } };
			const wrapper = mount({
				fieldset: {
					tabs: { content: { fields: { rows: { columns, fields } } } }
				}
			});
			expect(table(wrapper).props("columns")).toStrictEqual(columns);
		});

		it("finds the rows field in any tab", () => {
			const wrapper = mount({
				fieldset: {
					tabs: {
						content: { fields: {} },
						table: { fields: { rows: { fields } } }
					}
				}
			});
			expect(table(wrapper).props("columns")).toStrictEqual(fields);
		});

		it("has no columns without rows field", () => {
			const wrapper = mount({ fieldset: {} });
			expect(table(wrapper).props("columns")).toStrictEqual({});
		});
	});

	describe("rows computed", () => {
		it("has no rows without content", () => {
			const wrapper = mount({ content: {} });
			expect(table(wrapper).props("rows")).toStrictEqual([]);
		});
	});

	// events
	describe("events", () => {
		it("emits open on double click", async () => {
			const wrapper = mount();
			await wrapper.trigger("dblclick");
			expect(wrapper.emitted("open")).toHaveLength(1);
		});
	});
});
