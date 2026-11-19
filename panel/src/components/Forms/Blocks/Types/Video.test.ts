import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Video from "./Video.vue";

const Figure = {
	name: "k-block-figure",
	props: [
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
const VideoFrame = {
	name: "k-video-frame",
	props: ["controls", "element", "file", "poster", "url"],
	template: "<div />"
};

const file = { id: "a.mp4", url: "/media/a.mp4" };

function mount(props = {}, attrs = {}) {
	return vueMount(Video, {
		props: {
			content: { location: "web", url: "https://youtu.be/a" },
			...props
		},
		attrs,
		global: {
			stubs: { "k-block-figure": Figure, "k-video-frame": VideoFrame }
		}
	});
}

function figure(wrapper: ReturnType<typeof mount>) {
	return wrapper.findComponent(Figure);
}

function frame(wrapper: ReturnType<typeof mount>) {
	return wrapper.findComponent(VideoFrame);
}

describe("k-block-type-video", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "FIGURE", "k-block-type-video-figure");
		it.inheritsNoAttrs(mount);

		it("renders a figure with a video placeholder", () => {
			expect(figure(mount()).props()).toStrictEqual({
				caption: undefined,
				captionMarks: true,
				disabled: false,
				emptyIcon: "video",
				emptyText: "field.blocks.video.placeholder …",
				isEmpty: false
			});
		});
	});

	// props
	describe("content prop", () => {
		it("embeds an external video", () => {
			expect(frame(mount()).props()).toStrictEqual({
				controls: undefined,
				element: "div",
				file: undefined,
				poster: undefined,
				url: "https://youtu.be/a"
			});
		});

		it("plays a file from Kirby", () => {
			const wrapper = mount({
				content: {
					controls: true,
					location: "kirby",
					poster: [{ url: "/media/a.jpg" }],
					url: "https://youtu.be/a",
					video: [file]
				}
			});
			expect(frame(wrapper).props()).toStrictEqual({
				controls: true,
				element: "div",
				file,
				poster: "/media/a.jpg",
				url: undefined
			});
		});

		it("is empty without url", () => {
			const wrapper = mount({ content: { location: "web" } });
			expect(figure(wrapper).props("isEmpty")).toBe(true);
		});

		it("is empty without file from Kirby", () => {
			const wrapper = mount({
				content: { location: "kirby", url: "https://youtu.be/a" }
			});
			expect(figure(wrapper).props("isEmpty")).toBe(true);
		});

		it("shows the caption", () => {
			const wrapper = mount({
				content: { caption: "Hello", url: "https://youtu.be/a" },
				fieldset: {
					tabs: { content: { fields: { caption: { marks: ["bold"] } } } }
				}
			});
			expect(figure(wrapper).props()).toMatchObject({
				caption: "Hello",
				captionMarks: ["bold"]
			});
		});
	});

	describe("disabled prop", () => {
		it("disables the figure", () => {
			expect(figure(mount({ disabled: true })).props("disabled")).toBe(true);
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
				[{ caption: "Hi", location: "web", url: "https://youtu.be/a" }]
			]);
		});
	});
});
