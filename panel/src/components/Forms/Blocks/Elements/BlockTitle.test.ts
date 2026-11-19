import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import BlockTitle from "./BlockTitle.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(BlockTitle, {
		props: { fieldset: { icon: "title", name: "Heading" }, ...props },
		attrs
	});
}

describe("k-block-title", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-block-title");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	// props
	describe("content prop", () => {
		it("fills the label template", () => {
			const wrapper = mount({
				content: { text: "Hello" },
				fieldset: { label: "{{ text }}", name: "Heading" }
			});
			expect(wrapper.find(".k-block-label").text()).toBe("Hello");
		});

		it("shows the label as plain text", () => {
			const wrapper = mount({
				content: { text: "<b>Tom</b> &amp; Jerry" },
				fieldset: { label: "{{ text }}", name: "Heading" }
			});
			expect(wrapper.find(".k-block-label").text()).toBe("Tom & Jerry");
			expect(wrapper.find(".k-block-label b").exists()).toBe(false);
		});

		it("hides the label without content", () => {
			const wrapper = mount({
				fieldset: { label: "{{ text }}", name: "Heading" }
			});
			expect(wrapper.find(".k-block-label").exists()).toBe(false);
		});
	});

	describe("fieldset prop", () => {
		it("shows the icon", () => {
			const wrapper = mount();
			expect(wrapper.find("k-icon").attributes("type")).toBe("title");
		});

		it("shows a box without icon", () => {
			const wrapper = mount({ fieldset: { name: "Heading" } });
			expect(wrapper.find("k-icon").attributes("type")).toBe("box");
		});

		it("shows the name", () => {
			const wrapper = mount();
			expect(wrapper.find(".k-block-name").text()).toBe("Heading");
			expect(wrapper.find(".k-block-label").exists()).toBe(false);
		});

		it("shows the label as name without name", () => {
			const wrapper = mount({ fieldset: { label: "Intro" } });
			expect(wrapper.find(".k-block-name").text()).toBe("Intro");
			expect(wrapper.find(".k-block-label").exists()).toBe(false);
		});

		it("hides a label that equals the name", () => {
			const wrapper = mount({
				fieldset: { label: "Heading", name: "Heading" }
			});
			expect(wrapper.find(".k-block-label").exists()).toBe(false);
		});

		it("shows nothing without name and label", () => {
			const wrapper = mount({ fieldset: {} });
			expect(wrapper.find(".k-block-title-text").text()).toBe("");
		});
	});
});
