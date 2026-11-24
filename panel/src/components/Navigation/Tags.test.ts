import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Tag from "./Tag.vue";
import Tags from "./Tags.vue";

const Draggable = {
	name: "k-draggable",
	props: ["element", "list", "options"],
	emits: ["end"],
	template: `<div><slot /><slot name="footer" /></div>`
};
const Navigate = {
	name: "k-navigate",
	props: ["axis", "select"],
	methods: { move: vi.fn() },
	template: "<div><slot /></div>"
};
const options = [
	{ text: "Alpha", value: "a" },
	{ text: "Beta", value: "b" }
];

function mount(props = {}, attrs = {}, slots = {}) {
	return vueMount(Tags, {
		props,
		attrs,
		slots,
		global: {
			components: { "k-tag": Tag },
			stubs: { "k-draggable": Draggable, "k-navigate": Navigate }
		}
	});
}

function texts(wrapper: ReturnType<typeof mount>) {
	return wrapper.findAll(".k-tag").map((tag) => tag.text());
}

describe("k-tags", () => {
	// $el
	describe("element", () => {
		it("renders the k-draggable tags in a k-navigate", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Navigate).exists()).toBe(true);
			expect(wrapper.findComponent(Draggable).classes()).toContain("k-tags");
		});

		it("accepts a custom class and style on the tags", () => {
			const wrapper = mount({}, { class: "my-class", style: "--foo: bar" });
			const tags = wrapper.findComponent(Draggable);
			expect(tags.classes()).toContain("my-class");
			expect(tags.attributes("style")).toContain("--foo: bar");
		});

		it("navigates focusable tags", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Navigate).props("select")).toBe(
				":where(.k-tag, .k-tags-navigatable):not(:disabled)"
			);
		});
	});

	// props
	describe("disabled prop", () => {
		it("disables the tags", () => {
			const wrapper = mount({ disabled: true, removable: true, value: ["a"] });
			expect(wrapper.find(".k-tag").attributes("aria-disabled")).toBe("true");
			expect(wrapper.find(".k-tag-toggle").exists()).toBe(false);
		});
	});

	describe("element prop", () => {
		it("renders the tags as divs by default", () => {
			const wrapper = mount({ value: ["a"] });
			expect(wrapper.findComponent(Draggable).props("element")).toBe("div");
			expect(wrapper.find(".k-tag").element.tagName).toBe("DIV");
		});

		it("renders the tags as the element", () => {
			const wrapper = mount({ element: "li", value: ["a"] });
			expect(wrapper.find(".k-tag").element.tagName).toBe("LI");
		});
	});

	describe("layout prop", () => {
		it("navigates horizontally by default", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Navigate).props("axis")).toBe("x");
		});

		it("navigates vertically in a list", () => {
			const wrapper = mount({ layout: "list" });
			expect(wrapper.findComponent(Navigate).props("axis")).toBe("y");
			expect(wrapper.findComponent(Draggable).attributes("data-layout")).toBe(
				"list"
			);
		});
	});

	describe("removable prop", () => {
		it("makes the tags removable", () => {
			const wrapper = mount({ removable: true, value: ["a"] });
			expect(wrapper.find(".k-tag-toggle").exists()).toBe(true);
		});
	});

	describe("theme prop", () => {
		it("passes the theme to the tags", () => {
			const wrapper = mount({ theme: "positive", value: ["a"] });
			expect(wrapper.find(".k-tag").attributes("data-theme")).toBe("positive");
		});
	});

	describe("value prop", () => {
		it("renders a tag for each value", () => {
			const wrapper = mount({ value: ["a", "b"] });
			expect(texts(wrapper)).toStrictEqual(["a", "b"]);
		});

		it("uses the text of a matching option", () => {
			const wrapper = mount({ options, value: ["b", "x"] });
			expect(texts(wrapper)).toStrictEqual(["Beta", "x"]);
		});

		it("passes icon, image and link of a tag", () => {
			const wrapper = mount({
				value: [{ icon: "heart", link: "/a", text: "A", value: "a" }]
			});
			const tag = wrapper.findComponent(Tag);
			expect(tag.props("icon")).toBe("heart");
			expect(tag.props("link")).toBe("/a");
		});
	});

	describe("sort prop", () => {
		it("keeps the order of the value by default", () => {
			const wrapper = mount({ options, value: ["x", "b", "a"] });
			expect(texts(wrapper)).toStrictEqual(["x", "Beta", "Alpha"]);
		});

		it("sorts the tags by the options, custom tags last", () => {
			const wrapper = mount({ options, sort: true, value: ["x", "b", "a"] });
			expect(texts(wrapper)).toStrictEqual(["Alpha", "Beta", "x"]);
		});
	});

	// computed
	describe("dragOptions computed", () => {
		it("drags tags by their text", () => {
			const wrapper = mount({ value: ["a"] });
			expect(wrapper.findComponent(Draggable).props("options")).toStrictEqual({
				delay: 1,
				disabled: false,
				draggable: ".k-tag",
				handle: ".k-tag-text"
			});
		});
	});

	describe("isDraggable computed", () => {
		it("is draggable with tags", () => {
			const wrapper = mount({ value: ["a"] });
			expect(wrapper.vm.isDraggable).toBe(true);
		});

		it.each([
			["without tags", {}],
			["when sorted", { sort: true, value: ["a"] }],
			["when not draggable", { draggable: false, value: ["a"] }],
			["when disabled", { disabled: true, value: ["a"] }]
		])("is not draggable %s", (_, props) => {
			const wrapper = mount(props);
			expect(wrapper.vm.isDraggable).toBe(false);
		});
	});

	// watch
	describe("value watcher", () => {
		it("renders the tags of a new value", async () => {
			const wrapper = mount({ value: ["a"] });
			await wrapper.setProps({ value: ["a", "b"] } as never);
			expect(texts(wrapper)).toStrictEqual(["a", "b"]);
		});

		it("leaves the value it was passed untouched", () => {
			const value = ["a", "b"];
			const wrapper = mount({ removable: true, value });
			wrapper.vm.remove(0);
			expect(value).toStrictEqual(["a", "b"]);
		});
	});

	// methods
	describe("edit()", () => {
		it("emits the tag to edit", () => {
			const wrapper = mount({ value: ["a"] });
			const event = new Event("dblclick");
			wrapper.vm.edit(0, { text: "a", value: "a" }, event);
			expect(wrapper.emitted("edit")).toStrictEqual([
				[0, { text: "a", value: "a" }, event]
			]);
		});

		it("emits nothing when disabled", () => {
			const wrapper = mount({ disabled: true, value: ["a"] });
			wrapper.vm.edit(0, { text: "a", value: "a" }, new Event("dblclick"));
			expect(wrapper.emitted("edit")).toBeUndefined();
		});
	});

	describe("focus()", () => {
		it("moves to the last tag by default", () => {
			const wrapper = mount();
			wrapper.vm.focus();
			expect(Navigate.methods.move).toHaveBeenCalledWith("last");
		});

		it("moves to the given tag", () => {
			const wrapper = mount();
			wrapper.vm.focus("first");
			expect(Navigate.methods.move).toHaveBeenCalledWith("first");
		});
	});

	describe("input()", () => {
		it("emits the values of the tags", () => {
			const wrapper = mount({ options, value: ["b", "a"] });
			wrapper.vm.input();
			expect(wrapper.emitted("input")).toStrictEqual([[["b", "a"]]]);
		});
	});

	describe("remove()", () => {
		it("emits the values without the removed tag", () => {
			const wrapper = mount({ value: ["a", "b"] });
			wrapper.vm.remove(0);
			expect(wrapper.emitted("input")).toStrictEqual([[["b"]]]);
		});

		it("moves to the previous tag", () => {
			const wrapper = mount({ value: ["a", "b"] });
			wrapper.vm.remove(1);
			expect(Navigate.methods.move).toHaveBeenCalledWith("prev");
		});

		it("moves to the end when removing the last tag", () => {
			const wrapper = mount({ value: ["a"] });
			wrapper.vm.remove(0);
			expect(Navigate.methods.move).toHaveBeenCalledWith("last");
		});
	});

	describe("option()", () => {
		it("finds the option of a tag", () => {
			const wrapper = mount({ options });
			expect(wrapper.vm.option({ value: "b" })).toStrictEqual(options[1]);
		});

		it("returns undefined without matching option", () => {
			const wrapper = mount({ options });
			expect(wrapper.vm.option({ value: "x" })).toBeUndefined();
		});
	});

	describe("select()", () => {
		it("moves to the last tag", () => {
			const wrapper = mount();
			wrapper.vm.select();
			expect(Navigate.methods.move).toHaveBeenCalledWith("last");
		});
	});

	describe("tag()", () => {
		it("turns a string into a tag", () => {
			const wrapper = mount();
			expect(wrapper.vm.tag("a")).toStrictEqual({ text: "a", value: "a" });
		});

		it("returns the matching option", () => {
			const wrapper = mount({ options });
			expect(wrapper.vm.tag("a")).toStrictEqual(options[0]);
		});

		it("keeps the text of a tag object", () => {
			const wrapper = mount();
			expect(wrapper.vm.tag({ text: "A", value: "a" })).toStrictEqual({
				text: "A",
				value: "a"
			});
		});
	});

	// events
	describe("edit event", () => {
		it("emits on a double click on a tag", async () => {
			const wrapper = mount({ value: ["a"] });
			await wrapper.find(".k-tag").trigger("dblclick");
			expect(wrapper.emitted("edit")?.[0].slice(0, 2)).toStrictEqual([
				0,
				{ text: "a", value: "a" }
			]);
		});

		it("emits on enter on a tag", async () => {
			const wrapper = mount({ value: ["a"] });
			await wrapper.find(".k-tag").trigger("keypress.enter");
			expect(wrapper.emitted("edit")).toHaveLength(1);
		});
	});

	describe("input event", () => {
		it("emits when a tag is removed", async () => {
			const wrapper = mount({ removable: true, value: ["a", "b"] });
			await wrapper.find(".k-tag-toggle").trigger("click");
			expect(wrapper.emitted("input")).toStrictEqual([[["b"]]]);
		});

		it("emits when a tag was dragged", () => {
			const wrapper = mount({ value: ["a", "b"] });
			wrapper.findComponent(Draggable).vm.$emit("end");
			expect(wrapper.emitted("input")).toStrictEqual([[["a", "b"]]]);
		});
	});

	// slots
	describe("default slot", () => {
		it("renders in the footer after the tags", () => {
			const wrapper = mount(
				{ value: ["a"] },
				{},
				{ default: "<button class='add' />" }
			);
			const children = wrapper.findComponent(Draggable).element.children;
			expect(children[children.length - 1].className).toBe("add");
		});
	});
});
