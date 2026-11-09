import { describe, expect, it, vi } from "@test/unit";
import { flushPromises, mount as vueMount } from "@vue/test-utils";
import { clone } from "@/helpers/object";
import UsersFieldPreview from "./UsersFieldPreview.vue";

const helper = {
	clone,
	items: vi.fn()
};

function mount(props = {}) {
	return vueMount(UsersFieldPreview, {
		props,
		shallow: true,
		global: {
			mocks: {
				$helper: helper,
				$panel: { language: { code: "en" } }
			}
		}
	});
}

describe("UsersFieldPreview.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-TAGS-FIELD-PREVIEW", "k-models-field-preview");
	});

	// methods
	describe("tag()", () => {
		it("passes the username as plain string to be escaped", async () => {
			const username = '<img src="x">';
			const wrapper = mount({ value: [{ username }] });
			await flushPromises();

			// a plain string gets escaped by `k-tag`,
			// an `HtmlString` would be rendered as-is
			expect(wrapper.vm.tags).toStrictEqual([
				{ text: username, link: undefined, image: undefined }
			]);
		});

		it("falls back to the email", async () => {
			const wrapper = mount({ value: [{ email: "homer@simpsons.com" }] });
			await flushPromises();

			expect(wrapper.vm.tags).toStrictEqual([
				{ text: "homer@simpsons.com", link: undefined, image: undefined }
			]);
		});
	});
});
