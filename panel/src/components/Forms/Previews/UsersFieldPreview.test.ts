import { beforeEach, describe, expect, it, vi } from "@test/unit";
import { flushPromises, mount as vueMount } from "@vue/test-utils";
import { clone } from "@/helpers/object";
import UsersFieldPreview from "./UsersFieldPreview.vue";

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
	return vueMount(UsersFieldPreview, {
		props,
		attrs,
		global: {
			mocks: { $helper: helper, $panel: { language: { code: "en" } } },
			stubs: { "k-tags-field-preview": Tags }
		}
	});
}

describe("k-users-field-preview", () => {
	beforeEach(() => {
		helper.items.mockReset();
	});

	// $el
	describe("element", () => {
		it.rendersAs(mount, "UL", "k-users-field-preview");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("keeps the class of all model previews", () => {
			expect(mount().classes()).toContain("k-models-field-preview");
		});
	});

	// options
	describe("endpoint option", () => {
		it("loads the items from the users endpoint", async () => {
			helper.items.mockResolvedValueOnce([{ text: "a" }]);
			mount({ value: ["user://a"] });
			await flushPromises();
			expect(helper.items).toHaveBeenCalledExactlyOnceWith("items/users", [
				"user://a"
			]);
		});
	});

	// methods
	describe("tag()", () => {
		it("shows a user object by its username", () => {
			const image = { icon: "user" };
			const wrapper = mount({
				value: [
					{
						email: "a@getkirby.com",
						id: "a",
						image,
						link: "/users/a",
						username: "Ada"
					}
				]
			});
			expect(wrapper.findComponent(Tags).props("value")).toStrictEqual([
				{ image, link: "/users/a", text: "Ada" }
			]);
		});

		it("passes the username as plain string to be escaped", () => {
			const username = '<img src="x">';
			const wrapper = mount({ value: [{ username }] });

			// a plain string gets escaped by `k-tag`,
			// an `HtmlString` would be rendered as-is
			expect(wrapper.findComponent(Tags).props("value")).toStrictEqual([
				{ image: undefined, link: undefined, text: username }
			]);
		});

		it("falls back to the email address", () => {
			const wrapper = mount({
				value: [{ email: "a@getkirby.com", id: "a", link: "/users/a" }]
			});
			expect(wrapper.findComponent(Tags).props("value")).toStrictEqual([
				{ image: undefined, link: "/users/a", text: "a@getkirby.com" }
			]);
		});
	});
});
