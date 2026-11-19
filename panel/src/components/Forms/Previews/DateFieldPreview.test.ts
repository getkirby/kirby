import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import DateFieldPreview from "./DateFieldPreview.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(DateFieldPreview, {
		props: { value: "2024-03-05", ...props },
		attrs
	});
}

describe("k-date-field-preview", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "P", "k-date-field-preview");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("keeps the class of the text preview", () => {
			expect(mount().classes()).toContain("k-text-field-preview");
		});

		it("wraps the date with the column's before and after", () => {
			const wrapper = mount({ column: { after: ")", before: "(" } });
			expect(wrapper.text()).toBe("( 2024-03-05 )");
		});
	});

	// props
	describe("column prop", () => {
		it("formats the date with the column's display", () => {
			const wrapper = mount({
				column: { display: "DD.MM.YYYY" },
				field: { display: "MM/DD/YYYY" }
			});
			expect(wrapper.text()).toBe("05.03.2024");
		});

		it("adds the time with the column's time display", () => {
			const wrapper = mount({
				column: { time: { display: "HH:mm" } },
				field: { time: { display: "h:mm a" } },
				value: "2024-03-05 14:30:00"
			});
			expect(wrapper.text()).toBe("2024-03-05 14:30");
		});
	});

	describe("field prop", () => {
		it("formats the date with the field's display", () => {
			const wrapper = mount({ field: { display: "MM/DD/YYYY" } });
			expect(wrapper.text()).toBe("03/05/2024");
		});

		it("adds the time with the field's time display", () => {
			const wrapper = mount({
				field: { time: { display: "h:mm a" } },
				value: "2024-03-05 14:30:00"
			});
			expect(wrapper.text()).toBe("2024-03-05 2:30 pm");
		});

		it("ignores a disabled time", () => {
			const wrapper = mount({
				field: { time: false },
				value: "2024-03-05 14:30:00"
			});
			expect(wrapper.text()).toBe("2024-03-05");
		});
	});

	describe("value prop", () => {
		it("formats the date as ISO date by default", () => {
			expect(mount({ value: "2024-03-05 14:30:00" }).text()).toBe("2024-03-05");
		});

		it("shows a value that is no date as is", () => {
			expect(mount({ value: "tomorrow" }).text()).toBe("tomorrow");
		});
	});
});
