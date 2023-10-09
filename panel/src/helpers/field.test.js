import { describe, expect, it } from "vitest";
import { form } from "./field";

describe.concurrent("$helper.field.form()", () => {
	it("should create form object with default values for each field", () => {
		const fields = {
			name: { default: "John" },
			age: { default: 30 },
			email: {}
		};
		const result = form(fields);

		expect(result).toEqual({
			name: "John",
			age: 30,
			email: undefined
		});
	});
});
