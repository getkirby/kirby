import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Heading from "./Heading.vue";

const Input = {
	name: "k-input",
	props: ["options", "type", "value"],
	emits: ["input"],
	template: "<div />"
};
const Writer = {
	name: "k-writer-input",
	props: ["disabled", "inline", "keys", "marks", "placeholder", "value"],
	emits: ["input"],
	data: () => ({ isCursorAtEnd: false, isCursorAtStart: false }),
	methods: {
		focus: vi.fn(),
		getSplitContent: vi.fn(() => ["Hel", "lo"])
	},
	template: "<div />"
};

const levels = [
	{ text: "H1", value: "h1" },
	{ text: "H2", value: "h2" },
	{ text: "H6", value: "h6" }
];

const fieldset = {
	tabs: {
		content: {
			fields: {
				level: { options: levels },
				text: { marks: ["bold"], placeholder: "Heading …" }
			}
		}
	}
};

function mount(props = {}, attrs = {}) {
	return vueMount(Heading, {
		props: { content: { level: "h2", text: "Hello" }, fieldset, ...props },
		attrs,
		global: {
			stubs: { "k-input": Input, "k-writer-input": Writer }
		}
	});
}

function writer(wrapper: ReturnType<typeof mount>) {
	return wrapper.findComponent(Writer);
}

describe("k-block-type-heading", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-block-type-heading-input");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("marks the level", () => {
			expect(mount().attributes("data-level")).toBe("h2");
		});

		it("renders an inline writer with the text field's options", () => {
			const wrapper = mount();
			expect(writer(wrapper).props()).toMatchObject({
				disabled: false,
				inline: true,
				marks: ["bold"],
				placeholder: "Heading …",
				value: "Hello"
			});
		});

		it("renders a select for the level", () => {
			const select = mount().findComponent(Input);
			expect(select.classes()).toContain("k-block-type-heading-level");
			expect(select.props()).toStrictEqual({
				options: levels,
				type: "select",
				value: "h2"
			});
		});
	});

	// props
	describe("disabled prop", () => {
		it("disables the writer and hides the level", () => {
			const wrapper = mount({ disabled: true });
			expect(writer(wrapper).props("disabled")).toBe(true);
			expect(wrapper.findComponent(Input).exists()).toBe(false);
		});
	});

	// computed
	describe("keys computed", () => {
		it("appends a text block on enter at the end", () => {
			const wrapper = mount();
			writer(wrapper).vm.isCursorAtEnd = true;
			writer(wrapper).props("keys").Enter();
			expect(wrapper.emitted("append")).toStrictEqual([["text"]]);
			expect(wrapper.emitted("split")).toBeUndefined();
		});

		it("splits the heading on enter in the middle", () => {
			const wrapper = mount();
			writer(wrapper).props("keys").Enter();
			expect(wrapper.emitted("split")).toHaveLength(1);
			expect(wrapper.emitted("append")).toBeUndefined();
		});

		it("splits the heading on mod+enter", () => {
			const wrapper = mount();
			writer(wrapper).props("keys")["Mod-Enter"]();
			expect(wrapper.emitted("split")).toHaveLength(1);
		});
	});

	describe("levels computed", () => {
		it("hides the select for a single level", () => {
			const wrapper = mount({
				fieldset: {
					tabs: { content: { fields: { level: { options: [levels[1]] } } } }
				}
			});
			expect(wrapper.findComponent(Input).exists()).toBe(false);
		});
	});

	describe("textField computed", () => {
		it("allows marks without text field", () => {
			const wrapper = mount({ fieldset: {} });
			expect(writer(wrapper).props("marks")).toBe(true);
		});
	});

	describe("isSplitable computed", () => {
		it("is splitable with the cursor in the middle", () => {
			const wrapper = mount();
			expect(wrapper.vm.isSplitable).toBe(true);
		});

		it("is not splitable with the cursor at the start", async () => {
			const wrapper = mount();
			writer(wrapper).vm.isCursorAtStart = true;
			expect(wrapper.vm.isSplitable).toBe(false);
		});
	});

	// methods
	describe("focus()", () => {
		it("focuses the writer", () => {
			const wrapper = mount();
			const focus = vi.spyOn(writer(wrapper).vm, "focus");
			wrapper.vm.focus();
			expect(focus).toHaveBeenCalledOnce();
		});
	});

	describe("merge()", () => {
		it("joins the texts of the blocks", () => {
			const wrapper = mount();
			wrapper.vm.merge([
				{ content: { text: "Hello" } },
				{ content: { text: "World" } }
			]);
			expect(wrapper.emitted("update")).toStrictEqual([
				[{ level: "h2", text: "Hello World" }]
			]);
		});
	});

	describe("split()", () => {
		it("splits into the same and the next lower level", () => {
			const wrapper = mount();
			wrapper.vm.split();
			expect(wrapper.emitted("split")).toStrictEqual([
				[[{ text: "Hel" }, { level: "h3", text: "lo" }]]
			]);
		});

		it("keeps the lowest level", () => {
			const wrapper = mount({ content: { level: "h6", text: "Hello" } });
			wrapper.vm.split();
			expect(wrapper.emitted("split")?.[0][0]).toStrictEqual([
				{ text: "Hel" },
				{ level: "h6", text: "lo" }
			]);
		});

		it("does nothing without split content", () => {
			const wrapper = mount();
			vi.spyOn(writer(wrapper).vm, "getSplitContent").mockReturnValueOnce(
				undefined as never
			);
			wrapper.vm.split();
			expect(wrapper.emitted("split")).toBeUndefined();
		});
	});

	// events
	describe("events", () => {
		it("updates the text", () => {
			const wrapper = mount();
			writer(wrapper).vm.$emit("input", "Hi");
			expect(wrapper.emitted("update")).toStrictEqual([
				[{ level: "h2", text: "Hi" }]
			]);
		});

		it("updates the level", () => {
			const wrapper = mount();
			wrapper.findComponent(Input).vm.$emit("input", "h1");
			expect(wrapper.emitted("update")).toStrictEqual([
				[{ level: "h1", text: "Hello" }]
			]);
		});
	});
});
