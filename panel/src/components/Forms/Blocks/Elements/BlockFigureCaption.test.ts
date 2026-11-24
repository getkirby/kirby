import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import BlockFigureCaption from "./BlockFigureCaption.vue";

const Writer = {
	name: "k-writer-input",
	props: ["disabled", "inline", "marks", "spellcheck", "value"],
	emits: ["input"],
	template: "<div />"
};

function mount(props = {}, attrs = {}) {
	return vueMount(BlockFigureCaption, {
		props: { value: "Hello", ...props },
		attrs,
		global: {
			stubs: { "k-writer-input": Writer }
		}
	});
}

function writer(wrapper: ReturnType<typeof mount>) {
	return wrapper.findComponent(Writer);
}

describe("k-block-figure-caption", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "FIGCAPTION", "k-block-figure-caption");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);

		it("renders an inline writer without marks and spellcheck", () => {
			expect(writer(mount()).props()).toStrictEqual({
				disabled: false,
				inline: true,
				marks: false,
				spellcheck: false,
				value: "Hello"
			});
		});
	});

	// props
	describe("disabled prop", () => {
		it("disables the writer", () => {
			expect(writer(mount({ disabled: true })).props("disabled")).toBe(true);
		});
	});

	describe("marks prop", () => {
		it("passes the marks", () => {
			const wrapper = mount({ marks: ["bold", "italic"] });
			expect(writer(wrapper).props("marks")).toStrictEqual(["bold", "italic"]);
		});
	});

	// events
	describe("events", () => {
		it("emits the changed caption", () => {
			const wrapper = mount();
			writer(wrapper).vm.$emit("input", "Hi");
			expect(wrapper.emitted("input")).toStrictEqual([["Hi"]]);
		});
	});
});
