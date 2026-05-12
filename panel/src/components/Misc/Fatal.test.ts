import { describe, it, expect, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Fatal from "./Fatal.vue";

function mount(props = {}, attrs = {}, close = vi.fn()) {
	return vueMount(Fatal, {
		props,
		attrs,
		attachTo: document.body,
		global: {
			mocks: {
				$panel: { notification: { close } }
			}
		}
	});
}

describe("Fatal.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-OVERLAY", "k-fatal");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);

		it("sets visible on the overlay", () => {
			const wrapper = mount();
			expect(wrapper.attributes("visible")).toBe("true");
		});
	});

	// notification
	describe("notification", () => {
		it("renders the error message", () => {
			const wrapper = mount();
			expect(wrapper.find(".k-notification p").text()).toBe(
				"The JSON response could not be parsed"
			);
		});

		it("uses the negative theme", () => {
			const wrapper = mount();
			expect(wrapper.find(".k-notification").attributes("data-theme")).toBe(
				"negative"
			);
		});

		it("renders a close button with cancel icon", () => {
			const wrapper = mount();
			expect(wrapper.find("k-button").attributes("icon")).toBe("cancel");
		});

		it("calls $panel.notification.close() when the button is clicked", async () => {
			const close = vi.fn();
			const wrapper = mount({}, {}, close);
			await wrapper.find("k-button").trigger("click");
			expect(close).toHaveBeenCalledTimes(1);
		});
	});

	// iframe / html prop
	describe("html prop", () => {
		it("writes the html into the iframe document on mount", () => {
			const wrapper = mount({ html: "<h1>Boom</h1>" });
			const iframe = wrapper.find(".k-fatal-iframe")
				.element as HTMLIFrameElement;
			expect(iframe.contentDocument?.body.innerHTML).toContain("<h1>Boom</h1>");
		});

		it("does not throw when html is undefined", () => {
			expect(() => mount()).not.toThrow();
		});
	});
});
