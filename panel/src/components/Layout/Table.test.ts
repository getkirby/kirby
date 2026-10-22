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
			expect(title.find("k-icon").attributes("type")).toBe("angle-up-down");
		});

		it.each([
			["asc", "ascending"],
			["desc", "descending"]
		])("marks a column sorted %s", (sorted, aria) => {
			const wrapper = mount({
				columns: { title: { label: "Title", sorted } }
			});
			const title = wrapper.find('th[data-column-id="title"]');

			expect(title.find("button").exists()).toBe(true);
			expect(title.attributes("aria-sort")).toBe(aria);
			expect(title.find("k-icon").attributes("type")).toBe("angle-up-down");
		});

		it("keeps rows draggable without sortable columns", () => {
			const wrapper = mount({ sortable: true });
			expect(wrapper.findAll('tr[data-sortable="true"]')).toHaveLength(2);
		});

		it("keeps rows draggable while no column is sorted", () => {
			const wrapper = mount({
				columns: { title: { label: "Title", sorted: false } },
				sortable: true
			});
			expect(wrapper.findAll('tr[data-sortable="true"]')).toHaveLength(2);
		});

		it("stops dragging rows while a column is sorted", () => {
			const wrapper = mount({
				columns: {
					title: { label: "Title", sorted: false },
					date: { label: "Date", sorted: "desc" }
				},
				sortable: true
			});
			expect(wrapper.findAll('tr[data-sortable="true"]')).toHaveLength(0);
		});
	});

	describe("selected prop", () => {
		it("matches rows by uuid before id, like k-items", () => {
			const wrapper = mount({
				rows: [
					{ id: "a", uuid: "page://a", title: "A" },
					{ id: "b", uuid: "page://b", title: "B" }
				],
				selected: ["page://b"],
				selecting: true
			});
			const checkboxes = wrapper.findAll("input");

			expect(checkboxes[0].element.checked).toBe(false);
			expect(checkboxes[1].element.checked).toBe(true);
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

	describe("select event", () => {
		it("adds a checked row", async () => {
			const wrapper = mount({ selecting: true, selected: ["a"] });
			await wrapper.findAll("input")[1].trigger("change");
			expect(wrapper.emitted("select")).toStrictEqual([[["a", "b"]]]);
		});

		it("adds the uuid of a checked row", async () => {
			const wrapper = mount({
				rows: [{ id: "a", uuid: "page://a", title: "A" }],
				selecting: true
			});

			await wrapper.find("input").trigger("change");
			expect(wrapper.emitted("select")).toStrictEqual([[["page://a"]]]);
		});

		it("removes an unchecked row", async () => {
			const wrapper = mount({ selecting: true, selected: ["a", "b"] });
			await wrapper.findAll("input")[0].trigger("change");
			expect(wrapper.emitted("select")).toStrictEqual([[["b"]]]);
		});

		it("replaces the selection in single mode", async () => {
			const wrapper = mount({
				selecting: true,
				selected: ["a"],
				selectmode: "single"
			});
			await wrapper.findAll("input")[1].trigger("change");
			expect(wrapper.emitted("select")).toStrictEqual([[["b"]]]);
		});
	});
});
