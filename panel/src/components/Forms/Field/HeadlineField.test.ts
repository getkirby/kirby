import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import HeadlineField from "./HeadlineField.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(HeadlineField, { props, attrs });
}

describe("k-headline-field", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-headline-field");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	// props
	describe("help prop", () => {
		it("renders the help text in the footer", () => {
			const wrapper = mount({ help: "Some help" });
			const help = wrapper.find(".k-field-footer k-text");
			expect(help.attributes("text")).toBe("Some help");
			expect(help.classes()).toStrictEqual(["k-help", "k-field-help"]);
		});

		it("renders no footer without help", () => {
			const wrapper = mount();
			expect(wrapper.find(".k-field-footer").exists()).toBe(false);
		});
	});

	describe("label prop", () => {
		it("renders the label as headline", () => {
			const wrapper = mount({ label: "Details" });
			const headline = wrapper.find("k-headline");
			expect(headline.text()).toBe("Details");
			expect(headline.classes()).toContain("h2");
		});
	});
});
