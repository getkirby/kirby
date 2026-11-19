import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import UploadItemPreview from "./UploadItemPreview.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(UploadItemPreview, {
		props: { type: "image/png", url: "blob:a", ...props },
		attrs
	});
}

describe("k-upload-item-preview", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "A", "k-upload-item-preview");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);

		it("opens the upload in a new window", () => {
			const wrapper = mount();
			expect(wrapper.attributes()).toMatchObject({
				"aria-label": "open.newWindow",
				href: "blob:a",
				target: "_blank"
			});
		});
	});

	// props
	describe("type prop", () => {
		it.each([
			"image/jpeg",
			"image/jpg",
			"image/gif",
			"image/png",
			"image/webp",
			"image/avif",
			"image/svg+xml"
		])("previews a %s image", (type) => {
			const image = mount({ type }).find("k-image-frame");
			expect(image.attributes()).toMatchObject({
				back: "pattern",
				cover: "true",
				src: "blob:a"
			});
		});

		it.each([
			["image/heic", "image", "orange-500"],
			["audio/mpeg", "audio", "aqua-500"],
			["video/mp4", "video", "yellow-500"],
			["application/pdf", "file", "white"]
		])("shows an icon for %s", (type, icon, color) => {
			const frame = mount({ type }).find("k-icon-frame");
			expect(mount({ type }).find("k-image-frame").exists()).toBe(false);
			expect(frame.attributes()).toMatchObject({
				back: "black",
				color,
				icon,
				ratio: "1/1"
			});
		});

		it("shows a file icon without type", () => {
			const frame = mount({ type: undefined }).find("k-icon-frame");
			expect(frame.attributes("icon")).toBe("file");
		});
	});

	describe("back prop", () => {
		it("sets the back of the image", () => {
			const wrapper = mount({ back: "white" });
			expect(wrapper.find("k-image-frame").attributes("back")).toBe("white");
		});

		it("sets the back of the icon", () => {
			const wrapper = mount({ back: "white", type: "text/plain" });
			expect(wrapper.find("k-icon-frame").attributes("back")).toBe("white");
		});
	});

	describe("color prop", () => {
		it("replaces the color of the icon", () => {
			const wrapper = mount({ color: "blue-500", type: "text/plain" });
			expect(wrapper.find("k-icon-frame").attributes("color")).toBe("blue-500");
		});
	});

	describe("cover prop", () => {
		it("fits the image", () => {
			const wrapper = mount({ cover: false });
			expect(wrapper.find("k-image-frame").attributes("cover")).toBe("false");
		});
	});

	describe("icon prop", () => {
		it("replaces the icon", () => {
			const wrapper = mount({ icon: "document", type: "text/plain" });
			expect(wrapper.find("k-icon-frame").attributes("icon")).toBe("document");
		});
	});
});
