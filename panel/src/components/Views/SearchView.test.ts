import { afterEach, describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import { reactive } from "vue";
import { buildUrl } from "@/helpers/url";
import SearchView from "./SearchView.vue";

const Collection = {
	name: "k-collection",
	props: ["empty", "items", "pagination"],
	emits: ["paginate"],
	template: "<div />"
};
const Header = {
	name: "k-header",
	template: `<header><slot /><slot name="buttons" /></header>`
};
const Input = {
	name: "k-input",
	props: [
		"ariaLabel",
		"autofocus",
		"icon",
		"placeholder",
		"spellcheck",
		"type",
		"value"
	],
	emits: ["input"],
	methods: { focus: vi.fn() },
	template: "<input />"
};
const Inside = {
	name: "k-panel-inside",
	template: "<div><slot /></div>"
};
const Tabs = {
	name: "k-tabs",
	props: ["tab", "tabs"],
	template: "<nav />"
};

const results = [{ id: "a", text: "A" }];

function panel() {
	return reactive({
		isLoading: false,
		search: vi.fn(async () => ({ pagination: { page: 1, total: 1 }, results })),
		searcher: { isLoading: false },
		searches: {
			pages: { id: "pages", label: "Pages" },
			users: { id: "users", label: "Users" }
		},
		url: buildUrl
	});
}

function mount(props = {}, attrs = {}, $panel = panel()) {
	return vueMount(SearchView, {
		// no debounce, searches run after a timeout of 0
		props: { delay: 0, ...props },
		attrs,
		global: {
			mocks: { $panel },
			stubs: {
				"k-collection": Collection,
				"k-header": Header,
				"k-input": Input,
				"k-panel-inside": Inside,
				"k-tabs": Tabs
			}
		}
	});
}

// visits the search view with the query
function visit(query: string) {
	window.history.replaceState(null, "", "/panel/search?" + query);
}

async function searched() {
	await new Promise((resolve) => setTimeout(resolve, 5));
}

describe("k-search-view", () => {
	const url = window.location.href;

	afterEach(() => {
		window.history.replaceState(null, "", url);
	});

	// $el
	describe("element", () => {
		it.rendersAs(
			(attrs = {}) => {
				visit("query=kirby");
				return mount({}, attrs);
			},
			"DIV",
			"k-search-view"
		);

		it("renders a search input in the header", () => {
			visit("query=kirby");
			const wrapper = mount();
			expect(wrapper.find("header").text()).toBe("search");
			expect(wrapper.findComponent(Input).classes()).toContain(
				"k-search-view-input"
			);
			expect(wrapper.findComponent(Input).props()).toStrictEqual({
				ariaLabel: "search",
				autofocus: true,
				icon: "search",
				placeholder: "search …",
				spellcheck: false,
				type: "text",
				value: "kirby"
			});
		});

		it("renders a tab per search type", () => {
			visit("query=kirby");
			const wrapper = mount();
			expect(wrapper.findComponent(Tabs).props()).toStrictEqual({
				tab: "pages",
				tabs: [
					{
						label: "Pages",
						link: "/search/?type=pages&query=kirby",
						name: "pages"
					},
					{
						label: "Users",
						link: "/search/?type=users&query=kirby",
						name: "users"
					}
				]
			});
		});
	});

	// props
	describe("type prop", () => {
		it("searches the type", async () => {
			visit("query=kirby");
			const $panel = panel();
			const wrapper = mount({ type: "users" }, {}, $panel);
			await searched();
			expect(wrapper.findComponent(Tabs).props("tab")).toBe("users");
			expect($panel.search).toHaveBeenCalledWith("users", "kirby", {
				limit: 15,
				page: 1
			});
		});

		it("searches the first type for an unknown type", async () => {
			visit("query=kirby");
			const $panel = panel();
			mount({ type: "files" }, {}, $panel);
			await searched();
			expect($panel.search).toHaveBeenCalledWith("pages", "kirby", {
				limit: 15,
				page: 1
			});
		});
	});

	// computed
	describe("empty computed", () => {
		it("hints at a too short query", async () => {
			visit("query=k");
			const wrapper = mount();
			await searched();
			expect(wrapper.findComponent(Collection).props("empty")).toStrictEqual({
				icon: "search",
				text: "search.min"
			});
		});

		it("says when nothing was found", async () => {
			visit("query=kirby");
			const wrapper = mount();
			await searched();
			expect(wrapper.findComponent(Collection).props("empty").text).toBe(
				"search.results.none"
			);
		});

		it("says while searching", async () => {
			visit("query=kirby");
			const $panel = panel();
			$panel.searcher.isLoading = true;
			const wrapper = mount({}, {}, $panel);
			expect(wrapper.findComponent(Collection).props("empty")).toStrictEqual({
				icon: "loader",
				text: "searching…"
			});
			expect(wrapper.findComponent(Input).props("icon")).toBe("loader");
		});
	});

	// watch
	describe("isLoading watcher", () => {
		it("shows the Panel's loader while searching", async () => {
			visit("query=kirby");
			const $panel = panel();
			mount({}, {}, $panel);
			$panel.searcher.isLoading = true;
			await new Promise((resolve) => setTimeout(resolve));
			expect($panel.isLoading).toBe(true);
		});
	});

	describe("query watcher", () => {
		it("searches the first page of the query", async () => {
			visit("query=kirby&page=3");
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			await searched();
			expect($panel.search).toHaveBeenLastCalledWith("pages", "kirby", {
				limit: 15,
				page: 1
			});
			expect(wrapper.findComponent(Collection).props()).toMatchObject({
				items: results,
				pagination: { page: 1, total: 1 }
			});
			expect(new URL(window.location.href).searchParams.toString()).toBe(
				"query=kirby&page=1&type=pages"
			);
		});

		it("searches the new query", async () => {
			visit("query=kirby");
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			wrapper.findComponent(Input).vm.$emit("input", "cms");
			await searched();
			expect($panel.search).toHaveBeenLastCalledWith("pages", "cms", {
				limit: 15,
				page: 1
			});
		});
	});

	describe("type watcher", () => {
		it("searches the new type on the page of the URL", async () => {
			visit("query=kirby&page=2");
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			await searched();
			await wrapper.setProps({ type: "users" } as never);
			await searched();
			expect($panel.search).toHaveBeenLastCalledWith("users", "kirby", {
				limit: 15,
				page: "1"
			});
		});
	});

	// methods
	describe("focus()", () => {
		it("focuses the input", () => {
			visit("query=kirby");
			const wrapper = mount();
			const focus = vi.spyOn(wrapper.findComponent(Input).vm, "focus");
			wrapper.vm.focus();
			expect(focus).toHaveBeenCalledOnce();
		});
	});

	// events
	describe("events", () => {
		it("searches the page of the pagination", async () => {
			visit("query=kirby");
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			await searched();
			wrapper.findComponent(Collection).vm.$emit("paginate", { page: 2 });
			await searched();
			expect($panel.search).toHaveBeenLastCalledWith("pages", "kirby", {
				limit: 15,
				page: 2
			});
		});
	});
});
