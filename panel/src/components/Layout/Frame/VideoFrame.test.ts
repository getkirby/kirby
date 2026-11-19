import {
	afterEach,
	beforeEach,
	describe,
	expect,
	it,
	type MockInstance,
	vi
} from "@test/unit";
import { flushPromises, mount as vueMount } from "@vue/test-utils";
import { helper } from "@/helpers";
import embed from "@/helpers/embed";
import Frame from "./Frame.vue";
import VideoFrame from "./VideoFrame.vue";

// the file is fetched right on creation
let items: MockInstance;

function mount(props = {}, attrs = {}) {
	return vueMount(VideoFrame, {
		props: { url: "https://www.youtube.com/watch?v=abc", ...props },
		attrs,
		global: {
			components: { "k-frame": Frame }
		}
	});
}

describe("k-video-frame", () => {
	beforeEach(() => {
		items = vi.spyOn(helper, "items");
	});

	afterEach(() => {
		items.mockRestore();
	});

	// $el
	describe("element", () => {
		it.rendersAs(mount, "FIGURE", "k-frame");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("keeps the ratio of a video", () => {
			expect(mount().attributes("data-ratio")).toBe("16/9");
		});
	});

	// props
	describe("controls prop", () => {
		it("shows the controls of a file", async () => {
			items.mockResolvedValueOnce({ url: "/media/a.mp4" });
			const wrapper = mount({
				controls: true,
				file: "file://a",
				url: undefined
			});
			await flushPromises();
			expect(wrapper.find("video").attributes("controls")).toBe("");
		});
	});

	describe("file prop", () => {
		it("plays the file", async () => {
			items.mockResolvedValueOnce({ url: "/media/a.mp4" });
			const wrapper = mount({ file: "file://a", url: undefined });
			await flushPromises();
			expect(items).toHaveBeenCalledExactlyOnceWith("items/files", "file://a");
			expect(wrapper.find("video").attributes()).toMatchObject({
				class: "k-video",
				src: "/media/a.mp4"
			});
			expect(wrapper.find("video").attributes("controls")).toBeUndefined();
		});

		it("renders nothing for a missing file", async () => {
			items.mockResolvedValueOnce(undefined);
			const wrapper = mount({ file: "file://a", url: undefined });
			await flushPromises();
			expect(wrapper.html()).toBe("<!--v-if-->");
		});

		it("ignores a response for a previous file", async () => {
			const first = Promise.withResolvers();
			items.mockReturnValueOnce(first.promise);
			items.mockResolvedValueOnce({ url: "/media/b.mp4" });
			const wrapper = mount({ file: "file://a", url: undefined });
			await wrapper.setProps({ file: "file://b" } as never);
			await flushPromises();
			first.resolve({ url: "/media/a.mp4" });
			await flushPromises();
			expect(wrapper.find("video").attributes("src")).toBe("/media/b.mp4");
		});
	});

	describe("poster prop", () => {
		it("shows the poster of a file", async () => {
			items.mockResolvedValueOnce({ url: "/media/a.mp4" });
			const wrapper = mount({
				file: "file://a",
				poster: "/media/a.jpg",
				url: undefined
			});
			await flushPromises();
			expect(wrapper.find("video").attributes("poster")).toBe("/media/a.jpg");
		});
	});

	describe("ratio prop", () => {
		it("sets the ratio", () => {
			expect(mount({ ratio: "4/3" }).attributes("data-ratio")).toBe("4/3");
		});
	});

	describe("url prop", () => {
		it("embeds the video", () => {
			const iframe = mount().find("iframe");
			expect(iframe.classes()).toContain("k-video");
			expect(iframe.attributes()).toMatchObject({
				referrerpolicy: "strict-origin-when-cross-origin",
				src: embed.video("https://www.youtube.com/watch?v=abc", true),
				title: "video"
			});
			expect(items).not.toHaveBeenCalled();
		});

		it("renders nothing without url and file", () => {
			expect(mount({ url: undefined }).html()).toBe("<!--v-if-->");
		});
	});
});
