import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import TextareaField from "./TextareaField.vue";

const Field = {
	name: "k-field",
	props: ["counter", "input"],
	template: "<div><slot /></div>"
};
const Input = {
	name: "k-input",
	props: ["buttons", "id", "maxlength", "type", "uploads", "value"],
	emits: ["input"],
	methods: { focus: vi.fn() },
	template: "<div />"
};

function mount(props = {}, attrs = {}, stubs = {}) {
	return vueMount(TextareaField, {
		props,
		attrs,
		global: {
			stubs: { "k-input": Input, ...stubs }
		}
	});
}

describe("k-textarea-field", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-FIELD", "k-textarea-field");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("renders a textarea k-input", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Input).props("type")).toBe("textarea");
		});
	});

	// props
	describe("buttons prop", () => {
		it("passes the buttons to the k-input", () => {
			const wrapper = mount({ buttons: ["bold"] });
			expect(wrapper.findComponent(Input).props("buttons")).toStrictEqual([
				"bold"
			]);
		});
	});

	describe("counter prop", () => {
		it("counts the characters", () => {
			const wrapper = mount(
				{ maxlength: 100, value: "Hello" },
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

	describe("uploads prop", () => {
		it("passes the uploads to the k-input", () => {
			const wrapper = mount({ uploads: true });
			expect(wrapper.findComponent(Input).props("uploads")).toBe(true);
		});
	});

	describe("value prop", () => {
		it("passes the value to the k-input", () => {
			const wrapper = mount({ value: "Hello" });
			expect(wrapper.findComponent(Input).props("value")).toBe("Hello");
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
			wrapper.findComponent(Input).vm.$emit("input", "Hello");
			expect(wrapper.emitted("input")).toStrictEqual([["Hello"]]);
		});
	});
});
