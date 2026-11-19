import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import ListField from "./ListField.vue";

const Field = {
	name: "k-field",
	props: ["counter", "input"],
	template: "<div><slot /></div>"
};
const Input = {
	name: "k-input",
	props: ["id", "marks", "nodes", "type", "value"],
	emits: ["input"],
	methods: { focus: vi.fn() },
	template: "<div />"
};

function mount(props = {}, attrs = {}, stubs = {}) {
	return vueMount(ListField, {
		props,
		attrs,
		global: {
			stubs: { "k-input": Input, ...stubs }
		}
	});
}

describe("k-list-field", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-FIELD", "k-list-field");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("renders a list k-input", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Input).props("type")).toBe("list");
		});

		it("shows no counter", () => {
			const wrapper = mount(
				{ value: "<ul><li>A</li></ul>" },
				{},
				{ "k-field": Field }
			);
			expect(wrapper.findComponent(Field).props("counter")).toBe(false);
		});
	});

	// props
	describe("id prop", () => {
		it("links the k-field label to the k-input", () => {
			const wrapper = mount({ id: "list" });
			expect(wrapper.attributes("input")).toBe("list");
			expect(wrapper.findComponent(Input).props("id")).toBe("list");
		});
	});

	describe("nodes prop", () => {
		it("offers both lists by default", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Input).props("nodes")).toStrictEqual([
				"bulletList",
				"orderedList"
			]);
		});

		it("passes the marks and nodes to the k-input", () => {
			const wrapper = mount({ marks: ["bold"], nodes: ["orderedList"] });
			const input = wrapper.findComponent(Input);
			expect(input.props("marks")).toStrictEqual(["bold"]);
			expect(input.props("nodes")).toStrictEqual(["orderedList"]);
		});
	});

	describe("value prop", () => {
		it("passes the value to the k-input", () => {
			const wrapper = mount({ value: "<ul><li>A</li></ul>" });
			expect(wrapper.findComponent(Input).props("value")).toBe(
				"<ul><li>A</li></ul>"
			);
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
			wrapper.findComponent(Input).vm.$emit("input", "<ul><li>A</li></ul>");
			expect(wrapper.emitted("input")).toStrictEqual([["<ul><li>A</li></ul>"]]);
		});
	});
});
