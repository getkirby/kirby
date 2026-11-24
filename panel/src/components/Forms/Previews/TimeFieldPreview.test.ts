import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import TimeFieldPreview from "./TimeFieldPreview.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(TimeFieldPreview, {
		props: { value: "14:30:00", ...props },
		attrs
	});
}

describe("k-time-field-preview", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "P", "k-time-field-preview");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("keeps the class of the text preview", () => {
			expect(mount().classes()).toContain("k-text-field-preview");
		});
	});

	// props
	describe("column prop", () => {
		it("formats the time with the column's display", () => {
			const wrapper = mount({
				column: { display: "HH:mm:ss" },
				field: { display: "h:mm a" }
			});
			expect(wrapper.text()).toBe("14:30:00");
		});
	});

	describe("field prop", () => {
		it("formats the time with the field's display", () => {
			const wrapper = mount({ field: { display: "h:mm a" } });
			expect(wrapper.text()).toBe("2:30 pm");
		});
	});

	describe("value prop", () => {
		it("formats the time as hours and minutes by default", () => {
			expect(mount().text()).toBe("14:30");
		});

		it("shows a value that is no ISO time as is", () => {
			const wrapper = mount({ value: "09:05" });
			expect(wrapper.text()).toBe("09:05");
		});
	});
});
