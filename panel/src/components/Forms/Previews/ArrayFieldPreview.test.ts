import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import ArrayFieldPreview from "./ArrayFieldPreview.vue";

// renders the tags of the default slot like `k-collapsible` does
// when everything fits, which happy-dom cannot measure
const Collapsible = {
	name: "k-collapsible",
	props: ["element"],
	template: `<ul><slot :offset="Infinity" /></ul>`
};

function mount(props = {}, attrs = {}) {
	return vueMount(ArrayFieldPreview, {
		props: { value: [{ a: 1 }, { a: 2 }], ...props },
		attrs,
		global: {
			stubs: { "k-collapsible": Collapsible }
		}
	});
}

function texts(wrapper: ReturnType<typeof mount>) {
	return wrapper.findAll("k-tag").map((tag) => tag.attributes("text"));
}

describe("k-array-field-preview", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "UL", "k-array-field-preview");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("keeps the class of the tags preview", () => {
			expect(mount().classes()).toContain("k-tags-field-preview");
		});
	});

	// computed
	describe("tags computed", () => {
		it("counts the entries", () => {
			expect(texts(mount())).toStrictEqual(["2 entries"]);
		});

		it("counts a single entry", () => {
			expect(texts(mount({ value: [{ a: 1 }] }))).toStrictEqual(["1 entry"]);
		});
	});
});
