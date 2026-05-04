/**
 * @vitest-environment jsdom
 */

import { afterEach, beforeAll, describe, expect, it } from "vitest";
import InputValidator from "./Validator.js";

const TAG = "k-input-validator-test";

beforeAll(() => {
	customElements.define(TAG, InputValidator);
});

afterEach(() => {
	document.body.innerHTML = "";
});

function mount(attrs = {}, child = null) {
	const validator = document.createElement(TAG);

	// jsdom does not fully implement ElementInternals.setValidity;
	// stub validate so connectedCallback does not throw unhandled errors
	validator.validate = () => {};

	for (const [key, value] of Object.entries(attrs)) {
		validator.setAttribute(key, value);
	}

	if (child) {
		validator.appendChild(child);
	}

	document.body.appendChild(validator);

	return validator;
}

describe("InputValidator", () => {
	describe("connectedCallback", () => {
		it("transfers its id to a child without an existing id", () => {
			const input = document.createElement("input");
			const validator = mount({ id: "myField" }, input);

			expect(input.getAttribute("id")).toBe("myField");
			expect(validator.hasAttribute("id")).toBe(false);
		});

		it("does not overwrite the existing id on the child", () => {
			const input = document.createElement("input");
			input.setAttribute("id", "myField-0");
			mount({ id: "myField" }, input);

			expect(input.getAttribute("id")).toBe("myField-0");
		});

		it("removes its own id even when the transfer was skipped", () => {
			const input = document.createElement("input");
			input.setAttribute("id", "myField-0");
			const validator = mount({ id: "myField" }, input);

			expect(validator.hasAttribute("id")).toBe(false);
		});

		it("does not throw when there is no child input", () => {
			expect(() => mount({ id: "myField" })).not.toThrow();
		});
	});
});
