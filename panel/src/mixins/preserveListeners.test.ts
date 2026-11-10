import { describe, expect, it, vi } from "vitest";
import { mount } from "@vue/test-utils";
import preserveListeners from "./preserveListeners";

const ComponentWithoutInherit = {
	mixins: [preserveListeners],
	inheritAttrs: false,
	template: "<div />"
};

const ComponentForwardingAttrs = {
	mixins: [preserveListeners],
	inheritAttrs: false,
	preserveListeners: false,
	template: '<div><input v-bind="$attrs" /></div>'
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

		it("passes the event to the listener", async () => {
			const handler = vi.fn();
			const wrapper = mount(ComponentWithoutInherit, {
				attrs: { onClick: handler }
			});
			await wrapper.trigger("click");
			expect(handler.mock.calls[0][0]).toBeInstanceOf(Event);
		});

		it("calls the latest listener after a re-render", async () => {
			const before = vi.fn();
			const after = vi.fn();
			const wrapper = mount(ComponentWithoutInherit, {
				attrs: { onClick: before }
			});
			await wrapper.setProps({ onClick: after } as object);
			await wrapper.trigger("click");
			expect(before).not.toHaveBeenCalled();
			expect(after).toHaveBeenCalledOnce();
		});

		it("does not call a listener that has been removed", async () => {
			const handler = vi.fn();
			const wrapper = mount(ComponentWithoutInherit, {
				attrs: { onClick: handler }
			});
			await wrapper.setProps({ onClick: undefined } as object);
			await wrapper.trigger("click");
			expect(handler).not.toHaveBeenCalled();
		});

		it("does not attach non-event attributes as listeners", async () => {
			const handler = vi.fn();
			const wrapper = mount(ComponentWithoutInherit, {
				attrs: { "data-foo": handler }
			});
			await wrapper.trigger("click");
			expect(handler).not.toHaveBeenCalled();
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

	describe("when preserveListeners is false", () => {
		it("leaves the listeners to the component", async () => {
			const handler = vi.fn();
			const wrapper = mount(ComponentForwardingAttrs, {
				attrs: { onKeydown: handler }
			});
			await wrapper.find("input").trigger("keydown");
			expect(handler).toHaveBeenCalledOnce();
		});
	});
});
