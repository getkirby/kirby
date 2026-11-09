import { beforeEach, describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import PagePickerField from "./PagePickerField.vue";

// registered globally in the Panel
const Collection = { props: ["empty"], template: "<div />" };

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
			components: { "k-collection": Collection },
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

	// computed
	describe("emptyProps computed", () => {
		function empty(props = {}) {
			const wrapper = mount(props);
			return wrapper.findComponent(Collection).props("empty");
		}

		it("asks for a single page by default", () => {
			expect(empty()).toStrictEqual({
				icon: "page",
				text: "field.pages.empty.single"
			});
		});

		it("asks for pages when multiple can be selected", () => {
			expect(empty({ multiple: true }).text).toBe("field.pages.empty");
		});

		it("asks for a single page when the max is 1", () => {
			expect(empty({ max: 1, multiple: true }).text).toBe(
				"field.pages.empty.single"
			);
		});

		it("prefers the empty prop", () => {
			expect(empty({ empty: "No pages yet", multiple: true }).text).toBe(
				"No pages yet"
			);
		});
	});

	// methods
	describe("open()", () => {
		it("opens the picker dialog with the value", () => {
			const wrapper = mount();
			wrapper.vm.open();

			expect(dialog.open).toHaveBeenCalledWith(
				"pages/test/fields/related/picker",
				{
					query: { value: ["a", "b"] },
					on: { drop: undefined, submit: expect.any(Function) }
				}
			);
		});

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
