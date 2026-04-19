import { describe, it, expect } from "@test/unit";
import { mount } from "@vue/test-utils";
import CodeToken from "./CodeToken.vue";

describe("CodeToken.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(CodeToken, "CODE");
		it.acceptsClass(CodeToken);
		it.acceptsStyle(CodeToken);
	});

	// props
	describe("type prop", () => {
		it("defaults to true", () => {
			const wrapper = mount(CodeToken);
			expect(wrapper.attributes("data-type")).toBe("true");
		});

		it("renders value as lowercased attribute", () => {
			const wrapper = mount(CodeToken, { props: { type: "String" } });
			expect(wrapper.attributes("data-type")).toBe("string");
		});
	});

	// slots
	describe("default slot", () => {
		it("renders slotted content", () => {
			const wrapper = mount(CodeToken, {
				slots: { default: "hello" }
			});
			expect(wrapper.text()).toBe("hello");
		});
	});
});
