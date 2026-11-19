import { beforeEach, describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import UserPickerField from "./UserPickerField.vue";

// registered globally in the Panel
const Collection = { props: ["empty"], template: "<div />" };

const api = { get: vi.fn() };
const events = { off: vi.fn(), on: vi.fn() };

function mount(props = {}, attrs = {}) {
	return vueMount(UserPickerField, {
		props: {
			endpoints: { field: "pages/test/fields/authors" },
			name: "authors",
			value: [],
			...props
		},
		attrs,
		global: {
			components: { "k-collection": Collection },
			mocks: { $events: events, $panel: { api } }
		}
	});
}

describe("k-userpicker-field", () => {
	beforeEach(() => {
		api.get.mockResolvedValue([]);
	});

	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-FIELD", "k-userpicker-field");
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

		it("asks for a single user by default", () => {
			expect(empty()).toStrictEqual({
				icon: "users",
				text: "field.users.empty.single"
			});
		});

		it("asks for users when multiple can be selected", () => {
			expect(empty({ multiple: true }).text).toBe("field.users.empty");
		});

		it("asks for a single user when the max is 1", () => {
			expect(empty({ max: 1, multiple: true }).text).toBe(
				"field.users.empty.single"
			);
		});

		it("prefers the empty prop", () => {
			expect(empty({ empty: "No authors yet", multiple: true }).text).toBe(
				"No authors yet"
			);
		});
	});

	// lifecycle
	describe("mounted hook", () => {
		it("listens to deleted users until unmounted", () => {
			const wrapper = mount();
			const handler = wrapper.vm.onDelete;
			expect(events.on).toHaveBeenCalledWith("user.delete", handler);
			wrapper.unmount();
			expect(events.off).toHaveBeenCalledWith("user.delete", handler);
		});
	});

	// methods
	describe("onDelete()", () => {
		it("removes the deleted user", () => {
			const wrapper = mount({ value: ["user://a", "user://b"] });
			wrapper.vm.onDelete({
				event: "user.delete",
				user: { id: "a", uuid: "user://a" }
			});
			expect(wrapper.emitted("input")).toStrictEqual([[["user://b"]]]);
		});
	});
});
