import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import html from "@/panel/html";
import ChoiceInput from "./ChoiceInput.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(ChoiceInput, { props, attrs, attachTo: document.body });
}

describe("k-choice-input", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "LABEL", "k-choice-input");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	// props
	describe("autofocus prop", () => {
		it("sets the attribute on the input", () => {
			const wrapper = mount({ autofocus: true });
			expect(wrapper.find("input").attributes("autofocus")).toBe("");
		});
	});

	describe("checked prop", () => {
		it("is unchecked by default", () => {
			const wrapper = mount();
			expect(wrapper.find("input").element.checked).toBe(false);
		});

		it("checks the input", () => {
			const wrapper = mount({ checked: true });
			expect(wrapper.find("input").element.checked).toBe(true);
		});
	});

	describe("disabled prop", () => {
		it("disables the input", () => {
			const wrapper = mount({ disabled: true });
			expect(wrapper.find("input").element.disabled).toBe(true);
			expect(wrapper.attributes("aria-disabled")).toBe("true");
		});
	});

	describe("icon prop", () => {
		it("renders no icon by default", () => {
			const wrapper = mount();
			expect(wrapper.find("k-icon").exists()).toBe(false);
		});

		it("renders the icon", () => {
			const wrapper = mount({ icon: "heart" });
			expect(wrapper.find("k-icon").attributes("type")).toBe("heart");
		});
	});

	describe("id prop", () => {
		it("sets the id of the input", () => {
			const wrapper = mount({ id: "consent" });
			expect(wrapper.find("input").attributes("id")).toBe("consent");
		});
	});

	describe("info prop", () => {
		it("renders the info below the label", () => {
			const wrapper = mount({ info: "Some info", label: "Label" });
			const info = wrapper.find(".k-choice-input-label-info");
			expect(info.text()).toBe("Some info");
		});

		it("renders no info by default", () => {
			const wrapper = mount({ label: "Label" });
			expect(wrapper.find(".k-choice-input-label-info").exists()).toBe(false);
		});
	});

	describe("label prop", () => {
		it("renders no label by default", () => {
			const wrapper = mount();
			expect(wrapper.find(".k-choice-input-label").exists()).toBe(false);
		});

		it("escapes a plain string", () => {
			const wrapper = mount({ label: "<b>Label</b>" });
			const label = wrapper.find(".k-choice-input-label-text");
			expect(label.find("b").exists()).toBe(false);
			expect(label.text()).toBe("<b>Label</b>");
		});

		it("renders trusted HTML as is", () => {
			const wrapper = mount({ label: html("<b>Label</b>") });
			const label = wrapper.find(".k-choice-input-label-text");
			expect(label.find("b").text()).toBe("Label");
		});
	});

	describe("name prop", () => {
		it("sets the name of the input", () => {
			const wrapper = mount({ name: "consent" });
			expect(wrapper.find("input").attributes("name")).toBe("consent");
		});
	});

	describe("required prop", () => {
		it("requires the input", () => {
			const wrapper = mount({ required: true });
			expect(wrapper.find("input").element.required).toBe(true);
		});
	});

	describe("theme prop", () => {
		it("colors the icon", () => {
			const wrapper = mount({ icon: "heart", theme: "positive" });
			expect(wrapper.find("k-icon").attributes("data-theme")).toBe("positive");
		});
	});

	describe("type prop", () => {
		it("defaults to checkbox", () => {
			const wrapper = mount();
			expect(wrapper.find("input").attributes("type")).toBe("checkbox");
		});

		it("sets the type of the input", () => {
			const wrapper = mount({ type: "radio" });
			expect(wrapper.find("input").attributes("type")).toBe("radio");
		});
	});

	describe("value prop", () => {
		it("sets the value of the input", () => {
			const wrapper = mount({ value: "a" });
			expect(wrapper.find("input").element.value).toBe("a");
		});
	});

	describe("variant prop", () => {
		it("sets data-variant on the input", () => {
			const wrapper = mount({ variant: "toggle" });
			expect(wrapper.find("input").attributes("data-variant")).toBe("toggle");
		});

		it("hides the input visually for the invisible variant", () => {
			const wrapper = mount({ variant: "invisible" });
			expect(wrapper.find("input").classes()).toContain("sr-only");
		});
	});

	// methods
	// browsers forward focus from a label to its input, happy-dom does not
	describe("focus()", () => {
		it("focuses the label", () => {
			const wrapper = mount();
			const focus = vi.spyOn(wrapper.element, "focus");
			wrapper.vm.focus();
			expect(focus).toHaveBeenCalledOnce();
		});
	});

	// events
	describe("click event", () => {
		it("emits the click on the input", async () => {
			const wrapper = mount();
			await wrapper.find("input").trigger("click");
			expect(wrapper.emitted("click")).toHaveLength(1);
		});
	});

	describe("input event", () => {
		it("emits the checked state", async () => {
			const wrapper = mount();
			wrapper.find("input").element.click();
			expect(wrapper.emitted("input")).toStrictEqual([[true]]);
		});

		it("emits the unchecked state", async () => {
			const wrapper = mount({ checked: true });
			wrapper.find("input").element.click();
			expect(wrapper.emitted("input")).toStrictEqual([[false]]);
		});
	});
});
