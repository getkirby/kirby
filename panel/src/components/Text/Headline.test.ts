import { describe, it, expect } from "@test/unit";
import { mount } from "@vue/test-utils";
import Headline from "./Headline.vue";

describe("Headline.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(Headline, "H2", "k-headline");
		it.acceptsClass(Headline);
		it.acceptsStyle(Headline);
	});

	// props
	describe("tag prop", () => {
		it("defaults to h2", () => {
			const wrapper = mount(Headline);
			expect(wrapper.element.tagName).toBe("H2");
		});

		it("renders the given tag", () => {
			const wrapper = mount(Headline, { props: { tag: "h3" } });
			expect(wrapper.element.tagName).toBe("H3");
		});
	});

	describe("link prop", () => {
		it("wraps content in a k-link", () => {
			const wrapper = mount(Headline, { props: { link: "/foo" } });
			expect(wrapper.find("k-link").attributes("to")).toBe("/foo");
		});

		it("renders slot without k-link when not provided", () => {
			const wrapper = mount(Headline, {
				slots: { default: "Hello" }
			});
			expect(wrapper.find("a").exists()).toBe(false);
			expect(wrapper.find("k-link").exists()).toBe(false);
			expect(wrapper.text()).toBe("Hello");
		});
	});

	// events
	describe("click event", () => {
		it("emits click on click", async () => {
			const wrapper = mount(Headline);
			await wrapper.trigger("click");
			expect(wrapper.emitted("click")).toHaveLength(1);
		});
	});

	// slots
	describe("default slot", () => {
		it("renders slotted content", () => {
			const wrapper = mount(Headline, {
				slots: { default: "Hello" }
			});
			expect(wrapper.text()).toBe("Hello");
		});
	});
});
