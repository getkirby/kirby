import { describe, it, expect } from "@test/unit";
import { mount } from "@vue/test-utils";
import Checklist from "./Checklist.vue";

describe("Checklist.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(Checklist, "UL", "k-checklist");
		it.acceptsClass(Checklist);
		it.acceptsStyle(Checklist);
	});

	// props
	describe("theme prop", () => {
		it("defaults to positive", () => {
			const wrapper = mount(Checklist);
			expect(wrapper.attributes("data-theme")).toBe("positive");
		});

		it("renders value as attribute", () => {
			const wrapper = mount(Checklist, { props: { theme: "negative" } });
			expect(wrapper.attributes("data-theme")).toBe("negative");
		});
	});

	// slots
	describe("default slot", () => {
		it("renders slotted content", () => {
			const wrapper = mount(Checklist, {
				slots: { default: "<li>Foo</li>" }
			});
			expect(wrapper.html())
				.toBe(`<ul class="k-checklist k-stack" data-theme="positive">
  <li>Foo</li>
</ul>`);
		});
	});
});
