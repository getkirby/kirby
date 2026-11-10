import { describe, it, expect } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import PicklistInput from "./PicklistInput.vue";
import html, { HtmlString } from "@/panel/html";

// registered globally in the Panel
const ChoicesInput = {
	props: ["options"],
	template: "<div />"
};

const SearchInput = {
	props: ["value"],
	emits: ["input"],
	template: `<input :value="value" @input="$emit('input', $event.target.value)" />`
};

const stubs = {
	"k-checkboxes-input": ChoicesInput,
	"k-search-input": SearchInput
};

function mount(props = {}, attrs = {}, components = {}) {
	return vueMount(PicklistInput, { props, attrs, global: { components } });
}

// the options the checkbox input receives
function choices(wrapper: ReturnType<typeof mount>) {
	return wrapper.findComponent(ChoicesInput).props("options") as {
		disabled: boolean;
		value: string;
	}[];
}

async function search(wrapper: ReturnType<typeof mount>, query: string) {
	await wrapper.find(".k-picklist-input-search input").setValue(query);
}

const options = [
	{ text: "Design", value: "design" },
	{ text: "Development", value: "development" }
];

describe("k-picklist-input", () => {
	// $el
	describe("element", () => {
		const component = (attrs = {}) => mount({}, attrs);

		it.rendersAs(component, "K-NAVIGATE", "k-picklist-input");
		it.acceptsClass(component);
		it.acceptsStyle(component);
		it.inheritsNoAttrs(component);
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

	describe("max prop", () => {
		it("disables the unselected options once reached", () => {
			const wrapper = mount({ max: 1, options, value: ["design"] }, {}, stubs);
			expect(choices(wrapper).map((option) => option.disabled)).toEqual([
				false,
				true
			]);
		});

		it("keeps all options enabled below the maximum", () => {
			const wrapper = mount({ max: 2, options, value: ["design"] }, {}, stubs);
			expect(choices(wrapper).map((option) => option.disabled)).toEqual([
				false,
				false
			]);
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

	// computed
	describe("filteredOptions computed", () => {
		it("filters the options by the query", async () => {
			const wrapper = mount({ options }, {}, stubs);
			await search(wrapper, "velop");

			expect(choices(wrapper).map((option) => option.value)).toEqual([
				"development"
			]);
		});

		it("searches the info as well with `search.info`", async () => {
			const options = [
				{ text: "Design", value: "design", info: "Pixels" },
				{ text: "Development", value: "development", info: "Code" }
			];
			const wrapper = mount({ options, search: { info: true } }, {}, stubs);
			await search(wrapper, "code");

			expect(choices(wrapper).map((option) => option.value)).toEqual([
				"development"
			]);
		});

		it("keeps all options for a query shorter than `search.min`", async () => {
			const wrapper = mount({ options, search: { min: 3 } }, {}, stubs);

			await search(wrapper, "ve");
			expect(choices(wrapper).map((option) => option.value)).toEqual([
				"design",
				"development"
			]);

			await search(wrapper, "vel");
			expect(choices(wrapper).map((option) => option.value)).toEqual([
				"development"
			]);
		});
	});

	describe("showCreate computed", () => {
		it("hides the create button for an existing option", async () => {
			const wrapper = mount({ create: true, options }, {}, stubs);
			const button = () => wrapper.find(".k-picklist-input-create");

			await search(wrapper, "Marketing");
			expect(button().exists()).toBe(true);

			// matches the text of one option and the value of another
			await search(wrapper, "Design");
			expect(button().exists()).toBe(false);

			await search(wrapper, "development");
			expect(button().exists()).toBe(false);
		});

		it("hides the create button for an ignored query", async () => {
			const wrapper = mount(
				{ create: true, ignore: ["Marketing"], options },
				{},
				stubs
			);
			const button = () => wrapper.find(".k-picklist-input-create");

			await search(wrapper, "Sales");
			expect(button().exists()).toBe(true);

			await search(wrapper, "Marketing");
			expect(button().exists()).toBe(false);
		});

		it("hides the create button for a query ignored by `create`", async () => {
			const wrapper = mount(
				{ create: { ignore: ["Marketing"] }, options },
				{},
				stubs
			);
			const button = () => wrapper.find(".k-picklist-input-create");

			await search(wrapper, "Sales");
			expect(button().exists()).toBe(true);

			await search(wrapper, "Marketing");
			expect(button().exists()).toBe(false);
		});
	});

	// methods
	describe("add()", () => {
		it("emits `create` with the query on Enter", async () => {
			const wrapper = mount({ create: true, options }, {}, stubs);
			await search(wrapper, "Marketing");

			const input = wrapper.find(".k-picklist-input-search input");
			await input.trigger("keydown", { key: "Enter" });

			expect(wrapper.emitted("create")).toStrictEqual([["Marketing"]]);
		});

		it("emits `create` with the query on click", async () => {
			const wrapper = mount({ create: true, options }, {}, stubs);
			await search(wrapper, "Marketing");
			await wrapper.find(".k-picklist-input-create").trigger("click");

			expect(wrapper.emitted("create")).toStrictEqual([["Marketing"]]);
		});

		it("does not emit `create` on Enter for an existing option", async () => {
			const wrapper = mount({ create: true, options }, {}, stubs);
			await search(wrapper, "Design");

			const input = wrapper.find(".k-picklist-input-search input");
			await input.trigger("keydown", { key: "Enter" });

			expect(wrapper.emitted("create")).toBeUndefined();
		});
	});

	describe("escape()", () => {
		it("clears the query before emitting `escape`", async () => {
			const wrapper = mount({ options }, {}, stubs);
			const input = wrapper.find(".k-picklist-input-search input");
			await search(wrapper, "velop");

			await input.trigger("keydown", { key: "Escape" });
			expect((input.element as HTMLInputElement).value).toBe("");
			expect(choices(wrapper)).toHaveLength(2);
			expect(wrapper.emitted("escape")).toBeUndefined();

			await input.trigger("keydown", { key: "Escape" });
			expect(wrapper.emitted("escape")).toHaveLength(1);
		});
	});

	describe("highlight()", () => {
		// the text of the option the checkbox or radio input receives
		async function highlight(
			text: unknown,
			query = "",
			multiple = true
		): Promise<HtmlString> {
			const wrapper = mount(
				{ multiple, options: [{ text, value: "a" }] },
				{},
				{
					"k-checkboxes-input": ChoicesInput,
					"k-radio-input": ChoicesInput
				}
			);

			await wrapper.setData({ query });
			return wrapper.findComponent(ChoicesInput).props("options")[0].text;
		}

		it("wraps the match in bold", async () => {
			expect(String(await highlight("Kirby", "ir"))).toBe("K<b>ir</b>by");
		});

		it("matches case-insensitively", async () => {
			expect(String(await highlight("Kirby", "KIR"))).toBe("<b>Kir</b>by");
		});

		it("wraps every occurrence", async () => {
			expect(String(await highlight("aXbXc", "X"))).toBe("a<b>X</b>b<b>X</b>c");
		});

		it("returns the text unchanged without a query", async () => {
			expect(String(await highlight("Kirby"))).toBe("Kirby");
		});

		it("treats the query literally, not as a pattern", async () => {
			expect(String(await highlight("a.b", "."))).toBe("a<b>.</b>b");
		});

		it("returns trusted HTML so the match markup survives rendering", async () => {
			const result = await highlight(html("Kirby"), "ir");

			expect(result).toBeInstanceOf(HtmlString);
			expect(result.toString()).toBe("K<b>ir</b>by");
		});

		it("returns trusted HTML for the radio input as well", async () => {
			const result = await highlight("Kirby", "ir", false);

			expect(result).toBeInstanceOf(HtmlString);
			expect(result.toString()).toBe("K<b>ir</b>by");
		});

		it("escapes untrusted text before adding the match markup", async () => {
			const result = await highlight("<script>x</script>", "script");

			expect(result).toBeInstanceOf(HtmlString);
			expect(result.toString()).toBe(
				"&lt;<b>script</b>&gt;x&lt;&#x2F;<b>script</b>&gt;"
			);
		});

		it("neutralises untrusted markup that stripHTML would miss", async () => {
			// an unclosed tag never matches stripHTML's `<…>` regex,
			// so escaping — not stripping — is what makes this safe
			const result = await highlight("<img src=x onerror=alert(1)");

			expect(result).toBeInstanceOf(HtmlString);
			expect(result.toString()).not.toContain("<img");
			expect(result.toString()).toContain("&lt;img");
		});

		it("strips tags from trusted HTML so a match cannot land inside one", async () => {
			expect(String(await highlight(html("<b>Kirby</b>"), "b"))).toBe(
				"Kir<b>b</b>y"
			);
		});

		it("does not double-escape entities in trusted HTML", async () => {
			expect(String(await highlight(html("Tom &amp; Jerry")))).toBe(
				"Tom &amp; Jerry"
			);
		});
	});
});
