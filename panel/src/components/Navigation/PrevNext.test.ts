import { describe, it, expect } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import ButtonGroup from "./ButtonGroup.vue";
import PrevNext from "./PrevNext.vue";

/**
 * Returns the buttons rendered by the real button group
 */
function buttons(props = {}) {
	const wrapper = vueMount(PrevNext, {
		props,
		global: { components: { "k-button-group": ButtonGroup } }
	});

	return wrapper.findAll("k-button").map((button) => ({
		disabled: button.attributes("disabled"),
		icon: button.attributes("icon"),
		link: button.attributes("link")
	}));
}

function mount(attrs = {}) {
	return vueMount(PrevNext, {
		props: { prev: { link: "/prev" } },
		attrs
	});
}

describe("k-prev-next", () => {
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
	describe("next prop", () => {
		it("defaults to a disabled button", () => {
			expect(buttons({ prev: { link: "/prev" } })[1]).toStrictEqual({
				disabled: "true",
				icon: "angle-right",
				link: "#"
			});
		});

		it("passes config to the right button with angle-right icon", () => {
			expect(buttons({ next: { link: "/next" } })[1]).toStrictEqual({
				disabled: undefined,
				icon: "angle-right",
				link: "/next"
			});
		});
	});

	describe("prev prop", () => {
		it("defaults to a disabled button", () => {
			expect(buttons({ next: { link: "/next" } })[0]).toStrictEqual({
				disabled: "true",
				icon: "angle-left",
				link: "#"
			});
		});

		it("passes config to the left button with angle-left icon", () => {
			expect(buttons({ prev: { link: "/prev" } })[0]).toStrictEqual({
				disabled: undefined,
				icon: "angle-left",
				link: "/prev"
			});
		});
	});
});
