import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import TagsFieldPreview from "./TagsFieldPreview.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(TagsFieldPreview, {
		props,
		attrs,
		shallow: true,
		global: {
			mocks: {
				$t: (key: string) => key
			}
		}
	});
}

describe("TagsFieldPreview.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "UL", "k-tags-field-preview");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	// computed
	describe("tags", () => {
		const field = { options: [{ value: "a", text: "A" }] };

		it("uses the text of the matching option", () => {
			const wrapper = mount({ field, value: ["a"] });
			expect(wrapper.vm.tags).toStrictEqual([{ value: "a", text: "A" }]);
		});

		it("shows [unknown] for a value without option", () => {
			const wrapper = mount({ field, value: ["a", "b"] });
			expect(wrapper.vm.tags).toStrictEqual([
				{ value: "a", text: "A" },
				{ value: "b", text: "[unknown]" }
			]);
		});

		it("shows custom tags if the field accepts any value", () => {
			const wrapper = mount({
				field: { ...field, accept: "all" },
				value: ["b"]
			});
			expect(wrapper.vm.tags).toStrictEqual([{ value: "b", text: "b" }]);
		});

		it("shows the value if no options are known", () => {
			const wrapper = mount({ value: ["b"] });
			expect(wrapper.vm.tags).toStrictEqual([{ value: "b", text: "b" }]);
		});

		it("splits and trims a comma-separated value", () => {
			const wrapper = mount({ field, value: "a, b" });
			expect(wrapper.vm.tags).toStrictEqual([
				{ value: "a", text: "A" },
				{ value: "b", text: "[unknown]" }
			]);
		});
	});
});
