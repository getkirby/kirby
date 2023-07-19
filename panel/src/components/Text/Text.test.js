import { describe, expect, it } from "vitest";
import { mount } from "@vue/test-utils";
import Text from "./Text.vue";

describe("Text.vue", () => {
	it("has default slot", async () => {
		const wrapper = mount(Text, {
			slots: {
				default: "Foo"
			}
		});

		expect(wrapper.text()).toBe("Foo");
	});

	it("has attributes", async () => {
		const wrapper = mount(Text, {
			propsData: {
				align: "right",
				size: "small"
			}
		});

		expect(wrapper.attributes("data-align")).toBe("right");
		expect(wrapper.attributes("data-size")).toBe("small");
	});

	it("has CSS selector", async () => {
		const wrapper = mount(Text);
		expect(wrapper.classes()).toContain("k-text");
	});

	it("matches snapshot", async () => {
		const wrapper = mount(Text, {
			propsData: {
				align: "right",
				size: "large"
			},
			slots: {
				default: "This is some text"
			}
		});

		expect(wrapper.element).toMatchSnapshot();
	});
});
