import type { ConcreteComponent } from "vue";
import { describe, expect, it } from "vitest";
import { defaultValue, form, isVisible, subfields } from "./field";

// mock the Panel app with the component definitions used by defaultValue()
window.panel = {
	app: {
		component(name: string) {
			const components: Record<string, ConcreteComponent> = {
				"k-custom-field": {
					props: { value: { default: "test" } }
				},
				"k-fn-field": {
					props: { value: { default: () => "from function" } }
				},
				"k-no-default-field": {
					props: { value: {} }
				}
			};

			return components[name];
		}
	}
};

describe("$helper.field", () => {
	describe("defaultValue()", () => {
		it("should return the field's explicit default", () => {
			expect(defaultValue({ type: "text", default: "Hello" })).toBe("Hello");
		});

		it("should return undefined when the component has no value prop", () => {
			expect(defaultValue({ type: "unknown" })).toBeUndefined();
		});

		it("should resolve a function value-prop default", () => {
			expect(defaultValue({ type: "fn" })).toBe("from function");
		});

		it("should return the static value-prop default", () => {
			expect(defaultValue({ type: "custom" })).toBe("test");
		});

		it("should return null when the value prop has no default", () => {
			expect(defaultValue({ type: "no-default" })).toBeNull();
		});
	});

	describe("form()", () => {
		it("should create form object with default values for each field", () => {
			const fields = {
				name: { default: "John" },
				age: { default: 30 },
				email: {},
				custom: {
					type: "custom"
				}
			};
			const result = form(fields);

			expect(result).toEqual({
				name: "John",
				age: 30,
				email: undefined,
				custom: "test"
			});
		});
	});

	describe("isVisible()", () => {
		it("should hide fields of type hidden", () => {
			expect(isVisible({ type: "hidden" }, {})).toBe(false);
		});

		it("should hide fields with hidden: true", () => {
			expect(isVisible({ type: "text", hidden: true }, {})).toBe(false);
		});

		it("should be visible without a when condition", () => {
			expect(isVisible({ type: "text" }, {})).toBe(true);
		});

		it("should be visible when the when condition matches", () => {
			expect(
				isVisible(
					{ type: "text", when: { status: "draft" } },
					{ status: "draft" }
				)
			).toBe(true);
		});

		it("should be hidden when the when condition does not match", () => {
			expect(
				isVisible(
					{ type: "text", when: { status: "draft" } },
					{ status: "published" }
				)
			).toBe(false);
		});

		it("should lowercase the when key when reading values", () => {
			expect(
				isVisible(
					{ type: "text", when: { Status: "draft" } },
					{ status: "draft" }
				)
			).toBe(true);
		});

		it("should treat an undefined value as matching an empty-string condition", () => {
			expect(isVisible({ type: "text", when: { status: "" } }, {})).toBe(true);
		});

		it("should treat an undefined value as matching an empty-array condition", () => {
			expect(isVisible({ type: "text", when: { tags: [] } }, {})).toBe(true);
		});

		it("should check all when conditions", () => {
			const field = { type: "text", when: { a: 1, b: 2 } };
			expect(isVisible(field, { a: 1, b: 2 })).toBe(true);
			expect(isVisible(field, { a: 1, b: 3 })).toBe(false);
		});
	});

	describe("subfields()", () => {
		it("should set the section on each subfield", () => {
			const result = subfields(
				{ name: "structure" },
				{ title: { type: "text" }, age: { type: "number" } }
			);

			expect(result.title.section).toBe("structure");
			expect(result.age.section).toBe("structure");
			expect(result.title.endpoints).toBeUndefined();
		});

		it("should rewrite endpoints when the field has endpoints", () => {
			const result = subfields(
				{
					name: "structure",
					endpoints: {
						field: "pages/x/fields/structure",
						section: "content",
						model: "pages/x"
					}
				},
				{ title: { type: "text" } }
			);

			expect(result.title.endpoints).toEqual({
				field: "pages/x/fields/structure+title",
				section: "content",
				model: "pages/x"
			});
			expect(result.title.section).toBe("structure");
		});

		it("should return an empty object for empty fields", () => {
			expect(subfields({ name: "x" }, {})).toEqual({});
		});
	});
});
