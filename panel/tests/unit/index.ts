export * from "vitest";
import { it as vitestIt, expect } from "vitest";
import { mount, type VueWrapper, type DOMWrapper } from "@vue/test-utils";
import type { Component } from "vue";

type Wrapper = VueWrapper | DOMWrapper<Element>;
type MountCallback = (attrs?: Record<string, unknown>) => Wrapper;
type Mountable = Component | MountCallback;

/**
 * Ensure that the component is mounted,
 * either via the default mount() or a custom callback
 */
function ensureMount(
	Component: Mountable,
	attrs: Record<string, unknown> = {}
): Wrapper {
	if (typeof Component === "function") {
		return (Component as MountCallback)(attrs);
	}

	return mount(Component, { attrs });
}

export const it = Object.assign(vitestIt, {
	acceptsClass(Component: Mountable) {
		vitestIt("accepts a custom class", () => {
			const wrapper = ensureMount(Component, { class: "my-class" });
			expect(wrapper.classes()).toContain("my-class");
		});
	},
	acceptsStyle(Component: Mountable) {
		vitestIt("accepts a custom style", () => {
			const wrapper = ensureMount(Component, { style: "--foo: 1" });
			expect(wrapper.attributes("style")).toContain("--foo");
		});
	},
	inheritsNoAttrs(Component: Mountable) {
		vitestIt("does not inherit random attrs", () => {
			const wrapper = ensureMount(Component, { "data-foo": "bar" });
			expect(wrapper.attributes("data-foo")).toBeUndefined();
		});
	},
	rendersAs(Component: Mountable, tag: string, className?: string) {
		vitestIt(`renders a <${tag}>${className ? ` with class ${className}` : ""}`, () => {
			const wrapper = ensureMount(Component);
			expect(wrapper.element.tagName).toBe(tag);

			if (className) {
				expect(wrapper.classes()).toContain(className);
			}
		});
	}
});
