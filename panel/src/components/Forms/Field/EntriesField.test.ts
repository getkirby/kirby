import { describe, expect, it, onTestFinished, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import EntriesField from "./EntriesField.vue";

const Button = {
	name: "k-button",
	props: ["disabled", "icon", "text", "title"],
	emits: ["click"],
	methods: { focus: vi.fn() },
	template: `<button @click="$emit('click', $event)" />`
};
const Draggable = {
	name: "k-draggable",
	props: ["disabled", "handle", "list", "sort"],
	emits: ["sort"],
	template: "<div><slot /></div>"
};
const Dropdown = {
	name: "k-dropdown",
	props: ["options"],
	methods: { toggle: vi.fn() },
	template: "<div />"
};
const Field = {
	name: "k-field",
	template: `<div><div class="options"><slot name="options" /></div><slot /></div>`
};
const TextField = {
	name: "k-text-field",
	props: ["counter", "disabled", "label", "placeholder", "value"],
	emits: ["input"],
	methods: { focus: vi.fn() },
	template: "<input />"
};

const field = { placeholder: "Entry …", type: "text" };

function panel() {
	return {
		dialog: { close: vi.fn(), open: vi.fn() },
		notification: { success: vi.fn() }
	};
}

function mount(props = {}, attrs = {}, $panel = panel(), stubs = {}) {
	return vueMount(EntriesField, {
		props: { field, value: ["a", "b"], ...props },
		attrs,
		global: {
			components: { "k-text-field": TextField },
			mocks: { $panel },
			stubs: {
				"k-button": Button,
				"k-draggable": Draggable,
				"k-dropdown": Dropdown,
				...stubs
			}
		}
	});
}

// a literal <k-field> tag only renders the default slot
function field_(props = {}) {
	return mount(props, {}, panel(), { "k-field": Field });
}

function inputs(wrapper: ReturnType<typeof mount>) {
	return wrapper.findAllComponents(TextField);
}

function values(wrapper: ReturnType<typeof mount>) {
	return inputs(wrapper).map((input) => input.props("value"));
}

function lastInput(wrapper: ReturnType<typeof mount>) {
	return wrapper.emitted("input")?.at(-1)?.[0];
}

// the k-button stub takes the icon as prop, not as attribute
function buttons(
	wrapper: ReturnType<typeof mount>,
	icon: string,
	within?: string
) {
	return wrapper
		.findAllComponents(Button)
		.filter(
			(button) =>
				button.props("icon") === icon &&
				(within === undefined || button.element.closest(within) !== null)
		);
}

describe("k-entries-field", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-FIELD", "k-entries-field");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("keeps clicks inside the field", () => {
			const wrapper = mount();
			const parent = document.createElement("div");
			let bubbled = false;
			parent.addEventListener("click", () => (bubbled = true));
			parent.append(wrapper.element);
			wrapper.element.click();
			expect(bubbled).toBe(false);
		});
	});

	// props
	describe("disabled prop", () => {
		it("disables the entries and hides all actions", () => {
			const wrapper = field_({ disabled: true });
			expect(inputs(wrapper).every((input) => input.props("disabled"))).toBe(
				true
			);
			expect(wrapper.findAllComponents(Button)).toHaveLength(0);
			expect(wrapper.find(".k-entries-field-footer").exists()).toBe(false);
		});
	});

	describe("empty prop", () => {
		it("shows the default empty text", () => {
			const wrapper = mount({ value: [] });
			expect(wrapper.find("k-empty").text()).toBe("field.entries.empty");
		});

		it("shows the empty text", () => {
			const wrapper = mount({ empty: "No links yet", value: [] });
			expect(wrapper.find("k-empty").text()).toBe("No links yet");
		});
	});

	describe("field prop", () => {
		it("renders a field of the type for each entry", () => {
			const wrapper = mount();
			expect(values(wrapper)).toStrictEqual(["a", "b"]);
			expect(inputs(wrapper)[0].props()).toMatchObject({
				counter: false,
				label: false,
				placeholder: "Entry …"
			});
		});
	});

	describe("max prop", () => {
		it("offers add buttons below the maximum", () => {
			const wrapper = field_({ max: 3 });
			// one in the options, one per entry and one in the footer
			expect(buttons(wrapper, "add")).toHaveLength(4);
			expect(buttons(wrapper, "copy")).toHaveLength(2);
		});

		it("offers no add buttons at the maximum", () => {
			const wrapper = field_({ max: 2 });
			expect(buttons(wrapper, "add")).toHaveLength(0);
			expect(buttons(wrapper, "copy")).toHaveLength(0);
			expect(wrapper.find(".k-entries-field-footer").exists()).toBe(false);
		});

		it("passes the limits to the k-validator", () => {
			const wrapper = mount({ max: 3, min: 1, required: true });
			const validator = wrapper.find("k-validator");
			expect(validator.attributes()).toMatchObject({
				count: "2",
				max: "3",
				min: "1",
				required: "true"
			});
		});
	});

	describe("sortable prop", () => {
		it("renders a sort handle for each entry", () => {
			const wrapper = mount();
			expect(buttons(wrapper, "sort")).toHaveLength(2);
		});

		it("renders no sort handles when turned off", () => {
			const wrapper = mount({ sortable: false });
			expect(buttons(wrapper, "sort")).toHaveLength(0);
		});
	});

	describe("value prop", () => {
		it("renders the empty state without entries", () => {
			const wrapper = mount({ value: [] });
			expect(wrapper.find("k-empty").exists()).toBe(true);
			expect(wrapper.findComponent(Draggable).exists()).toBe(false);
		});
	});

	// computed
	describe("dragOptions computed", () => {
		it("sorts the entries by their handles", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Draggable).props()).toMatchObject({
				disabled: false,
				handle: true,
				sort: true
			});
		});
	});

	describe("isSortable computed", () => {
		it("is sortable with several entries", () => {
			const wrapper = mount();
			expect(wrapper.vm.isSortable).toBe(true);
		});

		it.each([
			["with a single entry", { value: ["a"] }],
			["when disabled", { disabled: true }],
			["when turned off", { sortable: false }]
		])("is not sortable %s", (_, props) => {
			const wrapper = mount(props);
			expect(wrapper.vm.isSortable).toBe(false);
		});
	});

	describe("more computed", () => {
		it("allows more entries below the maximum", () => {
			const wrapper = mount({ max: 3 });
			expect(wrapper.vm.more).toBe(true);
		});

		it("allows no more entries at the maximum", () => {
			const wrapper = mount({ max: 2 });
			expect(wrapper.vm.more).toBe(false);
		});
	});

	describe("options computed", () => {
		it("offers to copy and delete all entries", () => {
			const wrapper = field_();
			const options = wrapper.findComponent(Dropdown).props("options");
			expect(
				options.map((option: { icon?: string }) => option.icon ?? option)
			).toStrictEqual(["template", "-", "trash"]);
			expect(options[0].disabled).toBe(false);
		});

		it("disables the options without entries", () => {
			const wrapper = mount({ value: [] });
			expect(wrapper.vm.options[0]).toMatchObject({ disabled: true });
			expect(wrapper.vm.options[2]).toMatchObject({ disabled: true });
		});
	});

	// watch
	describe("value watcher", () => {
		it("renders the entries of a new value", async () => {
			const wrapper = mount();
			await wrapper.setProps({ value: ["c"] } as never);
			expect(values(wrapper)).toStrictEqual(["c"]);
		});

		it("keeps the entries for the same values", async () => {
			const wrapper = mount();
			const ids = wrapper.vm.entries.map((entry: { id: string }) => entry.id);
			await wrapper.setProps({ value: ["a", "b"] } as never);
			expect(
				wrapper.vm.entries.map((entry: { id: string }) => entry.id)
			).toStrictEqual(ids);
		});
	});

	// methods
	describe("add()", () => {
		it("appends an entry with the default of the field", async () => {
			const wrapper = mount({ field: { ...field, default: "new" } });
			await wrapper.vm.add();
			expect(lastInput(wrapper)).toStrictEqual(["a", "b", "new"]);
		});

		it("inserts an entry at the index", async () => {
			const wrapper = mount({ field: { ...field, default: "" } });
			// the null defaults make TypeScript expect null
			await wrapper.vm.add(1 as never, "c" as never);
			expect(lastInput(wrapper)).toStrictEqual(["a", "c", "b"]);
		});

		it("focuses the new entry", async () => {
			const wrapper = mount({ field: { ...field, default: "" } });
			await wrapper.vm.add();
			expect(TextField.methods.focus).toHaveBeenCalledOnce();
		});

		it("adds nothing at the maximum", async () => {
			const wrapper = mount({ max: 2 });
			await wrapper.vm.add(null, "c" as never);
			expect(wrapper.emitted("input")).toBeUndefined();
		});
	});

	describe("copyAll()", () => {
		it("copies the entries as list", () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			const write = vi.spyOn(wrapper.vm.$helper.clipboard, "write");
			write.mockImplementation(() => true);
			onTestFinished(() => write.mockRestore());
			wrapper.vm.copyAll();
			expect(write).toHaveBeenCalledWith("- a\n- b");
			expect($panel.notification.success).toHaveBeenCalledWith("copy.success");
		});
	});

	describe("duplicate()", () => {
		it("inserts a copy after the entry", async () => {
			const wrapper = mount();
			await wrapper.vm.duplicate(0);
			expect(lastInput(wrapper)).toStrictEqual(["a", "a", "b"]);
			expect(TextField.methods.focus).toHaveBeenCalledOnce();
		});

		it("duplicates nothing at the maximum", async () => {
			const wrapper = mount({ max: 2 });
			await wrapper.vm.duplicate(0);
			expect(wrapper.emitted("input")).toBeUndefined();
		});

		it("duplicates nothing for an unknown entry", async () => {
			const wrapper = mount();
			await wrapper.vm.duplicate(5);
			expect(wrapper.emitted("input")).toBeUndefined();
		});
	});

	describe("focus()", () => {
		it("focuses the input of the entry", () => {
			const wrapper = mount();
			wrapper.vm.focus(1);
			expect(TextField.methods.focus).toHaveBeenCalledOnce();
		});

		it("focuses the sort handle of the entry", () => {
			const wrapper = mount();
			wrapper.vm.focus(1, "sort-handle");
			expect(Button.methods.focus).toHaveBeenCalledOnce();
		});

		it("ignores an unknown entry", () => {
			const wrapper = mount();
			expect(() => wrapper.vm.focus(5)).not.toThrow();
		});
	});

	describe("onInput()", () => {
		it("emits the values with the changed entry", () => {
			const wrapper = mount();
			wrapper.vm.onInput(1, "c");
			expect(lastInput(wrapper)).toStrictEqual(["a", "c"]);
		});
	});

	describe("remove()", () => {
		it("removes the entry and focuses the previous one", () => {
			const wrapper = mount();
			wrapper.vm.remove(1);
			expect(lastInput(wrapper)).toStrictEqual(["a"]);
			expect(TextField.methods.focus).toHaveBeenCalledOnce();
		});

		it("removes nothing when disabled", () => {
			const wrapper = mount({ disabled: true });
			wrapper.vm.remove(1);
			expect(wrapper.emitted("input")).toBeUndefined();
		});
	});

	describe("removeAll()", () => {
		it("removes all entries once confirmed", () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			wrapper.vm.removeAll();
			const [dialog] = $panel.dialog.open.mock.calls[0];
			expect(dialog.component).toBe("k-remove-dialog");
			expect(dialog.props.text).toBe("field.entries.delete.confirm.all");
			dialog.on.submit();
			expect(lastInput(wrapper)).toStrictEqual([]);
			expect($panel.dialog.close).toHaveBeenCalledOnce();
		});
	});

	describe("sortDown()", () => {
		it("moves the entry down and focuses its handle", async () => {
			const wrapper = mount();
			await wrapper.vm.sortDown(0);
			await wrapper.vm.$nextTick();
			expect(lastInput(wrapper)).toStrictEqual(["b", "a"]);
			expect(Button.methods.focus).toHaveBeenCalledOnce();
		});

		it("keeps the last entry in place", () => {
			const wrapper = mount();
			wrapper.vm.sortDown(1);
			expect(wrapper.emitted("input")).toBeUndefined();
		});
	});

	describe("sortUp()", () => {
		it("moves the entry up", () => {
			const wrapper = mount();
			wrapper.vm.sortUp(1);
			expect(lastInput(wrapper)).toStrictEqual(["b", "a"]);
		});

		it("keeps the first entry in place", () => {
			const wrapper = mount();
			wrapper.vm.sortUp(0);
			expect(wrapper.emitted("input")).toBeUndefined();
		});

		it("keeps the entries when not sortable", () => {
			const wrapper = mount({ sortable: false });
			wrapper.vm.sortUp(1);
			expect(wrapper.emitted("input")).toBeUndefined();
		});
	});

	// events
	describe("click event", () => {
		it("adds an entry with the add button of the footer", async () => {
			const wrapper = mount({ field: { ...field, default: "" } });
			await wrapper.find(".k-entries-field-footer button").trigger("click");
			expect(lastInput(wrapper)).toStrictEqual(["a", "b", ""]);
		});

		it("adds an entry after an entry", async () => {
			const wrapper = mount({ field: { ...field, default: "" } });
			const [first] = buttons(wrapper, "add", ".k-entries-field-item-options");
			await first.trigger("click");
			expect(lastInput(wrapper)).toStrictEqual(["a", "", "b"]);
		});

		it("duplicates and removes an entry", async () => {
			const wrapper = mount();
			await buttons(wrapper, "copy")[1].trigger("click");
			expect(lastInput(wrapper)).toStrictEqual(["a", "b", "b"]);
			await buttons(wrapper, "trash")[0].trigger("click");
			expect(lastInput(wrapper)).toStrictEqual(["b", "b"]);
		});

		it("adds an entry with a click on the empty state", async () => {
			const wrapper = mount({ field: { ...field, default: "" }, value: [] });
			await wrapper.find("k-empty").trigger("click");
			expect(lastInput(wrapper)).toStrictEqual([""]);
		});

		it("toggles the options dropdown", async () => {
			const wrapper = field_();
			await buttons(wrapper, "dots", ".options")[0].trigger("click");
			expect(Dropdown.methods.toggle).toHaveBeenCalledOnce();
		});
	});

	describe("input event", () => {
		it("emits the values when an entry changes", () => {
			const wrapper = mount();
			inputs(wrapper)[0].vm.$emit("input", "c");
			expect(lastInput(wrapper)).toStrictEqual(["c", "b"]);
		});
	});

	describe("keydown event", () => {
		it("sorts with the arrow keys on a sort handle", async () => {
			const wrapper = mount();
			const [first] = buttons(wrapper, "sort");
			await first.trigger("keydown.down");
			expect(lastInput(wrapper)).toStrictEqual(["b", "a"]);
		});
	});

	describe("sort event", () => {
		it("emits the values after dragging", () => {
			const wrapper = mount();
			const list = wrapper.findComponent(Draggable).props("list");
			list.reverse();
			wrapper.findComponent(Draggable).vm.$emit("sort");
			expect(lastInput(wrapper)).toStrictEqual(["b", "a"]);
		});
	});
});
