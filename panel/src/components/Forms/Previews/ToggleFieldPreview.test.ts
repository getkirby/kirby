import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import ToggleFieldPreview from "./ToggleFieldPreview.vue";

const ToggleInput = {
	name: "k-toggle-input",
	props: ["disabled", "text", "value"],
	emits: ["input"],
	template: "<label />"
};

function mount(props = {}, attrs = {}) {
	return vueMount(ToggleFieldPreview, {
		props: { value: true, ...props },
		attrs,
		global: {
			stubs: { "k-toggle-input": ToggleInput }
		}
	});
}

function toggle(wrapper: ReturnType<typeof mount>) {
	return wrapper.findComponent(ToggleInput);
}

// clicks that reach the preview's own element would open the row
async function clickReachesRow(wrapper: ReturnType<typeof mount>) {
	const click = vi.fn();
	wrapper.element.addEventListener("click", click);
	await toggle(wrapper).trigger("click");
	return click.mock.calls.length > 0;
}

describe("k-toggle-field-preview", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-toggle-field-preview");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("renders an editable toggle", () => {
			const wrapper = mount();
			expect(toggle(wrapper).props()).toStrictEqual({
				disabled: false,
				text: undefined,
				value: true
			});
		});
	});

	// props
	describe("column prop", () => {
		it("hides the field's text", () => {
			const wrapper = mount({ column: { text: false }, field: { text: "On" } });
			expect(toggle(wrapper).props("text")).toBe(null);
		});
	});

	describe("disabled prop", () => {
		it("disables the toggle", async () => {
			const wrapper = mount({ disabled: true });
			expect(toggle(wrapper).props("disabled")).toBe(true);
			expect(await clickReachesRow(wrapper)).toBe(true);
		});
	});

	describe("field prop", () => {
		it("shows the field's text", () => {
			const wrapper = mount({ field: { text: ["Off", "On"] } });
			expect(toggle(wrapper).props("text")).toStrictEqual(["Off", "On"]);
		});

		it("disables the toggle of a disabled field", async () => {
			const wrapper = mount({ field: { disabled: true } });
			expect(toggle(wrapper).props("disabled")).toBe(true);
			expect(await clickReachesRow(wrapper)).toBe(true);
		});
	});

	describe("value prop", () => {
		it("passes the value", () => {
			expect(toggle(mount({ value: false })).props("value")).toBe(false);
		});
	});

	// events
	describe("events", () => {
		it("emits the toggled value", () => {
			const wrapper = mount();
			toggle(wrapper).vm.$emit("input", false);
			expect(wrapper.emitted("input")).toStrictEqual([[false]]);
		});

		it("keeps clicks on an editable toggle from the row", async () => {
			expect(await clickReachesRow(mount())).toBe(false);
		});
	});
});
