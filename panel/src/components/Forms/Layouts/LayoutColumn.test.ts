import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import LayoutColumn from "./LayoutColumn.vue";

const Blocks = {
	name: "k-blocks",
	props: [
		"disabled",
		"endpoints",
		"fieldsetGroups",
		"fieldsets",
		"group",
		"value"
	],
	emits: ["input"],
	methods: { choose: vi.fn() },
	template: "<div />"
};

const blocks = [
	{ content: {}, id: "x", type: "text" },
	{ content: {}, id: "y", type: "text" }
];
const endpoints = { field: "pages/a/fields/layout", model: "pages/a" };
const fieldsets = { text: { name: "Text", type: "text" } };

function mount(props = {}, attrs = {}) {
	return vueMount(LayoutColumn, {
		props: { blocks, endpoints, fieldsets, id: "a1", ...props },
		attrs,
		global: {
			stubs: { "k-blocks": Blocks }
		}
	});
}

function choose(wrapper: ReturnType<typeof mount>) {
	return vi.spyOn(wrapper.findComponent(Blocks).vm, "choose");
}

describe("k-layout-column", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-layout-column");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);

		it("is a column out of the Tab order", () => {
			const wrapper = mount();
			expect(wrapper.classes()).toContain("k-column");
			expect(wrapper.attributes("tabindex")).toBe("-1");
		});

		it("renders the blocks of the layout group", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Blocks).props()).toStrictEqual({
				disabled: false,
				endpoints,
				fieldsetGroups: undefined,
				fieldsets,
				group: "layout",
				value: blocks
			});
		});
	});

	// props
	describe("disabled prop", () => {
		it("disables the blocks", () => {
			const wrapper = mount({ disabled: true });
			expect(wrapper.findComponent(Blocks).props("disabled")).toBe(true);
		});
	});

	describe("fieldsetGroups prop", () => {
		it("passes the groups to the blocks", () => {
			const fieldsetGroups = { text: { label: "Text", sets: ["text"] } };
			const wrapper = mount({ fieldsetGroups });
			expect(
				wrapper.findComponent(Blocks).props("fieldsetGroups")
			).toStrictEqual(fieldsetGroups);
		});
	});

	describe("id prop", () => {
		it("sets the id", () => {
			expect(mount().attributes("id")).toBe("a1");
		});
	});

	describe("width prop", () => {
		it("spans the full width by default", () => {
			expect(mount().element.style.getPropertyValue("--width")).toBe("1/1");
		});

		it("sets the width", () => {
			const wrapper = mount({ width: "1/3" });
			expect(wrapper.element.style.getPropertyValue("--width")).toBe("1/3");
		});
	});

	// events
	describe("events", () => {
		it("emits the changed blocks", () => {
			const wrapper = mount();
			const value = [blocks[0]];
			wrapper.findComponent(Blocks).vm.$emit("input", value);
			expect(wrapper.emitted("input")).toStrictEqual([[value]]);
		});

		it("adds a block at the end on double click", async () => {
			const wrapper = mount();
			const spy = choose(wrapper);
			await wrapper.trigger("dblclick");
			expect(spy).toHaveBeenCalledWith(2);
		});

		it("ignores double clicks on the blocks", async () => {
			const wrapper = mount();
			const spy = choose(wrapper);
			await wrapper.findComponent(Blocks).trigger("dblclick");
			expect(spy).not.toHaveBeenCalled();
		});
	});
});
