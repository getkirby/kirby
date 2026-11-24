import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import BlocksField from "./BlocksField.vue";

const Blocks = {
	name: "k-blocks",
	props: ["fieldsets", "value"],
	emits: ["input"],
	methods: {
		choose: vi.fn(),
		collapseAll: vi.fn(),
		copyAll: vi.fn(),
		expandAll: vi.fn(),
		focus: vi.fn(),
		isCollapsible: vi.fn(() => true),
		isExpandable: vi.fn(() => true),
		isFullyCollapsed: vi.fn(() => false),
		isFullyExpanded: vi.fn(() => false),
		pasteboard: vi.fn(),
		removeAll: vi.fn()
	},
	template: "<div />"
};
const Button = {
	name: "k-button",
	props: ["disabled", "icon", "text", "title"],
	emits: ["click"],
	template: `<button @click="$emit('click', $event)" />`
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

const fieldsets = { text: { name: "Text", type: "text" } };
const blocks = [{ content: {}, id: "a", type: "text" }];

function mount(props = {}, attrs = {}, stubs = {}) {
	return vueMount(BlocksField, {
		props: { fieldsets, value: blocks, ...props },
		attrs,
		global: {
			stubs: {
				"k-blocks": Blocks,
				"k-button": Button,
				"k-dropdown": Dropdown,
				...stubs
			}
		}
	});
}

// a literal <k-field> tag only renders the default slot
function field(props = {}) {
	return mount(props, {}, { "k-field": Field });
}

function options(wrapper: ReturnType<typeof mount>) {
	const ready = vi.fn((options) => options);
	return wrapper.vm.options(ready);
}

function icons(list: (string | { icon: string })[]) {
	return list.map((option) =>
		option === "-" ? "-" : (option as { icon: string }).icon
	);
}

describe("k-blocks-field", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-FIELD", "k-blocks-field");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	// props
	describe("disabled prop", () => {
		it("hides the add buttons and options", () => {
			const wrapper = field({ disabled: true });
			expect(wrapper.findAllComponents(Button)).toHaveLength(0);
		});
	});

	describe("fieldsets prop", () => {
		it("offers to add blocks with fieldsets", () => {
			const wrapper = field();
			expect(wrapper.find(".options").findAllComponents(Button)).toHaveLength(
				2
			);
			expect(wrapper.find("footer").exists()).toBe(true);
		});

		it("offers nothing without fieldsets", () => {
			const wrapper = field({ fieldsets: {} });
			expect(wrapper.findAllComponents(Button)).toHaveLength(0);
		});
	});

	describe("max prop", () => {
		it("disables adding at the maximum", () => {
			const wrapper = field({ max: 1 });
			const [add] = wrapper.find(".options").findAllComponents(Button);
			expect(add.props("disabled")).toBe(true);
			expect(wrapper.find("footer").exists()).toBe(false);
		});

		it("passes the limits to the k-validator", () => {
			const wrapper = mount({ max: 3, min: 1, required: true });
			expect(wrapper.find("k-validator").attributes()).toMatchObject({
				count: "1",
				max: "3",
				min: "1",
				required: "true"
			});
		});
	});

	describe("value prop", () => {
		it("passes the blocks to the k-blocks", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Blocks).props("value")).toStrictEqual(
				blocks
			);
		});

		it("shows no footer without blocks", () => {
			const wrapper = mount({ value: [] });
			expect(wrapper.find("footer").exists()).toBe(false);
		});
	});

	// computed
	describe("isFull computed", () => {
		it("is full at the maximum", () => {
			const wrapper = mount({ max: 1 });
			expect(wrapper.vm.isFull).toBeTruthy();
		});

		it("is never full without a maximum", () => {
			const wrapper = mount();
			expect(wrapper.vm.isFull).toBeFalsy();
		});
	});

	// methods
	describe("focus()", () => {
		it("focuses the k-blocks", () => {
			const wrapper = mount();
			wrapper.vm.focus();
			expect(Blocks.methods.focus).toHaveBeenCalledOnce();
		});
	});

	describe("options()", () => {
		it("offers to copy, paste, collapse, expand and delete all", () => {
			const wrapper = mount();
			expect(icons(options(wrapper))).toStrictEqual([
				"template",
				"download",
				"-",
				"collapse",
				"expand",
				"-",
				"trash"
			]);
		});

		it("leaves out collapsing and expanding when not possible", () => {
			Blocks.methods.isCollapsible.mockReturnValueOnce(false);
			Blocks.methods.isCollapsible.mockReturnValueOnce(false);
			Blocks.methods.isExpandable.mockReturnValueOnce(false);
			Blocks.methods.isExpandable.mockReturnValueOnce(false);
			const wrapper = mount();
			expect(icons(options(wrapper))).toStrictEqual([
				"template",
				"download",
				"-",
				"trash"
			]);
		});

		it("disables the options without blocks", () => {
			const wrapper = mount({ value: [] });
			const list = options(wrapper);
			expect(list[0]).toMatchObject({ disabled: true, icon: "template" });
			expect(list.at(-1)).toMatchObject({ disabled: true, icon: "trash" });
		});

		it("disables pasting at the maximum", () => {
			const wrapper = mount({ max: 1 });
			expect(options(wrapper)[1]).toMatchObject({
				disabled: true,
				icon: "download"
			});
		});

		it("runs the option on the k-blocks", () => {
			const wrapper = mount();
			for (const option of options(wrapper)) {
				option.click?.();
			}
			for (const method of [
				"copyAll",
				"pasteboard",
				"collapseAll",
				"expandAll",
				"removeAll"
			] as const) {
				expect(Blocks.methods[method]).toHaveBeenCalledOnce();
			}
		});
	});

	// events
	describe("click event", () => {
		it("adds a block at the end with the add buttons", async () => {
			const wrapper = field();
			const [add] = wrapper.find(".options").findAllComponents(Button);
			await add.trigger("click");
			await wrapper.find("footer").findComponent(Button).trigger("click");
			expect(Blocks.methods.choose).toHaveBeenNthCalledWith(1, 1);
			expect(Blocks.methods.choose).toHaveBeenNthCalledWith(2, 1);
		});

		it("toggles the options", async () => {
			const wrapper = field();
			const [, dots] = wrapper.find(".options").findAllComponents(Button);
			await dots.trigger("click");
			expect(Dropdown.methods.toggle).toHaveBeenCalledOnce();
		});
	});

	describe("input event", () => {
		it("emits the blocks of the k-blocks", () => {
			const wrapper = mount();
			wrapper.findComponent(Blocks).vm.$emit("input", []);
			expect(wrapper.emitted("input")).toStrictEqual([[[]]]);
		});
	});
});
