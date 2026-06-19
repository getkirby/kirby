import { afterEach, beforeAll, describe, expect, it, vi } from "vitest";
import { wrap } from "@/helpers/array";
import InputValidator from "./Validator.js";

const TAG = "k-input-validator-test";

beforeAll(() => {
	// happy-dom does not implement ElementInternals or attachInternals.
	// Stub attachInternals to return a fresh per-instance fake so each
	// element gets its own setValidity spy target.
	HTMLElement.prototype.attachInternals ??= function () {
		return {
			setValidity: () => {},
			checkValidity: () => true,
			reportValidity: () => true,
			form: null,
			validity: {},
			validationMessage: "",
			willValidate: true
		} as ElementInternals;
	};

	customElements.define(TAG, InputValidator);

	// Minimal panel.t shim used by validate()
	window.panel = {
		t: (value: string) => value
	} as unknown as typeof window.panel;
});

afterEach(() => {
	document.body.innerHTML = "";
	vi.restoreAllMocks();
});

/**
 * Creates a validator element with the given attributes and children
 * and appends it to the document body so connectedCallback fires.
 */
function mount(
	attrs: Record<string, string> = {},
	children: HTMLElement | HTMLElement[] = []
): InputValidator {
	const validator = document.createElement(TAG) as InputValidator;

	for (const [key, value] of Object.entries(attrs)) {
		validator.setAttribute(key, value);
	}

	for (const child of wrap(children)) {
		validator.appendChild(child);
	}

	document.body.appendChild(validator);
	return validator;
}

function input(attrs: Record<string, string> = {}): HTMLInputElement {
	const el = document.createElement("input");

	for (const [key, value] of Object.entries(attrs)) {
		el.setAttribute(key, value);
	}

	return el;
}

describe("InputValidator", () => {
	describe("attributeChangedCallback", () => {
		it("coerces min and max to numbers", () => {
			const validator = mount();

			expect(validator.min).toBeNull();
			expect(validator.max).toBeNull();

			validator.setAttribute("min", "1");
			validator.setAttribute("max", "5");

			expect(validator.min).toBe(1);
			expect(validator.max).toBe(5);
		});

		it("resets min and max to null when the attributes are removed", () => {
			const validator = mount({ min: "2", max: "8" });

			expect(validator.min).toBe(2);
			expect(validator.max).toBe(8);

			validator.removeAttribute("min");
			validator.removeAttribute("max");

			expect(validator.min).toBeNull();
			expect(validator.max).toBeNull();
		});

		it("treats required as a boolean: present → true", () => {
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

		it("resets required to false when the attribute is removed", () => {
			const validator = mount({ required: "true" });
			expect(validator.required).toBe(true);
			validator.removeAttribute("required");
			expect(validator.required).toBe(false);
		});
	});

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

		it("matches input, textarea, select and button", () => {
			for (const tag of ["input", "textarea", "select", "button"]) {
				const child = document.createElement(tag);
				const validator = mount({}, child);
				expect(validator.input).toBe(child);
				document.body.innerHTML = "";
			}
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

	describe("type", () => {
		it("returns the local element name", () => {
			const validator = mount();
			expect(validator.type).toBe(TAG);
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

		it("treats required='false' as not required", () => {
			const validator = mount({ required: "false" }, input());
			const spy = vi.spyOn(validator.internals, "setValidity");

			validator.validate();

			expect(spy).toHaveBeenCalledWith({});
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
	});

	describe("value", () => {
		it("parses a JSON-encoded array into entries", () => {
			const validator = mount();
			validator.value = JSON.stringify(["a", "b"]);
			expect(validator.entries).toEqual(["a", "b"]);
		});

		it("falls back to an empty array for non-string input", () => {
			const validator = mount();
			// @ts-expect-error testing invalid input
			validator.value = null;
			expect(validator.entries).toEqual([]);
		});

		it("serializes entries back to a JSON string via the getter", () => {
			const validator = mount();
			validator.value = JSON.stringify([1, 2, 3]);
			expect(validator.value).toBe("[1,2,3]");
		});

		it("returns an empty array as JSON when entries are missing", () => {
			const validator = mount();
			// @ts-expect-error testing invalid input
			validator.entries = null;
			expect(validator.value).toBe("[]");
		});

		it("re-runs validation when set", () => {
			const validator = mount({ required: "true" }, input());
			const spy = vi.spyOn(validator.internals, "setValidity");

			validator.value = JSON.stringify(["x"]);

			expect(spy).toHaveBeenCalledWith({});
		});
	});
});
