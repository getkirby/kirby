import { describe, it, expect } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import PicklistInput from "./PicklistInput.vue";
import string from "@/helpers/string";
import html, { HtmlString } from "@/panel/html";

function mount(props = {}, attrs = {}) {
	return vueMount(PicklistInput, { props, attrs, shallow: true });
}

const options = [
	{ text: "Design", value: "design" },
	{ text: "Development", value: "development" }
];

describe("PicklistInput.vue", () => {
	// $el
	describe("element", () => {
		const component = (attrs = {}) => mount({}, attrs);

		it.rendersAs(component, "K-NAVIGATE", "k-picklist-input");
		it.acceptsClass(component);
		it.acceptsStyle(component);
	});

	// props
	describe("create prop", () => {
		it("renders no create button without a query", async () => {
			const wrapper = mount({ create: true, options });
			expect(wrapper.find(".k-picklist-input-create").exists()).toBe(false);
		});

		it("renders the create button for a query", async () => {
			const wrapper = mount({ create: true, options });
			await wrapper.setData({ query: "Marketing" });

			expect(wrapper.find(".k-picklist-input-create").exists()).toBe(true);
		});
	});

	describe("multiple prop", () => {
		it("renders checkboxes by default", () => {
			const wrapper = mount({ options });
			expect(wrapper.find("k-checkboxes-input").exists()).toBe(true);
		});

		it("renders radios when false", () => {
			const wrapper = mount({ options, multiple: false });
			expect(wrapper.find("k-radio-input").exists()).toBe(true);
		});
	});

	describe("options prop", () => {
		it("renders the options", () => {
			const wrapper = mount({ options });
			expect(wrapper.find(".k-picklist-input-options").exists()).toBe(true);
		});

		it("renders the empty state without options", () => {
			const wrapper = mount({ options: [] });

			expect(wrapper.find(".k-picklist-input-options").exists()).toBe(false);
			expect(wrapper.find(".k-picklist-input-empty").exists()).toBe(true);
		});
	});

	describe("search prop", () => {
		it("renders the search header by default", () => {
			const wrapper = mount({ options });
			expect(wrapper.find(".k-picklist-input-header").exists()).toBe(true);
		});

		it("drops the search header when false", () => {
			const wrapper = mount({ options, search: false });
			expect(wrapper.find(".k-picklist-input-header").exists()).toBe(false);
		});
	});

	// methods
	describe("highlight()", () => {
		// the method only needs `query` and the string helpers
		function highlight(text: unknown, query = ""): HtmlString {
			return PicklistInput.methods!.highlight.call(
				{ query, $helper: { string } },
				text
			);
		}

		it("wraps the match in bold", () => {
			expect(String(highlight("Kirby", "ir"))).toBe("K<b>ir</b>by");
		});

		it("matches case-insensitively", () => {
			expect(String(highlight("Kirby", "KIR"))).toBe("<b>Kir</b>by");
		});

		it("wraps every occurrence", () => {
			expect(String(highlight("aXbXc", "X"))).toBe("a<b>X</b>b<b>X</b>c");
		});

		it("returns the text unchanged without a query", () => {
			expect(String(highlight("Kirby"))).toBe("Kirby");
		});

		it("treats the query literally, not as a pattern", () => {
			expect(String(highlight("a.b", "."))).toBe("a<b>.</b>b");
		});

		it("returns trusted HTML so the match markup survives rendering", () => {
			const result = highlight(html("Kirby"), "ir");

			expect(result).toBeInstanceOf(HtmlString);
			expect(result.toString()).toBe("K<b>ir</b>by");
		});

		it("escapes untrusted text before adding the match markup", () => {
			const result = highlight("<script>x</script>", "script");

			expect(result.toString()).not.toContain("<script>");
			expect(result.toString()).toContain("<b>script</b>");
		});

		it("neutralises untrusted markup that stripHTML would miss", () => {
			// an unclosed tag never matches stripHTML's `<…>` regex,
			// so escaping — not stripping — is what makes this safe
			const result = highlight("<img src=x onerror=alert(1)");

			expect(result.toString()).not.toContain("<img");
			expect(result.toString()).toContain("&lt;img");
		});

		it("strips tags from trusted HTML so a match cannot land inside one", () => {
			expect(String(highlight(html("<b>Kirby</b>"), "b"))).toBe("Kir<b>b</b>y");
		});

		it("does not double-escape entities in trusted HTML", () => {
			expect(String(highlight(html("Tom &amp; Jerry")))).toBe("Tom &amp; Jerry");
		});
	});
});
