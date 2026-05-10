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

	// computed: sizes
	describe("width prop", () => {
		it("defaults to 1/1 sizes", () => {
			const wrapper = mount();
			expect(wrapper.attributes("size")).toContain("88em");
		});

		it("sets sizes for 1/2 width", () => {
			const wrapper = mount({ width: "1/2" });
			expect(wrapper.attributes("size")).toContain("44em");
		});

		it("uses the same sizes for 2/4 as 1/2", () => {
			const wrapper12 = mount({ width: "1/2" });
			const wrapper24 = mount({ width: "2/4" });
			expect(wrapper12.attributes("size")).toBe(wrapper24.attributes("size"));
		});

		it("sets sizes for 1/3 width", () => {
			const wrapper = mount({ width: "1/3" });
			expect(wrapper.attributes("size")).toContain("29.333em");
		});

		it("sets sizes for 1/4 width", () => {
			const wrapper = mount({ width: "1/4" });
			expect(wrapper.attributes("size")).toContain("22em");
		});

		it("sets sizes for 2/3 width", () => {
			const wrapper = mount({ width: "2/3" });
			expect(wrapper.attributes("size")).toContain("27em");
		});

		it("sets sizes for 3/4 width", () => {
			const wrapper = mount({ width: "3/4" });
			expect(wrapper.attributes("size")).toContain("66em");
		});
	});
});
