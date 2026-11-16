/**
 * @vitest-environment jsdom
 */

import { describe, expect, it } from "vitest";
import { form, options } from "./field";

// mock the app with the component setup used by form()
// and the translation used by options()
window.panel = {
	app: {
		$options: {
			components: {
				"k-custom-field": {
					options: {
						props: {
							value: {
								default: "test"
							}
						}
					}
				}
			}
		}
	},
	$t: (key) => key
};

describe.concurrent("$helper.field.form()", () => {
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

describe.concurrent("$helper.field.options()", () => {
	const list = [
		{ value: "a", text: "A" },
		{ value: 1, text: "One" }
	];

	it("should add an option for each value without option", () => {
		expect(options(list, ["a", "b", "c"])).toStrictEqual([
			...list,
			{ text: "[unknown]", value: "b" },
			{ text: "[unknown]", value: "c" }
		]);
	});

	it("should accept a single value", () => {
		expect(options(list, "b")).toStrictEqual([
			...list,
			{ text: "[unknown]", value: "b" }
		]);
	});

	it("should match values loosely", () => {
		expect(options(list, "1")).toStrictEqual(list);
	});

	it("should ignore empty values", () => {
		expect(options(list, "")).toStrictEqual(list);
		expect(options(list, null)).toStrictEqual(list);
		expect(options(list, undefined)).toStrictEqual(list);
		expect(options(list, [])).toStrictEqual(list);
	});

	it("should ignore a missing value cast to false", () => {
		expect(options(list, false)).toStrictEqual(list);
	});
});
