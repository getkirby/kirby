import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import List from "./List.vue";

// the writer sits inside the list input inside k-input
const Writer = {
	name: "k-writer-input",
	data: () => ({ isCursorAtEnd: false, isCursorAtStart: false }),
	methods: {
		getSplitContent: vi.fn(() => [
			"<ul><li><p>A</p></li><li><p></p></li></ul>",
			"<ul><li><p></p></li><li><p>B</p></li></ul>"
		])
	},
	template: "<div />"
};
const ListInput = {
	name: "k-list-input",
	components: { Writer },
	template: `<Writer ref="input" />`
};
const Input = {
	name: "k-input",
	components: { ListInput },
	props: ["disabled", "keys", "marks", "type", "value"],
	emits: ["input"],
	methods: { focus: vi.fn() },
	template: `<div><ListInput ref="input" /></div>`
};

const fieldset = {
	tabs: { content: { fields: { text: { marks: ["bold"] } } } }
};

function mount(props = {}, attrs = {}) {
	return vueMount(List, {
		props: {
			content: { text: "<ul><li><p>A</p></li><li><p>B</p></li></ul>" },
			fieldset,
			...props
		},
		attrs,
		global: {
			stubs: { "k-input": Input }
		}
	});
}

function input(wrapper: ReturnType<typeof mount>) {
	return wrapper.findComponent(Input);
}

function writer(wrapper: ReturnType<typeof mount>) {
	return wrapper.findComponent(Writer);
}

describe("k-block-type-list", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-block-type-list-input");
		it.inheritsNoAttrs(mount);

		it("renders a list input", () => {
			expect(input(mount()).props()).toMatchObject({
				disabled: false,
				marks: ["bold"],
				type: "list",
				value: "<ul><li><p>A</p></li><li><p>B</p></li></ul>"
			});
		});
	});

	// props
	describe("disabled prop", () => {
		it("disables the input", () => {
			expect(input(mount({ disabled: true })).props("disabled")).toBe(true);
		});
	});

	// computed
	describe("isSplitable computed", () => {
		it("is splitable with the cursor in the middle", () => {
			expect(mount().vm.isSplitable).toBe(true);
		});

		it("is not splitable with the cursor at the end", () => {
			const wrapper = mount();
			writer(wrapper).vm.isCursorAtEnd = true;
			expect(wrapper.vm.isSplitable).toBe(false);
		});
	});

	describe("keys computed", () => {
		it("splits the list on mod+enter", () => {
			const wrapper = mount();
			input(wrapper).props("keys")["Mod-Enter"]();
			expect(wrapper.emitted("split")).toHaveLength(1);
		});
	});

	// methods
	describe("focus()", () => {
		it("focuses the input", () => {
			const wrapper = mount();
			const focus = vi.spyOn(input(wrapper).vm, "focus");
			wrapper.vm.focus();
			expect(focus).toHaveBeenCalledOnce();
		});
	});

	describe("input()", () => {
		it("returns the writer", () => {
			const wrapper = mount();
			expect(wrapper.vm.input()).toBe(writer(wrapper).vm);
		});
	});

	describe("merge()", () => {
		it("joins the lists of the blocks", () => {
			const wrapper = mount();
			wrapper.vm.merge([
				{ content: { text: "<ul><li><p>A</p></li></ul>" } },
				{ content: { text: "<ul><li><p>B</p></li></ul>" } }
			]);
			expect(wrapper.emitted("update")).toStrictEqual([
				[{ text: "<ul><li><p>A</p></li><li><p>B</p></li></ul>" }]
			]);
		});
	});

	describe("split()", () => {
		it("splits into two lists without the empty items at the cut", () => {
			const wrapper = mount();
			wrapper.vm.split();
			expect(wrapper.emitted("split")).toStrictEqual([
				[
					[
						{ text: "<ul><li><p>A</p></li></ul>" },
						{ text: "<ul><li><p>B</p></li></ul>" }
					]
				]
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
			input(wrapper).vm.$emit("input", "<ul><li><p>C</p></li></ul>");
			expect(wrapper.emitted("update")).toStrictEqual([
				[{ text: "<ul><li><p>C</p></li></ul>" }]
			]);
		});
	});
});
