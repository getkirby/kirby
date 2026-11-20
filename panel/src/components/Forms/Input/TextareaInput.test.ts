import {
	afterAll,
	beforeAll,
	describe,
	expect,
	it,
	onTestFinished,
	vi
} from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import { library } from "@/libraries";
import TextareaInput from "./TextareaInput.vue";

const Toolbar = {
	name: "k-textarea-toolbar",
	props: ["buttons", "disabled", "uploads"],
	emits: ["command"],
	methods: { close: vi.fn(), shortcut: vi.fn() },
	template: "<nav />"
};

function panel(kirbytext = true) {
	return {
		config: { kirbytext },
		content: { update: vi.fn() },
		dialog: { close: vi.fn(), open: vi.fn() },
		drag: { data: null as unknown, type: null as string | null },
		upload: { open: vi.fn(), pick: vi.fn() },
		urls: { api: "/api" }
	};
}

function mount(props = {}, attrs = {}, $panel = panel()) {
	return vueMount(TextareaInput, {
		props: { endpoints: { field: "pages/a/fields/text" }, ...props },
		attrs,
		// focus and selection only work on connected elements
		attachTo: document.body,
		global: {
			directives: { direction: {} },
			mocks: { $panel },
			stubs: { "k-textarea-toolbar": Toolbar }
		}
	});
}

function textarea(wrapper: ReturnType<typeof mount>) {
	return wrapper.find("textarea").element;
}

// selects a range of the textarea like the user would
async function select(
	wrapper: ReturnType<typeof mount>,
	start: number,
	end: number
) {
	textarea(wrapper).setSelectionRange(start, end);
	await wrapper.find("textarea").trigger("selectionchange");
}

function drag(type: string, types: string[], files: unknown[] = []) {
	const event = new Event(type, { bubbles: true, cancelable: true });
	const dataTransfer = { dropEffect: "none", files, types };
	Object.defineProperty(event, "dataTransfer", { value: dataTransfer });
	return event as DragEvent;
}

describe("k-textarea-input", () => {
	// happy-dom has no execCommand, so insert() falls back to setRangeText()
	beforeAll(() => {
		Object.defineProperty(document, "execCommand", {
			configurable: true,
			value: () => false
		});
	});

	afterAll(() => {
		Reflect.deleteProperty(document, "execCommand");
	});

	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-textarea-input");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("renders a native textarea", () => {
			const wrapper = mount();
			expect(wrapper.find("textarea").classes()).toContain(
				"k-textarea-input-native"
			);
		});
	});

	// props
	describe.each([
		["id", "text"],
		["minlength", 10],
		["name", "text"],
		["placeholder", "Write something …"]
	])("%s prop", (prop, value) => {
		it("sets the attribute on the textarea", () => {
			const wrapper = mount({ [prop]: value });
			expect(wrapper.find("textarea").attributes(prop)).toBe(String(value));
		});
	});

	describe("buttons prop", () => {
		it("shows the toolbar by default", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Toolbar).props("buttons")).toBe(true);
		});

		it("passes the buttons to the toolbar", () => {
			const wrapper = mount({ buttons: ["bold"] });
			expect(wrapper.findComponent(Toolbar).props("buttons")).toStrictEqual([
				"bold"
			]);
		});

		it("hides the toolbar when turned off", () => {
			const wrapper = mount({ buttons: false });
			expect(wrapper.findComponent(Toolbar).exists()).toBe(false);
		});
	});

	describe("id prop", () => {
		it("names the toolbar after the field label", () => {
			const wrapper = mount({ id: "text" });
			expect(wrapper.findComponent(Toolbar).attributes("aria-labelledby")).toBe(
				"text-label"
			);
		});
	});

	describe("disabled prop", () => {
		it("disables the textarea and hides the toolbar", () => {
			const wrapper = mount({ disabled: true });
			expect(textarea(wrapper).disabled).toBe(true);
			expect(wrapper.findComponent(Toolbar).exists()).toBe(false);
		});
	});

	describe("font prop", () => {
		it("sets data-font on the textarea", () => {
			const wrapper = mount({ font: "monospace" });
			expect(wrapper.find("textarea").attributes("data-font")).toBe(
				"monospace"
			);
		});
	});

	describe("required prop", () => {
		it("requires the textarea", () => {
			const wrapper = mount({ required: true });
			expect(textarea(wrapper).required).toBe(true);
		});
	});

	describe("size prop", () => {
		it("sets data-size", () => {
			const wrapper = mount({ size: "large" });
			expect(wrapper.attributes("data-size")).toBe("large");
		});
	});

	describe("spellcheck prop", () => {
		it("checks the spelling by default", () => {
			const wrapper = mount();
			expect(wrapper.find("textarea").attributes("spellcheck")).toBe("true");
		});

		it("turns off spell checking", () => {
			const wrapper = mount({ spellcheck: false });
			expect(wrapper.find("textarea").attributes("spellcheck")).toBe("false");
		});
	});

	describe("uploads prop", () => {
		it("passes the uploads to the toolbar", () => {
			const wrapper = mount({ uploads: { template: "image" } });
			expect(wrapper.findComponent(Toolbar).props("uploads")).toStrictEqual({
				template: "image"
			});
		});
	});

	describe("value prop", () => {
		it("shows the value", () => {
			const wrapper = mount({ value: "Hello" });
			expect(textarea(wrapper).value).toBe("Hello");
		});
	});

	// computed
	describe("uploadOptions computed", () => {
		it("uploads a single file to the field", () => {
			const wrapper = mount();
			expect(wrapper.vm.uploadOptions).toMatchObject({
				multiple: false,
				url: "/api/pages/a/fields/text/upload"
			});
		});

		it("inserts the uploaded files when done", async () => {
			const $panel = panel();
			const wrapper = mount({ value: "Hello" }, {}, $panel);
			await select(wrapper, 5, 5);
			await wrapper.vm.uploadOptions.on.done([{ dragText: "(image: a.jpg)" }]);
			expect(textarea(wrapper).value).toBe("Hello(image: a.jpg)");
			expect($panel.content.update).toHaveBeenCalledOnce();
		});
	});

	// watch
	describe("value watcher", () => {
		it("resizes the textarea", async () => {
			const update = vi.spyOn(library.autosize, "update");
			onTestFinished(() => update.mockRestore());
			const wrapper = mount({ value: "a" });
			await wrapper.setProps({ value: "a\nb" } as never);
			await wrapper.vm.$nextTick();
			expect(update).toHaveBeenCalledWith(textarea(wrapper));
		});
	});

	// lifecycle
	describe("mounted hook", () => {
		it("sizes the textarea to its content", async () => {
			const autosize = vi.spyOn(library, "autosize");
			onTestFinished(() => autosize.mockRestore());
			const wrapper = mount();
			await wrapper.vm.$nextTick();
			expect(autosize).toHaveBeenCalledWith(textarea(wrapper));
		});

		it("focuses the textarea with autofocus", async () => {
			const wrapper = mount({ autofocus: true });
			await wrapper.vm.$nextTick();
			expect(document.activeElement).toBe(textarea(wrapper));
		});

		it("selects the value with preselect", async () => {
			const wrapper = mount({ preselect: true, value: "Hello" });
			await wrapper.vm.$nextTick();
			expect(textarea(wrapper).selectionStart).toBe(0);
			expect(textarea(wrapper).selectionEnd).toBe(5);
		});
	});

	// methods
	describe("dialog()", () => {
		it("opens the dialog with the selected link", async () => {
			const $panel = panel();
			const wrapper = mount({ value: "(link: a.de text: A)" }, {}, $panel);
			await select(wrapper, 0, 20);
			wrapper.vm.dialog("link");
			expect($panel.dialog.open).toHaveBeenCalledWith(
				expect.objectContaining({
					component: "k-toolbar-link-dialog",
					props: { value: { href: "a.de", title: "A" } }
				})
			);
		});

		it("inserts the submitted text", async () => {
			const $panel = panel();
			const wrapper = mount({ value: "Hello" }, {}, $panel);
			await select(wrapper, 0, 5);
			wrapper.vm.dialog("link");
			await $panel.dialog.open.mock.calls[0][0].on.submit("(link: a.de)");
			expect($panel.dialog.close).toHaveBeenCalledOnce();
			expect(textarea(wrapper).value).toBe("(link: a.de)");
		});
	});

	describe("file()", () => {
		it("inserts the selected files", async () => {
			const $panel = panel();
			const wrapper = mount({ value: "" }, {}, $panel);
			await select(wrapper, 0, 0);
			wrapper.vm.file();
			const [url, options] = $panel.dialog.open.mock.calls[0];
			expect(url).toBe("pages/a/fields/text/files");
			await options.on.submit({
				items: [{ dragText: "(file: a.pdf)" }, { dragText: "(file: b.pdf)" }]
			});
			expect(textarea(wrapper).value).toBe("(file: a.pdf)\n\n(file: b.pdf)");
		});
	});

	describe("focus()", () => {
		it("focuses the textarea", () => {
			const wrapper = mount();
			wrapper.vm.focus();
			expect(document.activeElement).toBe(textarea(wrapper));
		});
	});

	describe("insert()", () => {
		it("replaces the selection and emits the value", async () => {
			const wrapper = mount({ value: "Hello world" });
			await select(wrapper, 6, 11);
			const value = await wrapper.vm.insert("Kirby");
			expect(value).toBe("Hello Kirby");
			expect(wrapper.emitted("input")).toStrictEqual([["Hello Kirby"]]);
		});

		it("builds the text from the input and selection", async () => {
			const wrapper = mount({ value: "Hello world" });
			await select(wrapper, 6, 11);
			await wrapper.vm.insert((input: HTMLTextAreaElement, selection: string) =>
				selection.toUpperCase()
			);
			expect(textarea(wrapper).value).toBe("Hello WORLD");
		});
	});

	describe("insertFile()", () => {
		it("inserts nothing without files", async () => {
			const wrapper = mount({ value: "Hello" });
			await wrapper.vm.insertFile([]);
			expect(wrapper.emitted("input")).toBeUndefined();
		});
	});

	describe("onCommand()", () => {
		it("runs the command with its arguments", async () => {
			const wrapper = mount({ value: "Hello" });
			await select(wrapper, 0, 5);
			wrapper.vm.onCommand("wrap", "**");
			await wrapper.vm.$nextTick();
			expect(textarea(wrapper).value).toBe("**Hello**");
		});

		it("warns about an unknown command", () => {
			const warn = vi.spyOn(console, "warn").mockImplementation(() => {});
			onTestFinished(() => warn.mockRestore());
			const wrapper = mount();
			wrapper.vm.onCommand("unknown");
			expect(warn).toHaveBeenCalledWith("unknown is not a valid command");
		});
	});

	describe("onShortcut()", () => {
		it("passes the key to the toolbar", () => {
			const wrapper = mount();
			const event = new KeyboardEvent("keydown", { key: "b" });
			wrapper.vm.onShortcut(event);
			expect(Toolbar.methods.shortcut).toHaveBeenCalledWith("b", event);
		});

		it("ignores the modifier key itself", () => {
			const wrapper = mount();
			wrapper.vm.onShortcut(new KeyboardEvent("keydown", { key: "Meta" }));
			wrapper.vm.onShortcut(new KeyboardEvent("keydown", { key: "Control" }));
			expect(Toolbar.methods.shortcut).not.toHaveBeenCalled();
		});
	});

	describe("parseSelection()", () => {
		async function parse(value: string, kirbytext = true) {
			const wrapper = mount({ value }, {}, panel(kirbytext));
			await select(wrapper, 0, value.length);
			return wrapper.vm.parseSelection();
		}

		it("returns no link without selection", async () => {
			expect(await parse("")).toStrictEqual({ href: null, title: null });
		});

		it("reads a KirbyText link", async () => {
			expect(await parse("(link: a.de text: A)")).toStrictEqual({
				href: "a.de",
				title: "A"
			});
		});

		it("reads a Markdown link", async () => {
			expect(await parse("[A](a.de)", false)).toStrictEqual({
				href: "a.de",
				title: "A"
			});
		});

		it("reads an autolink", async () => {
			expect(await parse("<a.de>", false)).toStrictEqual({
				href: "a.de",
				title: null
			});
		});

		it("uses other text as title", async () => {
			expect(await parse("Kirby")).toStrictEqual({
				href: null,
				title: "Kirby"
			});
		});

		it("uses text around a Markdown link as title", async () => {
			expect(await parse("see [A](a.de) here", false)).toStrictEqual({
				href: null,
				title: "see [A](a.de) here"
			});
		});
	});

	describe("prepend()", () => {
		it("prepends the text to the selection", async () => {
			const wrapper = mount({ value: "Title" });
			await select(wrapper, 0, 5);
			await wrapper.vm.prepend("#");
			expect(textarea(wrapper).value).toBe("# Title");
		});
	});

	describe("restoreSelection()", () => {
		it("restores the last selection", async () => {
			const wrapper = mount({ value: "Hello world" });
			await select(wrapper, 6, 11);
			textarea(wrapper).setSelectionRange(0, 0);
			await wrapper.vm.restoreSelection();
			expect(textarea(wrapper).selectionStart).toBe(6);
			expect(textarea(wrapper).selectionEnd).toBe(11);
		});
	});

	describe("select()", () => {
		it("selects the value", () => {
			const wrapper = mount({ value: "Hello" });
			wrapper.vm.select();
			expect(textarea(wrapper).selectionEnd).toBe(5);
		});
	});

	describe("selection()", () => {
		it("is empty without selection", () => {
			const wrapper = mount({ value: "Hello" });
			expect(wrapper.vm.selection()).toBe("");
		});

		it("returns the selected text", async () => {
			const wrapper = mount({ value: "Hello world" });
			await select(wrapper, 0, 5);
			expect(wrapper.vm.selection()).toBe("Hello");
		});
	});

	describe("toggle()", () => {
		it("wraps the selection", async () => {
			const wrapper = mount({ value: "Hello" });
			await select(wrapper, 0, 5);
			await wrapper.vm.toggle("**");
			expect(textarea(wrapper).value).toBe("**Hello**");
		});

		it("unwraps a wrapped selection", async () => {
			const wrapper = mount({ value: "**Hello**" });
			await select(wrapper, 0, 9);
			await wrapper.vm.toggle("**");
			expect(textarea(wrapper).value).toBe("Hello");
		});

		it("unwraps with different text before and after", async () => {
			const wrapper = mount({ value: "<b>Hello</b>" });
			await select(wrapper, 0, 12);
			await wrapper.vm.toggle("<b>", "</b>");
			expect(textarea(wrapper).value).toBe("Hello");
		});
	});

	describe("upload()", () => {
		it("opens the file picker for uploads", () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			wrapper.vm.upload();
			expect($panel.upload.pick).toHaveBeenCalledWith(wrapper.vm.uploadOptions);
		});
	});

	describe("wrap()", () => {
		it("wraps the selection with the same text", async () => {
			const wrapper = mount({ value: "Hello" });
			await select(wrapper, 0, 5);
			await wrapper.vm.wrap("`");
			expect(textarea(wrapper).value).toBe("`Hello`");
		});

		it("wraps the selection with different text", async () => {
			const wrapper = mount({ value: "Hello" });
			await select(wrapper, 0, 5);
			await wrapper.vm.wrap("(", ")");
			expect(textarea(wrapper).value).toBe("(Hello)");
		});
	});

	// events
	describe("click event", () => {
		it("closes the toolbar dropdowns", async () => {
			const wrapper = mount();
			await wrapper.find("textarea").trigger("click");
			expect(Toolbar.methods.close).toHaveBeenCalledOnce();
		});
	});

	describe("command event", () => {
		it("runs the command of the toolbar", async () => {
			const wrapper = mount({ value: "Hello" });
			await select(wrapper, 0, 5);
			wrapper.findComponent(Toolbar).vm.$emit("command", "toggle", "*");
			await wrapper.vm.$nextTick();
			expect(textarea(wrapper).value).toBe("*Hello*");
		});
	});

	describe("drag events", () => {
		it("highlights the textarea for files to upload", () => {
			const wrapper = mount({ uploads: true });
			textarea(wrapper).dispatchEvent(drag("dragover", ["Files"]));
			return wrapper.vm.$nextTick(() => {
				expect(wrapper.attributes("data-over")).toBe("true");
			});
		});

		it("ignores files without uploads", async () => {
			const wrapper = mount();
			textarea(wrapper).dispatchEvent(drag("dragover", ["Files"]));
			await wrapper.vm.$nextTick();
			expect(wrapper.attributes("data-over")).toBe("false");
		});

		it("removes the highlight when leaving", async () => {
			const wrapper = mount({ uploads: true });
			textarea(wrapper).dispatchEvent(drag("dragover", ["Files"]));
			textarea(wrapper).dispatchEvent(new Event("dragleave"));
			await wrapper.vm.$nextTick();
			expect(wrapper.attributes("data-over")).toBe("false");
		});

		it("uploads dropped files", () => {
			const $panel = panel();
			const wrapper = mount({ uploads: true }, {}, $panel);
			const file = new File(["a"], "a.jpg");
			textarea(wrapper).dispatchEvent(drag("drop", ["Files"], [file]));
			expect($panel.upload.open).toHaveBeenCalledWith(
				[file],
				wrapper.vm.uploadOptions
			);
		});

		it("inserts dropped text", async () => {
			const $panel = panel();
			$panel.drag = { data: "(link: a.de)", type: "text" };
			const wrapper = mount({ value: "" }, {}, $panel);
			await select(wrapper, 0, 0);
			textarea(wrapper).dispatchEvent(drag("drop", ["text/plain"]));
			await wrapper.vm.$nextTick();
			expect(textarea(wrapper).value).toBe("(link: a.de)");
		});
	});

	describe("focus event", () => {
		it("emits the focus of the textarea", async () => {
			const wrapper = mount();
			await wrapper.find("textarea").trigger("focus");
			expect(wrapper.emitted("focus")).toHaveLength(1);
		});
	});

	describe("input event", () => {
		it("emits the typed value", async () => {
			const wrapper = mount();
			await wrapper.find("textarea").setValue("Hello");
			expect(wrapper.emitted("input")).toStrictEqual([["Hello"]]);
		});
	});

	describe("keydown event", () => {
		it.each([["ctrl"], ["meta"]])("submits on %s+enter", async (key) => {
			const wrapper = mount();
			await wrapper
				.find("textarea")
				.trigger("keydown.enter", { [key + "Key"]: true });
			expect(wrapper.emitted("submit")).toHaveLength(1);
		});

		it.each([["ctrl"], ["meta"]])(
			"passes %s shortcuts to the toolbar",
			async (key) => {
				const wrapper = mount();
				await wrapper
					.find("textarea")
					.trigger("keydown", { key: "b", [key + "Key"]: true });
				expect(Toolbar.methods.shortcut).toHaveBeenCalledWith(
					"b",
					expect.any(KeyboardEvent)
				);
			}
		);
	});
});
