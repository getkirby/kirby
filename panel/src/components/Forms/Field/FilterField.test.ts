import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import FilterField from "./FilterField.vue";

const fields = {
	title: { name: "title", label: "Title", type: "text" },
	status: { name: "status", label: "Status", type: "select" },
	date: { name: "date", label: "Date", type: "date" }
};

const is = { value: "==", text: "is" };
const isNot = { value: "!=", text: "is not" };
const greater = { value: ">", text: "is greater than" };

function mount(props = {}) {
	return vueMount(FilterField, {
		props: {
			fields,
			operators: {
				title: [is, isNot],
				status: [is],
				date: [is, greater]
			},
			...props
		},
		shallow: true,
		global: {
			mocks: {
				$helper: {
					field: { form: () => ({}) },
					uuid: () => Math.random().toString()
				},
				$t: (key: string) => key
			}
		}
	});
}

describe("FilterField.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-FIELD", "k-filter-field");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	describe("fieldOptions", () => {
		it("skips fields without operators", () => {
			const wrapper = mount({
				operators: { title: [is], status: [] }
			});

			expect(wrapper.vm.fieldOptions).toStrictEqual([
				{ value: "title", text: "Title" }
			]);
		});
	});

	describe("fixedOperator", () => {
		it("is null if fields offer different operators", () => {
			const wrapper = mount();
			expect(wrapper.vm.fixedOperator).toBe(null);
		});

		it("is null if fields offer different single operators", () => {
			const wrapper = mount({
				operators: { title: [is], status: [isNot], date: [is] }
			});

			expect(wrapper.vm.fixedOperator).toBe(null);
		});

		it("returns the operator all fields share", () => {
			const wrapper = mount({
				operators: { title: [is], status: [is], date: [is] }
			});

			expect(wrapper.vm.fixedOperator).toStrictEqual(is);
		});
	});

	describe("hasOperatorColumn", () => {
		it("is shown if fields offer different operators", () => {
			const wrapper = mount();
			expect(wrapper.vm.hasOperatorColumn).toBe(true);
		});

		it("is hidden if all fields share a single operator", () => {
			const wrapper = mount({
				operators: { title: [is], status: [is], date: [is] },
				value: [{ field: "title", operator: "==", value: "" }]
			});

			expect(wrapper.vm.hasOperatorColumn).toBe(false);
		});

		it("is shown if a condition has an operator that doesn't fit", () => {
			const wrapper = mount({
				operators: { title: [is], status: [is], date: [is] },
				value: [{ field: "title", operator: ">", value: "" }]
			});

			expect(wrapper.vm.hasOperatorColumn).toBe(true);
		});
	});

	describe("fitsField()", () => {
		it("checks if the field can be selected", () => {
			const wrapper = mount();

			expect(wrapper.vm.fitsField({ field: "title" })).toBe(true);
			expect(wrapper.vm.fitsField({ field: "unknown" })).toBe(false);
		});
	});

	describe("fitsOperator()", () => {
		it("checks if the operator fits the field", () => {
			const wrapper = mount();

			expect(wrapper.vm.fitsOperator({ field: "date", operator: ">" })).toBe(
				true
			);
			expect(wrapper.vm.fitsOperator({ field: "title", operator: ">" })).toBe(
				false
			);
			expect(
				wrapper.vm.fitsOperator({ field: "unknown", operator: "==" })
			).toBe(false);
		});
	});

	describe("isFixedOperator()", () => {
		it("is true for a field with a single fitting operator", () => {
			const wrapper = mount();

			expect(
				wrapper.vm.isFixedOperator({ field: "status", operator: "==" })
			).toBe(true);
		});

		it("is false for a field with multiple operators", () => {
			const wrapper = mount();

			expect(
				wrapper.vm.isFixedOperator({ field: "title", operator: "==" })
			).toBe(false);
		});

		it("is false if the operator doesn't fit", () => {
			const wrapper = mount();

			expect(
				wrapper.vm.isFixedOperator({ field: "status", operator: "!=" })
			).toBe(false);
		});
	});

	describe("onFieldInput()", () => {
		it("keeps an operator that fits the new field", () => {
			const wrapper = mount({
				value: [{ field: "title", operator: "==", value: "Kirby" }]
			});

			wrapper.vm.onFieldInput(0, "status");

			expect(wrapper.emitted("input")?.[0]?.[0]).toStrictEqual([
				{ field: "status", operator: "==", value: "" }
			]);
		});

		it("falls back to the first operator of the new field", () => {
			const wrapper = mount({
				value: [{ field: "date", operator: ">", value: "2026-01-01" }]
			});

			wrapper.vm.onFieldInput(0, "status");

			expect(wrapper.emitted("input")?.[0]?.[0]).toStrictEqual([
				{ field: "status", operator: "==", value: "" }
			]);
		});
	});
});
