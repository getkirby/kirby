import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import BlockOptions from "./BlockOptions.vue";

type Button = {
	click?: () => void;
	disabled?: boolean;
	dropdown?: (Button | "-")[];
	icon: string;
	key?: (event: KeyboardEvent) => void;
	when?: boolean;
};

const Toolbar = {
	name: "k-toolbar",
	props: ["buttons"],
	template: "<nav />"
};

function mount(props = {}, attrs = {}) {
	return vueMount(BlockOptions, {
		props,
		attrs,
		global: {
			stubs: { "k-toolbar": Toolbar }
		}
	});
}

function buttons(wrapper: ReturnType<typeof mount>): Button[] {
	return wrapper.findComponent(Toolbar).props("buttons");
}

function shown(list: (Button | "-")[]) {
	return list
		.filter((button) => button === "-" || button.when !== false)
		.map((button) => (button === "-" ? "-" : button.icon));
}

function more(wrapper: ReturnType<typeof mount>) {
	return (
		buttons(wrapper).find((button) => button.icon === "dots")?.dropdown ?? []
	);
}

function find(list: (Button | "-")[], icon: string) {
	return list.find(
		(button) => button !== "-" && button.icon === icon
	) as Button;
}

describe("k-block-options", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "NAV", "k-block-options");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);

		it("is an inline toolbar", () => {
			const wrapper = mount();
			expect(wrapper.attributes("data-inline")).toBe("true");
		});

		it("keeps the focus in the block on mousedown", () => {
			const wrapper = mount();
			const event = new MouseEvent("mousedown", { cancelable: true });
			wrapper.element.dispatchEvent(event);
			expect(event.defaultPrevented).toBe(true);
		});
	});

	// props
	describe("isBatched prop", () => {
		it("offers to copy and remove the selected blocks", () => {
			const wrapper = mount({ isBatched: true });
			expect(shown(buttons(wrapper))).toStrictEqual(["template", "trash"]);
			find(buttons(wrapper), "template").click?.();
			find(buttons(wrapper), "trash").click?.();
			expect(wrapper.emitted("copy")).toHaveLength(1);
			expect(wrapper.emitted("removeSelected")).toHaveLength(1);
		});

		it("offers to merge mergable blocks", () => {
			const wrapper = mount({ isBatched: true, isMergable: true });
			expect(shown(buttons(wrapper))).toStrictEqual([
				"template",
				"merge",
				"trash"
			]);
			find(buttons(wrapper), "merge").click?.();
			expect(wrapper.emitted("merge")).toHaveLength(1);
		});
	});

	describe("isEditable prop", () => {
		it("offers to edit the block", () => {
			const wrapper = mount({ isEditable: true });
			expect(shown(buttons(wrapper))).toStrictEqual([
				"edit",
				"add",
				"trash",
				"sort",
				"dots"
			]);
			expect(shown(more(wrapper))).toContain("edit");
		});

		it("offers no editing by default", () => {
			const wrapper = mount();
			expect(shown(buttons(wrapper))).toStrictEqual([
				"add",
				"trash",
				"sort",
				"dots"
			]);
			expect(shown(more(wrapper))).not.toContain("edit");
		});
	});

	describe("isFull prop", () => {
		it("disables adding and pasting", () => {
			const wrapper = mount({ isFull: true });
			expect(find(buttons(wrapper), "add").disabled).toBe(true);
			for (const icon of ["angle-up", "angle-down", "download"]) {
				expect(find(more(wrapper), icon).disabled).toBe(true);
			}
		});
	});

	describe("isHidden prop", () => {
		it("offers to hide a visible block", () => {
			const wrapper = mount();
			find(more(wrapper), "hidden").click?.();
			expect(wrapper.emitted("hide")).toHaveLength(1);
		});

		it("offers to show a hidden block", () => {
			const wrapper = mount({ isHidden: true });
			find(more(wrapper), "preview").click?.();
			expect(wrapper.emitted("show")).toHaveLength(1);
		});
	});

	describe("isSplitable prop", () => {
		it("offers to split the block", () => {
			const wrapper = mount({ isSplitable: true });
			find(more(wrapper), "split").click?.();
			expect(wrapper.emitted("split")).toHaveLength(1);
		});

		it("offers no splitting by default", () => {
			const wrapper = mount();
			expect(shown(more(wrapper))).not.toContain("split");
		});
	});

	// computed
	describe("buttons computed", () => {
		it.each([
			["add", "chooseToAppend"],
			["trash", "remove"]
		])("emits on a click on the %s button", (icon, event) => {
			const wrapper = mount();
			find(buttons(wrapper), icon).click?.();
			expect(wrapper.emitted(event)).toHaveLength(1);
		});

		it.each([
			["angle-up", "chooseToPrepend"],
			["angle-down", "chooseToAppend"],
			["refresh", "chooseToConvert"],
			["template", "copy"],
			["download", "paste"],
			["copy", "duplicate"],
			["trash", "remove"]
		])("emits on a click on the %s option", (icon, event) => {
			const wrapper = mount();
			find(more(wrapper), icon).click?.();
			expect(wrapper.emitted(event)).toHaveLength(1);
		});

		it("sorts with the sort handle", () => {
			const wrapper = mount();
			const handle = find(buttons(wrapper), "sort");
			expect(handle).toMatchObject({ class: "k-sort-handle" });
			handle.key?.(new KeyboardEvent("keydown", { key: "ArrowUp" }));
			expect(wrapper.emitted("sortUp")).toHaveLength(1);
		});
	});

	// methods
	describe("sort()", () => {
		it.each([
			["ArrowUp", "sortUp"],
			["ArrowDown", "sortDown"]
		])("sorts with %s", (key, event) => {
			const wrapper = mount();
			const keydown = new KeyboardEvent("keydown", { cancelable: true, key });
			wrapper.vm.sort(keydown);
			expect(wrapper.emitted(event)).toHaveLength(1);
			expect(keydown.defaultPrevented).toBe(true);
		});

		it("ignores other keys", () => {
			const wrapper = mount();
			const keydown = new KeyboardEvent("keydown", {
				cancelable: true,
				key: "a"
			});
			wrapper.vm.sort(keydown);
			expect(wrapper.emitted("sortUp")).toBeUndefined();
			expect(keydown.defaultPrevented).toBe(false);
		});
	});
});
