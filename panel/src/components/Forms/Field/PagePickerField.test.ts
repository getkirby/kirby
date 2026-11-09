import { beforeEach, describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import PagePickerField from "./PagePickerField.vue";

const api = { get: vi.fn() };
const dialog = { close: vi.fn(), open: vi.fn() };
const panel = { api, dialog };

function mount(props = {}, attrs = {}) {
	return vueMount(PagePickerField, {
		props: {
			endpoints: { field: "pages/test/fields/related" },
			name: "related",
			value: ["a", "b"],
			...props
		},
		attrs,
		global: {
			mocks: {
				$panel: panel
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

describe("k-pagepicker-field", () => {
	beforeEach(() => {
		api.get.mockReset();
		api.get.mockResolvedValue([]);
	});

	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-FIELD", "k-pagepicker-field");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	// methods
	describe("open()", () => {
		it("applies the IDs submitted by the picker dialog", () => {
			const wrapper = mount();
			wrapper.vm.open();

			submit()({ ids: ["a"], items: [{ id: "a" }] });

			expect(wrapper.emitted("input")).toStrictEqual([[["a"]]]);
			expect(dialog.close).toHaveBeenCalledOnce();
		});

		it("ignores a submission without IDs", () => {
			const wrapper = mount();
			wrapper.vm.open();

			// the save shortcut can submit the dialog's value prop,
			// which is a plain array of IDs instead of the selection
			submit()(["a", "b"]);

			expect(wrapper.emitted("input")).toBeUndefined();
			expect(dialog.close).not.toHaveBeenCalled();
		});
	});
});
