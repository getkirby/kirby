import type { App } from "vue";
import { describe, expect, it } from "vitest";
import isComponent from "./isComponent";

const app = {
	_context: { components: { "k-foo": {} } }
} as unknown as App;

describe("$helper.isComponent()", () => {
	it("should return true for a registered component", () => {
		expect(isComponent("k-foo", app)).toBe(true);
	});

	it("should return false for an unregistered component", () => {
		expect(isComponent("k-unknown", app)).toBe(false);
	});

	it("should fall back to the global panel app", () => {
		window.panel = { app };
		expect(isComponent("k-foo")).toBe(true);
	});

	it("should return false when no app is available", () => {
		window.panel = {};
		expect(isComponent("k-foo")).toBe(false);
	});
});
