import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import { nextTick } from "vue";
import { helper } from "@/helpers";
import StructureField from "./StructureField.vue";

const Table = {
	name: "k-table",
	props: ["fields", "pagination", "rows"],
	emits: ["input", "option", "paginate"],
	template: "<div />"
};

function panel() {
	return {
		dialog: { close: vi.fn(), open: vi.fn() },
		drawer: { close: vi.fn(), open: vi.fn(), props: {} }
	};
}

function mount(props = {}, attrs = {}, $panel = panel()) {
	return vueMount(StructureField, {
		props: {
			fields: {
				title: { type: "text" },
				label: { type: "text" }
			},
			...props
		},
		attrs,
		global: {
			mocks: {
				$events: { emit: vi.fn(), on: vi.fn(), off: vi.fn() },
				$helper: { ...helper, uuid: () => "uuid" },
				$panel
			},
			stubs: { "k-table": Table }
		}
	});
}

/**
 * Submits the dialog the field opened last
 */
function confirm($panel: ReturnType<typeof panel>) {
	$panel.dialog.open.mock.calls.at(-1)?.[0].on.submit();
}

function rows(wrapper: ReturnType<typeof mount>) {
	return wrapper.vm.items as Record<string, unknown>[];
}

describe("k-structure-field", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-FIELD", "k-structure-field");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	// computed
	describe("subfields computed", () => {
		it("passes the fields with their own endpoints to the table", () => {
			const wrapper = mount({
				endpoints: { field: "pages/x/fields/items", model: "pages/x" },
				value: [{ title: "A" }]
			});

			expect(wrapper.findComponent(Table).props("fields")).toStrictEqual({
				title: {
					type: "text",
					endpoints: { field: "pages/x/fields/items+title", model: "pages/x" }
				},
				label: {
					type: "text",
					endpoints: { field: "pages/x/fields/items+label", model: "pages/x" }
				}
			});
		});
	});

	// watch
	describe("value watcher", () => {
		it("converts the value to items", () => {
			const wrapper = mount({ value: [{ _id: "a", title: "A" }] });

			expect(wrapper.vm.items).toStrictEqual([{ _id: "a", title: "A" }]);
		});

		it("adds missing ids", () => {
			const wrapper = mount({ value: [{ title: "A" }] });

			expect(wrapper.vm.items).toStrictEqual([{ _id: "uuid", title: "A" }]);
		});

		it("syncs rows that are updated in place", async () => {
			// a new row gets added in the block drawer and its items
			// are passed on as value to the field in the block preview
			const drawer = mount();
			rows(drawer).push({ _id: "a", title: "", label: "" });

			const preview = mount({ value: drawer.vm.items });

			expect(rows(preview)[0].title).toBe("");

			// the row gets updated in place via the structure drawer
			rows(drawer)[0].title = "Title";
			await nextTick();

			expect(rows(preview)[0].title).toBe("Title");
		});

		it("keeps the selection when its own rows are updated in place", async () => {
			const wrapper = mount();
			const items = rows(wrapper);
			items.push({ _id: "a", title: "A" });

			await wrapper.setProps({ value: items });
			wrapper.vm.startSelecting();
			wrapper.vm.onSelect(["a"]);

			items[0].title = "B";
			await nextTick();

			expect(wrapper.vm.isSelecting).toBe(true);
			expect(wrapper.vm.selected).toStrictEqual(["a"]);
		});

		it("stops selecting when the rows get replaced", async () => {
			const wrapper = mount({ value: [{ _id: "a", title: "A" }] });
			wrapper.vm.startSelecting();

			await wrapper.setProps({ value: [{ _id: "b", title: "B" }] });

			expect(wrapper.vm.isSelecting).toBe(false);
		});

		it("keeps its own items when its own value is passed back", async () => {
			const wrapper = mount();
			const items = rows(wrapper);

			await wrapper.setProps({ value: items });
			items.push({ _id: "a", title: "A" });
			await nextTick();

			expect(wrapper.vm.items).toBe(items);
		});
	});

	// methods
	describe("add()", () => {
		const fields = { title: { type: "text", default: "New" } };
		const value = [{ _id: "a", title: "A" }];

		it("appends a new row and opens it", () => {
			const $panel = panel();
			const wrapper = mount({ fields, value }, {}, $panel);
			wrapper.vm.add();

			const row = { _id: "uuid", title: "New" };
			expect(wrapper.emitted("input")).toStrictEqual([[[...value, row]]]);
			expect($panel.drawer.open).toHaveBeenCalledWith(
				expect.objectContaining({
					component: "k-structure-drawer",
					props: expect.objectContaining({ value: row })
				})
			);
		});

		it("prepends the new row with the prepend prop", () => {
			const wrapper = mount({ fields, prepend: true, value });
			wrapper.vm.add();

			expect(wrapper.emitted("input")).toStrictEqual([
				[[{ _id: "uuid", title: "New" }, ...value]]
			]);
		});

		it("adds no row when the max is reached", () => {
			const $panel = panel();
			const wrapper = mount({ fields, max: 1, value }, {}, $panel);

			expect(wrapper.vm.add()).toBe(false);
			expect(wrapper.emitted("input")).toBeUndefined();
			expect($panel.drawer.open).not.toHaveBeenCalled();
		});
	});

	describe("onTableInput()", () => {
		it("emits the rows of the table", () => {
			const wrapper = mount({ value: [{ _id: "a" }, { _id: "b" }] });
			wrapper
				.findComponent(Table)
				.vm.$emit("input", [{ _id: "b" }, { _id: "a" }]);

			expect(wrapper.emitted("input")).toStrictEqual([
				[[{ _id: "b" }, { _id: "a" }]]
			]);
		});

		it("writes the rows of the current page back into all rows", async () => {
			const wrapper = mount({
				limit: 2,
				value: [
					{ _id: "a" },
					{ _id: "b" },
					{ _id: "c" },
					{ _id: "d" },
					{ _id: "e" }
				]
			});
			const table = wrapper.findComponent(Table);
			table.vm.$emit("paginate", { page: 2 });
			await nextTick();

			expect(table.props("rows")).toStrictEqual([{ _id: "c" }, { _id: "d" }]);

			table.vm.$emit("input", [{ _id: "c" }, { _id: "d", title: "D" }]);

			expect(wrapper.emitted("input")).toStrictEqual([
				[
					[
						{ _id: "a" },
						{ _id: "b" },
						{ _id: "c" },
						{ _id: "d", title: "D" },
						{ _id: "e" }
					]
				]
			]);
		});
	});

	describe("option()", () => {
		it("duplicates a row with a new id", () => {
			const $panel = panel();
			const wrapper = mount({ value: [{ _id: "a", title: "A" }] }, {}, $panel);
			const table = wrapper.findComponent(Table);
			table.vm.$emit("option", "duplicate", table.props("rows")[0]);

			expect(wrapper.emitted("input")).toStrictEqual([
				[
					[
						{ _id: "a", title: "A" },
						{ _id: "uuid", title: "A" }
					]
				]
			]);
			expect($panel.drawer.open).toHaveBeenCalledOnce();
		});
	});

	describe("remove()", () => {
		it("removes the row once the dialog is submitted", () => {
			const $panel = panel();
			const wrapper = mount(
				{ value: [{ _id: "a" }, { _id: "b" }] },
				{},
				$panel
			);
			const table = wrapper.findComponent(Table);
			table.vm.$emit("option", "remove", table.props("rows")[0]);

			expect(wrapper.emitted("input")).toBeUndefined();

			confirm($panel);

			expect(wrapper.emitted("input")).toStrictEqual([[[{ _id: "b" }]]]);
			expect($panel.dialog.close).toHaveBeenCalledOnce();
			expect($panel.drawer.close).toHaveBeenCalledOnce();
		});

		it("goes back a page when the last row of a page is removed", async () => {
			const $panel = panel();
			const wrapper = mount(
				{ limit: 1, value: [{ _id: "a" }, { _id: "b" }] },
				{},
				$panel
			);
			const table = wrapper.findComponent(Table);
			table.vm.$emit("paginate", { page: 2 });
			await nextTick();

			table.vm.$emit("option", "remove", table.props("rows")[0]);
			confirm($panel);
			await nextTick();

			expect(table.props("pagination").page).toBe(1);
			expect(table.props("rows")).toStrictEqual([{ _id: "a" }]);
		});
	});
});
