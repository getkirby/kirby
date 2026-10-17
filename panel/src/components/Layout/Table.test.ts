import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Table from "./Table.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(Table, {
		props: {
			columns: {
				title: { label: "Title" },
				date: { label: "Date" }
			},
			rows: [
				{ id: "a", title: "A", date: "2024-01-01" },
				{ id: "b", title: "B", date: "2024-01-02" }
			],
			...props
		},
		attrs
	});
}

describe("Table.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-table");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	// props
	describe("columns prop", () => {
		it("renders plain headers without sorted", () => {
			const wrapper = mount();
			expect(wrapper.find("th button").exists()).toBe(false);
			expect(wrapper.find("th[aria-sort]").exists()).toBe(false);
		});

		it("renders a sort button for sorted: false", () => {
			const wrapper = mount({
				columns: { title: { label: "Title", sorted: false } }
			});
			const title = wrapper.find('th[data-column-id="title"]');

			expect(title.find("button").exists()).toBe(true);
			expect(title.attributes("aria-sort")).toBeUndefined();
			expect(title.find("k-icon").exists()).toBe(false);
		});

		it.each([
			["asc", "ascending", "angle-up"],
			["desc", "descending", "angle-down"]
		])("marks a column sorted %s", (sorted, aria, icon) => {
			const wrapper = mount({
				columns: { title: { label: "Title", sorted } }
			});
			const title = wrapper.find('th[data-column-id="title"]');

			expect(title.find("button").exists()).toBe(true);
			expect(title.attributes("aria-sort")).toBe(aria);
			expect(title.find("k-icon").attributes("type")).toBe(icon);
		});

		it("keeps rows draggable without sortable columns", () => {
			const wrapper = mount({ sortable: true });
			expect(wrapper.findAll('tr[data-sortable="true"]')).toHaveLength(2);
		});

		it("stops dragging rows with sortable columns", () => {
			const wrapper = mount({
				columns: { title: { label: "Title", sorted: false } },
				sortable: true
			});
			expect(wrapper.findAll('tr[data-sortable="true"]')).toHaveLength(0);
		});
	});

	// events
	describe("header event", () => {
		it("is emitted for the sort button", async () => {
			const column = { label: "Title", sorted: "asc" };
			const wrapper = mount({ columns: { title: column } });

			await wrapper.find('th[data-column-id="title"] button').trigger("click");
			expect(wrapper.emitted("header")).toStrictEqual([
				[{ column, columnIndex: "title" }]
			]);
		});
	});
});
