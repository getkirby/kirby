import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import TagsField from "./TagsField.vue";

const Input = {
	name: "k-input",
	props: ["options"],
	emits: ["input"],
	template: "<div />"
};
const options = [{ text: "A", value: "a" }];

function mount(props = {}, attrs = {}) {
	return vueMount(TagsField, {
		props: { options, ...props },
		attrs,
		global: {
			stubs: {
				"k-input": Input
			}
		}
	});
}

describe("k-tags-field", () => {
	// $el
	describe("element", () => {
		const component = (attrs = {}) => mount({}, attrs);

		it.rendersAs(component, "K-FIELD", "k-tags-field");
		it.acceptsClass(component);
		it.acceptsStyle(component);
		it.inheritsNoAttrs(component);
	});

	// props
	describe("options prop", () => {
		it("passes the options to the input", () => {
			const wrapper = mount({ value: ["a"] });
			expect(wrapper.findComponent(Input).props("options")).toStrictEqual(
				options
			);
		});

		it("keeps a value without option as custom tag when accepting all", () => {
			const wrapper = mount({ accept: "all", value: ["a", "b"] });
			expect(wrapper.findComponent(Input).props("options")).toStrictEqual(
				options
			);
		});

		it("adds an option for a value without option when accepting only options", () => {
			const wrapper = mount({ accept: "options", value: ["a", "b"] });
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

		it("renders an empty state without options when accepting only options", () => {
			const wrapper = mount({ accept: "options", options: [] });
			expect(wrapper.find("k-empty").exists()).toBe(true);
			expect(wrapper.findComponent(Input).exists()).toBe(false);
		});

		it("renders the input without options when accepting all", () => {
			const wrapper = mount({ accept: "all", options: [] });
			expect(wrapper.find("k-empty").exists()).toBe(false);
			expect(wrapper.findComponent(Input).exists()).toBe(true);
		});
	});

	// events
	describe("input event", () => {
		it("emits the value of the input", () => {
			const wrapper = mount();
			wrapper.findComponent(Input).vm.$emit("input", ["a"]);
			expect(wrapper.emitted("input")).toStrictEqual([[["a"]]]);
		});
	});
});
