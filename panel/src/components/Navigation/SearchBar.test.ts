import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import { nextTick } from "vue";
import SearchBar from "./SearchBar.vue";

const Button = {
	name: "k-button",
	props: ["dropdown", "icon", "text", "title", "variant"],
	emits: ["click"],
	template: `<button @click="$emit('click', $event)"><slot /></button>`
};
const Collection = {
	name: "k-collection",
	props: ["items"],
	template: `<div><article v-for="item in items" :key="item.id" class="k-item" /></div>`
};
const Dropdown = {
	name: "k-dropdown",
	props: ["options"],
	methods: { close: vi.fn(), toggle: vi.fn() },
	template: "<div />"
};
const SearchInput = {
	name: "k-search-input",
	props: ["ariaLabel", "autofocus", "value"],
	emits: ["input"],
	methods: { focus: vi.fn() },
	template: "<input />"
};

const types = {
	pages: { icon: "page", id: "pages", label: "Pages" },
	users: { icon: "users", id: "users", label: "Users" }
};

const results = [
	{ id: "a", link: "/pages/a", text: "A" },
	{ id: "b", link: "/pages/b", text: "B" }
];

function mount(props = {}, attrs = {}) {
	return vueMount(SearchBar, {
		// shortest debounce, searches still wait for a timer
		props: { delay: 0, types, ...props },
		attrs,
		global: {
			stubs: {
				"k-button": Button,
				"k-collection": Collection,
				"k-dropdown": Dropdown,
				"k-search-input": SearchInput
			}
		}
	});
}

function button(wrapper: ReturnType<typeof mount>, selector: string) {
	return wrapper
		.findAllComponents(Button)
		.find((button) => button.classes(selector))!;
}

function input(wrapper: ReturnType<typeof mount>) {
	return wrapper.findComponent(SearchInput);
}

function selected(wrapper: ReturnType<typeof mount>) {
	return wrapper
		.findAll(".k-item")
		.map((item) => item.attributes("data-selected") ?? null);
}

// runs the watchers first, so their search timer is due before ours
async function debounce() {
	await nextTick();
	await new Promise((resolve) => setTimeout(resolve, 5));
}

describe("k-search-bar", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-search-bar");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);

		it("renders a search input", () => {
			expect(input(mount()).props()).toStrictEqual({
				ariaLabel: "search",
				autofocus: true,
				value: ""
			});
		});

		it("renders a close button", () => {
			const close = button(mount(), "k-search-bar-close");
			expect(close.props()).toMatchObject({ icon: "cancel", title: "close" });
		});
	});

	// props
	describe("defaultType prop", () => {
		it("starts with the first type", () => {
			const types = button(mount(), "k-search-bar-types");
			expect(types.props()).toMatchObject({ icon: "page", text: "Pages" });
		});

		it("starts with the default type", () => {
			const wrapper = mount({ defaultType: "users" });
			expect(button(wrapper, "k-search-bar-types").props("text")).toBe("Users");
		});

		it("ignores an unknown default type", () => {
			const wrapper = mount({ defaultType: "files" });
			expect(button(wrapper, "k-search-bar-types").props("text")).toBe("Pages");
		});
	});

	describe("isLoading prop", () => {
		it("shows a loader on the close button", () => {
			const wrapper = mount({ isLoading: true });
			expect(button(wrapper, "k-search-bar-close").props("icon")).toBe(
				"loader"
			);
		});
	});

	describe("pagination prop", () => {
		it("offers all results if there are more", async () => {
			const wrapper = mount({ pagination: { total: 5 }, results });
			const more = wrapper
				.findAllComponents(Button)
				.find((button) => button.props("icon") === "search")!;
			expect(more.text()).toBe("search.all");
			await more.trigger("click");
			expect(wrapper.emitted("more")).toStrictEqual([
				[{ query: "", type: "pages" }]
			]);
		});

		it("doesn't offer more without more results", () => {
			const wrapper = mount({ pagination: { total: 2 }, results });
			expect(
				wrapper
					.findAllComponents(Button)
					.some((button) => button.props("icon") === "search")
			).toBe(false);
		});
	});

	describe("results prop", () => {
		it("shows nothing before searching", () => {
			expect(mount().find(".k-search-bar-results").exists()).toBe(false);
		});

		it("shows the results", () => {
			const wrapper = mount({ results });
			expect(wrapper.findComponent(Collection).props("items")).toStrictEqual(
				results
			);
		});

		it("says when nothing was found", () => {
			const wrapper = mount({ results: [] });
			expect(wrapper.findComponent(Collection).exists()).toBe(false);
			expect(wrapper.find(".k-search-bar-footer p").text()).toBe(
				"search.results.none"
			);
		});
	});

	describe("types prop", () => {
		it("offers the types in a dropdown", () => {
			const wrapper = mount();
			expect(
				wrapper
					.findComponent(Dropdown)
					.props("options")
					.map((option: { current: boolean; id: string }) => [
						option.id,
						option.current
					])
			).toStrictEqual([
				["pages", true],
				["users", false]
			]);
		});

		it("hides the dropdown for a single type", () => {
			const wrapper = mount({ types: { pages: types.pages } });
			expect(wrapper.findComponent(Dropdown).exists()).toBe(false);
		});
	});

	// watch
	describe("query watcher", () => {
		it("searches for the query", async () => {
			const wrapper = mount();
			input(wrapper).vm.$emit("input", "kirby");
			await debounce();
			expect(wrapper.emitted("search")).toStrictEqual([
				[{ query: "kirby", type: "pages" }]
			]);
		});
	});

	describe("type watcher", () => {
		it("searches again in the new type", async () => {
			const wrapper = mount();
			const focus = vi.spyOn(input(wrapper).vm, "focus");
			wrapper.findComponent(Dropdown).props("options")[1].click();
			await debounce();
			expect(button(wrapper, "k-search-bar-types").props("text")).toBe("Users");
			expect(focus).toHaveBeenCalledOnce();
			expect(wrapper.emitted("search")).toStrictEqual([
				[{ query: "", type: "users" }]
			]);
		});
	});

	// methods
	describe("focus()", () => {
		it("focuses the input", () => {
			const wrapper = mount();
			const focus = vi.spyOn(input(wrapper).vm, "focus");
			wrapper.vm.focus();
			expect(focus).toHaveBeenCalledOnce();
		});
	});

	describe("search()", () => {
		it("closes the types and clears the selection", async () => {
			const wrapper = mount({ results });
			const close = vi.spyOn(wrapper.findComponent(Dropdown).vm, "close");
			wrapper.vm.select(1);
			wrapper.vm.search();
			await debounce();
			expect(close).toHaveBeenCalledOnce();
			expect(selected(wrapper)).toStrictEqual([null, null]);
		});
	});

	// events
	describe("events", () => {
		it("selects the results with the arrow keys", async () => {
			const wrapper = mount({ results });
			await input(wrapper).trigger("keydown", { key: "ArrowDown" });
			expect(selected(wrapper)).toStrictEqual(["true", null]);
			await input(wrapper).trigger("keydown", { key: "ArrowDown" });
			await input(wrapper).trigger("keydown", { key: "ArrowDown" });
			expect(selected(wrapper)).toStrictEqual([null, "true"]);
			await input(wrapper).trigger("keydown", { key: "ArrowUp" });
			await input(wrapper).trigger("keydown", { key: "ArrowUp" });
			await input(wrapper).trigger("keydown", { key: "ArrowUp" });
			expect(selected(wrapper)).toStrictEqual([null, null]);
			// going up past the input doesn't need extra steps back down
			await input(wrapper).trigger("keydown", { key: "ArrowDown" });
			expect(selected(wrapper)).toStrictEqual(["true", null]);
		});

		it("navigates to the selected result on enter", async () => {
			const wrapper = mount({ results });
			await input(wrapper).trigger("keydown", { key: "ArrowDown" });
			await input(wrapper).trigger("keydown", { key: "ArrowDown" });
			await input(wrapper).trigger("keydown", { key: "Enter" });
			expect(wrapper.emitted("navigate")).toStrictEqual([[results[1]]]);
		});

		it("navigates to the first result without selection", async () => {
			const wrapper = mount({ results });
			await input(wrapper).trigger("keydown", { key: "Enter" });
			expect(wrapper.emitted("navigate")).toStrictEqual([[results[0]]]);
		});

		it("clears the selection when the mouse leaves the results", async () => {
			const wrapper = mount({ results });
			wrapper.vm.select(0);
			await wrapper.findComponent(Collection).trigger("mouseout");
			expect(selected(wrapper)).toStrictEqual([null, null]);
		});

		it("toggles the types", async () => {
			const wrapper = mount();
			const toggle = vi.spyOn(wrapper.findComponent(Dropdown).vm, "toggle");
			await button(wrapper, "k-search-bar-types").trigger("click");
			expect(toggle).toHaveBeenCalledOnce();
		});

		it("emits close", async () => {
			const wrapper = mount();
			await button(wrapper, "k-search-bar-close").trigger("click");
			expect(wrapper.emitted("close")).toHaveLength(1);
		});
	});
});
