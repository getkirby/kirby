import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import type Editor from "../Writer/Editor";
import WriterInput from "./WriterInput.vue";

const Toolbar = {
	name: "k-writer-toolbar",
	props: ["editor", "inline", "marks"],
	emits: ["command"],
	methods: { close: vi.fn() },
	template: "<nav />"
};

function panel() {
	return {
		dialog: { close: vi.fn(), open: vi.fn() },
		events: { off: vi.fn(), on: vi.fn() }
	};
}

function mount(props = {}, attrs = {}, $panel = panel()) {
	return vueMount(WriterInput, {
		props,
		attrs,
		// the editor needs a connected element
		attachTo: document.body,
		global: {
			directives: { direction: {} },
			mocks: { $panel },
			stubs: { "k-writer-toolbar": Toolbar }
		}
	});
}

// the editor is created on mount, its state renders a tick later
async function ready(props = {}, attrs = {}, $panel = panel()) {
	const wrapper = mount(props, attrs, $panel);
	await wrapper.vm.$nextTick();
	return wrapper;
}

function editor(wrapper: ReturnType<typeof mount>) {
	return wrapper.vm.editor as unknown as Editor;
}

function editable(wrapper: ReturnType<typeof mount>) {
	return wrapper.find(".ProseMirror");
}

function output(wrapper: ReturnType<typeof mount>) {
	return wrapper.find<HTMLTextAreaElement>("textarea").element;
}

// validate() waits for the editor before it checks the content
function validated() {
	return new Promise((resolve) => setTimeout(resolve, 60));
}

describe("k-writer-input", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-writer-input");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("renders an editor and a hidden textarea for the form", () => {
			const wrapper = mount();
			expect(wrapper.classes()).toContain("k-writer");
			expect(editable(wrapper).exists()).toBe(true);
			expect(output(wrapper).getAttribute("aria-hidden")).toBe("true");
			expect(output(wrapper).tabIndex).toBe(-1);
		});
	});

	// props
	describe("disabled prop", () => {
		it("makes the editor read-only and hides the toolbar", () => {
			const wrapper = mount({ disabled: true });
			expect(wrapper.attributes("data-disabled")).toBe("true");
			expect(editable(wrapper).attributes("contenteditable")).toBe("false");
			expect(wrapper.findComponent(Toolbar).exists()).toBe(false);
		});

		it("shows the toolbar when enabled", async () => {
			const wrapper = await ready();
			expect(wrapper.findComponent(Toolbar).exists()).toBe(true);
		});
	});

	describe("headings prop", () => {
		it("limits the heading levels", () => {
			const wrapper = mount({ headings: [2] });
			const heading = wrapper.vm
				.createNodes()
				.find((node: { name: string }) => node.name === "heading");
			expect(heading?.options.levels).toStrictEqual([2]);
		});
	});

	describe("inline prop", () => {
		it("keeps only inline nodes", () => {
			const wrapper = mount({ inline: true });
			const names = wrapper.vm
				.createNodes()
				.map((node: { name: string }) => node.name);
			expect(names).toStrictEqual(["hardBreak"]);
		});
	});

	describe("name prop", () => {
		it("names the hidden textarea", () => {
			const wrapper = mount({ name: "text" });
			expect(output(wrapper).name).toBe("text");
		});
	});

	describe("nodes prop", () => {
		it("offers headings and lists by default", () => {
			const wrapper = mount();
			const names = wrapper.vm
				.createNodes()
				.map((node: { name: string }) => node.name);
			expect(names).toStrictEqual([
				"heading",
				"bulletList",
				"orderedList",
				"hardBreak",
				"listItem"
			]);
		});

		it("offers only line breaks without nodes", () => {
			const wrapper = mount({ nodes: false });
			const names = wrapper.vm
				.createNodes()
				.map((node: { name: string }) => node.name);
			expect(names).toStrictEqual(["hardBreak"]);
		});
	});

	describe("placeholder prop", () => {
		it("sets data-placeholder", () => {
			const wrapper = mount({ placeholder: "Write …" });
			expect(wrapper.attributes("data-placeholder")).toBe("Write …");
		});
	});

	describe("required prop", () => {
		it("requires the hidden textarea", () => {
			const wrapper = mount({ required: true });
			expect(output(wrapper).required).toBe(true);
		});
	});

	describe("spellcheck prop", () => {
		it("checks the spelling by default", () => {
			const wrapper = mount();
			expect(wrapper.attributes("spellcheck")).toBe("true");
		});
	});

	describe("value prop", () => {
		it("shows the value in the editor", async () => {
			const wrapper = await ready({ value: "<p>Hello</p>" });
			expect(editable(wrapper).html()).toContain("<p>Hello</p>");
			expect(wrapper.attributes("data-empty")).toBe("false");
		});

		it("passes the value to the hidden textarea", () => {
			const wrapper = mount({ value: "<p>Hello</p>" });
			expect(output(wrapper).value).toBe("<p>Hello</p>");
		});

		it("is empty without value", () => {
			const wrapper = mount();
			expect(wrapper.attributes("data-empty")).toBe("true");
		});
	});

	// computed
	describe("characters computed", () => {
		it("counts the text without HTML and entities", () => {
			const wrapper = mount({ value: "<p>a &amp; <b>b</b></p>" });
			expect(wrapper.vm.characters).toBe(5);
		});
	});

	describe("isCursorAtEnd computed", () => {
		it("follows the cursor", async () => {
			const wrapper = await ready({ value: "<p>Hello</p>" });
			const end = editor(wrapper).selectionAtEnd.head;
			editor(wrapper).setSelection(end, end);
			expect(wrapper.vm.isCursorAtEnd).toBe(true);
			editor(wrapper).setSelection(3, 3);
			expect(wrapper.vm.isCursorAtEnd).toBe(false);
			editor(wrapper).setSelection(end, end);
			expect(wrapper.vm.isCursorAtEnd).toBe(true);
		});
	});

	describe("isCursorAtStart computed", () => {
		it("follows the cursor", async () => {
			const wrapper = await ready({ value: "<p>Hello</p>" });
			editor(wrapper).setSelection(1, 1);
			expect(wrapper.vm.isCursorAtStart).toBe(true);
			editor(wrapper).setSelection(3, 3);
			expect(wrapper.vm.isCursorAtStart).toBe(false);
			editor(wrapper).setSelection(1, 1);
			expect(wrapper.vm.isCursorAtStart).toBe(true);
		});
	});

	describe("toolbarOptions computed", () => {
		it("passes the editor and the toolbar options", async () => {
			const wrapper = await ready();
			const toolbar = wrapper.findComponent(Toolbar);
			expect(toolbar.props("editor")).toBe(editor(wrapper));
			expect(toolbar.props("inline")).toBe(true);
			expect(toolbar.props("marks")).toBeUndefined();
		});

		it("offers the custom set of marks in the toolbar", async () => {
			const wrapper = await ready({ marks: ["bold"] });
			expect(wrapper.findComponent(Toolbar).props("marks")).toStrictEqual([
				"bold"
			]);
		});

		it("offers no marks when turned off", async () => {
			const wrapper = await ready({ marks: false });
			expect(wrapper.findComponent(Toolbar).props("marks")).toBe(false);
		});
	});

	// watch
	describe("value watcher", () => {
		it("shows a new value without emitting it", async () => {
			const wrapper = mount({ value: "<p>Hello</p>" });
			await wrapper.setProps({ value: "<p>World</p>" } as never);
			expect(editable(wrapper).html()).toContain("<p>World</p>");
			expect(wrapper.emitted("input")).toBeUndefined();
		});

		it("updates the empty state", async () => {
			const wrapper = mount({ value: "<p>Hello</p>" });
			await wrapper.setProps({ value: "" } as never);
			expect(wrapper.attributes("data-empty")).toBe("true");
		});

		it("validates the new value", async () => {
			const wrapper = mount({ maxlength: 3, value: "<p>Hello</p>" });
			await validated();
			expect(output(wrapper).validationMessage).toBe(
				"error.validation.maxlength"
			);
			await wrapper.setProps({ value: "<p>Hi</p>" } as never);
			await validated();
			expect(output(wrapper).validationMessage).toBe("");
		});
	});

	// lifecycle
	describe("mounted hook", () => {
		it("closes the toolbar on clicks and focus elsewhere", () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			expect($panel.events.on).toHaveBeenCalledWith("click", wrapper.vm.onBlur);
			expect($panel.events.on).toHaveBeenCalledWith("focus", wrapper.vm.onBlur);
		});

		it("marks a value that is too long as invalid", async () => {
			const wrapper = mount({ maxlength: 3, value: "<p>Hello</p>" });
			await validated();
			expect(output(wrapper).validationMessage).toBe(
				"error.validation.maxlength"
			);
		});
	});

	describe("beforeUnmount hook", () => {
		it("destroys the editor and stops listening", () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			const destroy = vi.spyOn(editor(wrapper), "destroy");
			const onBlur = wrapper.vm.onBlur;
			wrapper.unmount();
			expect(destroy).toHaveBeenCalledOnce();
			expect($panel.events.off).toHaveBeenCalledWith("click", onBlur);
			expect($panel.events.off).toHaveBeenCalledWith("focus", onBlur);
		});
	});

	// methods
	describe("command()", () => {
		it("runs the editor command", () => {
			const wrapper = mount();
			const command = vi.spyOn(editor(wrapper), "command");
			wrapper.vm.command("toggleLink", { href: "a.de" });
			expect(command).toHaveBeenCalledWith("toggleLink", { href: "a.de" });
		});
	});

	describe("focus()", () => {
		it("focuses the editor", () => {
			const wrapper = mount();
			const focus = vi.spyOn(editor(wrapper), "focus");
			wrapper.vm.focus();
			expect(focus).toHaveBeenCalledOnce();
		});
	});

	describe("getHTML()", () => {
		it("returns the HTML of the editor", () => {
			const wrapper = mount({ value: "<p>Hello</p>" });
			expect(wrapper.vm.getHTML()).toBe("<p>Hello</p>");
		});
	});

	describe("getSplitContent()", () => {
		it("splits the content at the cursor", () => {
			const wrapper = mount({ value: "<p>Hello</p>" });
			editor(wrapper).setSelection(3, 3);
			expect(wrapper.vm.getSplitContent()).toStrictEqual([
				"<p>He</p>",
				"<p>llo</p>"
			]);
		});
	});

	describe("onBlur()", () => {
		it("closes the toolbar for a click outside", async () => {
			const wrapper = await ready();
			wrapper.vm.onBlur({ target: document.body });
			expect(Toolbar.methods.close).toHaveBeenCalledOnce();
		});

		it("keeps the toolbar open for a click inside", async () => {
			const wrapper = await ready();
			wrapper.vm.onBlur({ target: editable(wrapper).element });
			expect(Toolbar.methods.close).not.toHaveBeenCalled();
		});
	});

	describe("validate()", () => {
		it("marks a value that is too short as invalid", async () => {
			const wrapper = mount({ minlength: 10, value: "<p>Hello</p>" });
			await wrapper.vm.validate();
			expect(output(wrapper).validationMessage).toBe(
				"error.validation.minlength"
			);
		});

		it("accepts a value within the limits", async () => {
			const wrapper = mount({
				maxlength: 10,
				minlength: 2,
				value: "<p>Hello</p>"
			});
			await wrapper.vm.validate();
			expect(output(wrapper).validationMessage).toBe("");
		});

		it("accepts an empty value", async () => {
			const wrapper = mount({ minlength: 10 });
			await wrapper.vm.validate();
			expect(output(wrapper).validationMessage).toBe("");
		});
	});

	// events
	describe("command event", () => {
		it("runs the command of the toolbar", async () => {
			const wrapper = await ready();
			const command = vi.spyOn(editor(wrapper), "command");
			wrapper.findComponent(Toolbar).vm.$emit("command", "toggleMark", "bold");
			expect(command).toHaveBeenCalledWith("toggleMark", "bold");
		});
	});

	describe("email event", () => {
		it("opens the email dialog and adds the email", () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			const command = vi.spyOn(editor(wrapper), "command");
			editor(wrapper).emit("email", editor(wrapper));
			const [dialog] = $panel.dialog.open.mock.calls[0];
			expect(dialog.component).toBe("k-email-dialog");
			dialog.on.submit({ href: "mail@getkirby.com" });
			expect($panel.dialog.close).toHaveBeenCalledOnce();
			expect(command).toHaveBeenCalledWith("toggleEmail", {
				href: "mail@getkirby.com"
			});
		});
	});

	describe("input event", () => {
		it("emits the HTML of the editor", () => {
			const wrapper = mount();
			editor(wrapper).insertText("Hello");
			expect(wrapper.emitted("input")).toStrictEqual([["<p>Hello</p>"]]);
		});

		it("emits an empty string for an empty editor", () => {
			const wrapper = mount({ value: "<p>Hello</p>" });
			editor(wrapper).clearContent(true);
			expect(wrapper.emitted("input")).toStrictEqual([[""]]);
		});
	});

	describe("link event", () => {
		it("opens the link dialog and adds the link", () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			const command = vi.spyOn(editor(wrapper), "command");
			editor(wrapper).emit("link", editor(wrapper));
			const [dialog] = $panel.dialog.open.mock.calls[0];
			expect(dialog.component).toBe("k-link-dialog");
			dialog.on.submit({ href: "a.de" });
			expect($panel.dialog.close).toHaveBeenCalledOnce();
			expect(command).toHaveBeenCalledWith("toggleLink", { href: "a.de" });
		});
	});
});
