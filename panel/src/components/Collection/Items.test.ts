import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Items from "./Items.vue";

const Draggable = {
	name: "k-draggable",
	props: ["handle", "list", "options"],
	emits: ["change", "end"],
	template: "<div><slot /></div>"
};
const ItemStub = {
	name: "k-item",
	props: [
		"id",
		"image",
		"layout",
		"link",
		"selectable",
		"selected",
		"selecting",
		"selectmode",
		"sortable",
		"text",
		"theme"
	],
	emits: ["click", "drag", "option", "select"],
	template: `<article><slot name="options" /></article>`
};
const Table = {
	name: "k-table",
	props: [
		"columns",
		"fields",
		"index",
		"rows",
		"selected",
		"selecting",
		"selectmode",
		"sortable"
	],
	emits: ["change", "option", "select", "sort"],
	template: `<table><slot name="options" :row="rows[0]" :rowIndex="0" /></table>`
};

function items() {
	return [
		{ id: "a", link: "/pages/a", text: "A" },
		{ link: "/pages/b", text: "B", uuid: "page://b" },
		{ _id: 3, text: "C" }
	];
}

function panel() {
	return { drag: { start: vi.fn() } };
}

function mount(props = {}, attrs = {}, slots = {}, $panel = panel()) {
	return vueMount(Items, {
		props: { items: items(), ...props },
		attrs,
		slots,
		global: {
			mocks: { $panel },
			stubs: { "k-draggable": Draggable, "k-item": ItemStub, "k-table": Table }
		}
	});
}

function rendered(wrapper: ReturnType<typeof mount>) {
	return wrapper.findAllComponents(ItemStub);
}

describe("k-items", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-items");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("renders an item per entry in a draggable list", () => {
			const wrapper = mount();
			expect(wrapper.classes()).toContain("k-list-items");
			expect(wrapper.attributes()).toMatchObject({
				"data-layout": "list",
				"data-size": "medium"
			});
			expect(wrapper.findComponent(Draggable).props()).toMatchObject({
				handle: true,
				list: items()
			});
			expect(rendered(wrapper)[0].props()).toStrictEqual({
				id: "a",
				image: {},
				layout: "list",
				link: "/pages/a",
				selectable: true,
				selected: false,
				selecting: false,
				selectmode: "multiple",
				sortable: false,
				text: "A",
				theme: undefined
			});
		});

		it("keeps the id or UUID of an item and adds one otherwise", () => {
			const ids = rendered(mount()).map((item) => item.props("id"));
			expect(ids.slice(0, 2)).toStrictEqual(["a", "page://b"]);
			expect(ids[2]).toMatch(/^[a-z0-9-]+$/);
		});
	});

	// props
	describe("image prop", () => {
		it("merges the global options over those of each item", () => {
			const wrapper = mount({
				image: { back: "black", cover: true },
				items: [{ id: "a", image: { back: "white", icon: "page" } }]
			});
			expect(rendered(wrapper)[0].props("image")).toStrictEqual({
				back: "black",
				cover: true,
				icon: "page"
			});
		});

		it("hides all images", () => {
			const wrapper = mount({
				image: false,
				items: [{ id: "a", image: { icon: "page" } }]
			});
			expect(rendered(wrapper)[0].props("image")).toBe(false);
		});

		it("hides the image of a single item", () => {
			const wrapper = mount({ items: [{ id: "a", image: false }] });
			expect(rendered(wrapper)[0].props("image")).toBe(false);
		});
	});

	describe("layout prop", () => {
		it("renders the layout", () => {
			const wrapper = mount({ layout: "cards" });
			expect(wrapper.classes()).toContain("k-cards-items");
			expect(wrapper.attributes("data-layout")).toBe("cards");
			expect(rendered(wrapper)[0].props("layout")).toBe("cards");
		});

		it("renders a table", () => {
			const wrapper = mount(
				{
					columns: { title: {} },
					fields: { title: { type: "text" } },
					index: 11,
					layout: "table",
					selected: ["a"],
					selecting: true,
					selectmode: "single",
					sortable: true
				},
				{ class: "custom", style: "--x: 1" }
			);
			const table = wrapper.findComponent(Table);
			expect(table.props()).toStrictEqual({
				columns: { title: {} },
				fields: { title: { type: "text" } },
				index: 11,
				rows: items(),
				selected: ["a"],
				selecting: true,
				selectmode: "single",
				sortable: true
			});
			expect(table.classes()).toContain("custom");
			expect(table.attributes("style")).toContain("--x: 1");
			expect(wrapper.findComponent(Draggable).exists()).toBe(false);
		});
	});

	describe("link prop", () => {
		it("turns off the links of all items", () => {
			const wrapper = mount({ link: false });
			expect(rendered(wrapper).map((item) => item.props("link"))).toStrictEqual(
				[false, false, false]
			);
		});
	});

	describe("selected prop", () => {
		it("marks the selected items by _id, UUID or id", () => {
			const wrapper = mount({ selected: ["a", "page://b", 3] });
			expect(
				rendered(wrapper).map((item) => item.props("selected"))
			).toStrictEqual([true, true, true]);
		});
	});

	describe("selecting prop", () => {
		it("lets items opt out of selecting", () => {
			const wrapper = mount({
				items: [{ id: "a" }, { id: "b", selectable: false }],
				selecting: true
			});
			expect(
				rendered(wrapper).map((item) => [
					item.props("selecting"),
					item.props("selectable")
				])
			).toStrictEqual([
				[true, true],
				[true, false]
			]);
		});
	});

	describe("size prop", () => {
		it("sets the card size", () => {
			expect(mount({ size: "small" }).attributes("data-size")).toBe("small");
		});
	});

	describe("sortable prop", () => {
		it("makes the items sortable", () => {
			const wrapper = mount({
				items: [{ id: "a" }, { id: "b", sortable: false }],
				sortable: true
			});
			expect(wrapper.findComponent(Draggable).props("options")).toStrictEqual({
				disabled: false,
				draggable: ".k-draggable-item",
				sort: true
			});
			expect(
				rendered(wrapper).map((item) => [
					item.props("sortable"),
					item.classes("k-draggable-item")
				])
			).toStrictEqual([
				[true, true],
				[false, false]
			]);
		});

		it("disables sorting by default", () => {
			expect(mount().findComponent(Draggable).props("options")).toStrictEqual({
				disabled: true,
				draggable: ".k-draggable-item",
				sort: false
			});
		});
	});

	describe("theme prop", () => {
		it("themes the items that have no theme of their own", () => {
			const wrapper = mount({
				items: [{ id: "a" }, { id: "b", theme: "negative" }],
				theme: "disabled"
			});
			expect(
				rendered(wrapper).map((item) => item.props("theme"))
			).toStrictEqual(["disabled", "negative"]);
		});
	});

	// methods
	describe("onSelect()", () => {
		it("adds an item to the selection", () => {
			const wrapper = mount({ selected: ["a"] });
			wrapper.vm.onSelect(items()[1]);
			expect(wrapper.emitted("select")).toStrictEqual([[["a", "page://b"]]]);
		});

		it("removes a selected item", () => {
			const wrapper = mount({ selected: ["a", "page://b"] });
			wrapper.vm.onSelect(items()[0]);
			expect(wrapper.emitted("select")).toStrictEqual([[["page://b"]]]);
		});

		it("replaces the selection in single mode", () => {
			const wrapper = mount({ selected: ["a"], selectmode: "single" });
			wrapper.vm.onSelect(items()[2]);
			expect(wrapper.emitted("select")).toStrictEqual([[[3]]]);
		});
	});

	// events
	describe("events", () => {
		it("emits the clicked item with its index", () => {
			const wrapper = mount();
			rendered(wrapper)[1].vm.$emit("click");
			expect(wrapper.emitted("item")?.[0]).toMatchObject([
				{ text: "B", uuid: "page://b" },
				1
			]);
		});

		it("emits the hovered item", async () => {
			const wrapper = mount();
			await rendered(wrapper)[0].trigger("mouseover");
			const [event, item, index] = wrapper.emitted("hover")?.[0] ?? [];
			expect(event).toBeInstanceOf(Event);
			expect(item).toMatchObject({ id: "a" });
			expect(index).toBe(0);
		});

		it("emits the option of an item", () => {
			const wrapper = mount();
			rendered(wrapper)[2].vm.$emit("option", "remove");
			expect(wrapper.emitted("option")?.[0]).toMatchObject([
				"remove",
				{ text: "C" },
				2
			]);
		});

		it("toggles the selection of an item", () => {
			const wrapper = mount({ selected: ["a"] });
			rendered(wrapper)[0].vm.$emit("select");
			expect(wrapper.emitted("select")).toStrictEqual([[[]]]);
		});

		it("starts dragging the text of an item", () => {
			const $panel = panel();
			const wrapper = mount(
				{ items: [{ dragText: "(link: a)", id: "a" }] },
				{},
				{},
				$panel
			);
			rendered(wrapper)[0].vm.$emit("drag", new Event("dragstart"));
			expect($panel.drag.start).toHaveBeenCalledWith("text", "(link: a)");
		});

		it("passes on the changes and the sorting", () => {
			const wrapper = mount();
			const draggable = wrapper.findComponent(Draggable);
			draggable.vm.$emit("change", { moved: {} });
			draggable.vm.$emit("end", { newIndex: 1 });
			expect(wrapper.emitted("change")).toStrictEqual([[{ moved: {} }]]);
			expect(wrapper.emitted("sort")).toStrictEqual([
				[items(), { newIndex: 1 }]
			]);
		});

		it("passes on the events of the table", () => {
			const wrapper = mount({ layout: "table" });
			const table = wrapper.findComponent(Table);
			table.vm.$emit("change", "changed");
			table.vm.$emit("select", ["a"]);
			table.vm.$emit("sort", "sorted");
			table.vm.$emit("option", "remove", items()[0], 0);
			expect(wrapper.emitted("change")).toStrictEqual([["changed"]]);
			expect(wrapper.emitted("select")).toStrictEqual([[["a"]]]);
			expect(wrapper.emitted("sort")).toStrictEqual([["sorted"]]);
			expect(wrapper.emitted("option")).toStrictEqual([
				["remove", items()[0], 0]
			]);
		});
	});

	// slots
	describe("default slot", () => {
		it("replaces each item", () => {
			const wrapper = mount(
				{},
				{},
				{
					default: `<template #default="{ item, itemIndex }"><p>{{ itemIndex }}: {{ item.text }}</p></template>`
				}
			);
			expect(wrapper.findAll("p").map((p) => p.text())).toStrictEqual([
				"0: A",
				"1: B",
				"2: C"
			]);
			expect(rendered(wrapper)).toHaveLength(0);
		});
	});

	describe("options slot", () => {
		it("renders the options of each item", () => {
			const wrapper = mount(
				{},
				{},
				{
					options: `<template #options="{ item, index }"><i>{{ index }}{{ item.text }}</i></template>`
				}
			);
			expect(wrapper.findAll("article i").map((i) => i.text())).toStrictEqual([
				"0A",
				"1B",
				"2C"
			]);
		});

		it("renders the options of each table row", () => {
			const wrapper = mount(
				{ layout: "table" },
				{},
				{
					options: `<template #options="{ item, index }"><i>{{ index }}{{ item.text }}</i></template>`
				}
			);
			expect(wrapper.find("table i").text()).toBe("0A");
		});
	});
});
