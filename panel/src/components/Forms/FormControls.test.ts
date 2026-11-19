import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import FormControls from "./FormControls.vue";

const Button = {
	name: "k-button",
	props: ["click", "disabled", "icon", "size", "text", "theme", "title"],
	template: `<button @click="click?.()" />`
};
const Dropdown = {
	name: "k-dropdown",
	methods: { toggle: vi.fn() },
	template: "<div><slot /></div>"
};

function panel() {
	return {
		dialog: { open: vi.fn() },
		user: { email: "me@getkirby.com" }
	};
}

function mount(props = {}, attrs = {}, $panel = panel()) {
	return vueMount(FormControls, {
		props,
		attrs,
		global: {
			mocks: { $panel },
			stubs: { "k-button": Button, "k-dropdown": Dropdown }
		}
	});
}

function buttons(wrapper: ReturnType<typeof mount>) {
	return wrapper.findAllComponents(Button);
}

function icons(wrapper: ReturnType<typeof mount>) {
	return buttons(wrapper).map((button) => button.props("icon"));
}

describe("k-form-controls", () => {
	// $el
	describe("element", () => {
		const changes = (attrs = {}) => mount({ hasDiff: true }, attrs);

		it.rendersAs(changes, "K-BUTTON-GROUP", "k-form-controls");
		it.acceptsClass(changes);
		it.acceptsStyle(changes);

		it("renders nothing without changes", () => {
			const wrapper = mount();
			expect(wrapper.find(".k-form-controls").exists()).toBe(false);
		});
	});

	// props
	describe("editor prop", () => {
		it("shows who edits the content", () => {
			const wrapper = mount({ editor: "editor@getkirby.com", hasDiff: true });
			expect(wrapper.find("dd").text()).toBe("editor@getkirby.com");
		});
	});

	describe("hasDiff prop", () => {
		it("offers to discard or save the changes", () => {
			const wrapper = mount({ hasDiff: true });
			expect(icons(wrapper)).toStrictEqual(["undo", "check", "dots"]);
			expect(buttons(wrapper)[1].props("text")).toBe("save");
			expect(wrapper.find("p").text()).toBe("form.unsaved");
		});
	});

	describe("isLocked prop", () => {
		it("shows the lock of the other editor", () => {
			const wrapper = mount({
				editor: "editor@getkirby.com",
				hasDiff: true,
				isLocked: true
			});
			expect(icons(wrapper)).toStrictEqual(["lock"]);
			expect(buttons(wrapper)[0].props()).toMatchObject({
				text: "editor@getkirby.com",
				theme: "negative"
			});
			expect(wrapper.find("p").text()).toBe("form.locked");
		});
	});

	describe("isProcessing prop", () => {
		it("disables the buttons and shows a loader", () => {
			const wrapper = mount({ hasDiff: true, isProcessing: true });
			expect(buttons(wrapper).every((button) => button.props("disabled"))).toBe(
				true
			);
			expect(icons(wrapper)[1]).toBe("loader");
		});
	});

	describe("modified prop", () => {
		it("shows when the content was changed", () => {
			const wrapper = mount({
				hasDiff: true,
				modified: "2022-01-15 14:30:00"
			});
			expect(wrapper.find("dd").text()).toBe("2022-01-15 14:30:00");
		});
	});

	describe("preview prop", () => {
		it("links to the preview of the changes", () => {
			const wrapper = mount({ hasDiff: true, preview: "/preview" });
			const link = wrapper.find("k-dropdown-item");
			expect(link.attributes("link")).toBe("/preview");
			expect(link.text()).toBe("form.preview");
		});

		it("offers no preview by default", () => {
			const wrapper = mount({ hasDiff: true });
			expect(wrapper.find("k-dropdown-item").exists()).toBe(false);
		});
	});

	describe("size prop", () => {
		it("defaults to sm", () => {
			const wrapper = mount({ hasDiff: true });
			expect(buttons(wrapper)[0].props("size")).toBe("sm");
		});
	});

	// methods
	describe("discard()", () => {
		it("asks to discard the changes", () => {
			const $panel = panel();
			const wrapper = mount({ hasDiff: true }, {}, $panel);
			wrapper.vm.discard();
			const [dialog] = $panel.dialog.open.mock.calls[0];
			expect(dialog.component).toBe("k-remove-dialog");
			expect(String(dialog.props.text)).toBe("form.discard.confirm");
			dialog.on.submit();
			expect(wrapper.emitted("discard")).toHaveLength(1);
		});

		it("names the other editor of the changes", () => {
			const $panel = panel();
			const wrapper = mount(
				{ editor: "editor@getkirby.com", hasDiff: true },
				{},
				$panel
			);
			wrapper.vm.discard();
			const [dialog] = $panel.dialog.open.mock.calls[0];
			expect(String(dialog.props.text)).toBe("form.discard.confirm.editor");
		});
	});

	// events
	describe("click event", () => {
		it("asks to discard with the discard button", async () => {
			const $panel = panel();
			const wrapper = mount({ hasDiff: true }, {}, $panel);
			await buttons(wrapper)[0].trigger("click");
			expect($panel.dialog.open).toHaveBeenCalledOnce();
		});

		it("toggles the details with the options button", async () => {
			const wrapper = mount({ hasDiff: true });
			await buttons(wrapper)[2].trigger("click");
			expect(Dropdown.methods.toggle).toHaveBeenCalledOnce();
		});

		it("toggles the details with the lock button", async () => {
			const wrapper = mount({ isLocked: true });
			await buttons(wrapper)[0].trigger("click");
			expect(Dropdown.methods.toggle).toHaveBeenCalledOnce();
		});
	});

	describe("submit event", () => {
		it("is emitted by the save button", async () => {
			const wrapper = mount({ hasDiff: true });
			await buttons(wrapper)[1].trigger("click");
			expect(wrapper.emitted("submit")).toHaveLength(1);
		});
	});
});
