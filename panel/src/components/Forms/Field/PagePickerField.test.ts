import { beforeEach, describe, expect, it, vi } from "@test/unit";
import { mount } from "@vue/test-utils";
import PagePickerField from "./PagePickerField.vue";

const api = { get: vi.fn() };
const dialog = { close: vi.fn(), open: vi.fn() };
const panel = { api, dialog };

function factory(value = ["a", "b"]) {
	return mount(PagePickerField, {
		props: {
			endpoints: { field: "pages/test/fields/related" },
			name: "related",
			value
		},
		shallow: true,
		global: {
			mocks: {
				$panel: panel,
				$t: (key: string) => key
			}
		}
	});
}

/**
 * The submit listener the field passed to the picker dialog
 */
function submit() {
	return dialog.open.mock.calls.at(-1)?.[1].on.submit;
}

describe("PagePickerField.vue", () => {
	beforeEach(() => {
		api.get.mockReset();
		api.get.mockResolvedValue([]);
		dialog.close.mockClear();
		dialog.open.mockClear();
	});

	it("applies the IDs submitted by the picker dialog", () => {
		const wrapper = factory();
		wrapper.vm.open();

		submit()({ ids: ["a"], items: [{ id: "a" }] });

		expect(wrapper.emitted("input")).toStrictEqual([[["a"]]]);
		expect(dialog.close).toHaveBeenCalledOnce();
	});

	it("ignores a submission without IDs", () => {
		const wrapper = factory();
		wrapper.vm.open();

		// the save shortcut can submit the dialog's value prop,
		// which is a plain array of IDs instead of the selection
		submit()(["a", "b"]);

		expect(wrapper.emitted("input")).toBeUndefined();
		expect(dialog.close).not.toHaveBeenCalled();
	});
});
