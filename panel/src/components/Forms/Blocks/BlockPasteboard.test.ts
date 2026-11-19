import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import BlockPasteboard from "./BlockPasteboard.vue";

const Dialog = {
	name: "k-dialog",
	props: ["cancelButton", "size", "submitButton", "visible"],
	emits: ["cancel", "submit"],
	template: "<div><slot /></div>"
};

function mount(attrs = {}) {
	return vueMount(BlockPasteboard, {
		attrs,
		global: {
			stubs: { "k-dialog": Dialog }
		}
	});
}

describe("k-block-pasteboard", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-block-importer");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("renders a large dialog without buttons", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Dialog).props()).toStrictEqual({
				cancelButton: false,
				size: "large",
				submitButton: false,
				visible: true
			});
		});

		it("asks to paste into the textarea", () => {
			const wrapper = mount();
			const label = wrapper.find("label");
			expect(label.attributes("for")).toBe("pasteboard");
			expect(label.text()).toBe("field.blocks.fieldsets.paste");
			expect(wrapper.find("textarea").attributes("id")).toBe("pasteboard");
		});
	});

	// computed
	describe("shortcut computed", () => {
		it("combines the meta key with v", () => {
			const wrapper = mount();
			expect(wrapper.vm.shortcut).toBe(
				wrapper.vm.$helper.keyboard.metaKey() + "+v"
			);
		});
	});

	// events
	describe("events", () => {
		it("closes and emits the paste event", async () => {
			const wrapper = mount();
			const event = new Event("paste", { cancelable: true });
			wrapper.find("textarea").element.dispatchEvent(event);
			expect(event.defaultPrevented).toBe(true);
			expect(Object.keys(wrapper.emitted())).toEqual(
				expect.arrayContaining(["close", "paste"])
			);
			expect(wrapper.emitted("paste")).toStrictEqual([[event]]);
		});

		it("emits cancel", () => {
			const wrapper = mount();
			wrapper.findComponent(Dialog).vm.$emit("cancel");
			expect(wrapper.emitted("cancel")).toHaveLength(1);
		});

		it("emits submit", () => {
			const wrapper = mount();
			wrapper.findComponent(Dialog).vm.$emit("submit");
			expect(wrapper.emitted("submit")).toHaveLength(1);
		});
	});
});
