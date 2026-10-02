import { beforeEach, describe, expect, it, vi } from "@test/unit";
import { flushPromises, mount } from "@vue/test-utils";
import { reactive } from "vue";
import { clone } from "@/helpers/object";
import PagesFieldPreview from "./PagesFieldPreview.vue";

const helper = {
	clone,
	items: vi.fn()
};

function factory(panel: { language: { code: string } }) {
	return mount(PagesFieldPreview, {
		props: {
			value: ["page://a"]
		},
		shallow: true,
		global: {
			mocks: {
				$helper: helper,
				$panel: panel
			}
		}
	});
}

describe("PagesFieldPreview.vue", () => {
	beforeEach(() => {
		helper.items.mockReset();
	});

	it("reloads the items on language switch", async () => {
		const panel = reactive({ language: { code: "en" } });

		helper.items.mockResolvedValueOnce([{ text: "Home" }]);
		const wrapper = factory(panel);
		await flushPromises();

		expect(wrapper.vm.tags).toStrictEqual([{ id: "page://a", text: "Home" }]);

		helper.items.mockResolvedValueOnce([{ text: "Startseite" }]);
		panel.language.code = "de";
		await flushPromises();

		expect(helper.items).toHaveBeenCalledTimes(2);
		expect(wrapper.vm.tags).toStrictEqual([
			{ id: "page://a", text: "Startseite" }
		]);
	});
});
