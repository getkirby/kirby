import { describe, it, expect } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import TogglesInput from "./TogglesInput.vue";

const options = [
	{ text: "A", value: "a" },
	{ text: "B", value: "b" }
];

function mount(props = {}, attrs = {}) {
	return vueMount(TogglesInput, {
		props: { options, ...props },
		attrs,
		attachTo: document.body
	});
}

describe("k-toggles-input", () => {
	// $el
	describe("element", () => {
		const component = (attrs = {}) => mount({}, attrs);

		it.rendersAs(component, "FIELDSET", "k-toggles-input");
		it.acceptsClass(component);
		it.acceptsStyle(component);
		it.inheritsNoAttrs(component);
	});

	// props
	describe("reset prop", () => {
		it("deselects the checked option on click", async () => {
			const wrapper = mount({ reset: true, value: "a" });
			await wrapper.findAll("input")[0].trigger("click");
			expect(wrapper.emitted("input")).toEqual([[""]]);
		});

		it("keeps the checked option without reset", async () => {
			const wrapper = mount({ reset: false, value: "a" });
			await wrapper.findAll("input")[0].trigger("click");
			expect(wrapper.emitted("input")).toBeUndefined();
		});

		it("keeps the checked option when required", async () => {
			const wrapper = mount({ required: true, reset: true, value: "a" });
			await wrapper.findAll("input")[0].trigger("click");
			expect(wrapper.emitted("input")).toBeUndefined();
		});
	});

	// events
	describe("input event", () => {
		it("emits the value of a picked option", async () => {
			const wrapper = mount({ value: "a" });
			await wrapper.findAll("label")[1].trigger("click");

			expect(wrapper.emitted("input")).toStrictEqual([["b"]]);
		});
	});
});
