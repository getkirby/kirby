import { describe, expect, it } from "vitest";
import { form } from "./field";

describe("$helper.field.form()", () => {
	// TODO: Remove once window.panel is globally typed
	// @ts-expect-error - window.panel is not typed yet
	// mock the app with the component setup
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
