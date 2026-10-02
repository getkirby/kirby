import { describe, expect, it, vi } from "@test/unit";
import { flushPromises, mount } from "@vue/test-utils";
import { reactive } from "vue";
import ModelPickerField from "./ModelPickerField.vue";

describe("ModelPickerField.vue", () => {
	it("reloads the items on language switch", async () => {
		const panel = reactive({
			api: { get: vi.fn() },
			language: { code: "en" }
		});

		panel.api.get.mockResolvedValueOnce([{ id: "a", text: "Home" }]);

		const wrapper = mount(ModelPickerField, {
			props: {
				endpoints: { field: "pages/test/fields/related" },
				value: ["a"]
			},
			shallow: true,
			global: {
				mocks: {
					$panel: panel,
					$t: (key: string) => key
				}
			}
		});

		await flushPromises();

		expect(wrapper.vm.selected).toStrictEqual([{ id: "a", text: "Home" }]);

		panel.api.get.mockResolvedValueOnce([{ id: "a", text: "Startseite" }]);
		panel.language.code = "de";
		await flushPromises();

		expect(panel.api.get).toHaveBeenCalledTimes(2);
		expect(wrapper.vm.selected).toStrictEqual([
			{ id: "a", text: "Startseite" }
		]);
	});
});
