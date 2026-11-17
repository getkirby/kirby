import { beforeEach, describe, expect, it, vi } from "@test/unit";
import { flushPromises, mount as vueMount } from "@vue/test-utils";
import PageListField from "./PageListField.vue";

// registered globally in the Panel
const Collection = { props: ["items"], template: "<div />" };

const api = {
	get: vi.fn(),
	pages: { changeStatus: vi.fn() }
};
const events = { emit: vi.fn(), off: vi.fn(), on: vi.fn() };

const state = {
	add: true,
	models: [],
	pagination: { limit: 20, offset: 0, page: 1, total: 0 },
	sortable: true
};

function page(id: string, permissions = {}) {
	return {
		id,
		link: `pages/${id}`,
		permissions: {
			changeStatus: true,
			delete: true,
			sort: true,
			...permissions
		},
		status: "listed",
		template: "article",
		text: id
	};
}

function panel(create = true) {
	return {
		dialog: { open: vi.fn() },
		error: vi.fn(),
		events: { emit: vi.fn() },
		notification: { success: vi.fn() },
		permissions: { pages: { create } }
	};
}

/**
 * Lets the field load the given pages
 */
function pages(models: object[], merge = {}) {
	api.get.mockResolvedValue({
		...state,
		models,
		pagination: { ...state.pagination, total: models.length },
		...merge
	});
}

function mount(props = {}, attrs = {}, $panel = panel()) {
	return vueMount(PageListField, {
		props: {
			endpoints: { field: "pages/test/fields/children" },
			name: "children",
			...props
		},
		attrs,
		global: {
			components: { "k-collection": Collection },
			mocks: {
				$api: api,
				// returns its arguments to check the dropdown of each page
				$dropdown: (link: string, options: object) => ({ link, ...options }),
				$events: events,
				$panel
			}
		}
	});
}

function items(wrapper: ReturnType<typeof mount>) {
	return wrapper.findComponent(Collection).props("items");
}

describe("k-pagelist-field", () => {
	beforeEach(() => {
		api.get.mockResolvedValue(state);
	});

	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-FIELD", "k-modellist-field");
		it.inheritsNoAttrs(mount);

		it("passes the type to the k-field", () => {
			const wrapper = mount({ type: "pages" });
			expect(wrapper.attributes("type")).toBe("pages");
		});
	});

	// computed
	describe("canAdd computed", () => {
		it("can add pages with permission", async () => {
			const wrapper = mount();
			await flushPromises();
			expect(wrapper.vm.canAdd).toBe(true);
		});

		it("cannot add pages without permission", async () => {
			const wrapper = mount({}, {}, panel(false));
			await flushPromises();
			expect(wrapper.vm.canAdd).toBe(false);
		});

		it("cannot add pages when the field does not allow it", async () => {
			pages([], { add: false });
			const wrapper = mount();
			await flushPromises();
			expect(wrapper.vm.canAdd).toBe(false);
		});
	});

	describe("icon computed", () => {
		it("is page", () => {
			const wrapper = mount();
			expect(wrapper.vm.icon).toBe("page");
		});
	});

	describe("items computed", () => {
		it("adds the status flag and data of each page", async () => {
			pages([page("a")]);
			const wrapper = mount();
			await flushPromises();
			const [item] = items(wrapper);
			expect(item.flag).toMatchObject({
				class: "k-page-status-icon-option",
				dialog: "pages/a/changeStatus",
				disabled: false,
				icon: "status-listed"
			});
			expect(item.buttons).toStrictEqual([item.flag]);
			expect(item.data).toStrictEqual({
				"data-id": "a",
				"data-status": "listed",
				"data-template": "article"
			});
			expect(item.options).toStrictEqual({
				link: "pages/a",
				query: { delete: true, sort: true, view: "list" }
			});
		});

		it("keeps the buttons of a page after the flag", async () => {
			pages([{ ...page("a"), buttons: [{ icon: "heart" }] }]);
			const wrapper = mount();
			await flushPromises();
			expect(items(wrapper)[0].buttons[1]).toStrictEqual({ icon: "heart" });
		});

		it("disables the flag without permission to change the status", async () => {
			pages([page("a", { changeStatus: false })]);
			const wrapper = mount();
			await flushPromises();
			expect(items(wrapper)[0].flag.disabled).toBe(true);
		});

		it("keeps pages from being deleted at the min", async () => {
			pages([page("a"), page("b")]);
			const wrapper = mount({ min: 2 });
			await flushPromises();
			expect(items(wrapper)[0].deletable).toBe(false);
			expect(items(wrapper)[0].options.query.delete).toBe(false);
		});

		it("keeps pages without permission from being sorted", async () => {
			pages([page("a", { sort: false })]);
			const wrapper = mount();
			await flushPromises();
			expect(items(wrapper)[0].sortable).toBe(false);
		});
	});

	// methods
	describe("onAdd()", () => {
		it("opens the create dialog", async () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			await flushPromises();
			wrapper.vm.onAdd();
			expect($panel.dialog.open).toHaveBeenCalledWith(
				"pages/test/fields/children/create"
			);
		});

		it("opens nothing without permission", async () => {
			const $panel = panel(false);
			const wrapper = mount({}, {}, $panel);
			await flushPromises();
			wrapper.vm.onAdd();
			expect($panel.dialog.open).not.toHaveBeenCalled();
		});
	});

	describe("onChange()", () => {
		it.each([["added"], ["moved"]])(
			"lists the %s page at its new position",
			async (type) => {
				pages([page("a"), page("b")], {
					pagination: { ...state.pagination, offset: 20, total: 22 }
				});
				const wrapper = mount();
				await flushPromises();
				await wrapper.vm.onChange({
					[type]: { element: page("b"), newIndex: 0 }
				});
				expect(api.pages.changeStatus).toHaveBeenCalledWith("b", "listed", 21);
			}
		);

		it("ignores removed pages", async () => {
			const wrapper = mount();
			await flushPromises();
			await wrapper.vm.onChange({ removed: { element: page("a") } });
			expect(api.pages.changeStatus).not.toHaveBeenCalled();
		});
	});

	describe("refreshEvents()", () => {
		it("reloads the pages after page changes", async () => {
			const wrapper = mount();
			await flushPromises();
			for (const event of wrapper.vm.refreshEvents()) {
				expect(events.on).toHaveBeenCalledWith(event, wrapper.vm.onRefresh);
			}
			expect(wrapper.vm.refreshEvents()).toContain("page.changeStatus");
		});
	});
});
