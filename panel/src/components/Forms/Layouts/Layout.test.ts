import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Layout from "./Layout.vue";

type Option = {
	click: () => void;
	disabled?: boolean;
	icon: string;
	text: string;
	when?: boolean;
};

const Button = {
	name: "k-button",
	props: ["icon", "title"],
	emits: ["click"],
	template: `<button @click="$emit('click', $event)" />`
};
const Column = {
	name: "k-layout-column",
	props: [
		"blocks",
		"disabled",
		"endpoints",
		"fieldsetGroups",
		"fieldsets",
		"id",
		"width"
	],
	emits: ["input"],
	template: "<div />"
};
const Dropdown = {
	name: "k-dropdown",
	props: ["alignX", "options"],
	methods: { toggle: vi.fn() },
	template: "<div />"
};

const columns = [
	{ blocks: [], id: "a1", width: "1/2" },
	{ blocks: [{ content: {}, id: "x", type: "text" }], id: "a2", width: "1/2" }
];
const endpoints = { field: "pages/a/fields/layout", model: "pages/a" };
const fieldsets = { text: { name: "Text", type: "text" } };

function settings() {
	return {
		tabs: {
			main: { fields: { class: { type: "text" }, id: { type: "text" } } }
		}
	};
}

function panel() {
	return {
		dialog: { close: vi.fn(), open: vi.fn() },
		drawer: { open: vi.fn() }
	};
}

function mount(props = {}, attrs = {}, $panel = panel()) {
	return vueMount(Layout, {
		props: { columns, endpoints, fieldsets, id: "a", ...props },
		attrs,
		global: {
			mocks: { $panel },
			stubs: {
				"k-button": Button,
				"k-dropdown": Dropdown,
				"k-layout-column": Column
			}
		}
	});
}

function buttons(wrapper: ReturnType<typeof mount>) {
	return wrapper.findAllComponents(Button);
}

function options(wrapper: ReturnType<typeof mount>) {
	return wrapper.vm.options as (Option | "-")[];
}

// finds a dropdown option by its icon
function option(wrapper: ReturnType<typeof mount>, icon: string) {
	return options(wrapper).find(
		(option) => option !== "-" && option.icon === icon
	) as Option;
}

describe("k-layout", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "SECTION", "k-layout");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);

		it("is focusable", () => {
			const wrapper = mount();
			expect(wrapper.attributes("tabindex")).toBe("0");
		});

		it("renders the columns in a grid", () => {
			const wrapper = mount();
			expect(wrapper.find("k-grid").classes()).toContain("k-layout-columns");
			expect(
				wrapper.findAllComponents(Column).map((column) => column.props())
			).toStrictEqual([
				{
					...columns[0],
					disabled: false,
					endpoints,
					fieldsetGroups: undefined,
					fieldsets
				},
				{
					...columns[1],
					disabled: false,
					endpoints,
					fieldsetGroups: undefined,
					fieldsets
				}
			]);
		});

		it("renders a toolbar", () => {
			const wrapper = mount();
			const toolbar = wrapper.find("nav.k-layout-toolbar");
			expect(toolbar.find("k-sort-handle").exists()).toBe(true);
			expect(
				buttons(wrapper).map((button) => button.props("icon"))
			).toStrictEqual(["angle-down"]);
			expect(wrapper.findComponent(Dropdown).props()).toStrictEqual({
				alignX: "end",
				options: options(wrapper)
			});
		});
	});

	// props
	describe("disabled prop", () => {
		it("disables the layout", () => {
			const wrapper = mount({ disabled: true });
			expect(wrapper.attributes("data-disabled")).toBe("true");
			expect(wrapper.findComponent(Column).props("disabled")).toBe(true);
		});

		it("hides the toolbar", () => {
			const wrapper = mount({ disabled: true, settings: settings() });
			expect(wrapper.find("nav").exists()).toBe(false);
		});
	});

	describe("fieldsetGroups prop", () => {
		it("passes the groups to each column", () => {
			const fieldsetGroups = { text: { label: "Text", sets: ["text"] } };
			const wrapper = mount({ fieldsetGroups });
			expect(
				wrapper.findComponent(Column).props("fieldsetGroups")
			).toStrictEqual(fieldsetGroups);
		});
	});

	describe("isSelected prop", () => {
		it("marks the layout as selected", () => {
			expect(mount().attributes("data-selected")).toBe("false");
			expect(mount({ isSelected: true }).attributes("data-selected")).toBe(
				"true"
			);
		});
	});

	describe("layouts prop", () => {
		it("disables changing a single layout", () => {
			const wrapper = mount();
			expect(option(wrapper, "dashboard").disabled).toBe(true);
		});

		it("allows changing between several layouts", () => {
			const wrapper = mount({ layouts: [["1/1"], ["1/2", "1/2"]] });
			expect(option(wrapper, "dashboard").disabled).toBe(false);
		});
	});

	describe("settings prop", () => {
		it("adds a settings button", () => {
			const wrapper = mount({ settings: settings() });
			expect(buttons(wrapper).map((button) => button.props())).toStrictEqual([
				{ icon: "settings", title: "settings" },
				{ icon: "angle-down", title: undefined }
			]);
		});

		it("offers the settings in the options", () => {
			expect(option(mount(), "settings").when).toBe(false);
			expect(option(mount({ settings: settings() }), "settings").when).toBe(
				true
			);
		});
	});

	// computed
	describe("options computed", () => {
		it("lists the layout options", () => {
			const wrapper = mount();
			expect(
				options(wrapper).map((option) =>
					option === "-" ? option : [option.icon, option.text]
				)
			).toStrictEqual([
				["angle-up", "insert.before"],
				["angle-down", "insert.after"],
				"-",
				["settings", "settings"],
				["copy", "duplicate"],
				["dashboard", "field.layout.change"],
				"-",
				["template", "copy"],
				["download", "paste.after"],
				"-",
				["trash", "field.layout.delete"]
			]);
		});

		it.each([
			["angle-up", "prepend"],
			["angle-down", "append"],
			["copy", "duplicate"],
			["dashboard", "change"],
			["template", "copy"],
			["download", "paste"]
		])("emits %s on click of the %s option", (icon, event) => {
			const wrapper = mount();
			option(wrapper, icon).click();
			expect(wrapper.emitted(event)).toHaveLength(1);
		});

		it("opens the settings", () => {
			const $panel = panel();
			const wrapper = mount({ settings: settings() }, {}, $panel);
			option(wrapper, "settings").click();
			expect($panel.drawer.open).toHaveBeenCalledOnce();
		});

		it("asks before removing the layout", () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			option(wrapper, "trash").click();
			expect($panel.dialog.open).toHaveBeenCalledOnce();
			expect(wrapper.emitted("remove")).toBeUndefined();
		});
	});

	describe("tabs computed", () => {
		it("adds the endpoints to each settings field", () => {
			const wrapper = mount({ settings: settings() });
			const fields = wrapper.vm.tabs.main.fields;
			expect(fields.class.endpoints).toStrictEqual({
				field: "pages/a/fields/layout/fields/class",
				model: "pages/a"
			});
			expect(fields.id.endpoints).toStrictEqual({
				field: "pages/a/fields/layout/fields/id",
				model: "pages/a"
			});
		});
	});

	// methods
	describe("openSettings()", () => {
		it("opens the settings drawer", () => {
			const $panel = panel();
			const attrs = { class: "a" };
			const wrapper = mount({ attrs, settings: settings() }, {}, $panel);
			wrapper.vm.openSettings();
			expect($panel.drawer.open).toHaveBeenCalledWith({
				component: "k-form-drawer",
				props: {
					icon: "settings",
					tabs: wrapper.vm.tabs,
					title: "settings",
					value: attrs
				},
				on: { input: expect.any(Function) }
			});
		});

		it("emits the changed attributes", () => {
			const $panel = panel();
			const wrapper = mount({ settings: settings() }, {}, $panel);
			wrapper.vm.openSettings();
			$panel.drawer.open.mock.calls[0][0].on.input({ class: "b" });
			expect(wrapper.emitted("updateAttrs")).toStrictEqual([[{ class: "b" }]]);
		});
	});

	describe("remove()", () => {
		it("removes the layout after confirmation", () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			wrapper.vm.remove();

			const dialog = $panel.dialog.open.mock.calls[0][0];
			expect(dialog).toMatchObject({
				component: "k-remove-dialog",
				props: { text: "field.layout.delete.confirm" }
			});

			dialog.on.submit();
			expect(wrapper.emitted("remove")).toHaveLength(1);
			expect($panel.dialog.close).toHaveBeenCalledOnce();
		});
	});

	// events
	describe("events", () => {
		it("emits select on click", async () => {
			const wrapper = mount();
			await wrapper.trigger("click");
			expect(wrapper.emitted("select")).toHaveLength(1);
		});

		it("emits the changed blocks of a column", () => {
			const wrapper = mount();
			const blocks = [{ content: {}, id: "y", type: "text" }];
			wrapper.findAllComponents(Column)[1].vm.$emit("input", blocks);
			expect(wrapper.emitted("updateColumn")).toStrictEqual([
				[{ blocks, column: columns[1], columnIndex: 1 }]
			]);
		});

		it("opens the settings from the settings button", async () => {
			const $panel = panel();
			const wrapper = mount({ settings: settings() }, {}, $panel);
			await buttons(wrapper)[0].trigger("click");
			expect($panel.drawer.open).toHaveBeenCalledOnce();
		});

		it("toggles the options from the options button", async () => {
			const wrapper = mount();
			const toggle = vi.spyOn(wrapper.findComponent(Dropdown).vm, "toggle");
			await buttons(wrapper)[0].trigger("click");
			expect(toggle).toHaveBeenCalledOnce();
		});
	});
});
