import { describe, expect, it, vi } from "@test/unit";
import { flushPromises, mount as vueMount } from "@vue/test-utils";
import FileBrowser from "./FileBrowser.vue";

const Browser = {
	name: "k-browser",
	props: ["items", "selected"],
	emits: ["select"],
	template: "<div />"
};
const Button = {
	name: "k-button",
	props: ["icon", "text"],
	emits: ["click"],
	template: `<button @click="$emit('click', $event)" />`
};
const PageTree = {
	name: "k-page-tree",
	props: ["current", "scrollable"],
	emits: ["select", "toggle"],
	template: "<ul />"
};
const Pagination = {
	name: "k-pagination",
	props: ["details", "limit", "page", "total"],
	emits: ["paginate"],
	template: "<nav />"
};

const site = { id: "/", label: "Site", value: "site://" };
const blog = { id: "blog/a", label: "A", value: "page://a" };

function api(pagination = { limit: 50, page: 1, total: 2 }) {
	return {
		get: vi.fn(async () => ({
			data: [
				{
					filename: "a.jpg",
					id: "blog/a/a.jpg",
					panelImage: { src: "a.jpg" },
					url: "/media/a.jpg",
					uuid: "file://a"
				},
				{ filename: "b.pdf", id: "blog/a/b.pdf", url: "/media/b.pdf" }
			],
			pagination
		})),
		pages: { id: (id: string) => id.replaceAll("/", "+") }
	};
}

function mount(props = {}, attrs = {}, $api = api()) {
	return vueMount(FileBrowser, {
		props,
		attrs,
		global: {
			mocks: { $api },
			stubs: {
				"k-browser": Browser,
				"k-button": Button,
				"k-page-tree": PageTree,
				"k-pagination": Pagination
			}
		}
	});
}

function tree(wrapper: ReturnType<typeof mount>) {
	return wrapper.findComponent(PageTree);
}

describe("k-file-browser", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-file-browser");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);

		it("shows the page tree", () => {
			expect(tree(mount()).props()).toStrictEqual({
				current: undefined,
				scrollable: true
			});
		});

		it("starts with the tree", () => {
			const wrapper = mount();
			expect(wrapper.attributes("data-view")).toBe("tree");
			expect(wrapper.findComponent(Browser).exists()).toBe(false);
		});
	});

	// props
	describe("limit prop", () => {
		it("loads the files in pages", async () => {
			const $api = api();
			const wrapper = mount({ limit: 10 }, {}, $api);
			await tree(wrapper).vm.$emit("select", site);
			expect($api.get).toHaveBeenCalledWith("/site/files", {
				limit: 10,
				page: 1,
				select: "filename,id,panelImage,url,uuid"
			});
		});
	});

	describe("opened prop", () => {
		it("starts with the files of the page", () => {
			const wrapper = mount({ opened: "page://a" });
			expect(wrapper.attributes("data-view")).toBe("files");
			expect(tree(wrapper).props("current")).toBe("page://a");
		});
	});

	describe("selected prop", () => {
		it("passes the selected file to the browser", async () => {
			const wrapper = mount({ selected: "file://a" });
			await tree(wrapper).vm.$emit("select", site);
			await flushPromises();
			expect(wrapper.findComponent(Browser).props("selected")).toBe("file://a");
		});
	});

	// methods
	describe("selectPage()", () => {
		it("shows the files of the site", async () => {
			const $api = api();
			const wrapper = mount({}, {}, $api);
			await wrapper.vm.selectPage(site);
			await flushPromises();
			expect($api.get).toHaveBeenCalledWith("/site/files", {
				limit: 50,
				page: 1,
				select: "filename,id,panelImage,url,uuid"
			});
			expect(wrapper.attributes("data-view")).toBe("files");
			expect(wrapper.findComponent(Browser).props("items")).toStrictEqual([
				{
					id: "blog/a/a.jpg",
					image: { src: "a.jpg" },
					label: "a.jpg",
					url: "/media/a.jpg",
					uuid: "file://a",
					value: "file://a"
				},
				{
					id: "blog/a/b.pdf",
					image: undefined,
					label: "b.pdf",
					url: "/media/b.pdf",
					uuid: undefined,
					value: "/media/b.pdf"
				}
			]);
		});

		it("shows the files of a page", async () => {
			const $api = api();
			const wrapper = mount({}, {}, $api);
			await wrapper.vm.selectPage(blog, 2);
			expect($api.get).toHaveBeenCalledWith("/pages/blog+a/files", {
				limit: 50,
				page: 2,
				select: "filename,id,panelImage,url,uuid"
			});
			await flushPromises();
			expect(tree(wrapper).props("current")).toBe("page://a");
		});

		it("names the page on the back button", async () => {
			const wrapper = mount();
			await wrapper.vm.selectPage(blog);
			await flushPromises();
			const back = wrapper
				.findAllComponents(Button)
				.find((button) => button.classes("k-file-browser-back-button"));
			expect(back?.props("text")).toBe("A");
		});
	});

	// events
	describe("events", () => {
		it("emits the selected file", async () => {
			const wrapper = mount();
			await wrapper.vm.selectPage(site);
			await flushPromises();
			wrapper.findComponent(Browser).vm.$emit("select", { value: "file://a" });
			expect(wrapper.emitted("select")).toStrictEqual([
				[{ value: "file://a" }]
			]);
		});

		it("goes back to the tree", async () => {
			const wrapper = mount({ opened: "page://a" });
			await wrapper.find(".k-file-browser-back-button").trigger("click");
			expect(wrapper.attributes("data-view")).toBe("tree");
		});

		it("paginates the files", async () => {
			const $api = api({ limit: 1, page: 1, total: 2 });
			const wrapper = mount({}, {}, $api);
			await wrapper.vm.selectPage(blog);
			await flushPromises();
			const pagination = wrapper.findComponent(Pagination);
			expect(pagination.props()).toStrictEqual({
				details: true,
				limit: 1,
				page: 1,
				total: 2
			});
			pagination.vm.$emit("paginate", { page: 2 });
			expect($api.get).toHaveBeenLastCalledWith("/pages/blog+a/files", {
				limit: 50,
				page: 2,
				select: "filename,id,panelImage,url,uuid"
			});
		});

		it("has no pagination for a single page", async () => {
			const wrapper = mount();
			await wrapper.vm.selectPage(blog);
			await flushPromises();
			expect(wrapper.findComponent(Pagination).exists()).toBe(false);
		});
	});
});
