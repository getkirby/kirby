import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import MultiselectInput from "./MultiselectInput.vue";

const Dropdown = {
	name: "k-picklist-dropdown",
	props: ["options", "value"],
	emits: ["input"],
	methods: { open: vi.fn() },
	template: "<div />"
};
const Tags = {
	name: "k-tags",
	props: ["removable", "value"],
	emits: ["click", "input"],
	methods: { focus: vi.fn() },
	template: `<div @click="$emit('click', $event)"><slot /></div>`
};
const options = [
	{ text: "A", value: "a" },
	{ text: "B", value: "b" }
];

function mount(props = {}, attrs = {}) {
	return vueMount(MultiselectInput, {
		props: { options, ...props },
		attrs,
		global: {
			stubs: { "k-picklist-dropdown": Dropdown, "k-tags": Tags }
		}
	});
}

describe("k-multiselect-input", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-multiselect-input");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("renders removable k-tags", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Tags).props("removable")).toBe(true);
		});
	});

	// props
	describe("autofocus prop", () => {
		it("passes autofocus to the toggle", () => {
			const wrapper = mount({ autofocus: true });
			expect(wrapper.find("k-button").attributes("autofocus")).toBe("true");
		});
	});

	describe("disabled prop", () => {
		it("disables the toggle", () => {
			const wrapper = mount({ disabled: true });
			expect(wrapper.attributes("data-disabled")).toBe("true");
			expect(wrapper.find("k-button").attributes("disabled")).toBe("true");
		});
	});

	describe("id prop", () => {
		it("sets the id of the toggle", () => {
			const wrapper = mount({ id: "letters" });
			expect(wrapper.find("k-button").attributes("id")).toBe("letters");
		});
	});

	describe("max prop", () => {
		it("hides the toggle once the maximum is reached", () => {
			const wrapper = mount({ max: 2, value: ["a", "b"] });
			expect(wrapper.find("k-button").exists()).toBe(false);
		});

		it("shows the toggle below the maximum", () => {
			const wrapper = mount({ max: 2, value: ["a"] });
			expect(wrapper.find("k-button").exists()).toBe(true);
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

	describe("options prop", () => {
		it("passes the options to the k-picklist-dropdown", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Dropdown).props("options")).toStrictEqual(
				options
			);
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
		it("passes the value to the k-tags and k-picklist-dropdown", () => {
			const wrapper = mount({ value: ["a"] });
			expect(wrapper.findComponent(Tags).props("value")).toStrictEqual(["a"]);
			expect(wrapper.findComponent(Dropdown).props("value")).toStrictEqual([
				"a"
			]);
		});

		it("passes the value to the k-input-validator", () => {
			const wrapper = mount({ value: ["a"] });
			expect(wrapper.find("k-input-validator").attributes("value")).toBe(
				'["a"]'
			);
		});
	});

	// methods
	describe("open()", () => {
		it("opens the k-picklist-dropdown at the input", () => {
			const wrapper = mount();
			wrapper.vm.open();
			expect(Dropdown.methods.open).toHaveBeenCalledWith(wrapper.element);
		});
	});

	// events
	describe("click event", () => {
		it("opens the k-picklist-dropdown on a click on the k-tags", async () => {
			const wrapper = mount();
			await wrapper.findComponent(Tags).trigger("click");
			expect(Dropdown.methods.open).toHaveBeenCalledOnce();
		});
	});

	describe("focus event", () => {
		it("opens the k-picklist-dropdown when the toggle is focused", async () => {
			const wrapper = mount();
			await wrapper.find("k-button").trigger("focus");
			expect(Dropdown.methods.open).toHaveBeenCalledOnce();
		});
	});

	describe("input event", () => {
		it("emits the value of the k-tags", () => {
			const wrapper = mount({ value: ["a", "b"] });
			wrapper.findComponent(Tags).vm.$emit("input", ["b"]);
			expect(wrapper.emitted("input")).toStrictEqual([[["b"]]]);
		});

		it("emits the value of the k-picklist-dropdown", () => {
			const wrapper = mount({ value: ["a"] });
			wrapper.findComponent(Dropdown).vm.$emit("input", ["a", "b"]);
			expect(wrapper.emitted("input")).toStrictEqual([[["a", "b"]]]);
		});
	});

	describe("keydown event", () => {
		it("moves to the last tag on delete in the toggle", async () => {
			const wrapper = mount({ value: ["a"] });
			await wrapper.find("k-button").trigger("keydown", { key: "Delete" });
			expect(Tags.methods.focus).toHaveBeenCalledWith("prev");
		});
	});
});
