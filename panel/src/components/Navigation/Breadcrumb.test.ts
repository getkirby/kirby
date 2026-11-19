import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import { defineComponent, h } from "vue";
import Breadcrumb from "./Breadcrumb.vue";

/**
 * Stands in for `k-collapsible`, which cannot measure without
 * a layout: hands over the given offset and renders the
 * fallback first when the crumbs are collapsed
 */
function collapsible(offset = Infinity, collapsed = false) {
	return defineComponent({
		name: "k-collapsible",
		props: { direction: String, element: String },
		setup(props, { attrs, slots }) {
			return () =>
				h(props.element ?? "div", attrs, [
					...(collapsed ? (slots.fallback?.({ offset, total: 4 }) ?? []) : []),
					...(slots.default?.({ offset }) ?? [])
				]);
		}
	});
}

const Button = {
	name: "k-button",
	props: [
		"current",
		"disabled",
		"icon",
		"link",
		"size",
		"text",
		"title",
		"variant"
	],
	emits: ["click"],
	template: `<button @click="$emit('click', $event)" />`
};
const Dropdown = {
	name: "k-dropdown",
	props: ["options"],
	methods: { toggle: vi.fn() },
	template: "<div />"
};

const crumbs = [
	{ icon: "home", label: "Site", link: "/site" },
	{ label: "Blog", link: "/pages/blog" },
	{ label: "Drafts" },
	{ label: "Article", link: "/pages/blog+article" }
];

function mount(props = {}, attrs = {}, stub = collapsible()) {
	return vueMount(Breadcrumb, {
		props: { crumbs, ...props },
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

describe("k-breadcrumb", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "NAV", "k-breadcrumb");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);

		it("renders the crumbs as list", () => {
			const wrapper = mount();
			expect(wrapper.find("ol").findAll("li")).toHaveLength(4);
			expect(
				buttons(wrapper).map((button) => button.props("text"))
			).toStrictEqual(["Site", "Blog", "Drafts", "Article"]);
		});

		it("collapses from the start", () => {
			const wrapper = mount();
			expect(
				wrapper.findComponent({ name: "k-collapsible" }).props()
			).toMatchObject({ direction: "start", element: "ol" });
		});
	});

	// props
	describe("crumbs prop", () => {
		it("renders each crumb as a small dimmed button", () => {
			const wrapper = mount();
			expect(buttons(wrapper)[1].props()).toStrictEqual({
				current: false,
				disabled: false,
				icon: undefined,
				link: "/pages/blog",
				size: "sm",
				text: "Blog",
				title: "Blog",
				variant: "dimmed"
			});
		});

		it("marks the last crumb as current page", () => {
			const wrapper = mount({}, {}, collapsible(4));
			expect(
				buttons(wrapper).map((button) => button.props("current"))
			).toStrictEqual([false, false, false, "page"]);
		});

		it("disables crumbs without link, click, dialog or drawer", () => {
			const wrapper = mount({
				crumbs: [
					{ label: "A" },
					{ click: () => {}, label: "B" },
					{ dialog: "a", label: "C" },
					{ drawer: "a", label: "D" }
				]
			});
			expect(
				buttons(wrapper).map((button) => button.props("disabled"))
			).toStrictEqual([true, false, false, false]);
		});

		it("shows a loader for loading crumbs", () => {
			const wrapper = mount({
				crumbs: [{ icon: "page", label: "A", loading: true }]
			});
			expect(buttons(wrapper)[0].props("icon")).toBe("loader");
		});

		it("prefers text and title over the label", () => {
			const wrapper = mount({
				crumbs: [{ label: "A", link: "/a", text: "Text", title: "Title" }]
			});
			expect(buttons(wrapper)[0].props()).toMatchObject({
				text: "Text",
				title: "Title"
			});
		});
	});

	describe("label prop", () => {
		it("labels the navigation", () => {
			expect(mount().attributes("aria-label")).toBe("Breadcrumb");
			expect(mount({ label: "Path" }).attributes("aria-label")).toBe("Path");
		});
	});

	// slots
	describe("collapsed crumbs", () => {
		it("keeps the first crumb and moves the hidden ones into a dropdown", () => {
			const wrapper = mount({}, {}, collapsible(1, true));
			expect(
				buttons(wrapper).map((button) => button.props("text"))
			).toStrictEqual(["Site", undefined, "Article"]);
			expect(buttons(wrapper)[1].props("icon")).toBe("dots");
			expect(wrapper.findComponent(Dropdown).props("options")).toStrictEqual([
				{
					disabled: false,
					icon: "angle-right",
					label: "Blog",
					link: "/pages/blog",
					text: "Blog",
					variant: null
				},
				{
					disabled: true,
					icon: "angle-right",
					label: "Drafts",
					text: "Drafts",
					variant: null
				}
			]);
		});

		it("moves all crumbs into the dropdown when none fits", () => {
			const wrapper = mount({ icon: "users" }, {}, collapsible(0, true));
			expect(buttons(wrapper)).toHaveLength(1);
			// the icon of the first crumb wins
			expect(buttons(wrapper)[0].props("icon")).toBe("home");
			expect(
				wrapper
					.findComponent(Dropdown)
					.props("options")
					.map((option: { text: string }) => option.text)
			).toStrictEqual(["Site", "Blog", "Drafts", "Article"]);
		});

		it("falls back to the icon prop when none fits", () => {
			const wrapper = mount(
				{ crumbs: crumbs.slice(1), icon: "users" },
				{},
				collapsible(0, true)
			);
			expect(buttons(wrapper)[0].props("icon")).toBe("users");
		});

		it("toggles the dropdown", async () => {
			const wrapper = mount({}, {}, collapsible(1, true));
			const toggle = vi.spyOn(wrapper.findComponent(Dropdown).vm, "toggle");
			await buttons(wrapper)[1].trigger("click");
			expect(toggle).toHaveBeenCalledOnce();
		});
	});
});
