import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import ImageFieldPreview from "./ImageFieldPreview.vue";

const ItemImage = {
	name: "k-item-image",
	props: ["image"],
	template: "<span />"
};

const image = { src: "a.jpg", url: "/media/a.jpg" };

function mount(props = {}, attrs = {}) {
	return vueMount(ImageFieldPreview, {
		props: { value: image, ...props },
		attrs,
		global: {
			stubs: { "k-item-image": ItemImage }
		}
	});
}

describe("k-image-field-preview", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "SPAN", "k-image-field-preview");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	// props
	describe("value prop", () => {
		it("shows the image", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(ItemImage).props("image")).toStrictEqual(
				image
			);
		});

		it("renders nothing without value", () => {
			const wrapper = mount({ value: null });
			expect(wrapper.findComponent(ItemImage).exists()).toBe(false);
		});
	});
});
