import { describe, expect, it, onTestFinished } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import html from "@/panel/html";
import Item from "./Item.vue";

const OptionsDropdown = {
	props: ["options"],
	emits: ["option"],
	render: () => null
};

function mount(props = {}, attrs = {}) {
	return vueMount(Item, {
		props,
		attrs,
		global: { components: { "k-options-dropdown": OptionsDropdown } }
	});
}

const component = (attrs = {}) => mount({}, attrs);

describe("k-item", () => {
	// $el
	describe("element", () => {
		it.rendersAs(component, "DIV", "k-item");
		it.acceptsClass(component);
		it.acceptsStyle(component);
		it.inheritsNoAttrs(component);
	});

	// props
	describe("info prop", () => {
		it("is not rendered when unset", () => {
			const wrapper = mount({ text: "Kirby" });
			expect(wrapper.find(".k-item-info").exists()).toBe(false);
		});

		it("renders the info text", () => {
			const wrapper = mount({ text: "Kirby", info: "Details" });
			expect(wrapper.find(".k-item-info").text()).toBe("Details");
		});

		it("gets a tooltip of its own, not the one of the text", () => {
			const wrapper = mount({ text: "Kirby", info: "Details" });

			expect(wrapper.find(".k-item-title").attributes("title")).toBe("Kirby");
			expect(wrapper.find(".k-item-info").attributes("title")).toBe("Details");
		});
	});

	describe("layout prop", () => {
		it("defaults to list", () => {
			const wrapper = mount();
			expect(wrapper.attributes("data-layout")).toBe("list");
			expect(wrapper.classes()).toContain("k-list-item");
		});

		it("renders value as attribute and class", () => {
			const wrapper = mount({ layout: "cards" });
			expect(wrapper.attributes("data-layout")).toBe("cards");
			expect(wrapper.classes()).toContain("k-cards-item");
		});
	});

	describe("link prop", () => {
		it("wraps the text in a link by default", () => {
			const wrapper = mount({ text: "Kirby", link: "/pages/test" });
			expect(wrapper.find("k-link").attributes("to")).toBe("/pages/test");
		});

		it("drops the link when it is `false`", () => {
			const wrapper = mount({ text: "Kirby", link: false });
			expect(wrapper.find("k-link").exists()).toBe(false);
		});

		it("drops the link while selecting", () => {
			const wrapper = mount({
				text: "Kirby",
				link: "/pages/test",
				selecting: true
			});
			expect(wrapper.find("k-link").exists()).toBe(false);
		});
	});

	describe("options prop", () => {
		it("renders an options dropdown", () => {
			const options = [{ click: "remove", text: "Remove" }];
			const wrapper = mount({ text: "Kirby", options });
			const dropdown = wrapper.findComponent(OptionsDropdown);

			expect(dropdown.props("options")).toStrictEqual(options);
		});

		it("renders no options dropdown without options", () => {
			// buttons render the options bar, but no empty dropdown in it
			const wrapper = mount({ text: "Kirby", buttons: [{ icon: "edit" }] });

			expect(wrapper.find(".k-item-options").exists()).toBe(true);
			expect(wrapper.findComponent(OptionsDropdown).exists()).toBe(false);
		});

		it("hides the options dropdown while selecting", () => {
			const wrapper = mount({
				text: "Kirby",
				options: [{ click: "remove", text: "Remove" }],
				selecting: true
			});
			expect(wrapper.findComponent(OptionsDropdown).exists()).toBe(false);
		});
	});

	describe("sortable prop", () => {
		it("renders no sort handle by default", () => {
			const wrapper = mount();
			expect(wrapper.find("k-sort-handle").exists()).toBe(false);
		});

		it("renders a sort handle when true", () => {
			const wrapper = mount({ sortable: true });
			expect(wrapper.find("k-sort-handle").exists()).toBe(true);
		});
	});

	describe("text prop", () => {
		it("escapes a plain string", () => {
			const wrapper = mount({ text: "<b>Hello</b>" });
			expect(wrapper.find(".k-item-title span").html()).toBe(
				"<span>&lt;b&gt;Hello&lt;/b&gt;</span>"
			);
		});

		it("renders trusted HTML as-is", () => {
			const wrapper = mount({ text: html("<b>Hello</b>") });
			expect(wrapper.find(".k-item-title span").html()).toBe(
				"<span><b>Hello</b></span>"
			);
		});

		it("falls back to a non-breaking space", () => {
			const wrapper = mount();
			expect(wrapper.find(".k-item-title span").html()).toBe(
				"<span>&nbsp;</span>"
			);
		});
	});

	// computed
	describe("hasFigure computed", () => {
		it("is false without an image", () => {
			const wrapper = mount({ text: "Kirby" });

			expect(wrapper.attributes("data-has-image")).toBe("false");
			expect(wrapper.find("k-item-image").exists()).toBe(false);
		});

		it("is false for an empty image object", () => {
			const wrapper = mount({ text: "Kirby", image: {} });

			expect(wrapper.attributes("data-has-image")).toBe("false");
			expect(wrapper.find("k-item-image").exists()).toBe(false);
		});

		it("is false when the image is disabled", () => {
			const wrapper = mount({ text: "Kirby", image: false });

			expect(wrapper.attributes("data-has-image")).toBe("false");
			expect(wrapper.find("k-item-image").exists()).toBe(false);
		});

		it("is true for an image with options", () => {
			const wrapper = mount({ text: "Kirby", image: { back: "black" } });

			expect(wrapper.attributes("data-has-image")).toBe("true");
			expect(wrapper.find("k-item-image").exists()).toBe(true);
		});
	});

	// methods
	describe("onClick()", () => {
		it("selects the item instead while selecting", async () => {
			// a checkbox only fires change when it is in the document
			const wrapper = vueMount(Item, {
				props: { text: "Kirby", selecting: true },
				attachTo: document.body
			});
			onTestFinished(() => wrapper.unmount());
			await wrapper.trigger("click");

			expect(wrapper.emitted("select")).toHaveLength(1);
			expect(wrapper.emitted("click")).toBeUndefined();
		});

		it("emits the click event when not selectable", async () => {
			const wrapper = mount({
				text: "Kirby",
				selecting: true,
				selectable: false
			});
			await wrapper.trigger("click");

			expect(wrapper.emitted("click")).toHaveLength(1);
			expect(wrapper.emitted("select")).toBeUndefined();
		});
	});

	describe("onOption()", () => {
		it("emits action and option for a dropdown option", () => {
			const wrapper = mount({
				text: "Kirby",
				options: [{ click: "remove", text: "Remove" }]
			});
			wrapper.findComponent(OptionsDropdown).vm.$emit("option", "remove");

			expect(wrapper.emitted("action")).toStrictEqual([["remove"]]);
			expect(wrapper.emitted("option")).toStrictEqual([["remove"]]);
		});
	});

	// the title reveals text the item truncates, so it always
	// has to match the characters the item actually shows
	describe("title()", () => {
		it("uses a plain string as-is", () => {
			const wrapper = mount({ text: "Tom & Jerry" });
			expect(wrapper.find(".k-item-title").attributes("title")).toBe(
				"Tom & Jerry"
			);
		});

		it("keeps markup a plain string renders as visible text", () => {
			const wrapper = mount({ text: "<b>Hello</b>" });
			expect(wrapper.find(".k-item-title").attributes("title")).toBe(
				"<b>Hello</b>"
			);
		});

		it("strips authored tags from trusted HTML", () => {
			const wrapper = mount({ text: html("<b>Tom &amp; Jerry</b>") });
			expect(wrapper.find(".k-item-title").attributes("title")).toBe(
				"Tom & Jerry"
			);
		});

		it("keeps escaped tags, which render as visible text", () => {
			const wrapper = mount({ text: html("Using &lt;div&gt; tags") });
			expect(wrapper.find(".k-item-title").attributes("title")).toBe(
				"Using <div> tags"
			);
		});

		it("trims surrounding whitespace", () => {
			const wrapper = mount({ text: "  Kirby  " });
			expect(wrapper.find(".k-item-title").attributes("title")).toBe("Kirby");
		});
	});
});
