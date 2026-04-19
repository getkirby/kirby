import { describe, it, expect } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import PrevNext from "./PrevNext.vue";

function mount(attrs = {}) {
	return vueMount(PrevNext, {
		props: { prev: { link: "/prev" } },
		attrs
	});
}

describe("PrevNext.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-BUTTON-GROUP", "k-prev-next");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);

		it("does not render when both prev and next are disabled", () => {
			const wrapper = vueMount(PrevNext);
			expect(wrapper.find("k-button-group").exists()).toBe(false);
		});

		it("renders when prev is enabled", () => {
			const wrapper = vueMount(PrevNext, {
				props: { prev: { link: "/prev" } }
			});
			expect(wrapper.find("k-button-group").exists()).toBe(true);
		});

		it("renders when next is enabled", () => {
			const wrapper = vueMount(PrevNext, {
				props: { next: { link: "/next" } }
			});
			expect(wrapper.find("k-button-group").exists()).toBe(true);
		});
	});

	// props
	describe("prev prop", () => {
		it("defaults to a disabled button", () => {
			const wrapper = vueMount(PrevNext, {
				props: { next: { link: "/next" } }
			});
			expect(wrapper.vm.buttons[0].disabled).toBe(true);
		});

		it("passes config to the left button with angle-left icon", () => {
			const wrapper = vueMount(PrevNext, {
				props: { prev: { link: "/prev" } }
			});
			expect(wrapper.vm.buttons[0]).toMatchObject({
				link: "/prev",
				icon: "angle-left"
			});
		});
	});

	describe("next prop", () => {
		it("defaults to a disabled button", () => {
			const wrapper = vueMount(PrevNext, {
				props: { prev: { link: "/prev" } }
			});
			expect(wrapper.vm.buttons[1].disabled).toBe(true);
		});

		it("passes config to the right button with angle-right icon", () => {
			const wrapper = vueMount(PrevNext, {
				props: { next: { link: "/next" } }
			});
			expect(wrapper.vm.buttons[1]).toMatchObject({
				link: "/next",
				icon: "angle-right"
			});
		});
	});
});
