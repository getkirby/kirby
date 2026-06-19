import { describe, expect, it, vi } from "vitest";
import html, { HtmlString } from "./html";

describe("HtmlString class", () => {
	describe("instance", () => {
		it("extends String", () => {
			const html = new HtmlString("<b>safe</b>");
			expect(html).toBeInstanceOf(HtmlString);
			expect(html).toBeInstanceOf(String);
		});

		it("returns its raw content via toString()", () => {
			expect(new HtmlString("<b>safe</b>").toString()).toBe("<b>safe</b>");
		});

		it("returns its raw content via String()", () => {
			expect(String(new HtmlString("<b>x</b>"))).toBe("<b>x</b>");
		});

		it("serializes to JSON as a plain string", () => {
			expect(JSON.stringify(new HtmlString("<b>x</b>"))).toBe('"<b>x</b>"');
		});

		it("concatenates like a string", () => {
			expect("a" + new HtmlString("b")).toBe("ab");
		});
	});

	describe("resolve()", () => {
		it("rewraps top-level <key> into HtmlString and strips brackets", () => {
			const result = HtmlString.resolve({
				title: "plain",
				"<body>": "<p>html</p>"
			}) as unknown as { title: string; body: HtmlString };

			expect(result.title).toBe("plain");
			expect(result.body).toBeInstanceOf(HtmlString);
			expect(result.body.toString()).toBe("<p>html</p>");
		});

		it("recurses into nested objects", () => {
			const result = HtmlString.resolve({
				view: {
					props: {
						"<help>": "<em>hi</em>",
						name: "plain"
					}
				}
			}) as unknown as { view: { props: { help: HtmlString; name: string } } };

			expect(result.view.props.help).toBeInstanceOf(HtmlString);
			expect(result.view.props.name).toBe("plain");
		});

		it("recurses into arrays of objects", () => {
			const result = HtmlString.resolve({
				options: [
					{ "<text>": "<b>Bold</b>", value: "a" },
					{ text: "Plain", value: "b" }
				]
			}) as unknown as {
				options: Array<{ text: string | HtmlString; value: string }>;
			};

			expect(result.options[0].text).toBeInstanceOf(HtmlString);
			expect(result.options[1].text).toBe("Plain");
		});

		it("does not touch plain objects/arrays", () => {
			const input = { a: 1, b: [{ c: 2 }] };
			const result = HtmlString.resolve(input);
			expect(result).toEqual(input);
		});

		it("does not mutate the input", () => {
			const input = { "<body>": "<p>x</p>" };
			HtmlString.resolve(input);
			expect(input).toEqual({ "<body>": "<p>x</p>" });
		});

		it("passes primitives through unchanged", () => {
			expect(HtmlString.resolve("plain")).toBe("plain");
			expect(HtmlString.resolve(42)).toBe(42);
			expect(HtmlString.resolve(null)).toBe(null);
			expect(HtmlString.resolve(undefined)).toBe(undefined);
		});

		it("ignores single-char angle pairs and empty bracket strings", () => {
			// "<>" alone is not a meaningful key wrapper
			const result = HtmlString.resolve({ "<>": "x" }) as Record<
				string,
				unknown
			>;
			expect(result["<>"]).toBe("x");
			expect(result["<>"] instanceof HtmlString).toBe(false);
		});

		it("warns on collision between key and <key> in the same object", () => {
			const warn = vi.spyOn(console, "warn").mockImplementation(() => {});
			HtmlString.resolve({
				foo: "untrusted",
				"<foo>": "<b>trusted</b>"
			});
			expect(warn).toHaveBeenCalledOnce();
			expect(warn.mock.calls[0][0]).toMatch(/foo/);
			warn.mockRestore();
		});

		it("wraps non-string values under <key> by recursing", () => {
			// Pragmatic edge case: if backend wraps an array under <key>,
			// don't blindly stringify - recurse into it.
			const result = HtmlString.resolve({
				"<items>": [{ "<text>": "<b>x</b>" }]
			}) as unknown as { items: Array<{ text: HtmlString }> };

			expect(Array.isArray(result.items)).toBe(true);
			expect(result.items[0].text).toBeInstanceOf(HtmlString);
		});
	});
});

describe("$html()", () => {
	it("wraps a plain string in HtmlString", () => {
		const result = html("<b>x</b>");
		expect(result).toBeInstanceOf(HtmlString);
		expect(result.toString()).toBe("<b>x</b>");
	});

	it("returns an HtmlString unchanged (same identity)", () => {
		const safe = new HtmlString("<b>x</b>");
		expect(html(safe)).toBe(safe);
	});

	it("coerces null to an empty HtmlString", () => {
		expect(html(null).toString()).toBe("");
	});

	it("coerces undefined to an empty HtmlString", () => {
		expect(html(undefined).toString()).toBe("");
	});

	it("coerces a number to its string form", () => {
		expect(html(42).toString()).toBe("42");
	});
});
