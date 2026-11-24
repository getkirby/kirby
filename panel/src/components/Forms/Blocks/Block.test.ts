import { beforeEach, describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import type { App } from "vue";
import Block from "./Block.vue";

const events = [
	"append",
	"chooseToAppend",
	"chooseToConvert",
	"chooseToPrepend",
	"close",
	"copy",
	"duplicate",
	"focus",
	"hide",
	"merge",
	"open",
	"paste",
	"prepend",
	"remove",
	"removeSelected",
	"show",
	"sortDown",
	"sortUp",
	"split",
	"update"
];

const TextType = {
	name: "k-block-type-text",
	props: ["content", "fieldset", "id", "tabs"],
	emits: events,
	methods: { focus: vi.fn(), split: vi.fn() },
	template: `<div class="text-type"><input /></div>`
};
const DefaultType = {
	name: "k-block-type-default",
	props: ["content", "fieldset", "id", "tabs"],
	emits: events,
	data: () => ({ collapsed: true }),
	methods: { collapse: vi.fn(), expand: vi.fn() },
	template: `<div class="default-type" />`
};
const Options = {
	name: "k-block-options",
	props: [
		"isBatched",
		"isEditable",
		"isFull",
		"isHidden",
		"isMergable",
		"isSplitable"
	],
	emits: events,
	template: `<nav><button /></nav>`
};

const fieldset = {
	icon: "text",
	name: "Text",
	tabs: { content: { fields: { text: { type: "writer" } } } },
	type: "text"
};

function panel() {
	return {
		dialog: { close: vi.fn(), open: vi.fn() },
		drawer: { close: vi.fn(), open: vi.fn() }
	};
}

function mount(props = {}, attrs = {}, $panel = panel()) {
	return vueMount(Block, {
		props: {
			content: { text: "Hello" },
			endpoints: { field: "pages/a/fields/blocks", model: "pages/a" },
			fieldset,
			id: "a",
			type: "text",
			...props
		},
		attrs,
		attachTo: document.body,
		global: {
			components: {
				"k-block-type-default": DefaultType,
				"k-block-type-text": TextType
			},
			mocks: { $panel },
			stubs: { "k-block-options": Options }
		}
	});
}

function drawer($panel: ReturnType<typeof panel>) {
	return $panel.drawer.open.mock.calls.at(-1)?.[0];
}

describe("k-block", () => {
	// lets $helper.isComponent() know about the block types
	beforeEach(() => {
		const components = {
			"k-block-type-default": DefaultType,
			"k-block-type-gallery": {},
			"k-block-type-text": TextType
		};
		const app = { _context: { components } } as unknown as App;
		vi.stubGlobal("panel", { ...window.panel, app });
	});

	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-block-container");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("marks the fieldset and preview type", () => {
			const wrapper = mount();
			expect(wrapper.classes()).toContain("k-block-container-fieldset-text");
			expect(wrapper.classes()).toContain("k-block-container-type-text");
			expect(wrapper.attributes("data-id")).toBe("a");
			expect(wrapper.attributes("tabindex")).toBe("0");
		});
	});

	// props
	describe("disabled prop", () => {
		it("makes the block unfocusable and hides the options", () => {
			const wrapper = mount({ disabled: true, isLastSelected: true });
			expect(wrapper.attributes("tabindex")).toBeUndefined();
			expect(wrapper.attributes("data-disabled")).toBe("true");
			expect(wrapper.findComponent(Options).exists()).toBe(false);
		});

		it("disables the block for a disabled fieldset", () => {
			const wrapper = mount({ fieldset: { ...fieldset, disabled: true } });
			expect(wrapper.attributes("data-disabled")).toBe("true");
		});
	});

	describe("fieldset prop", () => {
		it("previews the block with its type", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(TextType).props("content")).toStrictEqual({
				text: "Hello"
			});
			expect(wrapper.find(".k-block").classes()).toStrictEqual([
				"k-block-type-text",
				"k-block"
			]);
		});

		it("previews the block with a custom preview", () => {
			const wrapper = mount({ fieldset: { ...fieldset, preview: "gallery" } });
			expect(wrapper.classes()).toContain("k-block-container-type-gallery");
			expect(wrapper.find(".k-block").classes()).toContain(
				"k-block-type-gallery"
			);
		});

		it("previews the block with the default without preview", () => {
			const wrapper = mount({ fieldset: { ...fieldset, preview: false } });
			expect(wrapper.findComponent(DefaultType).exists()).toBe(true);
			expect(wrapper.find(".k-block").classes()).toContain(
				"k-block-type-default"
			);
		});

		it("previews an unknown type with the default", () => {
			const wrapper = mount({
				fieldset: { ...fieldset, type: "video" },
				type: "video"
			});
			expect(wrapper.findComponent(DefaultType).exists()).toBe(true);
			expect(wrapper.classes()).not.toContain("k-block-container-type-video");
		});

		it("marks a block for translation", () => {
			const wrapper = mount({ fieldset: { ...fieldset, translate: false } });
			expect(wrapper.attributes("data-translate")).toBe("false");
		});
	});

	describe("isLastSelected prop", () => {
		// the options ask the preview, which only exists after the first render
		it("shows the options of the block once selected", async () => {
			const wrapper = mount({ isMergable: true });
			await wrapper.setProps({ isLastSelected: true } as never);
			expect(wrapper.findComponent(Options).props()).toMatchObject({
				isEditable: true,
				isMergable: true,
				isSplitable: true
			});
		});

		it("shows no options by default", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Options).exists()).toBe(false);
		});
	});

	describe("isSelected prop", () => {
		it("marks the block", () => {
			const wrapper = mount({
				isBatched: true,
				isHidden: true,
				isSelected: true
			});
			expect(wrapper.attributes()).toMatchObject({
				"data-batched": "true",
				"data-hidden": "true",
				"data-selected": "true"
			});
		});
	});

	// computed
	describe("tabs computed", () => {
		it("adds the endpoints to each field", () => {
			const wrapper = mount();
			expect(wrapper.vm.tabs.content.fields.text.endpoints).toStrictEqual({
				field: "pages/a/fields/blocks/fieldsets/text/fields/text",
				model: "pages/a"
			});
		});
	});

	// methods
	describe("backspace()", () => {
		it("removes the block", () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			const event = new KeyboardEvent("keydown", { cancelable: true });
			Object.defineProperty(event, "target", { value: wrapper.element });
			wrapper.vm.backspace(event);
			expect(event.defaultPrevented).toBe(true);
			expect($panel.dialog.open).toHaveBeenCalledOnce();
		});

		it("keeps the block while typing", () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			const event = new KeyboardEvent("keydown");
			Object.defineProperty(event, "target", {
				value: wrapper.find("input").element
			});
			expect(wrapper.vm.backspace(event)).toBe(false);
			expect($panel.dialog.open).not.toHaveBeenCalled();
		});
	});

	describe("close()", () => {
		it("closes the drawer of the block", () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			wrapper.vm.close();
			expect($panel.drawer.close).toHaveBeenCalledWith("a");
		});
	});

	describe("collapse()", () => {
		it("collapses and expands the preview", () => {
			const wrapper = mount({ fieldset: { ...fieldset, preview: false } });
			wrapper.vm.collapse();
			wrapper.vm.expand();
			expect(DefaultType.methods.collapse).toHaveBeenCalledOnce();
			expect(DefaultType.methods.expand).toHaveBeenCalledOnce();
		});
	});

	describe("focus()", () => {
		it("focuses the preview", () => {
			const wrapper = mount();
			wrapper.vm.focus();
			expect(TextType.methods.focus).toHaveBeenCalledOnce();
		});

		it("focuses the block without focusable preview", () => {
			const wrapper = mount({ fieldset: { ...fieldset, preview: false } });
			wrapper.vm.focus();
			expect(document.activeElement).toBe(wrapper.element);
		});
	});

	describe("goTo()", () => {
		it("focuses and opens the other block", () => {
			const wrapper = mount();
			const other = mount({ id: "b" });
			const open = vi.spyOn(other.vm, "open");
			wrapper.vm.goTo(other.vm);
			expect(document.activeElement).toBe(other.element);
			expect(open).toHaveBeenCalledWith(null, true);
		});
	});

	describe("isCollapsible()", () => {
		it("asks the preview", () => {
			const text = mount();
			expect(text.vm.isCollapsible()).toBe(false);
			expect(text.vm.isExpandable()).toBe(false);
			expect(text.vm.isCollapsed()).toBe(false);

			const fallback = mount({ fieldset: { ...fieldset, preview: false } });
			expect(fallback.vm.isCollapsible()).toBe(true);
			expect(fallback.vm.isExpandable()).toBe(true);
			expect(fallback.vm.isCollapsed()).toBe(true);
		});
	});

	describe("isSplitable()", () => {
		it("splits a preview that can split", () => {
			const wrapper = mount();
			expect(wrapper.vm.isSplitable()).toBe(true);
		});

		it("splits no block in a full list", () => {
			const wrapper = mount({ isFull: true });
			expect(wrapper.vm.isSplitable()).toBe(false);
		});

		it("splits no preview without split()", () => {
			const wrapper = mount({ fieldset: { ...fieldset, preview: false } });
			expect(wrapper.vm.isSplitable()).toBe(false);
		});
	});

	describe("onClose()", () => {
		it("emits close and focuses the block", () => {
			const wrapper = mount();
			wrapper.vm.onClose();
			expect(wrapper.emitted("close")).toHaveLength(1);
			expect(TextType.methods.focus).toHaveBeenCalledOnce();
		});
	});

	describe("onFocusIn()", () => {
		it("emits focus for the block", async () => {
			const wrapper = mount();
			await wrapper.find("input").trigger("focusin");
			expect(wrapper.emitted("focus")).toHaveLength(1);
		});

		it("keeps the focus of the options", async () => {
			const wrapper = mount({ isLastSelected: true });
			await wrapper.find("nav button").trigger("focusin");
			expect(wrapper.emitted("focus")).toBeUndefined();
		});

		it("emits no focus when disabled", async () => {
			const wrapper = mount({ disabled: true });
			await wrapper.find("input").trigger("focusin");
			expect(wrapper.emitted("focus")).toBeUndefined();
		});
	});

	describe("onInput()", () => {
		it("emits the new content", () => {
			const wrapper = mount();
			wrapper.vm.onInput({ text: "Hi" });
			expect(wrapper.emitted("update")).toStrictEqual([[{ text: "Hi" }]]);
		});
	});

	describe("open()", () => {
		it("opens the drawer of the block", () => {
			const $panel = panel();
			const wrapper = mount(
				{ isHidden: true, next: {}, prev: undefined },
				{},
				$panel
			);
			wrapper.vm.open("content");
			expect(drawer($panel)).toMatchObject({
				component: "k-block-drawer",
				id: "a",
				props: {
					hidden: true,
					icon: "text",
					next: true,
					prev: false,
					title: "Text",
					value: { text: "Hello" }
				},
				replace: false,
				tab: "content"
			});
			expect(wrapper.emitted("open")).toHaveLength(1);
		});

		it("passes the drawer input on as update", () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			wrapper.vm.open();
			drawer($panel).on.input({ text: "Hi" });
			expect(wrapper.emitted("update")).toStrictEqual([[{ text: "Hi" }]]);
		});

		it.each([
			["not editable", { fieldset: { ...fieldset, editable: false } }],
			["batched", { isBatched: true }],
			["disabled", { disabled: true }]
		])("opens nothing for a block that is %s", (_, props) => {
			const $panel = panel();
			const wrapper = mount(props, {}, $panel);
			wrapper.vm.open();
			expect($panel.drawer.open).not.toHaveBeenCalled();
		});
	});

	describe("remove()", () => {
		it("removes the block once confirmed", () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			wrapper.vm.remove();
			const [dialog] = $panel.dialog.open.mock.calls[0];
			expect(dialog.props.text).toBe("field.blocks.delete.confirm");
			dialog.on.submit();
			expect($panel.drawer.close).toHaveBeenCalledWith("a");
			expect(wrapper.emitted("remove")).toStrictEqual([["a"]]);
		});

		it("removes all selected blocks when batched", () => {
			const $panel = panel();
			const wrapper = mount({ isBatched: true }, {}, $panel);
			wrapper.vm.remove();
			expect($panel.dialog.open).not.toHaveBeenCalled();
			expect(wrapper.emitted("removeSelected")).toHaveLength(1);
		});
	});

	describe("submit()", () => {
		it("closes the drawer and emits submit", () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			wrapper.vm.submit();
			expect($panel.drawer.close).toHaveBeenCalledWith("a");
			expect(wrapper.emitted("submit")).toHaveLength(1);
		});
	});

	// events
	describe("keydown event", () => {
		it.each([
			["j", { ctrlKey: true }, "merge"],
			["down", { altKey: true, ctrlKey: true }, "selectDown"],
			["up", { altKey: true, ctrlKey: true }, "selectUp"],
			["down", { ctrlKey: true, shiftKey: true }, "sortDown"],
			["up", { ctrlKey: true, shiftKey: true }, "sortUp"]
		])("emits on ctrl+%s with modifiers", async (key, modifiers, event) => {
			const wrapper = mount();
			await wrapper.trigger("keydown." + key, modifiers);
			expect(wrapper.emitted(event)).toHaveLength(1);
		});
	});

	describe("preview events", () => {
		it("passes the events of the preview on", () => {
			const wrapper = mount();
			const preview = wrapper.findComponent(TextType);
			preview.vm.$emit("append", "text");
			preview.vm.$emit("update", { text: "Hi" });
			preview.vm.$emit("split", [{}, {}]);
			expect(wrapper.emitted("append")).toStrictEqual([["text"]]);
			expect(wrapper.emitted("update")).toStrictEqual([[{ text: "Hi" }]]);
			expect(wrapper.emitted("split")).toStrictEqual([[[{}, {}]]]);
		});

		it("opens the drawer for the open event of the preview", () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			wrapper.findComponent(TextType).vm.$emit("open", "settings");
			expect(drawer($panel).tab).toBe("settings");
		});
	});

	describe("options events", () => {
		it("splits the preview", () => {
			const wrapper = mount({ isLastSelected: true });
			wrapper.findComponent(Options).vm.$emit("split");
			expect(TextType.methods.split).toHaveBeenCalledOnce();
		});

		it("opens the drawer", () => {
			const $panel = panel();
			const wrapper = mount({ isLastSelected: true }, {}, $panel);
			wrapper.findComponent(Options).vm.$emit("open");
			expect($panel.drawer.open).toHaveBeenCalledOnce();
		});
	});
});
