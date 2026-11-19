import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Tree from "./Tree.vue";

type Item = {
	children?: Item[];
	disabled?: boolean;
	hasChildren?: boolean;
	icon?: string;
	label: string;
	loading?: boolean;
	open?: boolean;
	value: string;
};

function items(): Item[] {
	return [
		{
			children: [{ hasChildren: false, label: "Article", value: "blog/a" }],
			hasChildren: true,
			label: "Blog",
			value: "blog"
		},
		{ hasChildren: false, icon: "page", label: "About", value: "about" }
	];
}

function mount(props = {}, attrs = {}) {
	return vueMount(Tree, {
		props: { items: items(), ...props },
		attrs,
		global: {
			// the tree renders itself for the children
			components: { "k-tree": Tree }
		}
	});
}

function branches(wrapper: ReturnType<typeof mount>) {
	return wrapper.findAll(":scope > li");
}

// the nested trees, without the tree itself
function children(wrapper: ReturnType<typeof mount>) {
	return wrapper.findAllComponents(Tree);
}

function state(wrapper: ReturnType<typeof mount>) {
	return wrapper.vm.state as Item[];
}

function folders(wrapper: ReturnType<typeof mount>) {
	return wrapper.findAll(".k-tree-folder");
}

describe("k-tree", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "UL", "k-tree");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("renders a branch per item", () => {
			const wrapper = mount();
			expect(branches(wrapper)).toHaveLength(2);
			expect(folders(wrapper).map((folder) => folder.text())).toStrictEqual([
				"Blog",
				"About"
			]);
		});

		it("shows a folder icon by default", () => {
			const wrapper = mount();
			expect(
				wrapper.findAll("k-icon-frame").map((frame) => frame.attributes("icon"))
			).toStrictEqual(["folder", "page"]);
		});

		it("can only toggle items with children", () => {
			const wrapper = mount();
			expect(
				wrapper
					.findAll(".k-tree-toggle")
					.map((toggle) => toggle.attributes("disabled"))
			).toStrictEqual([undefined, ""]);
		});
	});

	// props
	describe("current prop", () => {
		it("marks the current item", () => {
			const wrapper = mount({ current: "about" });
			expect(
				branches(wrapper).map((branch) => branch.attributes("aria-current"))
			).toStrictEqual(["false", "true"]);
		});
	});

	describe("items prop", () => {
		it("disables items", () => {
			const wrapper = mount({
				items: [{ disabled: true, label: "Blog", value: "blog" }]
			});
			expect(folders(wrapper)[0].attributes("disabled")).toBe("");
		});

		it("shows open items with their children", () => {
			const list = items();
			list[0].open = true;
			const wrapper = mount({ items: list });
			const child = children(wrapper)[0];
			expect(branches(wrapper)[0].attributes("aria-expanded")).toBe("true");
			expect(child.props()).toMatchObject({ level: 1, scrollable: false });
			expect(
				(child.element as HTMLElement).style.getPropertyValue("--tree-level")
			).toBe("1");
			expect(child.find(".k-tree-folder").text()).toBe("Article");
		});

		it("hides the children of closed items", () => {
			const wrapper = mount();
			expect(branches(wrapper)[0].attributes("aria-expanded")).toBe(undefined);
			expect(children(wrapper)).toHaveLength(0);
		});
	});

	describe("level prop", () => {
		it("starts at the top level", () => {
			const style = (mount().element as HTMLElement).style;
			expect(style.getPropertyValue("--tree-level")).toBe("0");
		});
	});

	describe("scrollable prop", () => {
		it("renders a scrollable list", () => {
			const wrapper = mount({ scrollable: true });
			expect(wrapper.element.tagName).toBe("K-SCROLLABLE");
			expect(wrapper.attributes("element")).toBe("ul");
			expect(wrapper.classes()).toContain("k-tree");
		});
	});

	// methods
	describe("arrow()", () => {
		it("points to the state of the item", () => {
			const wrapper = mount();
			expect(wrapper.vm.arrow({ open: false })).toBe("angle-right");
			expect(wrapper.vm.arrow({ open: true })).toBe("angle-down");
			expect(wrapper.vm.arrow({ loading: true, open: true })).toBe("loader");
		});
	});

	describe("toggle()", () => {
		it("opens a closed item", async () => {
			const wrapper = mount();
			const item = state(wrapper)[0];
			wrapper.vm.toggle(item);
			expect(item.open).toBe(true);
			expect(wrapper.emitted("toggle")).toStrictEqual([[item]]);
			expect(wrapper.emitted("open")).toStrictEqual([[item]]);
		});

		it("closes an open item", async () => {
			const list = items();
			list[0].open = true;
			const wrapper = mount({ items: list });
			wrapper.vm.toggle(list[0]);
			expect(list[0].open).toBe(false);
			expect(wrapper.emitted("close")).toStrictEqual([[list[0]]]);
		});
	});

	// events
	describe("events", () => {
		it("toggles an item with the arrow", async () => {
			const wrapper = mount();
			await wrapper.find(".k-tree-toggle").trigger("click");
			expect(wrapper.emitted("open")).toHaveLength(1);
			expect(children(wrapper)).toHaveLength(1);
		});

		it("toggles an item on double click", async () => {
			const wrapper = mount();
			await folders(wrapper)[0].trigger("dblclick");
			expect(wrapper.emitted("toggle")).toHaveLength(1);
		});

		it("selects an item on click", async () => {
			const wrapper = mount();
			await folders(wrapper)[1].trigger("click");
			expect(wrapper.emitted("select")).toStrictEqual([[state(wrapper)[1]]]);
		});

		it("passes on the events of the children", async () => {
			const list = items();
			list[0].open = true;
			const wrapper = mount({ items: list });
			await children(wrapper)[0].find(".k-tree-folder").trigger("click");
			expect(wrapper.emitted("select")).toStrictEqual([
				[list[0].children?.[0]]
			]);
		});
	});
});
