import { describe, expect, it, onTestFinished, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import ChoiceInput from "./ChoiceInput.vue";
import ToggleInput from "./ToggleInput.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(ToggleInput, {
		props,
		attrs,
		// clicks only check connected inputs
		attachTo: document.body,
		global: {
			// registered globally in the Panel
			components: { "k-choice-input": ChoiceInput }
		}
	});
}

function label(wrapper: ReturnType<typeof mount>) {
	return wrapper.find(".k-choice-input-label-text").text();
}

describe("k-toggle-input", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "LABEL", "k-toggle-input");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("renders a k-choice-input as toggle", () => {
			const wrapper = mount();
			const input = wrapper.find("input");
			expect(wrapper.classes()).toContain("k-choice-input");
			expect(input.attributes("type")).toBe("checkbox");
			expect(input.attributes("data-variant")).toBe("toggle");
		});

		it("renders no icon", () => {
			const wrapper = mount({ icon: "heart" });
			expect(wrapper.find("k-icon").exists()).toBe(false);
		});
	});

	// props
	describe("disabled prop", () => {
		it("disables the input", () => {
			const wrapper = mount({ disabled: true });
			expect(wrapper.find("input").element.disabled).toBe(true);
		});
	});

	describe("text prop", () => {
		it("shows off and on by default", async () => {
			const wrapper = mount();
			expect(label(wrapper)).toBe("off");
			await wrapper.setProps({ value: true } as never);
			expect(label(wrapper)).toBe("on");
		});

		it("shows a text for each state", async () => {
			const wrapper = mount({ text: ["Hidden", "Visible"] });
			expect(label(wrapper)).toBe("Hidden");
			await wrapper.setProps({ value: true } as never);
			expect(label(wrapper)).toBe("Visible");
		});

		it("shows the same text in both states", async () => {
			const wrapper = mount({ text: "Visible" });
			expect(label(wrapper)).toBe("Visible");
			await wrapper.setProps({ value: true } as never);
			expect(label(wrapper)).toBe("Visible");
		});
	});

	describe("value prop", () => {
		it("checks the input", () => {
			const wrapper = mount({ value: true });
			expect(wrapper.find("input").element.checked).toBe(true);
		});
	});

	// lifecycle
	describe("mounted hook", () => {
		it("focuses the toggle with autofocus", () => {
			const focus = vi.spyOn(HTMLLabelElement.prototype, "focus");
			onTestFinished(() => focus.mockRestore());
			mount({ autofocus: true });
			expect(focus).toHaveBeenCalledOnce();
		});
	});

	// methods
	// browsers forward focus from a label to its input, happy-dom does not
	describe("focus()", () => {
		it("focuses the toggle", () => {
			const wrapper = mount();
			const focus = vi.spyOn(wrapper.element, "focus");
			wrapper.vm.focus();
			expect(focus).toHaveBeenCalledOnce();
		});
	});

	describe("select()", () => {
		it("focuses the toggle", () => {
			const wrapper = mount();
			const focus = vi.spyOn(wrapper.element, "focus");
			wrapper.vm.select();
			expect(focus).toHaveBeenCalledOnce();
		});
	});

	// events
	describe("input event", () => {
		it("emits the checked state", () => {
			const wrapper = mount();
			wrapper.find("input").element.click();
			expect(wrapper.emitted("input")).toStrictEqual([[true]]);
		});
	});
});
