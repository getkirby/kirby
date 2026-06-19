import { mount } from "@vue/test-utils";
import { describe, expect, it, vi } from "vitest";
import App from "./app";
import Panel from "./panel";

function render(panel: Panel) {
	return mount(App, { global: { mocks: { $panel: panel } } });
}

describe("panel.app", () => {
	describe("render()", () => {
		it("should render nothing when no view component is set", () => {
			const panel = Panel.create(app);
			const wrapper = render(panel);

			expect(wrapper.find("k-test-view").exists()).toStrictEqual(false);

			wrapper.unmount();
		});

		it("should render the current view component with its props", () => {
			const panel = Panel.create(app);
			panel.view.component = "k-test-view";
			panel.view.props = { foo: "bar" };

			const wrapper = render(panel);

			const view = wrapper.find("k-test-view");
			expect(view.exists()).toStrictEqual(true);
			expect(view.attributes("foo")).toStrictEqual("bar");

			wrapper.unmount();
		});
	});

	describe("created()", () => {
		it("should subscribe to global events and unsubscribe on unmount", () => {
			const panel = Panel.create(app);
			const subscribe = vi.spyOn(panel.events, "subscribe");
			const unsubscribe = vi.spyOn(panel.events, "unsubscribe");

			const wrapper = render(panel);
			expect(subscribe).toHaveBeenCalledOnce();

			wrapper.unmount();
			expect(unsubscribe).toHaveBeenCalledOnce();
		});

		it("should register all created plugins with the instance", () => {
			const panel = Panel.create(app);
			const plugin = vi.fn();
			panel.plugins.created.push(plugin);

			const wrapper = render(panel);

			expect(plugin).toHaveBeenCalledOnce();

			wrapper.unmount();
		});

		it("should open the current location on popstate", () => {
			const panel = Panel.create(app);
			const open = vi.spyOn(panel, "open").mockResolvedValue(undefined);

			const wrapper = render(panel);
			panel.events.emit("popstate");

			expect(open).toHaveBeenCalledWith(window.location.href);

			wrapper.unmount();
		});

		it("should stop dragging on drop", () => {
			const panel = Panel.create(app);
			const stop = vi.spyOn(panel.drag, "stop");

			const wrapper = render(panel);
			panel.events.emit("drop");

			expect(stop).toHaveBeenCalledOnce();

			wrapper.unmount();
		});
	});
});
