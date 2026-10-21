import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Table from "@/components/Layout/Table.vue";
import Collection from "./Collection.vue";
import Items from "./Items.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(Collection, {
		props: {
			columns: { title: { label: "Title" } },
			items: [
				{ id: "a", title: "A" },
				{ id: "b", title: "B" }
			],
			layout: "table",
			...props
		},
		attrs,
		global: {
			components: { "k-items": Items, "k-table": Table }
		}
	});
}

describe("Collection.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-collection");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	// events
	describe("header event", () => {
		it("is passed on from the table", async () => {
			const column = { label: "Title", sorted: false };
			const wrapper = mount({ columns: { title: column } });

			await wrapper.find('th[data-column-id="title"] button').trigger("click");
			expect(wrapper.emitted("header")).toStrictEqual([
				[{ column, columnIndex: "title" }]
			]);
		});
	});

	describe("item event", () => {
		it("passes item and index on", async () => {
			const wrapper = mount({ layout: "list" });
			await wrapper.findAll("k-item")[1].trigger("click");
			expect(wrapper.emitted("item")).toStrictEqual([
				[{ id: "b", title: "B" }, 1]
			]);
		});

		it("adds the column for a table cell click", async () => {
			const wrapper = mount();
			await wrapper.findAll("k-table-cell")[1].trigger("click");
			expect(wrapper.emitted("item")).toStrictEqual([
				[{ id: "b", title: "B" }, 1, "title"]
			]);
		});

		it("toggles the selection for a table cell click while selecting", async () => {
			const wrapper = mount({ selecting: true });
			await wrapper.findAll("k-table-cell")[1].trigger("click");
			expect(wrapper.emitted("item")).toBeUndefined();
			expect(wrapper.emitted("select")).toStrictEqual([[["b"]]]);
		});

		it("is emitted for an unselectable row while selecting", async () => {
			const wrapper = mount({
				items: [{ id: "a", title: "A", selectable: false }],
				selecting: true
			});
			await wrapper.find("k-table-cell").trigger("click");
			expect(wrapper.emitted("item")).toHaveLength(1);
			expect(wrapper.emitted("select")).toBeUndefined();
		});
	});
});
