import { describe, it, expect } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import ItemImage from "./ItemImage.vue";

function mount(
	props: Record<string, unknown> = {},
	attrs: Record<string, unknown> = {}
) {
	return vueMount(ItemImage, {
		props: { image: {}, ...props },
		attrs,
		shallow: true
	});
}

describe("ItemImage.vue", () => {
	// $el
	describe("element", () => {
		const component = (attrs?: Record<string, unknown>) => mount({}, attrs);

		it.acceptsClass(component);
		it.acceptsStyle(component);
		it.inheritsNoAttrs(component);
	});

	// computed: component
	describe("component", () => {
		it("renders k-icon-frame when image has no src", () => {
			const wrapper = mount({ image: { icon: "file" } });
			expect(wrapper.element.tagName).toBe("K-ICON-FRAME");
		});

		it("renders k-image-frame when image has a src", () => {
			const wrapper = mount({ image: { src: "/image.jpg" } });
			expect(wrapper.element.tagName).toBe("K-IMAGE-FRAME");
		});
	});

	// computed: attrs
	describe("image prop", () => {
		it("passes through image props as attrs", () => {
			const wrapper = mount({ image: { back: "black" } });
			expect(wrapper.attributes("back")).toBe("black");
		});

		it("sets cover to true by default", () => {
			const wrapper = mount({ image: {} });
			expect(wrapper.attributes("cover")).toBe("true");
		});
	});

	// props
	describe("layout prop", () => {
		it("sets ratio to auto in list layout", () => {
			const wrapper = mount({ image: { ratio: "1/1" }, layout: "list" });
			expect(wrapper.attributes("ratio")).toBe("auto");
		});

		it("uses image ratio in non-list layout", () => {
			const wrapper = mount({ image: { ratio: "16/9" }, layout: "cards" });
			expect(wrapper.attributes("ratio")).toBe("16/9");
		});
	});
});
