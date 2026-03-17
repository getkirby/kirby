import { describe, it, expect } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import { hasEmoji } from "@/helpers/string.js";
import Icon from "./Icon.vue";

/**
 * Custom mount which injects a $helper stub
 */
function mount(props = {}, attrs = {}) {
	return vueMount(Icon, {
		props,
		attrs,
		global: {
			mocks: {
				$helper: {
					color: (c: string) => c ?? null,
					string: { hasEmoji }
				}
			}
		}
	});
}

describe("Icon.vue", () => {
	// $el
	describe("element", () => {
		const component = (attrs?: Record<string, unknown>) =>
			mount({ type: "edit" }, attrs);

		it.rendersAs(component, "svg", "k-icon");
		it.acceptsClass(component);
		it.acceptsStyle(component);
	});

	// props
	describe("type prop: icon", () => {
		it("reflects the prop as data-type attribute", () => {
			const wrapper = mount({ type: "edit" });
			expect(wrapper.attributes("data-type")).toBe("edit");
		});

		it("sets the use href to the icon id", () => {
			const wrapper = mount({ type: "edit" });
			expect(wrapper.find("use").attributes("href")).toBe("#icon-edit");
		});
	});

	describe("type prop: emoji", () => {
		it("renders a <span> instead of <svg>", () => {
			const wrapper = mount({ type: "🎉" });
			expect(wrapper.element.tagName).toBe("SPAN");
		});

		it("sets data-type to emoji", () => {
			const wrapper = mount({ type: "🎉" });
			expect(wrapper.attributes("data-type")).toBe("emoji");
		});

		it("renders the emoji as text content", () => {
			const wrapper = mount({ type: "🎉" });
			expect(wrapper.text()).toBe("🎉");
		});
	});

	describe("alt prop", () => {
		it("sets aria-label when alt is provided", () => {
			const wrapper = mount({ type: "edit", alt: "Edit item" });
			expect(wrapper.attributes("aria-label")).toBe("Edit item");
		});

		it("sets role to img when alt is provided", () => {
			const wrapper = mount({ type: "edit", alt: "Edit item" });
			expect(wrapper.attributes("role")).toBe("img");
		});

		it("sets aria-hidden to true when alt is not provided", () => {
			const wrapper = mount({ type: "edit" });
			expect(wrapper.attributes("aria-hidden")).toBe("true");
		});

		it("sets aria-hidden to false when alt is provided", () => {
			const wrapper = mount({ type: "edit", alt: "Edit item" });
			expect(wrapper.attributes("aria-hidden")).toBe("false");
		});
	});

	describe("color prop", () => {
		it("applies color as inline style", () => {
			const wrapper = mount({ type: "edit", color: "red" });
			expect(wrapper.element.style.color).toBe("red");
		});

		it("applies no color style when not provided", () => {
			const wrapper = mount({ type: "edit" });
			expect(wrapper.element.style.color).toBe("");
		});
	});
});
