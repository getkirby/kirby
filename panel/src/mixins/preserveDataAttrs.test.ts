import { describe, expect, it, vi } from "vitest";
import { mount } from "@vue/test-utils";
import preserveDataAttrs from "./preserveDataAttrs";
import preserveListeners from "./preserveListeners";

const ComponentWithoutInherit = {
	mixins: [preserveDataAttrs],
	inheritAttrs: false,
	template: "<div />"
};

const ComponentWithInherit = {
	mixins: [preserveDataAttrs],
	template: "<div />"
};

describe("preserveDataAttrs", () => {
	describe("when inheritAttrs is false", () => {
		it("applies data- attributes to the root element", () => {
			const wrapper = mount(ComponentWithoutInherit, {
				attrs: { "data-foo": "bar" }
			});
			expect(wrapper.attributes("data-foo")).toBe("bar");
		});

		it("applies multiple data- attributes", () => {
			const wrapper = mount(ComponentWithoutInherit, {
				attrs: { "data-foo": "bar", "data-id": "123" }
			});
			expect(wrapper.attributes("data-foo")).toBe("bar");
			expect(wrapper.attributes("data-id")).toBe("123");
		});

		it("does not apply non-data- attributes", () => {
			const wrapper = mount(ComponentWithoutInherit, {
				attrs: { "aria-label": "test", id: "foo" }
			});
			expect(wrapper.attributes("aria-label")).toBeUndefined();
			expect(wrapper.attributes("id")).toBeUndefined();
		});
	});

	describe("when inheritAttrs is true", () => {
		it("does not interfere with Vue's native attr inheritance", () => {
			const wrapper = mount(ComponentWithInherit, {
				attrs: { "data-foo": "bar" }
			});
			expect(wrapper.attributes("data-foo")).toBe("bar");
		});
	});
});

describe("preserveDataAttrs + preserveListeners combined", () => {
	const ComponentWithBoth = {
		mixins: [preserveDataAttrs, preserveListeners],
		inheritAttrs: false,
		template: "<div />"
	};

	it("applies data- attributes and attaches event listeners", async () => {
		const handler = vi.fn();
		const wrapper = mount(ComponentWithBoth, {
			attrs: { "data-id": "42", onClick: handler }
		});
		expect(wrapper.attributes("data-id")).toBe("42");
		await wrapper.trigger("click");
		expect(handler).toHaveBeenCalledOnce();
	});
});
