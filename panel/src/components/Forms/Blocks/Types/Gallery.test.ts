import { describe, expect, it, onTestFinished } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Gallery from "./Gallery.vue";

const Background = {
	name: "k-block-background-dropdown",
	props: ["value"],
	emits: ["input"],
	template: "<li />"
};
const Caption = {
	name: "k-block-figure-caption",
	props: ["disabled", "marks", "value"],
	emits: ["input"],
	template: "<figcaption />"
};
const ImageFrame = {
	name: "k-image-frame",
	props: ["cover", "file", "ratio"],
	template: "<div />"
};

const images = [
	{ id: "a", url: "/media/a.jpg" },
	{ id: "b", url: "/media/b.jpg" }
];
const storage = "kirby.galleryBlock.pages/a/fields/blocks.b1";

function mount(props = {}, attrs = {}) {
	return vueMount(Gallery, {
		props: {
			content: { images },
			endpoints: { field: "pages/a/fields/blocks" },
			id: "b1",
			...props
		},
		attrs,
		global: {
			stubs: {
				"k-block-background-dropdown": Background,
				"k-block-figure-caption": Caption,
				"k-image-frame": ImageFrame
			}
		}
	});
}

function frames(wrapper: ReturnType<typeof mount>) {
	return wrapper.findAllComponents(ImageFrame);
}

describe("k-block-type-gallery", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "FIGURE", "k-block-type-gallery-figure");
		it.inheritsNoAttrs(mount);

		it("sets the background", () => {
			const figure = mount().element as HTMLElement;
			expect(figure.style.getPropertyValue("--block-back")).toBe(
				"var(--block-color-back)"
			);
		});
	});

	// props
	describe("content prop", () => {
		it("shows the images in squares", () => {
			const wrapper = mount();
			expect(wrapper.attributes("data-empty")).toBe("false");
			expect(frames(wrapper).map((frame) => frame.props())).toStrictEqual([
				{ cover: undefined, file: images[0], ratio: "1/1" },
				{ cover: undefined, file: images[1], ratio: "1/1" }
			]);
			expect(wrapper.findComponent(Background).exists()).toBe(true);
		});

		it("crops the images to the ratio", () => {
			const wrapper = mount({ content: { crop: true, images, ratio: "4/3" } });
			expect(frames(wrapper)[0].props()).toMatchObject({
				cover: true,
				ratio: "4/3"
			});
		});

		it("shows placeholders without images", () => {
			const wrapper = mount({ content: { images: [], ratio: "4/3" } });
			expect(wrapper.attributes("data-empty")).toBe("true");
			expect(wrapper.findAll(".k-block-type-gallery-placeholder")).toHaveLength(
				3
			);
			expect(frames(wrapper)[0].props()).toMatchObject({ ratio: "4/3" });
			expect(wrapper.findComponent(Background).exists()).toBe(false);
		});

		it("shows the caption", () => {
			const wrapper = mount({
				content: { caption: "Hello", images },
				disabled: true,
				fieldset: {
					tabs: { content: { fields: { caption: { marks: ["bold"] } } } }
				}
			});
			expect(wrapper.findComponent(Caption).props()).toStrictEqual({
				disabled: true,
				marks: ["bold"],
				value: "Hello"
			});
		});

		it("shows no caption without value", () => {
			expect(mount().findComponent(Caption).exists()).toBe(false);
		});
	});

	// data
	describe("back data", () => {
		it("restores the background from the session", () => {
			sessionStorage.setItem(storage, "var(--pattern)");
			onTestFinished(() => sessionStorage.removeItem(storage));
			const figure = mount().element as HTMLElement;
			expect(figure.style.getPropertyValue("--block-back")).toBe(
				"var(--pattern)"
			);
		});
	});

	// methods
	describe("onBack()", () => {
		it("stores the background for the session", async () => {
			onTestFinished(() => sessionStorage.removeItem(storage));
			const wrapper = mount();
			await wrapper.findComponent(Background).vm.$emit("input", "black");
			expect(sessionStorage.getItem(storage)).toBe("black");
			expect(wrapper.findComponent(Background).props("value")).toBe("black");
		});
	});

	// events
	describe("events", () => {
		it("emits open on double click of the images", async () => {
			const wrapper = mount();
			await wrapper.find("ul").trigger("dblclick");
			expect(wrapper.emitted("open")).toHaveLength(1);
		});

		it("updates the caption", () => {
			const wrapper = mount({ content: { caption: "Hello", images } });
			wrapper.findComponent(Caption).vm.$emit("input", "Hi");
			expect(wrapper.emitted("update")).toStrictEqual([[{ caption: "Hi" }]]);
		});
	});
});
