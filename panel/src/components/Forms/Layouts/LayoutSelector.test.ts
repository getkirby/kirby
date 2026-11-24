import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import LayoutSelector from "./LayoutSelector.vue";

const Dialog = {
	name: "k-dialog",
	props: ["cancelButton", "size", "submitButton", "visible"],
	emits: ["cancel", "submit"],
	template: "<div><slot /></div>"
};

const layouts = [["1/1"], ["1/2", "1/2"], ["1/3", "2/3"]];

function mount(props = {}, attrs = {}) {
	return vueMount(LayoutSelector, {
		props: { layouts, ...props },
		attrs,
		global: {
			stubs: { "k-dialog": Dialog }
		}
	});
}

function options(wrapper: ReturnType<typeof mount>) {
	return wrapper.findAll(".k-layout-selector-option");
}

describe("k-layout-selector", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-layout-selector");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("renders a dialog without buttons", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Dialog).props()).toStrictEqual({
				cancelButton: false,
				size: "medium",
				submitButton: false,
				visible: false
			});
		});

		it("renders the options for horizontal navigation", () => {
			const wrapper = mount();
			const navigate = wrapper.find("k-navigate");
			expect(navigate.classes()).toContain("k-layout-selector-options");
			expect(navigate.attributes("axis")).toBe("x");
		});
	});

	// props
	describe("label prop", () => {
		it("asks to select a layout by default", () => {
			const wrapper = mount();
			expect(wrapper.find("h3.k-label").text()).toBe("field.layout.select");
		});

		it("replaces the label", () => {
			const wrapper = mount({ label: "Change layout" });
			expect(wrapper.find("h3.k-label").text()).toBe("Change layout");
		});
	});

	describe("layouts prop", () => {
		it("renders an option per layout", () => {
			const wrapper = mount();
			expect(
				options(wrapper).map((option) => [
					option.element.tagName,
					option.attributes("aria-label"),
					option.attributes("value")
				])
			).toStrictEqual([
				["BUTTON", "1/1", "1/1"],
				["BUTTON", "1/2,1/2", "1/2,1/2"],
				["BUTTON", "1/3,2/3", "1/3,2/3"]
			]);
		});

		it("previews the columns of each layout", () => {
			const wrapper = mount();
			const option = options(wrapper)[2];
			expect(
				option.findAll("k-column").map((column) => column.attributes("width"))
			).toStrictEqual(["1/3", "2/3"]);
		});
	});

	describe("selector prop", () => {
		it("shows three layouts per row by default", () => {
			const wrapper = mount();
			const navigate = wrapper.find("k-navigate").element as HTMLElement;
			expect(navigate.style.getPropertyValue("--columns")).toBe("3");
		});

		it("sets the columns and size", () => {
			const wrapper = mount({ selector: { columns: "2", size: "large" } });
			const navigate = wrapper.find("k-navigate").element as HTMLElement;
			expect(navigate.style.getPropertyValue("--columns")).toBe("2");
			expect(wrapper.findComponent(Dialog).props("size")).toBe("large");
		});
	});

	describe("value prop", () => {
		it("marks no layout as current without value", () => {
			const wrapper = mount();
			expect(
				options(wrapper).map((option) => option.attributes("aria-current"))
			).toStrictEqual(["false", "false", "false"]);
		});

		it("marks the current layout", () => {
			const wrapper = mount({ value: layouts[1] });
			expect(
				options(wrapper).map((option) => option.attributes("aria-current"))
			).toStrictEqual(["false", "true", "false"]);
		});
	});

	// events
	describe("events", () => {
		it("emits the clicked layout", async () => {
			const wrapper = mount();
			await options(wrapper)[2].trigger("click");
			expect(wrapper.emitted("input")).toStrictEqual([[layouts[2]]]);
		});

		it("emits cancel", () => {
			const wrapper = mount();
			wrapper.findComponent(Dialog).vm.$emit("cancel");
			expect(wrapper.emitted("cancel")).toHaveLength(1);
		});

		it("submits the value", () => {
			const wrapper = mount({ value: layouts[1] });
			wrapper.findComponent(Dialog).vm.$emit("submit");
			expect(wrapper.emitted("submit")).toStrictEqual([[layouts[1]]]);
		});
	});
});
