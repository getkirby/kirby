import { describe, it, expect } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import ColoroptionsInput from "./ColoroptionsInput.vue";

const options = [{ text: "Red", value: "#ff0000" }, { value: "#00ff00" }];

function mount(props = {}, attrs = {}) {
	return vueMount(ColoroptionsInput, {
		props: { options, ...props },
		attrs,
		attachTo: document.body
	});
}

describe("ColoroptionsInput.vue", () => {
	// $el
	describe("element", () => {
		const component = (attrs = {}) => mount({}, attrs);

		it.rendersAs(component, "FIELDSET", "k-coloroptions-input");
		it.acceptsClass(component);
		it.acceptsStyle(component);
		it.inheritsNoAttrs(component);
	});

	// props
	describe("options prop", () => {
		it("renders nothing without options", () => {
			const wrapper = mount({ options: [] });
			expect(wrapper.find("fieldset").exists()).toBe(false);
		});
	});

	describe("value prop", () => {
		it("checks the matching option", () => {
			const wrapper = mount({ value: "#00ff00" });
			const inputs = wrapper.findAll("input");
			expect(inputs[0].element.checked).toBe(false);
			expect(inputs[1].element.checked).toBe(true);
		});
	});

	// computed
	describe("choices computed", () => {
		it("uses the text as title", () => {
			const labels = mount().findAll("label");
			expect(labels[0].attributes("title")).toBe("Red");
			expect(labels[1].attributes("title")).toBe("#00ff00");
		});

		it("converts the values to the format", () => {
			const wrapper = mount({ options: [{ value: "rgb(255, 0, 0)" }] });
			expect(wrapper.find("input").element.value).toBe("#ff0000");
		});
	});

	// methods
	describe("colorToString()", () => {
		it("keeps invalid colors", () => {
			const wrapper = mount({ options: [{ value: "not-a-color" }] });
			expect(wrapper.find("input").element.value).toBe("not-a-color");
		});
	});

	describe("onClick()", () => {
		it("deselects the checked option", async () => {
			const wrapper = mount({ value: "#ff0000" });
			await wrapper.findAll("input")[0].trigger("click");
			expect(wrapper.emitted("input")).toEqual([[""]]);
		});

		it("keeps the checked option when required", async () => {
			const wrapper = mount({ required: true, value: "#ff0000" });
			await wrapper.findAll("input")[0].trigger("click");
			expect(wrapper.emitted("input")).toBeUndefined();
		});
	});

	// events
	describe("input event", () => {
		it("emits the value of the selected option", async () => {
			const wrapper = mount({ value: "#ff0000" });
			await wrapper.findAll("input")[1].trigger("click");
			expect(wrapper.emitted("input")).toEqual([["#00ff00"]]);
		});
	});
});
