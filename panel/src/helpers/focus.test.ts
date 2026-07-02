import { afterEach, describe, expect, it } from "vitest";
import focus, { focusIsInModal, focusTarget, isFocusable } from "./focus";

afterEach(() => {
	document.body.innerHTML = "";
});

describe("isFocusable()", () => {
	it("should return false for null", () => {
		expect(isFocusable(null)).toBe(false);
	});

	it("should return true for a button", () => {
		const button = document.createElement("button");
		expect(isFocusable(button)).toBe(true);
	});

	it("should return true for an input", () => {
		const input = document.createElement("input");
		expect(isFocusable(input)).toBe(true);
	});

	it("should return false for a disabled element", () => {
		const button = document.createElement("button");
		button.setAttribute("disabled", "");
		expect(isFocusable(button)).toBe(false);
	});

	it("should return false for an aria-disabled element", () => {
		const button = document.createElement("button");
		button.setAttribute("aria-disabled", "true");
		expect(isFocusable(button)).toBe(false);
	});

	it("should return false for a hidden input", () => {
		const input = document.createElement("input");
		input.setAttribute("type", "hidden");
		expect(isFocusable(input)).toBe(false);
	});

	it("should return false for a child of a disabled element", () => {
		const parent = document.createElement("fieldset");
		parent.setAttribute("disabled", "");
		const input = document.createElement("input");
		parent.appendChild(input);
		expect(isFocusable(input)).toBe(false);
	});
});

describe("focusIsInModal()", () => {
	it("should return false when not in a modal", () => {
		const el = document.createElement("div");
		expect(focusIsInModal(el)).toBe(false);
	});

	it("should return true when inside a k-dialog", () => {
		const dialog = document.createElement("div");
		dialog.className = "k-dialog";
		const el = document.createElement("input");
		dialog.appendChild(el);
		expect(focusIsInModal(el)).toBe(true);
	});

	it("should return true when inside a k-drawer", () => {
		const drawer = document.createElement("div");
		drawer.className = "k-drawer";
		const el = document.createElement("input");
		drawer.appendChild(el);
		expect(focusIsInModal(el)).toBe(true);
	});
});

describe("focusTarget()", () => {
	it("should return null when no focusable child is found", () => {
		const parent = document.createElement("div");
		expect(focusTarget(parent, ["button"])).toBe(null);
	});

	it("should return the first matching focusable child", () => {
		const parent = document.createElement("div");
		const button = document.createElement("button");
		parent.appendChild(button);
		expect(focusTarget(parent, ["button"])).toBe(button);
	});

	it("should skip disabled elements", () => {
		const parent = document.createElement("div");
		const button = document.createElement("button");
		button.setAttribute("disabled", "");
		parent.appendChild(button);
		expect(focusTarget(parent, ["button"])).toBe(null);
	});

	it("should respect selector priority order", () => {
		const parent = document.createElement("div");
		const button = document.createElement("button");
		const input = document.createElement("input");
		input.setAttribute("autofocus", "");
		parent.appendChild(button);
		parent.appendChild(input);
		const target = focusTarget(parent, ["[autofocus]", "button"]);
		expect(target).toBe(input);
	});
});

describe("focus()", () => {
	it("should return false for null", () => {
		expect(focus(null, "")).toBe(false);
	});

	it("should return false for a selector that matches nothing", () => {
		expect(focus("#nonexistent", "")).toBe(false);
	});

	it("should focus a child input and return it", () => {
		const parent = document.createElement("div");
		const input = document.createElement("input");
		parent.appendChild(input);
		document.body.appendChild(parent);
		expect(focus(parent, "")).toBe(input);
	});

	it("should focus by CSS selector string", () => {
		const input = document.createElement("input");
		input.id = "my-input";
		document.body.appendChild(input);
		expect(focus("#my-input", "")).toBe(input);
	});

	it("should focus a named field when field is specified", () => {
		const parent = document.createElement("div");
		const input = document.createElement("input");
		input.setAttribute("name", "email");
		const other = document.createElement("input");
		other.setAttribute("autofocus", "");
		parent.appendChild(other);
		parent.appendChild(input);
		document.body.appendChild(parent);
		expect(focus(parent, "email")).toBe(input);
	});

	it("should return false if a child already has focus", () => {
		const parent = document.createElement("div");
		const input = document.createElement("input");
		parent.appendChild(input);
		document.body.appendChild(parent);
		input.focus();
		expect(focus(parent, "")).toBe(false);
	});
});
