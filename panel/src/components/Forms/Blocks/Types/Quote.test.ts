import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Quote from "./Quote.vue";

const Writer = {
	name: "k-writer-input",
	props: ["disabled", "inline", "marks", "nodes", "placeholder", "value"],
	emits: ["input"],
	methods: { focus: vi.fn() },
	template: "<div />"
};

const fieldset = {
	tabs: {
		content: {
			fields: {
				citation: { marks: ["link"], placeholder: "by …" },
				text: { marks: ["bold"], nodes: false, placeholder: "Quote …" }
			}
		}
	}
};

function mount(props = {}, attrs = {}) {
	return vueMount(Quote, {
		props: {
			content: { citation: "Ada", text: "Hello" },
			fieldset,
			...props
		},
		attrs,
		global: {
			stubs: { "k-writer-input": Writer }
		}
	});
}

function writers(wrapper: ReturnType<typeof mount>) {
	return wrapper.findAllComponents(Writer);
}

describe("k-block-type-quote", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-block-type-quote-editor");
		it.inheritsNoAttrs(mount);

		it("renders a writer for the text", () => {
			const text = writers(mount())[0];
			expect(text.classes()).toContain("k-block-type-quote-text");
			expect(text.props()).toStrictEqual({
				disabled: false,
				inline: false,
				marks: ["bold"],
				nodes: false,
				placeholder: "Quote …",
				value: "Hello"
			});
		});

		it("renders an inline writer for the citation", () => {
			const citation = writers(mount())[1];
			expect(citation.classes()).toContain("k-block-type-quote-citation");
			expect(citation.props()).toStrictEqual({
				disabled: false,
				inline: true,
				marks: ["link"],
				nodes: undefined,
				placeholder: "by …",
				value: "Ada"
			});
		});
	});

	// props
	describe("disabled prop", () => {
		it("disables both writers", () => {
			const wrapper = mount({ disabled: true });
			expect(
				writers(wrapper).map((writer) => writer.props("disabled"))
			).toStrictEqual([true, true]);
		});
	});

	describe("fieldset prop", () => {
		it("lets the fields switch the inline mode", () => {
			const wrapper = mount({
				fieldset: {
					tabs: {
						content: {
							fields: { citation: { inline: false }, text: { inline: true } }
						}
					}
				}
			});
			expect(
				writers(wrapper).map((writer) => writer.props("inline"))
			).toStrictEqual([true, false]);
		});
	});

	// methods
	describe("focus()", () => {
		it("focuses the text", () => {
			const wrapper = mount();
			const focus = vi.spyOn(writers(wrapper)[0].vm, "focus");
			wrapper.vm.focus();
			expect(focus).toHaveBeenCalledOnce();
		});
	});

	// events
	describe("events", () => {
		it("updates the text", () => {
			const wrapper = mount();
			writers(wrapper)[0].vm.$emit("input", "Hi");
			expect(wrapper.emitted("update")).toStrictEqual([
				[{ citation: "Ada", text: "Hi" }]
			]);
		});

		it("updates the citation", () => {
			const wrapper = mount();
			writers(wrapper)[1].vm.$emit("input", "Grace");
			expect(wrapper.emitted("update")).toStrictEqual([
				[{ citation: "Grace", text: "Hello" }]
			]);
		});
	});
});
