import { describe, it, expect, vi } from "@test/unit";
import { mount } from "@vue/test-utils";
import Button from "./Button.vue";

function button(attrs: Record<string, unknown> = {}) {
	return mount(Button, { attrs }).find("k-button");
}

describe("ViewButton.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(() => mount(Button), "DIV", "k-view-button");
		it.acceptsClass(button);
		it.acceptsStyle(button);

		it("renders a k-button inside the wrapper", () => {
			const wrapper = mount(Button, { props: { icon: "cog" } });
			expect(wrapper.find("k-button").attributes("icon")).toBe("cog");
		});
	});

	// attrs
	describe("attrs", () => {
		it("passes attrs on to the button", () => {
			const wrapper = button({ "data-foo": "bar", title: "Settings" });
			expect(wrapper.attributes("data-foo")).toBe("bar");
			expect(wrapper.attributes("title")).toBe("Settings");
		});
	});

	// props
	describe("options prop", () => {
		it("renders a dropdown for an options array", () => {
			const wrapper = mount(Button, {
				props: { options: [{ text: "Option A" }] }
			});
			expect(wrapper.find("k-dropdown").exists()).toBe(true);
			expect(wrapper.find("k-button").attributes("dropdown")).toBe("true");
		});

		it("renders a dropdown for an options route", () => {
			const wrapper = mount(Button, {
				props: { options: "account" },
				global: { mocks: { $dropdown: () => [] } }
			});
			expect(wrapper.find("k-dropdown").exists()).toBe(true);
		});

		it("renders no dropdown without options", () => {
			const wrapper = mount(Button);
			expect(wrapper.find("k-dropdown").exists()).toBe(false);
			expect(wrapper.find("k-button").attributes("dropdown")).toBe("false");
		});
	});

	// events
	describe("click event", () => {
		it("emits click when there is no dropdown", async () => {
			const wrapper = mount(Button);
			await wrapper.find("k-button").trigger("click");
			expect(wrapper.emitted("click")).toHaveLength(1);
		});

		it("toggles the dropdown instead of emitting click", async () => {
			const toggle = vi.fn();
			const wrapper = mount(Button, {
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
