import { describe, it, expect, vi } from "@test/unit";
import { mount } from "@vue/test-utils";
import ViewButton from "./Button.vue";

describe("ViewButton.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(ViewButton, "DIV", "k-view-button");
		it.acceptsClass(ViewButton);
		it.acceptsStyle(ViewButton);
	});

	// props
	describe("options prop", () => {
		it("renders a dropdown for an options array", () => {
			const wrapper = mount(ViewButton, {
				props: { options: [{ text: "Option A" }] }
			});
			expect(wrapper.find("k-dropdown").exists()).toBe(true);
			expect(wrapper.find("k-button").attributes("dropdown")).toBe("true");
		});

		it("renders no dropdown without options", () => {
			const wrapper = mount(ViewButton);
			expect(wrapper.find("k-dropdown").exists()).toBe(false);
			expect(wrapper.find("k-button").attributes("dropdown")).toBe("false");
		});
	});

	// events
	describe("click event", () => {
		it("emits click without dropdown", async () => {
			const wrapper = mount(ViewButton);
			await wrapper.find("k-button").trigger("click");
			expect(wrapper.emitted("click")).toHaveLength(1);
		});

		it("toggles the dropdown instead", async () => {
			const toggle = vi.fn();
			const wrapper = mount(ViewButton, {
				props: { options: [{ text: "Option A" }] },
				global: {
					components: {
						"k-dropdown": { template: "<div />", methods: { toggle } }
					}
				}
			});
			await wrapper.find("k-button").trigger("click");
			expect(toggle).toHaveBeenCalled();
			expect(wrapper.emitted("click")).toBeUndefined();
		});
	});
});
