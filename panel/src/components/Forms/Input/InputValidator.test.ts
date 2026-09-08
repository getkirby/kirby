import { afterEach, describe, expect, it, vi } from "vitest";
import { input, mounter } from "../Validator.test.helpers";
import InputValidator from "./InputValidator";

const mount = mounter<InputValidator>("k-input-validator-test", InputValidator);

afterEach(() => {
	document.body.innerHTML = "";
	vi.restoreAllMocks();
});

describe("InputValidator", () => {
	describe("has", () => {
		it("returns true when the value is in entries", () => {
			const validator = mount();
			validator.value = JSON.stringify(["red", "blue"]);
			expect(validator.has("red")).toBe(true);
		});

		it("returns false when the value is not in entries", () => {
			const validator = mount();
			validator.value = JSON.stringify(["red"]);
			expect(validator.has("blue")).toBe(false);
		});
	});

	describe("validate", () => {
		it("flags valueMissing when required and entries are empty", () => {
			const validator = mount({ required: "true" }, input());
			const spy = vi.spyOn(validator.internals, "setValidity");

			validator.validate();

			expect(spy).toHaveBeenCalledWith(
				{ valueMissing: true },
				"error.validation.required",
				validator.input
			);
		});

		it("flags rangeUnderflow when entries are below min", () => {
			const validator = mount({ min: "3" }, input());
			validator.value = JSON.stringify(["a", "b"]);
			const spy = vi.spyOn(validator.internals, "setValidity");

			validator.validate();

			expect(spy).toHaveBeenCalledWith(
				{ rangeUnderflow: true },
				expect.stringContaining("error.validation.min"),
				validator.input
			);
		});

		it("flags rangeOverflow when entries are above max", () => {
			const validator = mount({ max: "2" }, input());
			validator.value = JSON.stringify(["a", "b", "c"]);
			const spy = vi.spyOn(validator.internals, "setValidity");

			validator.validate();

			expect(spy).toHaveBeenCalledWith(
				{ rangeOverflow: true },
				expect.stringContaining("error.validation.max"),
				validator.input
			);
		});

		it("clears validity when constraints are satisfied", () => {
			const validator = mount({ min: "1", max: "3" }, input());
			validator.value = JSON.stringify(["a", "b"]);
			const spy = vi.spyOn(validator.internals, "setValidity");

			validator.validate();

			expect(spy).toHaveBeenCalledWith({});
		});
	});

	describe("value", () => {
		it("parses a JSON-encoded array into entries", () => {
			const validator = mount();
			validator.value = JSON.stringify(["a", "b"]);
			expect(validator.entries).toEqual(["a", "b"]);
		});

		it("counts the entries", () => {
			const validator = mount();
			validator.value = JSON.stringify(["a", "b", "c"]);
			expect(validator.count).toBe(3);
		});

		it("falls back to an empty array for non-string input", () => {
			const validator = mount();
			validator.value = null;
			expect(validator.entries).toEqual([]);
		});

		it("falls back to an empty array for an empty attribute", () => {
			const validator = mount({ value: "" });
			expect(validator.entries).toEqual([]);
		});

		it("serializes entries back to a JSON string via the getter", () => {
			const validator = mount();
			validator.value = JSON.stringify([1, 2, 3]);
			expect(validator.value).toBe("[1,2,3]");
		});

		it.each([["a"], [0], [1], [false], [true]])(
			"counts the single value %j as one entry",
			(value) => {
				const validator = mount();
				validator.value = JSON.stringify(value);
				expect(validator.entries).toEqual([value]);
				expect(validator.count).toBe(1);
			}
		);

		it.each([[null], [""]])("counts %j as no entry", (value) => {
			const validator = mount();
			validator.value = JSON.stringify(value);
			expect(validator.entries).toEqual([]);
			expect(validator.count).toBe(0);
		});

		it("satisfies required for a falsy selection", () => {
			const validator = mount({ required: "true" }, input());
			const spy = vi.spyOn(validator.internals, "setValidity");

			validator.value = JSON.stringify(0);

			expect(spy).toHaveBeenCalledWith({});
		});

		it("re-runs validation when set", () => {
			const validator = mount({ required: "true" }, input());
			const spy = vi.spyOn(validator.internals, "setValidity");

			validator.value = JSON.stringify(["x"]);

			expect(spy).toHaveBeenCalledWith({});
		});
	});
});
