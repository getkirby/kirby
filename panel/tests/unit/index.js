export * from "vitest";
import { it as vitestIt, expect } from "vitest";
import { mount } from "@vue/test-utils";

/**
 * Ensure that the component is mounted,
 * either via the default mount() or a custom callback
 */
function ensureMount(Component, attrs = {}) {
	if (typeof Component === "function") {
		return Component(attrs);
	}

	return mount(Component, { attrs });
}

export const it = Object.assign(vitestIt, {
	acceptsClass(Component) {
		vitestIt("accepts a custom class", () => {
			const wrapper = ensureMount(Component, { class: "my-class" });
			expect(wrapper.classes()).toContain("my-class");
		});
	},
	acceptsStyle(Component) {
		vitestIt("accepts a custom style", () => {
			const wrapper = ensureMount(Component, { style: "--foo: 1" });
			expect(wrapper.attributes("style")).toContain("--foo");
		});
	},
	inheritsNoAttrs(Component) {
		vitestIt("does not inherit random attrs", () => {
			const wrapper = ensureMount(Component, { "data-foo": "bar" });
			expect(wrapper.attributes("data-foo")).toBeUndefined();
		});
	},
	rendersAs(Component, tag, className) {
		vitestIt(`renders a <${tag}> with class ${className}`, () => {
			const wrapper = ensureMount(Component);
			expect(wrapper.element.tagName).toBe(tag);
			expect(wrapper.classes()).toContain(className);
		});
	}
});
