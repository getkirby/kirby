import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import BlockSelector from "./BlockSelector.vue";

const Button = {
	name: "k-button",
	props: ["disabled", "icon", "size", "text"],
	emits: ["click"],
	template: `<button @click="$emit('click', $event)" />`
};
const Dialog = {
	name: "k-dialog",
	props: ["cancelButton", "size", "submitButton", "visible"],
	emits: ["cancel", "submit"],
	template: "<div><slot /></div>"
};

const fieldsets = {
	heading: { icon: "title", name: "Heading", type: "heading" },
	image: { name: "Image", type: "image" },
	text: { icon: "text", name: "Text", type: "text" }
};

const events = { off: vi.fn(), on: vi.fn() };

function mount(props = {}, attrs = {}) {
	return vueMount(BlockSelector, {
		props: { fieldsets, ...props },
		attrs,
		global: {
			mocks: { $events: events },
			stubs: { "k-button": Button, "k-dialog": Dialog }
		}
	});
}

function buttons(wrapper: ReturnType<typeof mount>) {
	return wrapper.findAllComponents(Button);
}

describe("k-block-selector", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-block-selector");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("renders a dialog without buttons", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Dialog).props()).toMatchObject({
				cancelButton: false,
				size: "medium",
				submitButton: false,
				visible: true
			});
		});

		it("hints at pasting blocks", () => {
			const wrapper = mount();
			expect(wrapper.find(".k-clipboard-hint").text()).toBe(
				"field.blocks.fieldsets.paste"
			);
		});
	});

	// props
	describe("disabledFieldsets prop", () => {
		it("disables the fieldsets", () => {
			const wrapper = mount({ disabledFieldsets: ["text"] });
			expect(
				buttons(wrapper).map((button) => button.props("disabled"))
			).toStrictEqual([false, false, true]);
		});
	});

	describe("fieldsets prop", () => {
		it("offers all fieldsets in one group", () => {
			const wrapper = mount();
			expect(wrapper.findAll("details")).toHaveLength(1);
			expect(wrapper.find("summary").text()).toBe(
				"field.blocks.fieldsets.label"
			);
			expect(
				buttons(wrapper).map((button) => button.props("text"))
			).toStrictEqual(["Heading", "Image", "Text"]);
		});

		it("shows a box for a fieldset without icon", () => {
			const wrapper = mount();
			expect(buttons(wrapper)[1].props("icon")).toBe("box");
		});
	});

	describe("fieldsetGroups prop", () => {
		it("groups the fieldsets", () => {
			const wrapper = mount({
				fieldsetGroups: {
					media: { label: "Media", open: false, sets: ["image", "video"] },
					text: { label: "Text", sets: ["heading", "text"] },
					unknown: { label: "Unknown", sets: ["video"] }
				}
			});
			const groups = wrapper.findAll("details");
			expect(groups.map((group) => group.find("summary").text())).toStrictEqual(
				["Media", "Text"]
			);
			expect(groups[0].element.open).toBe(false);
			expect(groups[1].element.open).toBe(true);
			expect(
				buttons(wrapper).map((button) => button.props("text"))
			).toStrictEqual(["Image", "Heading", "Text"]);
		});
	});

	describe("headline prop", () => {
		it("shows the headline", () => {
			const wrapper = mount({ headline: "Change type" });
			expect(wrapper.find("k-headline").text()).toBe("Change type");
		});

		it("shows no headline by default", () => {
			const wrapper = mount();
			expect(wrapper.find("k-headline").exists()).toBe(false);
		});
	});

	describe("size prop", () => {
		it("passes the size to the dialog", () => {
			const wrapper = mount({ size: "large" });
			expect(wrapper.findComponent(Dialog).props("size")).toBe("large");
		});
	});

	// computed
	describe("shortcut computed", () => {
		it("pastes with v and the meta key", () => {
			const wrapper = mount();
			expect(wrapper.vm.shortcut).toBe(
				wrapper.vm.$helper.keyboard.metaKey() + "+v"
			);
		});
	});

	// lifecycle
	describe("mounted hook", () => {
		it("listens to pasting until unmounted", () => {
			const wrapper = mount();
			const handler = wrapper.vm.paste;
			expect(events.on).toHaveBeenCalledWith("paste", handler);
			wrapper.unmount();
			expect(events.off).toHaveBeenCalledWith("paste", handler);
		});
	});

	// methods
	describe("paste()", () => {
		it("emits the paste and closes the selector", () => {
			const wrapper = mount();
			const event = new Event("paste");
			wrapper.vm.paste(event);
			expect(wrapper.emitted("paste")).toStrictEqual([[event]]);
			expect(wrapper.emitted("close")).toHaveLength(1);
		});
	});

	// events
	describe("cancel event", () => {
		it("is emitted when the dialog is cancelled", () => {
			const wrapper = mount();
			wrapper.findComponent(Dialog).vm.$emit("cancel");
			expect(wrapper.emitted("cancel")).toHaveLength(1);
		});
	});

	describe("input event", () => {
		it("emits the type of the focused fieldset", async () => {
			const wrapper = mount();
			await buttons(wrapper)[2].trigger("focus");
			expect(wrapper.emitted("input")).toStrictEqual([["text"]]);
		});
	});

	describe("submit event", () => {
		it("emits the type of the clicked fieldset", async () => {
			const wrapper = mount();
			await buttons(wrapper)[0].trigger("click");
			expect(wrapper.emitted("submit")).toStrictEqual([["heading"]]);
		});

		it("emits the value when the dialog is submitted", () => {
			const wrapper = mount({ value: "image" });
			wrapper.findComponent(Dialog).vm.$emit("submit");
			expect(wrapper.emitted("submit")).toStrictEqual([["image"]]);
		});
	});
});
