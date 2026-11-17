import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import TagsField from "./TagsField.vue";

const Field = {
	name: "k-field",
	props: ["counter"],
	template: "<div><slot /></div>"
};
const Input = {
	name: "k-input",
	props: ["accept", "id", "options", "type", "value"],
	emits: ["input"],
	methods: { focus: vi.fn() },
	template: "<div />"
};
const options = [{ text: "Alpha", value: "a" }];

function mount(props = {}, attrs = {}, stubs = {}) {
	return vueMount(TagsField, {
		props,
		attrs,
		global: {
			stubs: { "k-input": Input, ...stubs }
		}
	});
}

describe("k-tags-field", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-FIELD", "k-tags-field");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("renders a tags k-input", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Input).props("type")).toBe("tags");
		});
	});

	// props
	describe("accept prop", () => {
		it("passes accept and options to the k-input", () => {
			const wrapper = mount({ accept: "options", options });
			expect(wrapper.findComponent(Input).props("accept")).toBe("options");
			expect(wrapper.findComponent(Input).props("options")).toStrictEqual(
				options
			);
		});
	});

	describe("counter prop", () => {
		it("counts the tags", () => {
			const wrapper = mount(
				{ max: 3, value: ["a", "b"] },
				{},
				{ "k-field": Field }
			);
			expect(wrapper.findComponent(Field).props("counter")).toStrictEqual({
				count: 2,
				max: 3,
				min: undefined
			});
		});
	});

	describe("id prop", () => {
		it("links the k-field label to the k-input", () => {
			const wrapper = mount({ id: "tags" });
			expect(wrapper.attributes("input")).toBe("tags");
			expect(wrapper.findComponent(Input).props("id")).toBe("tags");
		});
	});

	describe("options prop", () => {
		it("keeps a value without option as custom tag when accepting all", () => {
			const wrapper = mount({ accept: "all", options, value: ["a", "b"] });
			expect(wrapper.findComponent(Input).props("options")).toStrictEqual(
				options
			);
		});

		it("adds an option for a value without option when accepting only options", () => {
			const wrapper = mount({ accept: "options", options, value: ["a", "b"] });
			expect(wrapper.findComponent(Input).props("options")).toStrictEqual([
				...options,
				{ text: "[unknown]", value: "b" }
			]);
		});

		it("renders the input for a value without options when accepting only options", () => {
			const wrapper = mount({ accept: "options", options: [], value: ["b"] });
			expect(wrapper.find("k-empty").exists()).toBe(false);
			expect(wrapper.findComponent(Input).props("options")).toStrictEqual([
				{ text: "[unknown]", value: "b" }
			]);
		});
	});

	describe("value prop", () => {
		it("passes the value to the k-input", () => {
			const wrapper = mount({ value: ["a"] });
			expect(wrapper.findComponent(Input).props("value")).toStrictEqual(["a"]);
		});
	});

	// computed
	describe("hasNoOptions computed", () => {
		it("shows an empty state for options only without options", () => {
			const wrapper = mount({ accept: "options", icon: "tag" });
			const empty = wrapper.find("k-empty");
			expect(empty.attributes("icon")).toBe("tag");
			expect(empty.attributes("text")).toBe("options.none");
			expect(wrapper.findComponent(Input).exists()).toBe(false);
		});

		it("renders the input for custom tags without options", () => {
			const wrapper = mount();
			expect(wrapper.vm.hasNoOptions).toBe(false);
			expect(wrapper.find("k-empty").exists()).toBe(false);
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
			wrapper.findComponent(Input).vm.$emit("input", ["a"]);
			expect(wrapper.emitted("input")).toStrictEqual([[["a"]]]);
		});
	});
});
