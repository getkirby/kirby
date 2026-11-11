import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import { defineComponent, h } from "vue";
import TagsFieldPreview from "./TagsFieldPreview.vue";

/**
 * Stands in for `k-collapsible`, which cannot measure without
 * a layout: hands over the given offset and renders the
 * fallback when the tags are collapsed
 */
function collapsible(offset = Infinity, collapsed = false) {
	return defineComponent({
		name: "k-collapsible",
		props: { element: { type: String, default: "div" } },
		setup(props, { attrs, slots }) {
			return () =>
				h(props.element, attrs, [
					...(slots.default?.({ offset }) ?? []),
					...(collapsed ? (slots.fallback?.({ offset }) ?? []) : [])
				]);
		}
	});
}

function mount(props = {}, attrs = {}, stub = collapsible()) {
	return vueMount(TagsFieldPreview, {
		props: { value: ["Design", "Development", "Photography"], ...props },
		attrs,
		global: {
			mocks: {
				$t: (key: string) => key
			},
			stubs: { "k-collapsible": stub }
		}
	});
}

function texts(wrapper: ReturnType<typeof mount>) {
	return wrapper.findAll("k-tag").map((tag) => tag.attributes("text"));
}

describe("k-tags-field-preview", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "UL", "k-tags-field-preview");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("shows all tags when they fit", () => {
			const wrapper = mount();
			expect(texts(wrapper)).toStrictEqual([
				"Design",
				"Development",
				"Photography"
			]);
		});

		it("counts the tags that don't fit", () => {
			const wrapper = mount({}, {}, collapsible(1, true));
			expect(texts(wrapper)).toStrictEqual(["Design", "+2"]);
			expect(wrapper.find(".k-tags-field-preview-more").exists()).toBe(true);
		});

		it("keeps the first tag next to the counter when none fits", () => {
			const wrapper = mount({}, {}, collapsible(0, true));
			expect(texts(wrapper)).toStrictEqual(["Design", "+2"]);
		});

		it("has no counter for a single tag", () => {
			const wrapper = mount({ value: ["Design"] }, {}, collapsible(0, true));
			expect(texts(wrapper)).toStrictEqual(["Design"]);
			expect(wrapper.find(".k-tags-field-preview-more").exists()).toBe(false);
		});
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

	// methods
	describe("visible()", () => {
		it("returns the tags that fit", () => {
			const wrapper = mount();
			expect(wrapper.vm.visible(2)).toHaveLength(2);
			expect(wrapper.vm.visible(Infinity)).toHaveLength(3);
		});

		it("returns at least the first tag", () => {
			const wrapper = mount();
			expect(wrapper.vm.visible(0)).toStrictEqual([
				{ text: "Design", value: "Design" }
			]);
		});
	});
});
