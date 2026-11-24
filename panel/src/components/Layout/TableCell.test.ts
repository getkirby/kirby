import { beforeEach, describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import type { App } from "vue";
import preserveListeners from "@/mixins/preserveListeners";
import TableCell from "./TableCell.vue";

function preview(name: string) {
	return {
		name,
		props: ["column", "disabled", "field", "row", "value"],
		emits: ["input"],
		template: "<span />"
	};
}

const components = {
	"k-array-field-preview": preview("k-array-field-preview"),
	"k-date-field-preview": preview("k-date-field-preview"),
	"k-object-field-preview": preview("k-object-field-preview"),
	"k-table-date-cell": preview("k-table-date-cell"),
	"k-table-status-cell": preview("k-table-status-cell"),
	"k-text-field-preview": preview("k-text-field-preview")
};

function mount(props = {}, attrs = {}) {
	return vueMount(TableCell, {
		props: { column: {}, value: "Hello", ...props },
		attrs,
		global: { components, mixins: [preserveListeners] }
	});
}

// the preview the cell renders
function cell(wrapper: ReturnType<typeof mount>) {
	const previews = Object.values(components).map((preview) =>
		wrapper.findComponent(preview)
	);

	return previews.find((preview) => preview.exists()) ?? previews[0];
}

function component(wrapper: ReturnType<typeof mount>) {
	return cell(wrapper).vm.$options.name;
}

describe("k-table-cell", () => {
	// lets $helper.isComponent() know about the previews
	beforeEach(() => {
		const app = { _context: { components } };
		vi.stubGlobal("panel", { ...window.panel, app: app as unknown as App });
	});

	// $el
	describe("element", () => {
		it.rendersAs(mount, "TD", "k-table-cell");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("renders the value as text by default", () => {
			const wrapper = mount();
			expect(component(wrapper)).toBe("k-text-field-preview");
			expect(cell(wrapper).props()).toStrictEqual({
				column: {},
				disabled: false,
				field: undefined,
				row: undefined,
				value: "Hello"
			});
		});
	});

	// props
	describe("column prop", () => {
		it("aligns the cell", () => {
			const wrapper = mount({ column: { align: "right" } });
			expect(wrapper.attributes("data-align")).toBe("right");
		});

		it("previews the value by the column type", () => {
			const wrapper = mount({ column: { type: "date" }, value: "2024-03-05" });
			expect(component(wrapper)).toBe("k-date-field-preview");
		});

		it("prefers a field preview over a table cell type", () => {
			const wrapper = mount({ column: { type: "date" } });
			expect(component(wrapper)).toBe("k-date-field-preview");
		});

		it("uses a table cell type without field preview", () => {
			const wrapper = mount({ column: { type: "status" } });
			expect(component(wrapper)).toBe("k-table-status-cell");
		});
	});

	describe("disabled prop", () => {
		it("disables the preview", () => {
			const wrapper = mount({ disabled: true });
			expect(cell(wrapper).props("disabled")).toBe(true);
		});
	});

	describe("field prop", () => {
		it("previews the value by the field type", () => {
			const field = { type: "date" };
			const wrapper = mount({ field });
			expect(component(wrapper)).toBe("k-date-field-preview");
			expect(cell(wrapper).props("field")).toStrictEqual(field);
		});

		it("gives way to the column type", () => {
			const wrapper = mount({
				column: { type: "status" },
				field: { type: "date" }
			});
			expect(component(wrapper)).toBe("k-table-status-cell");
		});
	});

	describe("id prop", () => {
		it("marks the column", () => {
			const wrapper = mount({ id: "title" });
			expect(wrapper.attributes("data-column-id")).toBe("title");
		});
	});

	describe("mobile prop", () => {
		it("hides the cell on mobile by default", () => {
			expect(mount().attributes("data-mobile")).toBe("false");
		});

		it("keeps the cell on mobile", () => {
			expect(mount({ mobile: true }).attributes("data-mobile")).toBe("true");
		});
	});

	describe("row prop", () => {
		it("passes the row to the preview", () => {
			const row = { id: "a", title: "Hello" };
			expect(cell(mount({ row })).props("row")).toStrictEqual(row);
		});
	});

	describe("value prop", () => {
		it("previews an array without type", () => {
			const wrapper = mount({ value: ["a", "b"] });
			expect(component(wrapper)).toBe("k-array-field-preview");
		});

		it("previews an object without type", () => {
			const wrapper = mount({ value: { a: 1 } });
			expect(component(wrapper)).toBe("k-object-field-preview");
		});

		it.each([[""], [null], [[]], [{}]])(
			"renders nothing for the empty value %j",
			(value) => {
				const wrapper = mount({ value });
				expect(wrapper.element.children).toHaveLength(0);
			}
		);

		it("renders zero", () => {
			const wrapper = mount({ value: 0 });
			expect(cell(wrapper).props("value")).toBe(0);
		});
	});

	// events
	describe("events", () => {
		it("emits the edited value", () => {
			const wrapper = mount();
			cell(wrapper).vm.$emit("input", "Hi");
			expect(wrapper.emitted("input")).toStrictEqual([["Hi"]]);
		});

		it("passes clicks on to the listener", async () => {
			const onClick = vi.fn();
			const wrapper = mount({}, { onClick });
			await wrapper.trigger("click");
			expect(onClick).toHaveBeenCalledOnce();
		});
	});
});
