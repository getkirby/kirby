import { describe, expect, it, vi } from "vitest";
import { mount } from "@vue/test-utils";
import preserveListeners from "./preserveListeners";

const ComponentWithoutInherit = {
	mixins: [preserveListeners],
	inheritAttrs: false,
	template: "<div />"
};

describe("preserveListeners", () => {
	describe("when inheritAttrs is false", () => {
		it("attaches event listeners to the root element", async () => {
			const handler = vi.fn();
			const wrapper = mount(ComponentWithoutInherit, {
				attrs: { onClick: handler }
			});
			await wrapper.trigger("click");
			expect(handler).toHaveBeenCalledOnce();
		});

		it("attaches multiple listeners for the same event", async () => {
			const handlerA = vi.fn();
			const handlerB = vi.fn();
			const wrapper = mount(ComponentWithoutInherit, {
				attrs: { onClick: [handlerA, handlerB] }
			});
			await wrapper.trigger("click");
			expect(handlerA).toHaveBeenCalledOnce();
			expect(handlerB).toHaveBeenCalledOnce();
		});

		it("does not attach non-event attributes as listeners", async () => {
			const wrapper = mount(ComponentWithoutInherit, {
				attrs: { "data-foo": "bar" }
			});
			expect(wrapper.attributes("data-foo")).toBeUndefined();
		});

		it("removes listeners when unmounted", async () => {
			const handler = vi.fn();
			const wrapper = mount(ComponentWithoutInherit, {
				attrs: { onClick: handler }
			});
			wrapper.unmount();
			wrapper.element.dispatchEvent(new Event("click"));
			expect(handler).not.toHaveBeenCalled();
		});
	});
});
