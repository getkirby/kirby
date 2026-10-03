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
		shallow: true,
		global: {
			mocks: {
				$helper: helper,
				$panel
			}
		}
	});
}

describe("PagesFieldPreview.vue", () => {
	beforeEach(() => {
		helper.items.mockReset();
	});

	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-TAGS-FIELD-PREVIEW", "k-models-field-preview");
		it.inheritsNoAttrs(mount);
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
	});
});
