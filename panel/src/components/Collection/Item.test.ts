import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import html from "@/panel/html";
import Item from "./Item.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(Item, { props, attrs });
}

const component = (attrs = {}) => mount({}, attrs);

describe("Item.vue", () => {
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
	describe("hasFigure", () => {
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
		it("clicks the selector instead while selecting", async () => {
			const wrapper = mount({ text: "Kirby", selecting: true });
			const selector = wrapper.vm.$refs.selector as HTMLInputElement;
			const click = vi.spyOn(selector, "click");

			await wrapper.trigger("click");

			expect(click).toHaveBeenCalledOnce();
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
		it("emits action and option for the same event", () => {
			const wrapper = mount({ text: "Kirby" });
			wrapper.vm.onOption("remove");

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
