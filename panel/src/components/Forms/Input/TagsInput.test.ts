import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Tag from "@/components/Navigation/Tag.vue";
import Tags from "@/components/Navigation/Tags.vue";
import TagsInput from "./TagsInput.vue";

const Draggable = {
	name: "k-draggable",
	template: `<div><slot /><slot name="footer" /></div>`
};
const Dropdown = {
	name: "k-picklist-dropdown",
	props: [
		"create",
		"disabled",
		"max",
		"min",
		"multiple",
		"options",
		"search",
		"value"
	],
	emits: ["create", "input"],
	data: () => ({ isOpen: false }),
	methods: {
		close(this: { isOpen: boolean }) {
			this.isOpen = false;
		},
		open(this: { isOpen: boolean }) {
			this.isOpen = true;
		}
	},
	template: "<div />"
};
const Navigate = {
	name: "k-navigate",
	methods: { move: vi.fn() },
	template: "<div><slot /></div>"
};
const options = [
	{ text: "Alpha", value: "a" },
	{ text: "Beta", value: "b" }
];

function mount(props = {}, attrs = {}) {
	return vueMount(TagsInput, {
		props,
		attrs,
		global: {
			components: { "k-tag": Tag, "k-tags": Tags },
			stubs: {
				// keeps the click and keydown listeners on a plain button
				"k-button": { template: "<button />" },
				"k-draggable": Draggable,
				"k-navigate": Navigate,
				"k-picklist-dropdown": Dropdown
			}
		}
	});
}

function replace(wrapper: ReturnType<typeof mount>) {
	return wrapper.findAllComponents(Dropdown)[0];
}

function create(wrapper: ReturnType<typeof mount>) {
	return wrapper.findAllComponents(Dropdown)[1];
}

function toggle(wrapper: ReturnType<typeof mount>) {
	return wrapper.find(".k-tags-input-toggle");
}

describe("k-tags-input", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-tags-input");
		it.inheritsNoAttrs(mount);

		it("renders removable k-tags", () => {
			const wrapper = mount({ value: ["a"] });
			expect(wrapper.find(".k-tag-toggle").exists()).toBe(true);
		});
	});

	// props
	describe("accept prop", () => {
		it("accepts custom tags by default", () => {
			const wrapper = mount();
			expect(create(wrapper).props("create")).toBe(true);
		});

		it("offers no custom tags for options only", () => {
			const wrapper = mount({ accept: "options", options });
			expect(create(wrapper).props("create")).toBe(false);
		});
	});

	describe("disabled prop", () => {
		it("hides the toggle", () => {
			const wrapper = mount({ disabled: true });
			expect(wrapper.attributes("data-disabled")).toBe("true");
			expect(toggle(wrapper).exists()).toBe(false);
		});
	});

	describe("max prop", () => {
		it("hides the toggle once the maximum is reached", () => {
			const wrapper = mount({ max: 1, value: ["a"] });
			expect(wrapper.attributes("data-can-add")).toBe("false");
			expect(toggle(wrapper).exists()).toBe(false);
		});

		it("passes the maximum to the k-input-validator", () => {
			const wrapper = mount({ max: 2 });
			expect(wrapper.find("k-input-validator").attributes("max")).toBe("2");
		});
	});

	describe("min prop", () => {
		it("passes the minimum to the k-input-validator", () => {
			const wrapper = mount({ min: 1 });
			expect(wrapper.find("k-input-validator").attributes("min")).toBe("1");
		});
	});

	describe("required prop", () => {
		it("passes the required state to the k-input-validator", () => {
			const wrapper = mount({ required: true });
			expect(wrapper.find("k-input-validator").attributes("required")).toBe(
				"true"
			);
		});
	});

	describe("value prop", () => {
		it("renders the tags", () => {
			const wrapper = mount({ options, value: ["a", "x"] });
			const texts = wrapper.findAll(".k-tag").map((tag) => tag.text());
			expect(texts).toStrictEqual(["Alpha", "x"]);
		});

		it("passes the value to the k-input-validator", () => {
			const wrapper = mount({ value: ["a"] });
			expect(wrapper.find("k-input-validator").attributes("value")).toBe(
				'["a"]'
			);
		});
	});

	// computed
	describe("canAdd computed", () => {
		it("can add tags without a maximum", () => {
			const wrapper = mount({ value: ["a", "b"] });
			expect(wrapper.vm.canAdd).toBe(true);
		});

		it("can add tags below the maximum", () => {
			const wrapper = mount({ max: 2, value: ["a"] });
			expect(wrapper.vm.canAdd).toBe(true);
		});

		it("cannot add tags at the maximum", () => {
			const wrapper = mount({ max: 2, value: ["a", "b"] });
			expect(wrapper.vm.canAdd).toBe(false);
		});
	});

	describe("creatableOptions computed", () => {
		it("offers only options that are not selected", () => {
			const wrapper = mount({ options, value: ["a"] });
			expect(create(wrapper).props("options")).toStrictEqual([options[1]]);
		});
	});

	describe("picklist computed", () => {
		it("passes the settings to both dropdowns", () => {
			const wrapper = mount({ disabled: true, max: 3, min: 1 });
			expect(create(wrapper).props()).toMatchObject({
				disabled: true,
				max: 3,
				min: 1
			});
			expect(replace(wrapper).props()).toMatchObject({
				disabled: true,
				max: 3,
				min: 1,
				multiple: false
			});
		});
	});

	describe("replacableOptions computed", () => {
		it("offers unselected options and the edited one", () => {
			const wrapper = mount({
				options: [...options, { text: "Gamma", value: "c" }],
				value: ["a", "b"]
			});
			wrapper.vm.edit(0, { text: "Alpha", value: "a" });
			expect(
				wrapper.vm.replacableOptions.map(
					(option: { value: string }) => option.value
				)
			).toStrictEqual(["a", "c"]);
		});
	});

	describe("showCreate computed", () => {
		it("offers a replace button when editing", () => {
			const wrapper = mount({ value: ["a"] });
			wrapper.vm.edit(0, { text: "a", value: "a" });
			expect(wrapper.vm.showCreate).toStrictEqual({ submit: "replace.with" });
		});
	});

	describe("showSearch computed", () => {
		it("offers to add tags by default", () => {
			const wrapper = mount();
			expect(wrapper.vm.showSearch).toStrictEqual({
				placeholder: "field.tags.placeholder…"
			});
		});

		it("offers to filter for options only", () => {
			const wrapper = mount({ accept: "options" });
			expect(wrapper.vm.showSearch).toStrictEqual({ placeholder: "filter" });
		});

		it("offers to replace when editing", () => {
			const wrapper = mount({ value: ["a"] });
			wrapper.vm.edit(0, { text: "a", value: "a" });
			expect(wrapper.vm.showSearch).toStrictEqual({
				placeholder: "replace.with"
			});
		});

		it("offers no search when turned off", () => {
			const wrapper = mount({ search: false });
			expect(wrapper.vm.showSearch).toBe(false);
		});
	});

	// methods
	describe("create()", () => {
		it("adds the trimmed tags of the input", () => {
			const wrapper = mount({ value: ["a"] });
			wrapper.vm.create(" b , c ");
			expect(wrapper.emitted("input")).toStrictEqual([[["a", "b", "c"]]]);
		});

		it("splits the input by the separator", () => {
			const wrapper = mount({ separator: ";" });
			wrapper.vm.create("a;b");
			expect(wrapper.emitted("input")).toStrictEqual([[["a", "b"]]]);
		});

		it("skips tags that are already selected", () => {
			const wrapper = mount({ value: ["a"] });
			wrapper.vm.create("a, b");
			expect(wrapper.emitted("input")).toStrictEqual([[["a", "b"]]]);
		});

		it("adds only options for options only", () => {
			const wrapper = mount({ accept: "options", options });
			wrapper.vm.create("a, x");
			expect(wrapper.emitted("input")).toStrictEqual([[["a"]]]);
		});

		it("closes the dropdown", async () => {
			const wrapper = mount();
			wrapper.vm.focus();
			wrapper.vm.create("a");
			expect(create(wrapper).vm.isOpen).toBe(false);
		});
	});

	describe("edit()", () => {
		it("opens the replace dropdown for the tag", async () => {
			const wrapper = mount({ value: ["a"] });
			await wrapper.vm.edit(0, { text: "a", value: "a" });
			await wrapper.vm.$nextTick();
			expect(replace(wrapper).vm.isOpen).toBe(true);
			expect(replace(wrapper).props("value")).toBe("a");
		});
	});

	describe("focus()", () => {
		it("opens the create dropdown", () => {
			const wrapper = mount();
			wrapper.vm.focus();
			expect(create(wrapper).vm.isOpen).toBe(true);
		});

		it("does nothing at the maximum", () => {
			const wrapper = mount({ max: 1, value: ["a"] });
			wrapper.vm.focus();
			expect(create(wrapper).vm.isOpen).toBe(false);
		});
	});

	describe("isAllowed()", () => {
		it("allows a new tag", () => {
			const wrapper = mount({ value: ["a"] });
			expect(wrapper.vm.isAllowed({ text: "b", value: "b" })).toBe(true);
		});

		it.each([
			["a string", "b"],
			["an empty tag", { text: " ", value: " " }],
			["a selected tag", { text: "a", value: "a" }]
		])("rejects %s", (_, tag) => {
			const wrapper = mount({ value: ["a"] });
			expect(wrapper.vm.isAllowed(tag)).toBe(false);
		});

		it("rejects custom tags for options only", () => {
			const wrapper = mount({ accept: "options", options });
			expect(wrapper.vm.isAllowed({ text: "x", value: "x" })).toBe(false);
			expect(wrapper.vm.isAllowed({ text: "Alpha", value: "a" })).toBe(true);
		});
	});

	describe("pick()", () => {
		it("emits the picked tags and closes the dropdown", () => {
			const wrapper = mount();
			wrapper.vm.focus();
			wrapper.vm.pick(["a", "b"]);
			expect(wrapper.emitted("input")).toStrictEqual([[["a", "b"]]]);
			expect(create(wrapper).vm.isOpen).toBe(false);
		});
	});

	describe("replace()", () => {
		it("replaces the edited tag", async () => {
			const wrapper = mount({ value: ["a", "b"] });
			await wrapper.vm.edit(1, { text: "b", value: "b" });
			wrapper.vm.replace("c");
			expect(wrapper.emitted("input")).toStrictEqual([[["a", "c"]]]);
			expect(Navigate.methods.move).toHaveBeenCalledWith(1);
		});

		it("keeps the tags for a tag that is not allowed", async () => {
			const wrapper = mount({ value: ["a", "b"] });
			await wrapper.vm.edit(1, { text: "b", value: "b" });
			wrapper.vm.replace("a");
			expect(wrapper.emitted("input")).toBeUndefined();
		});

		it("closes the dropdown and stops editing", async () => {
			const wrapper = mount({ value: ["a"] });
			await wrapper.vm.edit(0, { text: "a", value: "a" });
			wrapper.vm.replace("b");
			expect(replace(wrapper).vm.isOpen).toBe(false);
			expect(wrapper.vm.editing).toBe(null);
		});
	});

	describe("toggle()", () => {
		it("opens the create dropdown on arrow down", () => {
			const wrapper = mount();
			const event = new KeyboardEvent("keydown", {
				cancelable: true,
				key: "ArrowDown"
			});
			wrapper.vm.toggle(event);
			expect(create(wrapper).vm.isOpen).toBe(true);
			expect(event.defaultPrevented).toBe(true);
		});

		it("opens the create dropdown when typing a letter", () => {
			const wrapper = mount();
			wrapper.vm.toggle(new KeyboardEvent("keydown", { keyCode: 65 }));
			expect(create(wrapper).vm.isOpen).toBe(true);
		});

		it("ignores keys with a modifier", () => {
			const wrapper = mount();
			wrapper.vm.toggle(
				new KeyboardEvent("keydown", { ctrlKey: true, keyCode: 65 })
			);
			expect(create(wrapper).vm.isOpen).toBe(false);
		});
	});

	// events
	describe("click event", () => {
		it("opens the create dropdown with the toggle", async () => {
			const wrapper = mount();
			await toggle(wrapper).trigger("click");
			expect(create(wrapper).vm.isOpen).toBe(true);
		});

		// happy-dom lacks the guard against nested clicks that keeps
		// the click on the toggle from bouncing back via the tags
		it("opens the create dropdown on a click on the tags", () => {
			const wrapper = mount({ value: ["a"] });
			wrapper.findComponent(Tags).vm.$emit("click", new MouseEvent("click"));
			expect(create(wrapper).vm.isOpen).toBe(true);
		});
	});

	describe("edit event", () => {
		it("opens the replace dropdown on a double click on a tag", async () => {
			const wrapper = mount({ value: ["a"] });
			await wrapper.find(".k-tag").trigger("dblclick");
			expect(replace(wrapper).vm.isOpen).toBe(true);
		});
	});

	describe("input event", () => {
		it("emits when a tag is removed", async () => {
			const wrapper = mount({ value: ["a", "b"] });
			await wrapper.find(".k-tag-toggle").trigger("click");
			expect(wrapper.emitted("input")).toStrictEqual([[["b"]]]);
		});

		it("emits the tags picked in the create dropdown", () => {
			const wrapper = mount();
			create(wrapper).vm.$emit("input", ["a"]);
			expect(wrapper.emitted("input")).toStrictEqual([[["a"]]]);
		});

		it("emits the tags created in the create dropdown", () => {
			const wrapper = mount();
			create(wrapper).vm.$emit("create", "a, b");
			expect(wrapper.emitted("input")).toStrictEqual([[["a", "b"]]]);
		});
	});

	describe("keydown event", () => {
		it("moves to the last tag on delete in the toggle", async () => {
			const wrapper = mount({ value: ["a"] });
			await toggle(wrapper).trigger("keydown.delete");
			expect(Navigate.methods.move).toHaveBeenCalledWith("prev");
		});
	});
});
