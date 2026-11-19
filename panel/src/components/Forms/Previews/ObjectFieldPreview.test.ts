import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import ObjectFieldPreview from "./ObjectFieldPreview.vue";

// renders the tags of the default slot like `k-collapsible` does
// when everything fits, which happy-dom cannot measure
const Collapsible = {
	name: "k-collapsible",
	props: ["element"],
	template: `<ul><slot :offset="Infinity" /></ul>`
};

function mount(props = {}, attrs = {}) {
	return vueMount(ObjectFieldPreview, {
		props: { value: { name: "Kirby" } as never, ...props },
		attrs,
		global: {
			stubs: { "k-collapsible": Collapsible }
		}
	});
}

function texts(wrapper: ReturnType<typeof mount>) {
	return wrapper.findAll("k-tag").map((tag) => tag.attributes("text"));
}

describe("k-object-field-preview", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "UL", "k-object-field-preview");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("keeps the class of the tags preview", () => {
			expect(mount().classes()).toContain("k-tags-field-preview");
		});
	});

	// computed
	describe("tags computed", () => {
		it("hints at the object", () => {
			expect(texts(mount())).toStrictEqual(["{ ... }"]);
		});

		it("shows nothing without value", () => {
			expect(texts(mount({ value: null }))).toStrictEqual([]);
		});
	});
});
