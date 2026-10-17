import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Table from "./Table.vue";

const TableCell = {
	props: ["value"],
	emits: ["input"],
	render: () => null
};

const Draggable = {
	name: "k-draggable",
	props: ["element", "handle", "list", "options"],
	emits: ["change", "end"],
	template: "<tbody><slot /></tbody>"
};
const OptionsDropdown = {
	name: "k-options-dropdown",
	props: ["options", "text"],
	emits: ["option"],
	template: "<button />"
};
const Pagination = {
	name: "k-pagination",
	props: ["limit", "page", "total"],
	emits: ["paginate"],
	template: "<nav />"
};

function mount(props = {}, attrs = {}, components = {}, slots = {}) {
	return vueMount(Table, {
		props: {
			columns: { title: { label: "Title" } },
			rows: [
				{ id: "a", title: "A" },
				{ id: "b", title: "B" }
			],
			...props
		},
		attrs,
		slots,
		global: {
			components,
			stubs: {
				"k-draggable": Draggable,
				"k-options-dropdown": OptionsDropdown,
				"k-pagination": Pagination
			}
		}
	});
}

// a cell that renders its value and takes clicks
const Cell = {
	name: "k-table-cell",
	props: ["column", "disabled", "field", "id", "mobile", "row", "value"],
	emits: ["input"],
	template: "<td>{{ value }}</td>"
};

function cells(wrapper: ReturnType<typeof mount>) {
	return wrapper.findAllComponents(Cell);
}

function rows(wrapper: ReturnType<typeof mount>) {
	return wrapper.findAll("tbody tr");
}

describe("k-table", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-table");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("renders a header and a row per entry", () => {
			const wrapper = mount({}, {}, { "k-table-cell": Cell });
			expect(wrapper.findAll("thead th").map((th) => th.text())).toStrictEqual([
				"#",
				"Title"
			]);
			expect(rows(wrapper)).toHaveLength(2);
			expect(cells(wrapper).map((cell) => cell.text())).toStrictEqual([
				"A",
				"B"
			]);
		});

		it("is responsive by default", () => {
			expect(mount().attributes("data-responsive")).toBe("true");
		});
	});

	// props
	describe("columns prop", () => {
		it("renders the column labels as header", () => {
			const wrapper = mount({ columns: { title: { label: "Headline" } } });
			expect(wrapper.find("thead th.k-table-column").text()).toBe("Headline");
		});

		it("falls back to the capitalized column name", () => {
			const wrapper = mount({ columns: { date: {} } });
			expect(wrapper.find("thead th.k-table-column").text()).toBe("Date");
		});

		it("passes the column options to the header and cells", () => {
			const column = { align: "right", mobile: true, width: "1/4" };
			const wrapper = mount(
				{ columns: { title: column } },
				{},
				{ "k-table-cell": Cell }
			);
			const header = wrapper.find("thead th.k-table-column");
			expect(header.attributes("data-align")).toBe("right");
			expect(header.attributes("data-column-id")).toBe("title");
			expect(header.attributes("data-mobile")).toBe("true");
			expect((header.element as HTMLElement).style.width).toBe("25%");
			expect(cells(wrapper)[0].props()).toMatchObject({
				column,
				id: "title",
				mobile: true,
				row: { id: "a", title: "A" },
				value: "A"
			});
		});

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

	describe("disabled prop", () => {
		it("disables the table and its cells", () => {
			const wrapper = mount({ disabled: true }, {}, { "k-table-cell": Cell });
			expect(wrapper.attributes("aria-disabled")).toBe("true");
			expect(wrapper.find("table").attributes("data-disabled")).toBe("true");
			expect(cells(wrapper)[0].props("disabled")).toBe(true);
		});
	});

	describe("empty prop", () => {
		it("renders the text without rows", () => {
			const wrapper = mount({ rows: [], empty: "No entries yet" });
			expect(wrapper.find(".k-table-empty").text()).toBe("No entries yet");
		});

		it("spans all columns", () => {
			const wrapper = mount({
				columns: { text: {}, title: {} },
				options: [{ text: "Edit" }],
				rows: []
			});
			expect(wrapper.find(".k-table-empty").attributes("colspan")).toBe("4");
		});

		it("spans only the columns without index and options", () => {
			const wrapper = mount({ index: false, rows: [] });
			expect(wrapper.find(".k-table-empty").attributes("colspan")).toBe("1");
		});
	});

	describe("fields prop", () => {
		it("passes the field of each column to its cells", () => {
			const fields = { title: { type: "text" } };
			const wrapper = mount({ fields }, {}, { "k-table-cell": Cell });
			expect(cells(wrapper)[0].props("field")).toStrictEqual({ type: "text" });
		});
	});

	describe("index prop", () => {
		it("numbers the rows from one", () => {
			const wrapper = mount();
			expect(wrapper.find("table").attributes("data-indexed")).toBe("true");
			expect(
				wrapper.findAll("tbody .k-table-index").map((index) => index.text())
			).toStrictEqual(["1", "2"]);
		});

		it("numbers the rows from the index", () => {
			const wrapper = mount({ index: 11 });
			expect(
				wrapper.findAll("tbody .k-table-index").map((index) => index.text())
			).toStrictEqual(["11", "12"]);
		});

		it("hides the index column", () => {
			const wrapper = mount({ index: false });
			expect(wrapper.find("table").attributes("data-indexed")).toBe("false");
			expect(wrapper.find(".k-table-index-column").exists()).toBe(false);
		});

		it("shows a loader for skeleton rows", () => {
			const wrapper = mount({ rows: [{ id: "a", theme: "skeleton" }] });
			const loader = wrapper.find("tbody k-icon.k-table-index");
			expect(loader.attributes("type")).toBe("loader");
		});

		it("fits the width to the highest index", () => {
			const style = (index: number) =>
				(mount({ index }).find("table").element as HTMLElement).style;
			expect(style(1).getPropertyValue("--table-index-width")).toBe("1ch");
			expect(style(99).getPropertyValue("--table-index-width")).toBe("3ch");
		});
	});

	describe("options prop", () => {
		const options = [{ text: "Edit" }, { text: "Delete" }];

		it("renders the options for each row", () => {
			const wrapper = mount({ options });
			const dropdowns = wrapper.findAllComponents(OptionsDropdown);
			expect(wrapper.find("thead .k-table-options-column").exists()).toBe(true);
			expect(dropdowns).toHaveLength(2);
			expect(dropdowns[0].props()).toStrictEqual({ options, text: true });
		});

		it("shows no text for a single option", () => {
			const wrapper = mount({ options: [{ text: "Edit" }] });
			expect(wrapper.findComponent(OptionsDropdown).props("text")).toBe(false);
		});

		it("prefers the options of the row", () => {
			const wrapper = mount({
				options,
				rows: [{ id: "a", options: [{ text: "Open" }] }, { id: "b" }]
			});
			const dropdowns = wrapper.findAllComponents(OptionsDropdown);
			expect(dropdowns[0].props("options")).toStrictEqual([{ text: "Open" }]);
			expect(dropdowns[1].props("options")).toStrictEqual(options);
		});

		it("shows the options column for row options alone", () => {
			const wrapper = mount({
				rows: [{ id: "a", options: [{ text: "Open" }] }]
			});
			expect(wrapper.find("thead .k-table-options-column").exists()).toBe(true);
		});

		it("has no options column without options", () => {
			const wrapper = mount();
			expect(wrapper.find(".k-table-options-column").exists()).toBe(false);
		});
	});

	describe("pagination prop", () => {
		it("renders the pagination", () => {
			const pagination = { limit: 10, page: 2, total: 30 };
			const wrapper = mount({ pagination });
			const component = wrapper.findComponent(Pagination);
			expect(component.classes()).toContain("k-table-pagination");
			expect(component.props()).toStrictEqual(pagination);
		});

		it("renders no pagination by default", () => {
			expect(mount().findComponent(Pagination).exists()).toBe(false);
		});
	});

	describe("responsive prop", () => {
		it("turns off the responsive layout", () => {
			const wrapper = mount({ responsive: false });
			expect(wrapper.attributes("data-responsive")).toBe("false");
		});
	});

	describe("rows prop", () => {
		it("marks the theme of a row", () => {
			const wrapper = mount({ rows: [{ id: "a", theme: "negative" }] });
			expect(rows(wrapper)[0].attributes("data-theme")).toBe("negative");
		});
	});

	describe("selected prop", () => {
		it("checks the selected rows", () => {
			const wrapper = mount({ selecting: true, selected: ["b"] });
			const inputs = wrapper.findAll("input");
			expect(inputs[0].element.checked).toBe(false);
			expect(inputs[1].element.checked).toBe(true);
		});

		it("matches rows by _id before id", () => {
			const wrapper = mount({
				rows: [
					{ _id: "1", id: "a" },
					{ _id: "2", id: "b" }
				],
				selecting: true,
				selected: ["a", "2"]
			});
			const inputs = wrapper.findAll("input");
			expect(inputs[0].element.checked).toBe(false);
			expect(inputs[1].element.checked).toBe(true);
		});
	});

	describe("selecting prop", () => {
		it("disables rows that are not selectable", () => {
			const wrapper = mount({
				rows: [{ id: "a", selectable: false }, { id: "b" }],
				selecting: true
			});
			const inputs = wrapper.findAll("input");
			expect(inputs[0].element.disabled).toBe(true);
			expect(inputs[1].element.disabled).toBe(false);
		});

		it("replaces the options with checkboxes", () => {
			const wrapper = mount({ options: [{ text: "Edit" }], selecting: true });
			expect(wrapper.find("table").attributes("data-selecting")).toBe("true");
			expect(wrapper.findAll("input[type=checkbox]")).toHaveLength(2);
			expect(wrapper.findComponent(OptionsDropdown).exists()).toBe(false);
		});

		it("marks the selectable rows", () => {
			const wrapper = mount({
				rows: [{ id: "a", selectable: false }, { id: "b" }],
				selecting: true
			});
			expect(
				rows(wrapper).map((row) => row.attributes("data-selectable"))
			).toStrictEqual(["false", "true"]);
		});
	});

	describe("selectmode prop", () => {
		it("selects a single row with radio buttons", () => {
			const wrapper = mount({ selecting: true, selectmode: "single" });
			expect(wrapper.findAll("input[type=radio]")).toHaveLength(2);
		});
	});

	describe("sortable prop", () => {
		it("makes the rows sortable", () => {
			const wrapper = mount({ sortable: true });
			expect(
				rows(wrapper).map((row) => row.classes("k-table-sortable-row"))
			).toStrictEqual([true, true]);
			expect(wrapper.findAll("k-sort-handle.k-table-sort-handle")).toHaveLength(
				2
			);
			expect(wrapper.findComponent(Draggable).props()).toStrictEqual({
				element: "tbody",
				handle: true,
				list: [
					{ id: "a", title: "A" },
					{ id: "b", title: "B" }
				],
				options: {
					disabled: false,
					draggable: ".k-table-sortable-row",
					fallbackClass: "k-table-row-fallback",
					ghostClass: "k-table-row-ghost"
				}
			});
		});

		it("keeps the index column for the handles", () => {
			const wrapper = mount({ index: false, sortable: true });
			expect(wrapper.find("table").attributes("data-indexed")).toBe("true");
		});

		it("keeps rows that are not sortable in place", () => {
			const wrapper = mount({
				rows: [{ id: "a", sortable: false }, { id: "b" }],
				sortable: true
			});
			expect(
				rows(wrapper).map((row) => row.attributes("data-sortable"))
			).toStrictEqual(["false", "true"]);
			expect(wrapper.findAll("k-sort-handle")).toHaveLength(1);
		});

		it("doesn't sort while selecting", () => {
			const wrapper = mount({ selecting: true, sortable: true });
			expect(wrapper.find("k-sort-handle").exists()).toBe(false);
		});

		it("doesn't sort without rows", () => {
			const wrapper = mount({ rows: [], sortable: true });
			expect(wrapper.findComponent(Draggable).props("options").disabled).toBe(
				true
			);
		});

		it("doesn't sort by default", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Draggable).props("options").disabled).toBe(
				true
			);
			expect(wrapper.find("k-sort-handle").exists()).toBe(false);
		});
	});

	// watch
	describe("rows watcher", () => {
		it("renders the new rows", async () => {
			const wrapper = mount({}, {}, { "k-table-cell": Cell });
			await wrapper.setProps({ rows: [{ id: "c", title: "C" }] } as never);
			expect(cells(wrapper).map((cell) => cell.text())).toStrictEqual(["C"]);
		});
	});

	// methods
	describe("width()", () => {
		it("converts a fraction to a percentage", () => {
			expect(mount().vm.width("1/2")).toBe("50%");
		});

		it("keeps any other width", () => {
			expect(mount().vm.width("10rem")).toBe("10rem");
		});

		it("is auto without width", () => {
			expect(mount().vm.width(undefined as never)).toBe("auto");
		});
	});

	// events
	describe("cell event", () => {
		it("is emitted on click of a cell", async () => {
			const wrapper = mount({}, {}, { "k-table-cell": Cell });
			await cells(wrapper)[1].trigger("click");
			expect(wrapper.emitted("cell")).toStrictEqual([
				[
					{
						column: { label: "Title" },
						columnIndex: "title",
						row: { id: "b", title: "B" },
						rowIndex: 1
					}
				]
			]);
		});
	});

	describe("change event", () => {
		it("passes on the change of the sorting", () => {
			const wrapper = mount({ sortable: true });
			const event = { moved: { newIndex: 1, oldIndex: 0 } };
			wrapper.findComponent(Draggable).vm.$emit("change", event);
			expect(wrapper.emitted("change")).toStrictEqual([[event]]);
		});
	});

	describe("header event", () => {
		it("is emitted on click of a column header", async () => {
			const wrapper = mount();
			await wrapper.find("thead th.k-table-column").trigger("click");
			expect(wrapper.emitted("header")).toStrictEqual([
				[{ column: { label: "Title" }, columnIndex: "title" }]
			]);
		});

		it("is emitted for the sort button", async () => {
			const column = { label: "Title", sorted: "asc" };
			const wrapper = mount({ columns: { title: column } });

			await wrapper.find('th[data-column-id="title"] button').trigger("click");
			expect(wrapper.emitted("header")).toStrictEqual([
				[{ column, columnIndex: "title" }]
			]);
		});
	});

	// events
	describe("input event", () => {
		it("writes an edited cell value into its row", async () => {
			const wrapper = mount(
				{
					columns: { title: {}, text: {} },
					rows: [
						{ id: "a", title: "A", text: "a" },
						{ id: "b", title: "B", text: "b" }
					]
				},
				{},
				{ "k-table-cell": TableCell }
			);
			const cells = wrapper.findAllComponents(TableCell);
			cells[3].vm.$emit("input", "Edited");

			expect(wrapper.emitted("input")).toStrictEqual([
				[
					[
						{ id: "a", title: "A", text: "a" },
						{ id: "b", title: "B", text: "Edited" }
					]
				]
			]);

			await wrapper.vm.$nextTick();
			expect(cells[3].props("value")).toBe("Edited");
		});
	});

	describe("option event", () => {
		it("is emitted with the row of the option", () => {
			const wrapper = mount({ options: [{ click: "edit", text: "Edit" }] });
			wrapper.findAllComponents(OptionsDropdown)[1].vm.$emit("option", "edit");
			expect(wrapper.emitted("option")).toStrictEqual([
				["edit", { id: "b", title: "B" }, 1]
			]);
		});
	});

	describe("paginate event", () => {
		it("is passed on from the pagination", () => {
			const wrapper = mount({ pagination: { page: 1 } });
			wrapper.findComponent(Pagination).vm.$emit("paginate", { page: 2 });
			expect(wrapper.emitted("paginate")).toStrictEqual([[{ page: 2 }]]);
		});
	});

	describe("select event", () => {
		it("adds a checked row", async () => {
			const wrapper = mount({ selecting: true, selected: ["a"] });
			await wrapper.findAll("input")[1].trigger("change");
			expect(wrapper.emitted("select")).toStrictEqual([[["a", "b"]]]);
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

	describe("sort event", () => {
		it("emits the sorted rows", () => {
			const wrapper = mount({ sortable: true });
			wrapper.findComponent(Draggable).vm.$emit("end");
			const sorted = [
				{ id: "a", title: "A" },
				{ id: "b", title: "B" }
			];
			expect(wrapper.emitted("input")).toStrictEqual([[sorted]]);
			expect(wrapper.emitted("sort")).toStrictEqual([[sorted]]);
		});
	});

	// slots
	describe("header slot", () => {
		it("replaces the column label", () => {
			const wrapper = mount(
				{},
				{},
				{},
				{
					header: `<template #header="{ columnIndex, label }"><b>{{ label }} ({{ columnIndex }})</b></template>`
				}
			);
			expect(wrapper.find("thead th.k-table-column b").text()).toBe(
				"Title (title)"
			);
		});
	});

	describe("index slot", () => {
		it("replaces the index", () => {
			const wrapper = mount(
				{},
				{},
				{},
				{
					index: `<template #index="{ row, rowIndex }"><i>{{ row.id }}{{ rowIndex }}</i></template>`
				}
			);
			expect(
				wrapper.findAll("tbody .k-table-index-column i").map((i) => i.text())
			).toStrictEqual(["a0", "b1"]);
			expect(wrapper.find("tbody .k-table-index").exists()).toBe(false);
		});
	});

	describe("options slot", () => {
		it("replaces the options of each row", () => {
			const wrapper = mount(
				{},
				{},
				{},
				{
					options: `<template #options="{ row, rowIndex }"><u>{{ row.id }}{{ rowIndex }}</u></template>`
				}
			);
			expect(wrapper.find("thead .k-table-options-column").exists()).toBe(true);
			expect(
				wrapper.findAll("tbody .k-table-options-column u").map((u) => u.text())
			).toStrictEqual(["a0", "b1"]);
		});
	});
});
