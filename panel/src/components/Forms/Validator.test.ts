import { afterEach, describe, expect, it, vi } from "vitest";
import { input, mounter } from "./Validator.test.helpers";
import Validator from "./Validator";

const TAG = "k-validator-test";
const mount = mounter<Validator>(TAG, Validator);

afterEach(() => {
	document.body.innerHTML = "";
	vi.restoreAllMocks();
});

describe("Validator", () => {
	// Vue assigns bound values as DOM properties and never as
	// attributes, so both paths have to behave the same
	describe("count", () => {
		it("coerces the attribute to a number", () => {
			const validator = mount();
			expect(validator.count).toBe(0);

			validator.setAttribute("count", "4");
			expect(validator.count).toBe(4);
		});

		it("resets to zero when the attribute is removed", () => {
			const validator = mount({ count: "4" });
			expect(validator.count).toBe(4);

			validator.removeAttribute("count");
			expect(validator.count).toBe(0);
		});

		it("re-runs validation when set", () => {
			const validator = mount({ min: "3" }, input());
			const spy = vi.spyOn(validator.internals, "setValidity");

			validator.count = 5;

			expect(spy).toHaveBeenCalledWith({});
		});

		it("treats a missing count as zero", () => {
			const validator = mount({ count: "5" }, input());

			validator.count = null;

			expect(validator.count).toBe(0);
		});

		it("coerces a property to a number", () => {
			const validator = mount();

			validator.count = "4";

			expect(validator.count).toBe(4);
		});

		it("treats an unparsable count as zero", () => {
			const validator = mount({ count: "nope" });
			expect(validator.count).toBe(0);
		});
	});

	describe("input", () => {
		it("returns the element matching the anchor selector", () => {
			const target = input({ class: "preferred" });
			const other = input();
			const validator = mount({ anchor: ".preferred" }, [other, target]);
			expect(validator.input).toBe(target);
		});

		it("falls back to the first focusable descendant", () => {
			const wrapper = document.createElement("div");
			const button = document.createElement("button");
			wrapper.appendChild(button);
			const validator = mount({}, wrapper);
			expect(validator.input).toBe(button);
		});

		it.each(["input", "textarea", "select", "button"])("matches %s", (tag) => {
			const child = document.createElement(tag);
			const validator = mount({}, child);
			expect(validator.input).toBe(child);
		});

		it("falls back to the first direct child when nothing focusable exists", () => {
			const wrapper = document.createElement("div");
			const validator = mount({}, wrapper);
			expect(validator.input).toBe(wrapper);
		});

		it("returns null when there are no children", () => {
			const validator = mount();
			expect(validator.input).toBeNull();
		});
	});

	describe("max", () => {
		it("coerces the attribute to a number", () => {
			const validator = mount();
			expect(validator.max).toBeNull();

			validator.setAttribute("max", "5");
			expect(validator.max).toBe(5);
		});

		it("resets when the attribute is removed", () => {
			const validator = mount({ max: "8" });
			expect(validator.max).toBe(8);

			validator.removeAttribute("max");
			expect(validator.max).toBeNull();
		});

		it("re-runs validation when set", () => {
			const validator = mount({ count: "5" }, input());
			const spy = vi.spyOn(validator.internals, "setValidity");

			validator.max = 3;

			expect(spy).toHaveBeenCalledWith(
				{ rangeOverflow: true },
				expect.stringContaining("error.validation.max"),
				validator.input
			);
		});

		it("re-runs validation when removed", () => {
			const validator = mount({ count: "5", max: "3" }, input());
			const spy = vi.spyOn(validator.internals, "setValidity");

			validator.max = null;

			expect(spy).toHaveBeenCalledWith({});
		});

		it("coerces a property to a number", () => {
			const validator = mount();

			validator.max = "5";

			expect(validator.max).toBe(5);
		});

		it("treats an unparsable max as unset", () => {
			const validator = mount({ max: "nope" });
			expect(validator.max).toBeNull();
		});
	});

	describe("min", () => {
		it("coerces the attribute to a number", () => {
			const validator = mount();
			expect(validator.min).toBeNull();

			validator.setAttribute("min", "1");
			expect(validator.min).toBe(1);
		});

		it("resets when the attribute is removed", () => {
			const validator = mount({ min: "2" });
			expect(validator.min).toBe(2);

			validator.removeAttribute("min");
			expect(validator.min).toBeNull();
		});

		it("re-runs validation when the attribute changes", () => {
			const validator = mount({ count: "1" }, input());
			const spy = vi.spyOn(validator.internals, "setValidity");

			validator.setAttribute("min", "3");

			expect(spy).toHaveBeenCalledWith(
				{ rangeUnderflow: true },
				expect.stringContaining("error.validation.min"),
				validator.input
			);
		});

		it("coerces a property to a number", () => {
			const validator = mount();

			validator.min = "1";

			expect(validator.min).toBe(1);
		});

		it("treats an unparsable min as unset", () => {
			const validator = mount({ min: "nope" });
			expect(validator.min).toBeNull();
		});

		/**
		 * Vue passes undefined for a prop without a default,
		 * e.g. the min of a picklist input
		 */
		it("treats an undefined min as unset", () => {
			const validator = mount({ min: "2" });

			validator.min = undefined;

			expect(validator.min).toBeNull();
		});
	});

	describe("name", () => {
		it("reflects the name attribute", () => {
			const validator = mount({ name: "tags" });
			expect(validator.name).toBe("tags");
		});

		it("returns null when name is not set", () => {
			const validator = mount();
			expect(validator.name).toBeNull();
		});
	});

	describe("required", () => {
		it("treats a present attribute as true", () => {
			const validator = mount();
			expect(validator.required).toBe(false);

			validator.setAttribute("required", "true");
			expect(validator.required).toBe(true);
		});

		it("treats required='false' as not required", () => {
			const validator = mount({ required: "true" });
			expect(validator.required).toBe(true);

			validator.setAttribute("required", "false");
			expect(validator.required).toBe(false);
		});

		it("resets to false when the attribute is removed", () => {
			const validator = mount({ required: "true" });
			expect(validator.required).toBe(true);

			validator.removeAttribute("required");
			expect(validator.required).toBe(false);
		});

		it("treats a bare attribute as true", () => {
			const validator = mount({ required: "" });
			expect(validator.required).toBe(true);
		});

		it.each([[true], [false]])("takes %s as a property", (value) => {
			const validator = mount();

			validator.required = value;

			expect(validator.required).toBe(value);
		});
	});

	describe("type", () => {
		it("returns the local element name", () => {
			const validator = mount();
			expect(validator.type).toBe(TAG);
		});
	});

	describe("validate", () => {
		it("flags valueMissing when required and the count is zero", () => {
			const validator = mount({ required: "true" }, input());
			const spy = vi.spyOn(validator.internals, "setValidity");

			validator.validate();

			expect(spy).toHaveBeenCalledWith(
				{ valueMissing: true },
				"error.validation.required",
				validator.input
			);
		});

		it("treats a missing count as zero", () => {
			const validator = mount({ min: "1" }, input());
			const spy = vi.spyOn(validator.internals, "setValidity");

			validator.validate();

			expect(spy).toHaveBeenCalledWith(
				{ rangeUnderflow: true },
				expect.stringContaining("error.validation.min"),
				validator.input
			);
		});

		it("treats required='false' as not required", () => {
			const validator = mount({ required: "false" }, input());
			const spy = vi.spyOn(validator.internals, "setValidity");

			validator.validate();

			expect(spy).toHaveBeenCalledWith({});
		});

		it("flags rangeUnderflow when the count is below min", () => {
			const validator = mount({ count: "2", min: "3" }, input());
			const spy = vi.spyOn(validator.internals, "setValidity");

			validator.validate();

			expect(spy).toHaveBeenCalledWith(
				{ rangeUnderflow: true },
				expect.stringContaining("error.validation.min"),
				validator.input
			);
		});

		it("flags rangeOverflow when the count is above max", () => {
			const validator = mount({ count: "3", max: "2" }, input());
			const spy = vi.spyOn(validator.internals, "setValidity");

			validator.validate();

			expect(spy).toHaveBeenCalledWith(
				{ rangeOverflow: true },
				expect.stringContaining("error.validation.max"),
				validator.input
			);
		});

		it("clears validity when constraints are satisfied", () => {
			const validator = mount({ count: "2", min: "1", max: "3" }, input());
			const spy = vi.spyOn(validator.internals, "setValidity");

			validator.validate();

			expect(spy).toHaveBeenCalledWith({});
		});

		it("prefers valueMissing over min when both could apply", () => {
			const validator = mount({ required: "true", min: "2" }, input());
			const spy = vi.spyOn(validator.internals, "setValidity");

			validator.validate();

			expect(spy).toHaveBeenCalledWith(
				{ valueMissing: true },
				expect.any(String),
				validator.input
			);
		});

		it("passes no anchor when there is nothing to attach to", () => {
			const validator = mount({ required: "true" });
			const spy = vi.spyOn(validator.internals, "setValidity");

			validator.validate();

			expect(spy).toHaveBeenCalledWith(
				{ valueMissing: true },
				"error.validation.required",
				undefined
			);
		});
	});
});
