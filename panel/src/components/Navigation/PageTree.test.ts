import { describe, expect, it, vi } from "@test/unit";
import { flushPromises, mount as vueMount } from "@vue/test-utils";
import PageTree from "./PageTree.vue";

type Item = {
	children?: Item[] | string;
	hasChildren: boolean;
	id: string;
	label: string;
	loading?: boolean;
	open?: boolean;
	uuid: string;
	value: string;
};

function page(id: string, children?: string): Item {
	return {
		children,
		hasChildren: children !== undefined,
		id,
		label: id,
		uuid: "page://" + id,
		value: "page://" + id
	};
}

// the branches the tree route returns by parent
const branches: Record<string, () => Item[]> = {
	"": () => [
		{ ...page("site", "/site"), id: "/", uuid: "site://", value: "site://" }
	],
	"/site": () => [page("blog", "/pages/blog"), page("about")],
	"/pages/blog": () => [page("article")]
};

const parents: Record<string, string[]> = {
	"page://article": ["site://", "page://blog"]
};

function panel() {
	return {
		get: vi.fn(
			async (path: string, { query }: { query: Record<string, string> }) => {
				if (path === "site/tree/parents") {
					return { data: parents[query.page] ?? [] };
				}

				return branches[query.parent ?? ""]();
			}
		)
	};
}

function mount(props = {}, attrs = {}, $panel = panel()) {
	return vueMount(PageTree, {
		props,
		attrs,
		global: {
			// the tree renders itself for the children
			components: { "k-page-tree": PageTree },
			mocks: { $panel }
		}
	});
}

function labels(wrapper: ReturnType<typeof mount>) {
	return wrapper.findAll(".k-tree-folder-label").map((label) => label.text());
}

describe("k-page-tree", () => {
	// $el
	describe("element", () => {
		const component = (attrs = {}) => mount({ items: [page("blog")] }, attrs);

		it.rendersAs(component, "UL", "k-page-tree");
		it.acceptsClass(component);
		it.acceptsStyle(component);
		it.inheritsNoAttrs(component);

		it("keeps the class of the tree", () => {
			expect(component().classes()).toContain("k-tree");
		});
	});

	// props
	describe("items prop", () => {
		it("shows the items without loading", async () => {
			const $panel = panel();
			const wrapper = mount(
				{ items: [page("blog"), page("about")] },
				{},
				$panel
			);
			await flushPromises();
			expect(labels(wrapper)).toStrictEqual(["blog", "about"]);
			expect($panel.get).not.toHaveBeenCalled();
		});
	});

	describe("move prop", () => {
		it("passes the page to move to the tree route", async () => {
			const $panel = panel();
			mount({ move: "page://about" }, {}, $panel);
			await flushPromises();
			expect($panel.get).toHaveBeenCalledWith("site/tree", {
				query: { move: "page://about", parent: null }
			});
		});
	});

	describe("root prop", () => {
		it("loads the site with its first level", async () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			expect(wrapper.vm.state).toStrictEqual([{ icon: "loader" }]);
			await flushPromises();
			expect($panel.get).toHaveBeenCalledWith("site/tree", {
				query: { move: null, parent: "/site" }
			});
			expect(labels(wrapper)).toStrictEqual(["site", "blog", "about"]);
		});

		it("shows the first level without the site", async () => {
			const wrapper = mount({ root: false });
			await flushPromises();
			expect(labels(wrapper)).toStrictEqual(["blog", "about"]);
		});
	});

	describe("current prop", () => {
		it("selects a loaded page right away", async () => {
			const $panel = panel();
			const wrapper = mount(
				{ current: "page://about", root: false },
				{},
				$panel
			);
			await flushPromises();
			expect(wrapper.emitted("select")?.[0][0]).toMatchObject({ id: "about" });
			expect($panel.get).not.toHaveBeenCalledWith(
				"site/tree/parents",
				expect.anything()
			);
		});

		it("opens the parents of a deeper page", async () => {
			const $panel = panel();
			const wrapper = mount({ current: "page://article" }, {}, $panel);
			await flushPromises();
			expect($panel.get).toHaveBeenCalledWith("site/tree/parents", {
				query: { page: "page://article", root: true }
			});
			expect(labels(wrapper)).toStrictEqual([
				"site",
				"blog",
				"article",
				"about"
			]);
			expect(wrapper.emitted("select")?.[0][0]).toMatchObject({
				id: "article"
			});
		});

		it("stops at a parent that isn't in the tree", async () => {
			parents["page://lost"] = ["site://", "page://unknown"];
			const wrapper = mount({ current: "page://lost" });
			await flushPromises();
			expect(wrapper.emitted("select")).toBeUndefined();
		});
	});

	// methods
	describe("isItem()", () => {
		it("matches the value, UUID or id", () => {
			const wrapper = mount({ items: [] });
			const item = { id: "blog", uuid: "page://x", value: "v" };
			expect(wrapper.vm.isItem(item, "v")).toBe(true);
			expect(wrapper.vm.isItem(item, "page://x")).toBe(true);
			expect(wrapper.vm.isItem(item, "blog")).toBe(true);
			expect(wrapper.vm.isItem(item, "about")).toBe(false);
		});
	});

	describe("open()", () => {
		it("loads the children once", async () => {
			const $panel = panel();
			const wrapper = mount({ items: [] }, {}, $panel);
			const item = page("blog", "/pages/blog");
			await wrapper.vm.open(item);
			expect(item).toMatchObject({ loading: false, open: true });
			expect((item.children as Item[]).map((child) => child.id)).toStrictEqual([
				"article"
			]);
			await wrapper.vm.open(item);
			expect($panel.get).toHaveBeenCalledOnce();
		});

		it("doesn't open an item without children", async () => {
			const wrapper = mount({ items: [] });
			const item = page("about");
			expect(await wrapper.vm.open(item)).toBe(false);
			expect(item.open).toBeUndefined();
		});

		it("ignores a missing item", async () => {
			const wrapper = mount({ items: [] });
			expect(await wrapper.vm.open(undefined)).toBeUndefined();
		});
	});

	// events
	describe("events", () => {
		it("opens a branch with the arrow", async () => {
			const wrapper = mount({ root: false });
			await flushPromises();
			await wrapper.find(".k-tree-toggle").trigger("click");
			await flushPromises();
			expect(labels(wrapper)).toStrictEqual(["blog", "article", "about"]);
			expect(wrapper.emitted("toggle")).toHaveLength(1);
		});

		it("selects a page on click", async () => {
			const wrapper = mount({ root: false });
			await flushPromises();
			await wrapper.findAll(".k-tree-folder")[1].trigger("click");
			expect(wrapper.emitted("select")?.[0][0]).toMatchObject({ id: "about" });
		});
	});
});
