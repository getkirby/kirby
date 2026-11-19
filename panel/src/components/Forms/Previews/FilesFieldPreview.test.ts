import { beforeEach, describe, expect, it, vi } from "@test/unit";
import { flushPromises, mount as vueMount } from "@vue/test-utils";
import { clone } from "@/helpers/object";
import FilesFieldPreview from "./FilesFieldPreview.vue";

const Tags = {
	name: "k-tags-field-preview",
	props: ["removable", "value"],
	emits: ["remove"],
	template: "<ul />"
};

const helper = {
	clone,
	items: vi.fn()
};

function mount(props = {}, attrs = {}) {
	return vueMount(FilesFieldPreview, {
		props,
		attrs,
		global: {
			mocks: { $helper: helper, $panel: { language: { code: "en" } } },
			stubs: { "k-tags-field-preview": Tags }
		}
	});
}

describe("k-files-field-preview", () => {
	beforeEach(() => {
		helper.items.mockReset();
	});

	// $el
	describe("element", () => {
		it.rendersAs(mount, "UL", "k-files-field-preview");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("keeps the class of all model previews", () => {
			expect(mount().classes()).toContain("k-models-field-preview");
		});
	});

	// options
	describe("endpoint option", () => {
		it("loads the items from the files endpoint", async () => {
			helper.items.mockResolvedValueOnce([{ text: "a.jpg" }]);
			mount({ value: ["file://a"] });
			await flushPromises();
			expect(helper.items).toHaveBeenCalledExactlyOnceWith("items/files", [
				"file://a"
			]);
		});
	});

	// methods
	describe("tag()", () => {
		it("shows a file object by its filename", () => {
			const image = { src: "a.jpg" };
			const wrapper = mount({
				value: [{ filename: "a.jpg", id: "a", image, link: "/files/a" }]
			});
			expect(wrapper.findComponent(Tags).props("value")).toStrictEqual([
				{ image, link: "/files/a", text: "a.jpg" }
			]);
		});
	});
});
