import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Table from "@/components/Layout/Table.vue";
import Collection from "./Collection.vue";
import RealItems from "./Items.vue";

const Empty = {
	name: "k-empty",
	props: ["icon", "layout", "text"],
	emits: ["click"],
	template: `<div class="k-empty" @click="$emit('click', $event)" />`
};
const Items = {
	name: "k-items",
	props: [
		"columns",
		"fields",
		"index",
		"items",
		"layout",
		"link",
		"selected",
		"selecting",
		"selectmode",
		"size",
		"sortable",
		"theme"
	],
	emits: ["change", "item", "option", "select", "sort"],
	template: `<div class="k-items"><slot name="options" :item="items[0]" :index="0" /></div>`
};
const Pagination = {
	name: "k-pagination",
	props: ["details", "hide", "keys", "limit", "offset", "page", "total"],
	emits: ["paginate"],
	template: "<nav />"
};

const items = [
	{ id: "a", text: "A" },
	{ id: "b", text: "B" }
];

function mount(props = {}, attrs = {}, slots = {}) {
	return vueMount(Collection, {
		props: { items, ...props },
		attrs,
		slots,
		global: {
			stubs: { "k-empty": Empty, "k-items": Items, "k-pagination": Pagination }
		}
	});
}

// the real items and table, for their header and cell events
function mountTable(props = {}) {
	return vueMount(Collection, {
		props: {
			columns: { text: { label: "Text" } },
			items,
			layout: "table",
			...props
		},
		global: {
			components: { "k-items": RealItems, "k-table": Table }
		}
	});
}

describe("k-collection", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-collection");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("renders the items", () => {
			const wrapper = mount();
			expect(wrapper.find(".k-collection-body").element.tagName).toBe("DIV");
			expect(wrapper.findComponent(Items).props()).toStrictEqual({
				columns: {},
				fields: {},
				index: 1,
				items,
				layout: "list",
				link: true,
				selected: [],
				selecting: false,
				selectmode: "multiple",
				size: "medium",
				sortable: false,
				theme: undefined
			});
		});

		it("has no footer without help and pagination", () => {
			expect(mount().find(".k-collection-footer").exists()).toBe(false);
		});
	});

	// props
	describe("empty prop", () => {
		it("shows the empty state without items", () => {
			const wrapper = mount({
				empty: { icon: "page", text: "No pages yet" },
				items: [],
				layout: "cards"
			});
			expect(wrapper.findComponent(Items).exists()).toBe(false);
			expect(wrapper.findComponent(Empty).props()).toStrictEqual({
				icon: "page",
				layout: "cards",
				text: "No pages yet"
			});
		});
	});

	describe("help prop", () => {
		it("shows the help below the items", () => {
			const wrapper = mount({ help: "Some help" });
			const help = wrapper.find(".k-collection-footer k-text");
			expect(help.classes()).toStrictEqual(["k-help", "k-collection-help"]);
			expect(help.attributes("text")).toBe("Some help");
		});
	});

	describe("pagination prop", () => {
		it("paginates the items", () => {
			const wrapper = mount({
				pagination: { limit: 2, offset: 2, page: 2, total: 5 }
			});
			expect(wrapper.findComponent(Pagination).props()).toStrictEqual({
				details: true,
				hide: false,
				keys: false,
				limit: 2,
				offset: 2,
				page: 2,
				total: 5
			});
		});

		it("numbers the items from the offset", () => {
			const wrapper = mount({
				pagination: { limit: 2, offset: 2, page: 2, total: 5 }
			});
			expect(wrapper.findComponent(Items).props("index")).toBe(3);
		});

		it("hides the pagination for a single page", () => {
			const wrapper = mount({ pagination: { limit: 10, total: 5 } });
			expect(wrapper.findComponent(Pagination).exists()).toBe(false);
		});

		it("hides the pagination on request", () => {
			const wrapper = mount({
				pagination: { hide: true, limit: 2, total: 5 }
			});
			expect(wrapper.findComponent(Pagination).exists()).toBe(false);
		});

		it("has no pagination by default", () => {
			expect(mount().findComponent(Pagination).exists()).toBe(false);
		});
	});

	describe("index prop", () => {
		it("numbers the items from the index", () => {
			const wrapper = mount({ index: 5 });
			expect(wrapper.findComponent(Items).props("index")).toBe(5);
		});
	});

	describe("scrollable prop", () => {
		it("scrolls the items", () => {
			const wrapper = mount({ scrollable: true });
			expect(wrapper.attributes("data-scrollable")).toBe("true");
			expect(wrapper.find(".k-collection-body").element.tagName).toBe(
				"K-SCROLLABLE"
			);
		});
	});

	// events
	describe("events", () => {
		it("emits empty on a click of the empty state", async () => {
			const onEmpty = vi.fn();
			const onClick = vi.fn();
			const wrapper = mount({ items: [] }, { onEmpty });
			wrapper.element.addEventListener("click", onClick);
			await wrapper.findComponent(Empty).trigger("click");
			expect(onEmpty).toHaveBeenCalledOnce();
			expect(onClick).not.toHaveBeenCalled();
		});

		it("emits empty when the listener is added after the first render", async () => {
			const onEmpty = vi.fn();
			const wrapper = mount({ items: [] });
			await wrapper.setProps({ onEmpty });
			await wrapper.findComponent(Empty).trigger("click");
			expect(onEmpty).toHaveBeenCalledOnce();
		});

		it("lets the empty state be without empty listener", async () => {
			const onClick = vi.fn();
			const wrapper = mount({ items: [] });
			wrapper.element.addEventListener("click", onClick);
			await wrapper.findComponent(Empty).trigger("click");
			expect(onClick).toHaveBeenCalledOnce();
		});

		it("passes on the events of the items", () => {
			const wrapper = mount();
			const component = wrapper.findComponent(Items);
			component.vm.$emit("change", "changed");
			component.vm.$emit("item", items[1], 1);
			component.vm.$emit("select", ["a"]);
			component.vm.$emit("sort", "sorted");
			expect(wrapper.emitted("change")).toStrictEqual([["changed"]]);
			expect(wrapper.emitted("item")).toStrictEqual([[items[1], 1]]);
			expect(wrapper.emitted("select")).toStrictEqual([[["a"]]]);
			expect(wrapper.emitted("sort")).toStrictEqual([["sorted"]]);
		});

		it("passes on the header of the table", async () => {
			const column = { label: "Text", sorted: false };
			const wrapper = mountTable({ columns: { text: column } });
			await wrapper.find('th[data-column-id="text"] button').trigger("click");
			expect(wrapper.emitted("header")).toStrictEqual([
				[{ column, columnIndex: "text" }]
			]);
		});

		it("adds the column to the item of a table cell click", async () => {
			const wrapper = mountTable();
			await wrapper.findAll("k-table-cell")[1].trigger("click");
			expect(wrapper.emitted("item")).toStrictEqual([[items[1], 1, "text"]]);
		});

		it("toggles the selection for a table cell click while selecting", async () => {
			const wrapper = mountTable({ selecting: true });
			await wrapper.findAll("k-table-cell")[1].trigger("click");
			expect(wrapper.emitted("item")).toBeUndefined();
			expect(wrapper.emitted("select")).toStrictEqual([[["b"]]]);
		});

		it("emits the item of an unselectable row while selecting", async () => {
			const wrapper = mountTable({
				items: [{ id: "a", text: "A", selectable: false }],
				selecting: true
			});
			await wrapper.find("k-table-cell").trigger("click");
			expect(wrapper.emitted("item")).toHaveLength(1);
			expect(wrapper.emitted("select")).toBeUndefined();
		});

		it("emits the option of an item as action and option", () => {
			const wrapper = mount();
			wrapper.findComponent(Items).vm.$emit("option", "remove", items[0], 0);
			expect(wrapper.emitted("action")).toStrictEqual([
				["remove", items[0], 0]
			]);
			expect(wrapper.emitted("option")).toStrictEqual([
				["remove", items[0], 0]
			]);
		});

		it("passes on the pagination", () => {
			const wrapper = mount({ pagination: { limit: 1, total: 2 } });
			wrapper.findComponent(Pagination).vm.$emit("paginate", { page: 2 });
			expect(wrapper.emitted("paginate")).toStrictEqual([[{ page: 2 }]]);
		});
	});

	// slots
	describe("options slot", () => {
		it("renders the options of each item", () => {
			const wrapper = mount(
				{},
				{},
				{
					options: `<template #options="{ item, index }"><i>{{ index }}{{ item.text }}</i></template>`
				}
			);
			expect(wrapper.find(".k-items i").text()).toBe("0A");
		});
	});
});
