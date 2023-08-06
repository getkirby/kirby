import { describe, expect, it } from "vitest";
import { mount } from "@vue/test-utils";
import Code from "./Code.vue";

describe("Code.vue", () => {
	it("has default slot", async () => {
		const content = 'console.log("Hello, world!");';
		const wrapper = mount(Code, {
			slots: {
				default: content
			}
		});

		expect(wrapper.text()).toContain(content);
	});

	it("has CSS selector", async () => {
		const wrapper = mount(Code);
		expect(wrapper.classes()).toContain("k-code");
	});

	it("matches snapshot", async () => {
		const wrapper = mount(Code, {
			slots: {
				default: "This is some code"
			}
		});

		expect(wrapper.element).toMatchSnapshot();
	});
});
