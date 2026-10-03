import { describe, expect, it, vi } from "@test/unit";
import { flushPromises, mount as vueMount } from "@vue/test-utils";
import { reactive } from "vue";
import ModelPickerField from "./ModelPickerField.vue";

function panel() {
	return reactive({
		api: { get: vi.fn() },
		language: { code: "en" }
	});
}

function mount(props = {}, attrs = {}, $panel = panel()) {
	return vueMount(ModelPickerField, {
		props: {
			endpoints: { field: "pages/test/fields/related" },
			...props
		},
		attrs,
		shallow: true,
		global: {
			mocks: {
				// the abstract base leaves `emptyProps` to its subclasses
				emptyProps: {},
				$panel,
				$t: (key: string) => key
			}
		}
	});
}

describe("ModelPickerField.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-FIELD", "k-models-field");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	// watch
	describe("$panel.language.code watcher", () => {
		it("reloads the items", async () => {
			const $panel = panel();
			$panel.api.get.mockResolvedValueOnce([{ id: "a", text: "Home" }]);

			const wrapper = mount({ value: ["a"] }, {}, $panel);
			await flushPromises();

			expect(wrapper.vm.selected).toStrictEqual([{ id: "a", text: "Home" }]);

			$panel.api.get.mockResolvedValueOnce([{ id: "a", text: "Startseite" }]);
			$panel.language.code = "de";
			await flushPromises();

			expect($panel.api.get).toHaveBeenCalledTimes(2);
			expect(wrapper.vm.selected).toStrictEqual([
				{ id: "a", text: "Startseite" }
			]);
		});
	});
});
