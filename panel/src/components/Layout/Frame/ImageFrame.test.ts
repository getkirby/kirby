import { beforeEach, describe, expect, it, vi } from "@test/unit";
import {
	flushPromises,
	mount as vueMount,
	type VueWrapper
} from "@vue/test-utils";
import type { ComponentPublicInstance } from "vue";
import ImageFrame from "./ImageFrame.vue";

type Props = Record<string, unknown>;

const items = vi.fn();

function mount(props: Props = {}, attrs: Props = {}) {
	return vueMount(ImageFrame, {
		props,
		attrs,
		global: {
			mocks: {
				$helper: { items }
			}
		}
	}) as unknown as VueWrapper<ComponentPublicInstance<Props>>;
}

describe("ImageFrame.vue", () => {
	beforeEach(() => {
		items.mockReset();
	});

	// $el
	describe("element", () => {
		const component = (attrs?: Record<string, unknown>) => mount({}, attrs);

		it.rendersAs(component, "K-FRAME", "k-image-frame");
		it.acceptsClass(component);
		it.acceptsStyle(component);
		it.inheritsNoAttrs(component);

		it("passes props to k-frame", () => {
			const wrapper = mount({ ratio: "16/9", theme: "positive" });

			expect(wrapper.attributes("element")).toBe("figure");
			expect(wrapper.attributes("ratio")).toBe("16/9");
			expect(wrapper.attributes("theme")).toBe("positive");
		});
	});

	// props
	describe("alt prop", () => {
		it("renders the alt text", () => {
			const img = mount({ src: "/image.jpg", alt: "A cat" }).find("img");
			expect(img.attributes("alt")).toBe("A cat");
		});

		it("falls back to an empty alt attribute", () => {
			const img = mount({ src: "/image.jpg" }).find("img");
			expect(img.attributes("alt")).toBe("");
		});
	});

	describe("file prop", () => {
		it("resolves the image for a file id", async () => {
			items.mockResolvedValue({
				alt: "A cat",
				image: { src: "/cat.jpg", srcset: "/cat.jpg 1x" }
			});

			const wrapper = mount({ file: "file://cat" });
			await flushPromises();
			const img = wrapper.find("img");

			expect(img.attributes("src")).toBe("/cat.jpg");
			expect(img.attributes("srcset")).toBe("/cat.jpg 1x");
			expect(img.attributes("alt")).toBe("A cat");
		});

		it("requests the file with ratio and cover", async () => {
			mount({ file: "file://cat", ratio: "16/9", cover: true });
			await flushPromises();

			expect(items).toHaveBeenCalledWith("items/files", "file://cat", {
				layout: "auto",
				image: JSON.stringify({ ratio: "16/9", cover: true })
			});
		});

		it("renders no img when the file cannot be resolved", async () => {
			items.mockResolvedValue(undefined);

			const wrapper = mount({ file: "file://cat" });
			await flushPromises();

			expect(wrapper.find("img").exists()).toBe(false);
		});

		it("is overridden by explicit src and alt props", async () => {
			items.mockResolvedValue({
				alt: "A cat",
				image: { src: "/cat.jpg" }
			});

			const wrapper = mount({
				file: "file://cat",
				src: "/dog.jpg",
				alt: "A dog"
			});
			await flushPromises();
			const img = wrapper.find("img");

			expect(img.attributes("src")).toBe("/dog.jpg");
			expect(img.attributes("alt")).toBe("A dog");
		});
	});

	describe("lazy prop", () => {
		it("loads lazily by default", () => {
			const img = mount({ src: "/image.jpg" }).find("img");
			expect(img.attributes("loading")).toBe("lazy");
		});

		it("loads eagerly when false", () => {
			const img = mount({ src: "/image.jpg", lazy: false }).find("img");
			expect(img.attributes("loading")).toBe("eager");
		});
	});

	describe("src prop", () => {
		it("renders an img", () => {
			const img = mount({ src: "/image.jpg" }).find("img");

			expect(img.attributes("src")).toBe("/image.jpg");
			expect(img.attributes("decoding")).toBe("async");
		});

		it("renders no img when unset", () => {
			const wrapper = mount();
			expect(wrapper.find("img").exists()).toBe(false);
		});
	});

	describe("srcset prop", () => {
		it("renders the srcset attribute", () => {
			const img = mount({
				src: "/image.jpg",
				srcset: "/image.jpg 1x, /image@2x.jpg 2x"
			}).find("img");

			expect(img.attributes("srcset")).toBe("/image.jpg 1x, /image@2x.jpg 2x");
		});
	});

	// computed
	describe("resolvedSizes", () => {
		it("lets the browser measure a lazy image", () => {
			const img = mount({ src: "/image.jpg" }).find("img");
			expect(img.attributes("sizes")).toBe("auto");
		});

		it("omits sizes for an eager image", () => {
			const img = mount({ src: "/image.jpg", lazy: false }).find("img");
			expect(img.attributes("sizes")).toBeUndefined();
		});

		it("keeps an explicit sizes prop", () => {
			const img = mount({ src: "/image.jpg", sizes: "50vw" }).find("img");
			expect(img.attributes("sizes")).toBe("50vw");
		});
	});

	// methods
	describe("fetch()", () => {
		it("fetches again when the file changes", async () => {
			items.mockResolvedValueOnce({ image: { src: "/cat.jpg" } });
			items.mockResolvedValueOnce({ image: { src: "/dog.jpg" } });

			const wrapper = mount({ file: "file://cat" });
			await wrapper.setProps({ file: "file://dog" });
			await flushPromises();

			expect(wrapper.find("img").attributes("src")).toBe("/dog.jpg");
		});

		it("drops a response that arrives after the file changed", async () => {
			const cat = Promise.withResolvers();
			items.mockReturnValueOnce(cat.promise);
			items.mockResolvedValueOnce({ image: { src: "/dog.jpg" } });

			const wrapper = mount({ file: "file://cat" });
			await wrapper.setProps({ file: "file://dog" });
			await flushPromises();

			cat.resolve({ image: { src: "/cat.jpg" } });
			await flushPromises();

			expect(wrapper.find("img").attributes("src")).toBe("/dog.jpg");
		});
	});

	// events
	describe("dragstart event", () => {
		it("prevents dragging the image out of the frame", () => {
			const img = mount({ src: "/image.jpg" }).find("img");
			const event = new Event("dragstart", { cancelable: true });
			img.element.dispatchEvent(event);
			expect(event.defaultPrevented).toBe(true);
		});
	});
});
