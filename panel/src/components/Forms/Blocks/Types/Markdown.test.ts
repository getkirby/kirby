import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Markdown from "./Markdown.vue";

const Input = {
	name: "k-input",
	props: [
		"buttons",
		"disabled",
		"font",
		"placeholder",
		"spellcheck",
		"type",
		"value"
	],
	emits: ["input"],
	methods: { focus: vi.fn() },
	template: "<div />"
};

const fieldset = {
	tabs: {
		content: { fields: { text: { placeholder: "Markdown …" } } }
	}
};

function mount(props = {}, attrs = {}) {
	return vueMount(Markdown, {
		props: { content: { text: "# Hello" }, fieldset, ...props },
		attrs,
		global: {
			stubs: { "k-input": Input }
		}
	});
}

function input(wrapper: ReturnType<typeof mount>) {
	return wrapper.findComponent(Input);
}

describe("k-block-type-markdown", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-block-type-markdown-input");
		it.inheritsNoAttrs(mount);

		it("renders a monospace textarea", () => {
			expect(input(mount()).props()).toStrictEqual({
				buttons: false,
				disabled: false,
				font: "monospace",
				placeholder: "Markdown …",
				spellcheck: false,
				type: "textarea",
				value: "# Hello"
			});
		});
	});

	// props
	describe("disabled prop", () => {
		it("disables the textarea", () => {
			expect(input(mount({ disabled: true })).props("disabled")).toBe(true);
		});
	});

	// computed
	describe("placeholder computed", () => {
		it("has no placeholder without text field", () => {
			const wrapper = mount({ fieldset: {} });
			expect(input(wrapper).props("placeholder")).toBeUndefined();
		});
	});

	// methods
	describe("focus()", () => {
		it("focuses the textarea", () => {
			const wrapper = mount();
			const focus = vi.spyOn(input(wrapper).vm, "focus");
			wrapper.vm.focus();
			expect(focus).toHaveBeenCalledOnce();
		});
	});

	// events
	describe("events", () => {
		it("updates the text", () => {
			const wrapper = mount({ content: { foo: "bar", text: "# Hello" } });
			input(wrapper).vm.$emit("input", "# Hi");
			expect(wrapper.emitted("update")).toStrictEqual([
				[{ foo: "bar", text: "# Hi" }]
			]);
		});
	});
});
