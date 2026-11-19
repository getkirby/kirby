import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import { defineComponent, h } from "vue";
import Tabs from "./Tabs.vue";

/**
 * Stands in for `k-collapsible`, which cannot measure without
 * a layout: hands over the given offset and renders the
 * fallback when the tabs are collapsed
 */
function collapsible(offset = Infinity, collapsed = false) {
	return defineComponent({
		name: "k-collapsible",
		props: { element: { type: String, default: "div" } },
		setup(props, { attrs, slots }) {
			return () =>
				h(props.element, attrs, [
					...(slots.default?.({ offset }) ?? []),
					...(collapsed ? (slots.fallback?.({ offset }) ?? []) : [])
				]);
		}
	});
}

const Button = {
	name: "k-button",
	props: [
		"badge",
		"current",
		"icon",
		"link",
		"name",
		"text",
		"title",
		"variant"
	],
	emits: ["click"],
	template: `<button @click="$emit('click', $event)"><slot /></button>`
};
const Dropdown = {
	name: "k-dropdown",
	props: ["alignX", "options"],
	methods: { toggle: vi.fn() },
	template: "<div />"
};

const tabs = [
	{ label: "Content", link: "/content", name: "content" },
	{ badge: 3, label: "Settings", link: "/settings", name: "settings" },
	{ link: "/seo", name: "seo", text: "SEO" }
];

function mount(props = {}, attrs = {}, stub = collapsible()) {
	return vueMount(Tabs, {
		props: { tab: "settings", tabs, ...props },
		attrs,
		global: {
			stubs: {
				"k-button": Button,
				"k-collapsible": stub,
				"k-dropdown": Dropdown
			}
		}
	});
}

function buttons(wrapper: ReturnType<typeof mount>) {
	return wrapper.findAllComponents(Button);
}

describe("k-tabs", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "NAV", "k-tabs");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);

		it("renders a button per tab", () => {
			const wrapper = mount();
			expect(buttons(wrapper).map((button) => button.text())).toStrictEqual([
				"Content",
				"Settings",
				"SEO"
			]);
			expect(buttons(wrapper)[0].classes()).toContain("k-tabs-button");
			expect(buttons(wrapper)[0].props()).toMatchObject({
				current: false,
				link: "/content",
				title: "Content",
				variant: "dimmed"
			});
		});
	});

	// props
	describe("tab prop", () => {
		it("marks the current tab", () => {
			const wrapper = mount();
			expect(
				buttons(wrapper).map((button) => button.props("current"))
			).toStrictEqual([false, true, false]);
		});

		it("marks the first tab without a matching tab", () => {
			const wrapper = mount({ tab: "unknown" });
			expect(buttons(wrapper)[0].props("current")).toBe(true);
		});
	});

	describe("tabs prop", () => {
		it("renders nothing for a single tab", () => {
			const wrapper = mount({ tabs: [tabs[0]] });
			expect(wrapper.html()).toBe("<!--v-if-->");
		});

		it("names a tab by its label, text or name", () => {
			const wrapper = mount({ tabs: [tabs[0], { name: "files" }] });
			expect(buttons(wrapper).map((button) => button.text())).toStrictEqual([
				"Content",
				"files"
			]);
		});

		it("turns a number or string into a badge", () => {
			const wrapper = mount();
			expect(buttons(wrapper)[1].props("badge")).toStrictEqual({
				text: 3,
				theme: "passive"
			});
		});

		it("keeps the theme of a badge", () => {
			const wrapper = mount({
				tabs: [tabs[0], { badge: { text: "!", theme: "negative" }, name: "a" }]
			});
			expect(buttons(wrapper)[1].props("badge")).toStrictEqual({
				text: "!",
				theme: "negative"
			});
		});

		it("drops a disabled badge", () => {
			const wrapper = mount({ tabs: [tabs[0], { badge: false, name: "a" }] });
			expect(buttons(wrapper)[1].props("badge")).toBeUndefined();
		});
	});

	describe("theme prop", () => {
		it("themes the badges", () => {
			const wrapper = mount({ theme: "notice" });
			expect(buttons(wrapper)[1].props("badge")).toStrictEqual({
				text: 3,
				theme: "notice"
			});
		});
	});

	// methods
	describe("hasCurrent()", () => {
		it("finds the current tab among the hidden ones", () => {
			const wrapper = mount();
			expect(wrapper.vm.hasCurrent(1)).toBe(true);
			expect(wrapper.vm.hasCurrent(2)).toBe(false);
		});
	});

	// slots
	describe("collapsed tabs", () => {
		it("moves the tabs that don't fit into a dropdown", () => {
			const wrapper = mount({}, {}, collapsible(1, true));
			const more = buttons(wrapper)[1];
			expect(buttons(wrapper)).toHaveLength(2);
			expect(more.classes()).toContain("k-tabs-dropdown-button");
			expect(more.props()).toMatchObject({
				current: true,
				icon: "dots",
				title: "more",
				variant: "dimmed"
			});
			expect(
				wrapper
					.findComponent(Dropdown)
					.props("options")
					.map((option: { name: string }) => option.name)
			).toStrictEqual(["settings", "seo"]);
			expect(wrapper.findComponent(Dropdown).props("alignX")).toBe("end");
		});

		it("doesn't mark the dropdown without the current tab in it", () => {
			const wrapper = mount({ tab: "content" }, {}, collapsible(1, true));
			expect(buttons(wrapper)[1].props("current")).toBe(false);
		});

		it("toggles the dropdown", async () => {
			const wrapper = mount({}, {}, collapsible(1, true));
			const toggle = vi.spyOn(wrapper.findComponent(Dropdown).vm, "toggle");
			await buttons(wrapper)[1].trigger("click");
			expect(toggle).toHaveBeenCalledOnce();
		});
	});
});
