import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import WriterField from "./WriterField.vue";

const Field = {
	name: "k-field",
	props: ["counter", "input"],
	template: "<div><slot /></div>"
};
const Input = {
	name: "k-input",
	props: [
		"after",
		"before",
		"icon",
		"id",
		"inline",
		"marks",
		"nodes",
		"type",
		"value"
	],
	emits: ["input"],
	methods: { focus: vi.fn() },
	template: "<div />"
};

function mount(props = {}, attrs = {}, stubs = {}) {
	return vueMount(WriterField, {
		props,
		attrs,
		global: {
			stubs: { "k-input": Input, ...stubs }
		}
	});
}

describe("k-writer-field", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-FIELD", "k-writer-field");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("renders a writer k-input", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Input).props("type")).toBe("writer");
		});
	});

	// props
	describe("after prop", () => {
		it("passes the text after to the k-input", () => {
			const wrapper = mount({ after: "after", before: "before", icon: "text" });
			const input = wrapper.findComponent(Input);
			expect(input.props("after")).toBe("after");
			expect(input.props("before")).toBe("before");
			expect(input.props("icon")).toBe("text");
		});
	});

	describe("counter prop", () => {
		it("counts the characters of the text", () => {
			const wrapper = mount(
				{ maxlength: 100, value: "<p>a &amp; <b>b</b></p>" },
				{},
				{ "k-field": Field }
			);
			expect(wrapper.findComponent(Field).props("counter")).toStrictEqual({
				count: 5,
				max: 100,
				min: undefined
			});
		});
	});

	describe("id prop", () => {
		it("links the k-field label to the k-input", () => {
			const wrapper = mount({ id: "text" });
			expect(wrapper.attributes("input")).toBe("text");
			expect(wrapper.findComponent(Input).props("id")).toBe("text");
		});
	});

	describe("marks prop", () => {
		it("passes the editor options to the k-input", () => {
			const wrapper = mount({ inline: true, marks: ["bold"], nodes: false });
			const input = wrapper.findComponent(Input);
			expect(input.props("inline")).toBe(true);
			expect(input.props("marks")).toStrictEqual(["bold"]);
			expect(input.props("nodes")).toBe(false);
		});
	});

	describe("value prop", () => {
		it("passes the value to the k-input", () => {
			const wrapper = mount({ value: "<p>Hello</p>" });
			expect(wrapper.findComponent(Input).props("value")).toBe("<p>Hello</p>");
		});
	});

	// computed
	describe("counterValue computed", () => {
		it("is the plain text of the value", () => {
			const wrapper = mount({ value: "<p>a &amp; <b>b</b></p>" });
			expect(wrapper.vm.counterValue).toBe("a & b");
		});
	});

	// methods
	describe("focus()", () => {
		it("focuses the k-input", () => {
			const wrapper = mount();
			wrapper.vm.focus();
			expect(Input.methods.focus).toHaveBeenCalledOnce();
		});
	});

	// events
	describe("input event", () => {
		it("emits the value of the k-input", () => {
			const wrapper = mount();
			wrapper.findComponent(Input).vm.$emit("input", "<p>Hello</p>");
			expect(wrapper.emitted("input")).toStrictEqual([["<p>Hello</p>"]]);
		});
	});
});
