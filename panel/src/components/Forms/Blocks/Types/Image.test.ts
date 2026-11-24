import { describe, expect, it, onTestFinished } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Image from "./Image.vue";

const Background = {
	name: "k-block-background-dropdown",
	props: ["value"],
	emits: ["input"],
	template: "<div />"
};
const Figure = {
	name: "k-block-figure",
	props: [
		"back",
		"caption",
		"captionMarks",
		"disabled",
		"emptyIcon",
		"emptyText",
		"isEmpty"
	],
	emits: ["open", "update"],
	template: "<figure><slot /></figure>"
};
const ImageFrame = {
	name: "k-image-frame",
	props: ["alt", "cover", "file", "ratio", "src"],
	template: "<div />"
};

const file = "file://a";
const storage = "kirby.imageBlock.pages/a/fields/blocks.b1";

function mount(props = {}, attrs = {}) {
	return vueMount(Image, {
		props: {
			content: { alt: "A", location: "web", src: "https://a.de/a.jpg" },
			endpoints: { field: "pages/a/fields/blocks" },
			id: "b1",
			...props
		},
		attrs,
		global: {
			stubs: {
				"k-block-background-dropdown": Background,
				"k-block-figure": Figure,
				"k-image-frame": ImageFrame
			}
		}
	});
}

function figure(wrapper: ReturnType<typeof mount>) {
	return wrapper.findComponent(Figure);
}

function frame(wrapper: ReturnType<typeof mount>) {
	return wrapper.findComponent(ImageFrame);
}

describe("k-block-type-image", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "FIGURE");
		it.inheritsNoAttrs(mount);

		it("renders a figure with an image placeholder", () => {
			expect(figure(mount()).props()).toStrictEqual({
				back: "transparent",
				caption: undefined,
				captionMarks: true,
				disabled: false,
				emptyIcon: "image",
				emptyText: "field.blocks.image.placeholder …",
				isEmpty: false
			});
		});
	});

	// props
	describe("content prop", () => {
		it("shows an external image", () => {
			expect(frame(mount()).props()).toStrictEqual({
				alt: "A",
				cover: false,
				file: undefined,
				ratio: "intrinsic",
				src: "https://a.de/a.jpg"
			});
		});

		it("shows a file from Kirby", () => {
			const wrapper = mount({
				content: {
					crop: true,
					image: [file],
					location: "kirby",
					ratio: "16/9",
					src: "https://a.de/a.jpg"
				}
			});
			expect(frame(wrapper).props()).toStrictEqual({
				alt: undefined,
				cover: true,
				file,
				ratio: "16/9",
				src: undefined
			});
		});

		it("shows a file from Kirby without location field", () => {
			const wrapper = mount({ content: { image: [file] } });
			expect(figure(wrapper).props("isEmpty")).toBe(false);
			expect(frame(wrapper).props()).toMatchObject({ file, src: undefined });
		});

		it("is empty without src", () => {
			const wrapper = mount({ content: { location: "web" } });
			expect(figure(wrapper).props("isEmpty")).toBe(true);
		});

		it("is empty without file from Kirby", () => {
			const wrapper = mount({ content: { location: "kirby" } });
			expect(figure(wrapper).props("isEmpty")).toBe(true);
		});

		it("shows the caption", () => {
			const wrapper = mount({
				content: { caption: "Hello", src: "https://a.de/a.jpg" },
				fieldset: {
					tabs: { content: { fields: { caption: { marks: false } } } }
				}
			});
			expect(figure(wrapper).props()).toMatchObject({
				caption: "Hello",
				captionMarks: false
			});
		});
	});

	describe("disabled prop", () => {
		it("disables the figure", () => {
			expect(figure(mount({ disabled: true })).props("disabled")).toBe(true);
		});
	});

	// data
	describe("back data", () => {
		it("restores the background from the session", () => {
			sessionStorage.setItem(storage, "var(--pattern)");
			onTestFinished(() => sessionStorage.removeItem(storage));
			const wrapper = mount();
			expect(figure(wrapper).props("back")).toBe("var(--pattern)");
			expect(wrapper.findComponent(Background).props("value")).toBe(
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
			expect(figure(wrapper).props("back")).toBe("black");
		});

		it("returns the stored background without value", () => {
			sessionStorage.setItem(storage, "black");
			onTestFinished(() => sessionStorage.removeItem(storage));
			expect(mount().vm.onBack()).toBe("black");
		});
	});

	// events
	describe("events", () => {
		it("emits open", () => {
			const wrapper = mount();
			figure(wrapper).vm.$emit("open");
			expect(wrapper.emitted("open")).toHaveLength(1);
		});

		it("updates the content", () => {
			const wrapper = mount();
			figure(wrapper).vm.$emit("update", { caption: "Hi" });
			expect(wrapper.emitted("update")).toStrictEqual([
				[
					{
						alt: "A",
						caption: "Hi",
						location: "web",
						src: "https://a.de/a.jpg"
					}
				]
			]);
		});
	});
});
