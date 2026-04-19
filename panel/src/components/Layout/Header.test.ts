import { describe, it, expect } from "@test/unit";
import { mount } from "@vue/test-utils";
import Header from "./Header.vue";

describe("Header.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(Header, "HEADER", "k-header");
		it.acceptsClass(Header);
		it.acceptsStyle(Header);
	});

	// props
	describe("editable prop", () => {
		it("defaults to false", () => {
			const wrapper = mount(Header);
			expect(wrapper.attributes("data-editable")).toBe("false");
			expect(wrapper.find("button.k-header-title-button").exists()).toBe(false);
		});

		it("renders value as attribute and a button", () => {
			const wrapper = mount(Header, { props: { editable: true } });
			expect(wrapper.attributes("data-editable")).toBe("true");
			expect(wrapper.find("button.k-header-title-button").exists()).toBe(true);
		});
	});

	// events
	describe("edit event", () => {
		it("emits edit when the title button is clicked", async () => {
			const wrapper = mount(Header, { props: { editable: true } });
			await wrapper.find("button.k-header-title-button").trigger("click");
			expect(wrapper.emitted("edit")).toHaveLength(1);
		});
	});

	// slots
	describe("default slot", () => {
		it("renders slotted content", () => {
			const wrapper = mount(Header, { slots: { default: "My Title" } });
			expect(wrapper.find(".k-header-title").text()).toContain("My Title");
		});
	});

	describe("buttons slot", () => {
		it("renders buttons area when provided", () => {
			const wrapper = mount(Header, {
				slots: { buttons: "<button>Save</button>" }
			});
			expect(wrapper.find(".k-header-buttons").exists()).toBe(true);
		});

		it("does not render buttons area when not provided", () => {
			const wrapper = mount(Header);
			expect(wrapper.find(".k-header-buttons").exists()).toBe(false);
		});
	});
});
