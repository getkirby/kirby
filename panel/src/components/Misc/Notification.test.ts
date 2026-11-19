import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import { reactive } from "vue";
import Notification from "./Notification.vue";

const Button = {
	name: "k-button",
	props: ["icon"],
	emits: ["click"],
	template: `<button @click="$emit('click', $event)" />`
};

function panel(notification = {}) {
	return reactive({
		notification: {
			close: vi.fn(),
			isOpen: true,
			message: "Saved",
			theme: "positive",
			...notification
		}
	});
}

function mount(attrs = {}, $panel = panel()) {
	return vueMount(Notification, {
		attrs,
		global: {
			mocks: { $panel },
			stubs: { "k-button": Button }
		}
	});
}

describe("k-notification", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-notification");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);

		it("shows the message in its theme", () => {
			const wrapper = mount();
			expect(wrapper.attributes("data-theme")).toBe("positive");
			expect(wrapper.find("p").text()).toBe("Saved");
			expect(wrapper.findComponent(Button).props("icon")).toBe("cancel");
		});

		it("renders nothing without an open notification", () => {
			const wrapper = mount({}, panel({ isOpen: false }));
			expect(wrapper.html()).toBe("<!--v-if-->");
		});

		it("follows the notification", async () => {
			const $panel = panel({ isOpen: false });
			const wrapper = mount({}, $panel);
			$panel.notification.isOpen = true;
			$panel.notification.message = "Error";
			$panel.notification.theme = "negative";
			await wrapper.vm.$nextTick();
			expect(wrapper.attributes("data-theme")).toBe("negative");
			expect(wrapper.find("p").text()).toBe("Error");
		});
	});

	// events
	describe("events", () => {
		it("closes the notification", async () => {
			const $panel = panel();
			const wrapper = mount({}, $panel);
			await wrapper.findComponent(Button).trigger("click");
			expect($panel.notification.close).toHaveBeenCalledOnce();
		});
	});
});
