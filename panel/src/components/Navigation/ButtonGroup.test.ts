import { describe, it, expect } from "@test/unit";
import { mount } from "@vue/test-utils";
import ButtonGroup from "./ButtonGroup.vue";

const buttons = [{ text: "Edit" }, { text: "Delete" }];

describe("ButtonGroup.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(ButtonGroup, "DIV", "k-button-group");
		it.acceptsClass(ButtonGroup);
		it.acceptsStyle(ButtonGroup);
	});

	// props
	describe("buttons prop", () => {
		it("renders a k-button for each item", () => {
			const wrapper = mount(ButtonGroup, { props: { buttons } });
			expect(wrapper.findAll("k-button")).toHaveLength(2);
		});

		it("passes button object data to each k-button", () => {
			const wrapper = mount(ButtonGroup, {
				props: { buttons: [{ text: "Edit", icon: "edit" }] }
			});
			const button = wrapper.find("k-button");
			expect(button.attributes("text")).toBe("Edit");
			expect(button.attributes("icon")).toBe("edit");
		});
	});

	describe("layout prop", () => {
		it("renders as data-layout attribute", () => {
			const wrapper = mount(ButtonGroup, { props: { layout: "collapsed" } });
			expect(wrapper.attributes("data-layout")).toBe("collapsed");
		});
	});

	describe("responsive prop", () => {
		it("passes to each k-button", () => {
			const wrapper = mount(ButtonGroup, {
				props: { buttons, responsive: true }
			});
			for (const button of wrapper.findAll("k-button")) {
				expect(button.attributes("responsive")).toBe("true");
			}
		});

		it("is overridden by individual button config", () => {
			const wrapper = mount(ButtonGroup, {
				props: {
					responsive: true,
					buttons: [{ text: "Edit", responsive: false }]
				}
			});
			expect(wrapper.find("k-button").attributes("responsive")).toBe("false");
		});
	});

	describe("size prop", () => {
		it("passes to each k-button", () => {
			const wrapper = mount(ButtonGroup, { props: { buttons, size: "sm" } });
			for (const button of wrapper.findAll("k-button")) {
				expect(button.attributes("size")).toBe("sm");
			}
		});

		it("is overridden by individual button config", () => {
			const wrapper = mount(ButtonGroup, {
				props: { size: "sm", buttons: [{ text: "Edit", size: "xs" }] }
			});
			expect(wrapper.find("k-button").attributes("size")).toBe("xs");
		});
	});

	describe("theme prop", () => {
		it("passes to each k-button", () => {
			const wrapper = mount(ButtonGroup, {
				props: { buttons, theme: "positive" }
			});
			for (const button of wrapper.findAll("k-button")) {
				expect(button.attributes("theme")).toBe("positive");
			}
		});

		it("is overridden by individual button config", () => {
			const wrapper = mount(ButtonGroup, {
				props: {
					theme: "positive",
					buttons: [{ text: "Edit", theme: "negative" }]
				}
			});
			expect(wrapper.find("k-button").attributes("theme")).toBe("negative");
		});
	});

	describe("variant prop", () => {
		it("passes to each k-button", () => {
			const wrapper = mount(ButtonGroup, {
				props: { buttons, variant: "filled" }
			});
			for (const button of wrapper.findAll("k-button")) {
				expect(button.attributes("variant")).toBe("filled");
			}
		});

		it("is overridden by individual button config", () => {
			const wrapper = mount(ButtonGroup, {
				props: {
					variant: "filled",
					buttons: [{ text: "Edit", variant: "dimmed" }]
				}
			});
			expect(wrapper.find("k-button").attributes("variant")).toBe("dimmed");
		});
	});

	// slots
	describe("default slot", () => {
		it("renders slotted content instead of buttons", () => {
			const slot = "<button>Custom</button>";
			const wrapper = mount(ButtonGroup, {
				props: { buttons },
				slots: { default: slot }
			});
			expect(wrapper.element.innerHTML).toBe(slot);
		});
	});
});
