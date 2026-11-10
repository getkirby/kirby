import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Table from "./Table.vue";

const TableCell = {
	props: ["value"],
	emits: ["input"],
	render: () => null
};

function mount(props = {}, attrs = {}, components = {}) {
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
		global: { components }
	});
}

describe("k-table", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-table");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
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
	});

	describe("empty prop", () => {
		it("renders the text without rows", () => {
			const wrapper = mount({ rows: [], empty: "No entries yet" });
			expect(wrapper.find(".k-table-empty").text()).toBe("No entries yet");
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
});
