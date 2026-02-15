/**
 * @vitest-environment jsdom
 */

import { describe, expect, it } from "vitest";
import { sanitizeHTML } from "./string.js";

describe.concurrent("$helper.string.sanitizeHTML", () => {
	it("should strip script tags", () => {
		expect(sanitizeHTML("<script>alert('xss')</script>")).toBe("");
	});

	it("should strip disallowed block elements but keep text", () => {
		expect(sanitizeHTML("<div>hello</div>")).toBe("hello");
	});

	it("should strip disallowed inline elements but keep text", () => {
		expect(sanitizeHTML("<span>hello</span>")).toBe("hello");
	});

	it("should preserve bold with strong tag", () => {
		expect(sanitizeHTML("<strong>bold</strong>")).toBe("<strong>bold</strong>");
	});

	it("should preserve bold with b tag", () => {
		expect(sanitizeHTML("<b>bold</b>")).toBe("<strong>bold</strong>");
	});

	it("should preserve italic with em tag", () => {
		expect(sanitizeHTML("<em>italic</em>")).toBe("<em>italic</em>");
	});

	it("should preserve italic with i tag", () => {
		expect(sanitizeHTML("<i>italic</i>")).toBe("<em>italic</em>");
	});

	it("should preserve underline", () => {
		expect(sanitizeHTML("<u>underline</u>")).toBe("<u>underline</u>");
	});

	it("should preserve links with attributes", () => {
		const html =
			'<a href="https://example.com" target="_blank" title="Example">link</a>';
		expect(sanitizeHTML(html)).toBe(
			'<a href="https://example.com" target="_blank" title="Example">link</a>'
		);
	});

	it("should preserve strike, code, sub, sup marks", () => {
		expect(sanitizeHTML("<s>strike</s>")).toBe("<s>strike</s>");
		expect(sanitizeHTML("<code>code</code>")).toBe("<code>code</code>");
		expect(sanitizeHTML("<sub>sub</sub>")).toBe("<sub>sub</sub>");
		expect(sanitizeHTML("<sup>sup</sup>")).toBe("<sup>sup</sup>");
	});

	it("should strip unsupported elements", () => {
		expect(sanitizeHTML("<font>text</font>")).toBe("text");
		expect(sanitizeHTML("<mark>text</mark>")).toBe("text");
	});

	it("should return empty string for empty, null or undefined input", () => {
		expect(sanitizeHTML("")).toBe("");
		expect(sanitizeHTML(null)).toBe("");
		expect(sanitizeHTML(undefined)).toBe("");
	});

	it("should handle plain text", () => {
		expect(sanitizeHTML("just text")).toBe("just text");
	});

	it("should handle nested allowed marks", () => {
		expect(sanitizeHTML("<strong><em>bold italic</em></strong>")).toBe(
			"<strong><em>bold italic</em></strong>"
		);
	});

	it("should restrict to custom marks", () => {
		const marks = ["bold", "italic"];
		expect(sanitizeHTML("<strong>bold</strong>", { marks })).toBe(
			"<strong>bold</strong>"
		);
		expect(sanitizeHTML("<u>underline</u>", { marks })).toBe("underline");
		expect(sanitizeHTML("<code>code</code>", { marks })).toBe("code");
	});

	it("should support custom nodes with block content", () => {
		const nodes = ["doc", "paragraph", "text"];
		expect(sanitizeHTML("<p>hello</p>", { nodes })).toBe("<p>hello</p>");
		expect(sanitizeHTML("hello", { nodes })).toBe("<p>hello</p>");
	});

	it("should combine custom marks and nodes", () => {
		expect(
			sanitizeHTML("<p><strong>bold</strong> <em>italic</em></p>", {
				marks: ["bold"],
				nodes: ["doc", "paragraph", "text"]
			})
		).toBe("<p><strong>bold</strong> italic</p>");
	});
});
