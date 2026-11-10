import { beforeEach, describe, expect, it, vi } from "@test/unit";
import { flushPromises, mount as vueMount } from "@vue/test-utils";
import { reactive } from "vue";
import { clone } from "@/helpers/object";
import PagesFieldPreview from "./PagesFieldPreview.vue";

const helper = {
	clone,
	items: vi.fn()
};

function mount(props = {}, attrs = {}, $panel = { language: { code: "en" } }) {
	return vueMount(PagesFieldPreview, {
		props,
		attrs,
		global: {
			mocks: {
				$helper: helper,
				$panel
			}
		}
	});
}

describe("k-pages-field-preview", () => {
	beforeEach(() => {
		helper.items.mockReset();
	});

	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-TAGS-FIELD-PREVIEW", "k-pages-field-preview");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("keeps the class of all model previews", () => {
			const wrapper = mount();
			expect(wrapper.classes()).toContain("k-models-field-preview");
		});
	});

	// options
	describe("endpoint option", () => {
		it("loads the items from the pages endpoint", async () => {
			helper.items.mockResolvedValueOnce([{ text: "Home" }, { text: "Blog" }]);
			mount({ value: ["page://a", "page://b"] });
			await flushPromises();

			expect(helper.items).toHaveBeenCalledExactlyOnceWith("items/pages", [
				"page://a",
				"page://b"
			]);
		});
	});

	// watch
	describe("$panel.language.code watcher", () => {
		it("reloads the items", async () => {
			const $panel = reactive({ language: { code: "en" } });

			helper.items.mockResolvedValueOnce([{ text: "Home" }]);
			const wrapper = mount({ value: ["page://a"] }, {}, $panel);
			await flushPromises();

			expect(wrapper.vm.tags).toStrictEqual([{ id: "page://a", text: "Home" }]);

			helper.items.mockResolvedValueOnce([{ text: "Startseite" }]);
			$panel.language.code = "de";
			await flushPromises();

			expect(helper.items).toHaveBeenCalledTimes(2);
			expect(wrapper.vm.tags).toStrictEqual([
				{ id: "page://a", text: "Startseite" }
			]);
		});

		it("ignores a late response for the previous language", async () => {
			const $panel = reactive({ language: { code: "en" } });
			let en: (items: object[]) => void = () => {};
			let de: (items: object[]) => void = () => {};

			helper.items
				.mockReturnValueOnce(new Promise((resolve) => (en = resolve)))
				.mockReturnValueOnce(new Promise((resolve) => (de = resolve)));

			const wrapper = mount({ value: ["page://a"] }, {}, $panel);
			$panel.language.code = "de";
			await flushPromises();

			de([{ text: "Startseite" }]);
			await flushPromises();
			en([{ text: "Home" }]);
			await flushPromises();

			expect(wrapper.vm.tags).toStrictEqual([
				{ id: "page://a", text: "Startseite" }
			]);
		});
	});

	// methods
	describe("collect()", () => {
		it("shows the stored ID for unknown items", async () => {
			helper.items.mockResolvedValueOnce([{ text: "Home" }, undefined]);
			const wrapper = mount({ value: ["page://a", "page://b"] });
			await flushPromises();

			expect(wrapper.vm.tags).toStrictEqual([
				{ id: "page://a", text: "Home" },
				{
					id: "page://b",
					text: "page://b",
					image: { icon: "alert" },
					theme: "negative"
				}
			]);
		});

		it("reuses the tags of known IDs", async () => {
			helper.items.mockResolvedValueOnce([{ text: "Home" }]);
			const wrapper = mount({ value: ["page://a"] });
			await flushPromises();

			helper.items.mockResolvedValueOnce([{ text: "Blog" }]);
			await wrapper.setProps({ value: ["page://b", "page://a"] });
			await flushPromises();

			expect(helper.items).toHaveBeenLastCalledWith("items/pages", [
				"page://b"
			]);
			expect(wrapper.vm.tags).toStrictEqual([
				{ id: "page://b", text: "Blog" },
				{ id: "page://a", text: "Home" }
			]);
		});

		it("drops a response for a tag removed while loading", async () => {
			const request = Promise.withResolvers<unknown[]>();
			helper.items.mockReturnValueOnce(request.promise);
			const wrapper = mount({ value: ["page://a", "page://b"] });

			// the loading tag of `page://b` is reused, nothing is refetched
			await wrapper.setProps({ value: ["page://b"] });
			request.resolve([{ text: "Home" }, { text: "Blog" }]);
			await flushPromises();

			// the removed tag must not be written to a missing index
			expect(wrapper.vm.tags).not.toHaveProperty("-1");

			expect(helper.items).toHaveBeenCalledOnce();
			expect(wrapper.vm.tags).toStrictEqual([{ id: "page://b", text: "Blog" }]);
		});
	});
});
