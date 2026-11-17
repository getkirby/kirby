import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Code from "./Code.vue";

const Input = {
	name: "k-input",
	props: [
		"buttons",
		"disabled",
		"font",
		"icon",
		"options",
		"placeholder",
		"spellcheck",
		"type",
		"value"
	],
	emits: ["input"],
	methods: { focus: vi.fn() },
	template: "<div />"
};

const languages = [
	{ text: "CSS", value: "css" },
	{ text: "PHP", value: "php" }
];

const fieldset = {
	tabs: {
		content: {
			fields: {
				code: { placeholder: "Your code …" },
				language: { options: languages }
			}
		}
	}
};

function mount(props = {}, attrs = {}) {
	return vueMount(Code, {
		props: {
			content: { code: "<?php", language: "php" },
			fieldset,
			...props
		},
		attrs,
		global: {
			stubs: { "k-input": Input }
		}
	});
}

function inputs(wrapper: ReturnType<typeof mount>) {
	return wrapper.findAllComponents(Input);
}

describe("k-block-type-code", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-block-type-code-editor");
		it.inheritsNoAttrs(mount);

		it("renders a monospace textarea for the code", () => {
			expect(inputs(mount())[0].props()).toStrictEqual({
				buttons: false,
				disabled: false,
				font: "monospace",
				icon: undefined,
				options: undefined,
				placeholder: "Your code …",
				spellcheck: false,
				type: "textarea",
				value: "<?php"
			});
		});

		it("renders a select for the language", () => {
			const wrapper = mount();
			const select = inputs(wrapper)[1];
			expect(wrapper.find(".k-block-type-code-editor-language").exists()).toBe(
				true
			);
			expect(select.props()).toMatchObject({
				disabled: false,
				icon: "code",
				options: languages,
				type: "select",
				value: "php"
			});
		});
	});

	// props
	describe("disabled prop", () => {
		it("disables both inputs", () => {
			const wrapper = mount({ disabled: true });
			expect(
				inputs(wrapper).map((input) => input.props("disabled"))
			).toStrictEqual([true, true]);
		});
	});

	// computed
	describe("languages computed", () => {
		it("hides the select without languages", () => {
			const wrapper = mount({ fieldset: {} });
			expect(inputs(wrapper)).toHaveLength(1);
			expect(wrapper.find(".k-block-type-code-editor-language").exists()).toBe(
				false
			);
		});
	});

	// methods
	describe("focus()", () => {
		it("focuses the code", () => {
			const wrapper = mount();
			const focus = vi.spyOn(inputs(wrapper)[0].vm, "focus");
			wrapper.vm.focus();
			expect(focus).toHaveBeenCalledOnce();
		});
	});

	// events
	describe("events", () => {
		it("updates the code", () => {
			const wrapper = mount();
			inputs(wrapper)[0].vm.$emit("input", "<?php echo 1;");
			expect(wrapper.emitted("update")).toStrictEqual([
				[{ code: "<?php echo 1;", language: "php" }]
			]);
		});

		it("updates the language", () => {
			const wrapper = mount();
			inputs(wrapper)[1].vm.$emit("input", "css");
			expect(wrapper.emitted("update")).toStrictEqual([
				[{ code: "<?php", language: "css" }]
			]);
		});
	});
});
