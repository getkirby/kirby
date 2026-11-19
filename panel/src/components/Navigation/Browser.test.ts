import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Scrollable from "@/components/Layout/Scrollable.vue";
import Browser from "./Browser.vue";

const ItemImage = {
	name: "k-item-image",
	props: ["image"],
	template: "<span />"
};

const items = [
	{ image: { src: "a.jpg" }, label: "a.jpg", value: "file://a" },
	{ label: "b.pdf", value: "file://b" }
];

function mount(props = {}, attrs = {}) {
	return vueMount(Browser, {
		props: { items, ...props },
		attrs,
		global: {
			components: { "k-scrollable": Scrollable },
			stubs: { "k-item-image": ItemImage }
		}
	});
}

function inputs(wrapper: ReturnType<typeof mount>) {
	return wrapper.findAll("input");
}

describe("k-browser", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "NAV", "k-browser");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);

		it("is scrollable", () => {
			expect(mount().classes()).toContain("k-scrollable");
		});
	});

	// props
	describe("items prop", () => {
		it("renders an option per item", () => {
			const wrapper = mount();
			expect(
				wrapper.findAll(".k-browser-item-info").map((info) => info.text())
			).toStrictEqual(["a.jpg", "b.pdf"]);
		});

		it("shows the image of an item as cover", () => {
			const wrapper = mount();
			const images = wrapper.findAllComponents(ItemImage);
			expect(images).toHaveLength(1);
			expect(images[0].classes()).toContain("k-browser-item-image");
			expect(images[0].props("image")).toStrictEqual({
				back: "black",
				cover: true,
				src: "a.jpg"
			});
		});
	});

	describe("name prop", () => {
		it("names the options items by default", () => {
			expect(inputs(mount())[0].attributes("name")).toBe("items");
		});

		it("names the options", () => {
			expect(inputs(mount({ name: "files" }))[0].attributes("name")).toBe(
				"files"
			);
		});
	});

	describe("selected prop", () => {
		it("checks the selected item", () => {
			const wrapper = mount({ selected: "file://b" });
			expect(
				inputs(wrapper).map(
					(input) => (input.element as HTMLInputElement).checked
				)
			).toStrictEqual([false, true]);
			expect(
				wrapper
					.findAll("label")
					.map((label) => label.attributes("aria-selected"))
			).toStrictEqual(["false", "true"]);
		});
	});

	describe("type prop", () => {
		it("renders radio buttons by default", () => {
			expect(inputs(mount())[0].attributes("type")).toBe("radio");
		});

		it("renders checkboxes", () => {
			const wrapper = mount({ type: "checkbox" });
			expect(inputs(wrapper)[0].attributes("type")).toBe("checkbox");
		});
	});

	// events
	describe("events", () => {
		it("emits the selected item", async () => {
			const wrapper = mount();
			await inputs(wrapper)[1].trigger("change");
			expect(wrapper.emitted("select")).toStrictEqual([[items[1]]]);
		});
	});
});
