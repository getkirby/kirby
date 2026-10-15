import { describe, it, expect, vi } from "@test/unit";
import { flushPromises, mount } from "@vue/test-utils";
import Button from "@/components/Navigation/Button.vue";
import ViewButton from "./Button.vue";

const Dropdown = {
	props: ["options"],
	emits: ["action"],
	render: () => null
};

describe("k-view-button", () => {
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

		it("passes an options array on as-is", () => {
			const $dropdown = vi.fn();
			const options = [{ text: "Option A" }];
			const wrapper = mount(ViewButton, {
				props: { options },
				global: {
					components: { "k-dropdown": Dropdown },
					mocks: { $dropdown }
				}
			});
			expect(wrapper.findComponent(Dropdown).props("options")).toStrictEqual(
				options
			);
			expect($dropdown).not.toHaveBeenCalled();
		});

		it("loads string options via $dropdown", () => {
			const loader = vi.fn();
			const $dropdown = vi.fn(() => loader);
			const wrapper = mount(ViewButton, {
				props: { options: "pages/test/buttons/open" },
				global: {
					components: { "k-dropdown": Dropdown },
					mocks: { $dropdown }
				}
			});
			expect($dropdown).toHaveBeenCalledWith("pages/test/buttons/open");
			expect(wrapper.findComponent(Dropdown).props("options")).toBe(loader);
		});
	});

	// events
	describe("action event", () => {
		it("forwards the dropdown action", () => {
			const wrapper = mount(ViewButton, {
				props: { options: [{ text: "Option A" }] },
				global: { components: { "k-dropdown": Dropdown } }
			});
			wrapper.findComponent(Dropdown).vm.$emit("action", "edit");
			expect(wrapper.emitted("action")).toStrictEqual([["edit"]]);
		});
	});

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

	describe("success event", () => {
		it("passes on the success event of the button", async () => {
			const wrapper = mount(ViewButton, {
				props: { request: () => "done" },
				global: { components: { "k-button": Button } }
			});
			await wrapper.find("button").trigger("click");
			await flushPromises();
			expect(wrapper.emitted("success")).toStrictEqual([["done"]]);
		});
	});
});
