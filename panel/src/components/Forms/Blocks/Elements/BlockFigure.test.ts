import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import BlockFigure from "./BlockFigure.vue";

const Button = {
	name: "k-button",
	props: ["disabled", "icon", "text"],
	emits: ["click"],
	template: `<button @click="$emit('click', $event)" />`
};
const Caption = {
	name: "k-block-figure-caption",
	props: ["disabled", "marks", "value"],
	emits: ["input"],
	template: "<figcaption />"
};

function mount(props = {}, attrs = {}) {
	return vueMount(BlockFigure, {
		props,
		attrs,
		slots: { default: `<img src="a.jpg" />` },
		global: {
			stubs: { "k-block-figure-caption": Caption, "k-button": Button }
		}
	});
}

describe("k-block-figure", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "FIGURE", "k-block-figure");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	// props
	describe("back prop", () => {
		it("sets the background", () => {
			const wrapper = mount({ back: "var(--pattern)" });
			const figure = wrapper.element as HTMLElement;
			expect(figure.style.getPropertyValue("--block-figure-back")).toBe(
				"var(--pattern)"
			);
		});

		it("keeps the background next to a custom style", () => {
			const wrapper = mount({ back: "black" }, { style: "--foo: bar" });
			const figure = wrapper.element as HTMLElement;
			expect(figure.style.getPropertyValue("--block-figure-back")).toBe(
				"black"
			);
			expect(figure.style.getPropertyValue("--foo")).toBe("bar");
		});
	});

	describe("caption prop", () => {
		it("shows the caption", () => {
			const wrapper = mount({ caption: "Hello", disabled: true });
			expect(wrapper.findComponent(Caption).props()).toStrictEqual({
				disabled: true,
				marks: true,
				value: "Hello"
			});
		});

		it("shows no caption without value", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Caption).exists()).toBe(false);
		});
	});

	describe("captionMarks prop", () => {
		it("passes the marks to the caption", () => {
			const wrapper = mount({ caption: "Hello", captionMarks: ["bold"] });
			expect(wrapper.findComponent(Caption).props("marks")).toStrictEqual([
				"bold"
			]);
		});
	});

	describe("disabled prop", () => {
		it("disables the content", () => {
			const wrapper = mount({ disabled: true });
			expect(
				wrapper.find(".k-block-figure-container").attributes("data-disabled")
			).toBe("true");
		});

		it("disables the empty button", () => {
			const wrapper = mount({ disabled: true, isEmpty: true });
			expect(wrapper.findComponent(Button).props("disabled")).toBe(true);
		});
	});

	describe("isEmpty prop", () => {
		it("shows the content", () => {
			const wrapper = mount();
			expect(wrapper.attributes("data-empty")).toBe("false");
			expect(wrapper.find(".k-block-figure-container img").exists()).toBe(true);
			expect(wrapper.findComponent(Button).exists()).toBe(false);
		});

		it("shows a button instead of the content", () => {
			const wrapper = mount({
				emptyIcon: "image",
				emptyText: "Select an image",
				isEmpty: true
			});
			const button = wrapper.findComponent(Button);
			expect(wrapper.attributes("data-empty")).toBe("true");
			expect(wrapper.find("img").exists()).toBe(false);
			expect(button.classes()).toContain("k-block-figure-empty");
			expect(button.props()).toStrictEqual({
				disabled: false,
				icon: "image",
				text: "Select an image"
			});
		});
	});

	// events
	describe("events", () => {
		it("emits open on click of the empty button", async () => {
			const wrapper = mount({ isEmpty: true });
			await wrapper.findComponent(Button).trigger("click");
			expect(wrapper.emitted("open")).toHaveLength(1);
		});

		it("emits open on double click of the content", async () => {
			const wrapper = mount();
			await wrapper.find(".k-block-figure-container").trigger("dblclick");
			expect(wrapper.emitted("open")).toHaveLength(1);
		});

		it("emits the changed caption", () => {
			const wrapper = mount({ caption: "Hello" });
			wrapper.findComponent(Caption).vm.$emit("input", "Hi");
			expect(wrapper.emitted("update")).toStrictEqual([[{ caption: "Hi" }]]);
		});
	});

	// slots
	describe("default slot", () => {
		it("renders the content in the container", () => {
			const wrapper = mount();
			expect(wrapper.find(".k-block-figure-container").html()).toContain(
				`<img src="a.jpg">`
			);
		});
	});
});
