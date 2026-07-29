import { describe, expect, it } from "vitest";
import { sanitizeHTML } from "./string";
import { createExtensionsFromPlugins } from "./writer";
import Mark from "@/components/Forms/Writer/Mark";

describe("$helper.string.sanitizeHTML", () => {
	it("should strip script tags", async () => {
		expect(await sanitizeHTML("<script>alert('xss')</script>")).toBe("");
	});

	it("should strip disallowed block elements but keep text", async () => {
		expect(await sanitizeHTML("<div>hello</div>")).toBe("hello");
	});

	it("should strip disallowed inline elements but keep text", async () => {
		expect(await sanitizeHTML("<span>hello</span>")).toBe("hello");
	});

	it("should strip class and style attributes", async () => {
		expect(
			await sanitizeHTML('<span class="k-tag" style="background: red">hello</span>')
		).toBe("hello");
	});

	it("should preserve bold with strong tag", async () => {
		expect(await sanitizeHTML("<strong>bold</strong>")).toBe("<strong>bold</strong>");
	});

	it("should preserve bold with b tag", async () => {
		expect(await sanitizeHTML("<b>bold</b>")).toBe("<strong>bold</strong>");
	});

	it("should preserve italic with em tag", async () => {
		expect(await sanitizeHTML("<em>italic</em>")).toBe("<em>italic</em>");
	});

	it("should preserve italic with i tag", async () => {
		expect(await sanitizeHTML("<i>italic</i>")).toBe("<em>italic</em>");
	});

	it("should preserve underline", async () => {
		expect(await sanitizeHTML("<u>underline</u>")).toBe("<u>underline</u>");
	});

	it("should preserve links with attributes", async () => {
		const html =
			'<a href="https://example.com" target="_blank" title="Example">link</a>';
		expect(await sanitizeHTML(html)).toBe(
			'<a href="https://example.com" target="_blank" title="Example">link</a>'
		);
	});

	it("should preserve strike, code, sub, sup marks", async () => {
		expect(await sanitizeHTML("<s>strike</s>")).toBe("<s>strike</s>");
		expect(await sanitizeHTML("<code>code</code>")).toBe("<code>code</code>");
		expect(await sanitizeHTML("<sub>sub</sub>")).toBe("<sub>sub</sub>");
		expect(await sanitizeHTML("<sup>sup</sup>")).toBe("<sup>sup</sup>");
	});

	it("should strip unsupported elements", async () => {
		expect(await sanitizeHTML("<font>text</font>")).toBe("text");
		expect(await sanitizeHTML("<mark>text</mark>")).toBe("text");
	});

	it("should return empty string for empty, null or undefined input", async () => {
		expect(await sanitizeHTML("")).toBe("");
		expect(await sanitizeHTML(null)).toBe("");
		expect(await sanitizeHTML(undefined)).toBe("");
	});

	it("should handle plain text", async () => {
		expect(await sanitizeHTML("just text")).toBe("just text");
	});

	it("should handle nested allowed marks", async () => {
		expect(await sanitizeHTML("<strong><em>bold italic</em></strong>")).toBe(
			"<strong><em>bold italic</em></strong>"
		);
	});

	it("should restrict to custom marks", async () => {
		const marks = ["bold", "italic"];
		expect(await sanitizeHTML("<strong>bold</strong>", { marks })).toBe(
			"<strong>bold</strong>"
		);
		expect(await sanitizeHTML("<u>underline</u>", { marks })).toBe("underline");
		expect(await sanitizeHTML("<code>code</code>", { marks })).toBe("code");
	});

	it("should support custom nodes with block content", async () => {
		const nodes = ["doc", "paragraph", "text"];
		expect(await sanitizeHTML("<p>hello</p>", { nodes })).toBe("<p>hello</p>");
		expect(await sanitizeHTML("hello", { nodes })).toBe("<p>hello</p>");
	});

	it("should support custom mark instances", async () => {
		const { highlight } = createExtensionsFromPlugins(
			{
				highlight: {
					get schema() {
						return { parseDOM: [{ tag: "mark" }], toDOM: () => ["mark", 0] };
					}
				}
			},
			Mark.prototype
		);

		expect(await sanitizeHTML("<mark>text</mark>", { marks: [highlight] })).toBe(
			"<mark>text</mark>"
		);
		expect(await sanitizeHTML("<b>bold</b>", { marks: [highlight] })).toBe("bold");
	});

	it("should combine custom marks and nodes", async () => {
		expect(
			await sanitizeHTML("<p><strong>bold</strong> <em>italic</em></p>", {
				marks: ["bold"],
				nodes: ["doc", "paragraph", "text"]
			})
		).toBe("<p><strong>bold</strong> italic</p>");
	});
});
