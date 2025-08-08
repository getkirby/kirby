/**
 * @vitest-environment jsdom
 */

import { describe, expect, it } from "vitest";
import { form } from "./field";

describe.concurrent("$helper.field.form()", () => {
	// mock the app with the component setup
	window.panel = {
		app: {
			component(name) {
				const components = {
					"k-custom-field": {
						props: {
							value: {
								default: "test"
							}
						}
					}
				};
				return components[name];
			}
		}
	};

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
