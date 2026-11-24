import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import ErrorView from "./ErrorView.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(ErrorView, {
		props: { error: "Not found", layout: "inside", ...props },
		attrs
	});
}

describe("k-error-view", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-PANEL-INSIDE", "k-error-view");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
	});

	// props
	describe("error prop", () => {
		it("shows the error in a negative box", () => {
			const box = mount().find("k-box");
			expect(box.attributes()).toMatchObject({
				icon: "alert",
				theme: "negative"
			});
			expect(box.text()).toBe("Not found");
		});
	});

	describe("layout prop", () => {
		it("shows a header inside the Panel", () => {
			const wrapper = mount();
			expect(wrapper.find("k-header").text()).toBe("error");
		});

		it("shows only the error outside the Panel", () => {
			const wrapper = mount({ layout: "outside" });
			expect(wrapper.element.tagName).toBe("K-PANEL-OUTSIDE");
			expect(wrapper.find("k-header").exists()).toBe(false);
			expect(wrapper.find("div > k-box").text()).toBe("Not found");
		});
	});
});
