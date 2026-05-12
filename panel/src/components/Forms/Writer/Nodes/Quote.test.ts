import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import type { EditorState } from "prosemirror-state";
import {
	applyInputRule,
	createSchemaWithNodes,
	hasNode,
	toHTML
} from "@test/unit/editor";
import utils from "../Utils";
import Quote from "./Quote";

const node = new Quote();
const schema = createSchemaWithNodes({ quote: node.schema });
const context = { type: schema.nodes.quote, schema, utils };

describe("Quote node", () => {
	beforeEach(() => {
		vi.stubGlobal("panel", { t: (key: string) => key });
	});

	afterEach(() => {
		vi.unstubAllGlobals();
	});

	describe("button", () => {
		it("returns the button config", () => {
			const button = node.button;
			expect(button.icon).toBe("quote");
			expect(button.id).toBe("quote");
			expect(button.name).toBe("quote");
			expect(button.label).toBeDefined();
		});
	});

	describe("commands", () => {
		it("returns a command that toggles the wrap", () => {
			const toggleWrap = vi.fn();
			const command = node.commands({
				...context,
				utils: { ...utils, toggleWrap }
			});
			command();
			expect(toggleWrap).toHaveBeenCalledWith(schema.nodes.quote);
		});
	});

	describe("inputRules", () => {
		const [rule] = node.inputRules(context);

		it("wraps paragraph in blockquote when '> ' is typed", () => {
			const result = applyInputRule(schema, rule, "> ", "Foo");
			expect(result).toBe("<blockquote><p>Foo</p></blockquote>");
		});

		it("wraps paragraph in blockquote with extra leading whitespace", () => {
			const result = applyInputRule(schema, rule, "	  > ", "Bar");
			expect(result).toBe("<blockquote><p>Bar</p></blockquote>");
		});

		it("does not trigger for other input", () => {
			const result = applyInputRule(schema, rule, "not a > quote");
			expect(result).toBeNull();
		});
	});

	describe("keys", () => {
		it("maps Shift-Tab to lift", () => {
			const lift = vi.fn();
			const keys = node.keys({ ...context, utils: { ...utils, lift } });
			const state = {} as EditorState;
			const dispatch = vi.fn();
			keys["Shift-Tab"](state, dispatch);
			expect(lift).toHaveBeenCalledWith(state, dispatch);
		});
	});

	describe("name", () => {
		it("returns 'quote'", () => {
			expect(node.name).toBe("quote");
		});
	});

	describe("schema", () => {
		describe("parseDOM", () => {
			it("parses `<blockquote>` as quote node", () => {
				const html = "<blockquote>foo</blockquote>";
				expect(hasNode(schema, html, schema.nodes.quote)).toBe(true);
			});
		});

		describe("toDOM", () => {
			it("renders the quote node as a `<blockquote>` element", () => {
				const node = schema.nodes.quote.create(null, [schema.text("foo")]);
				const doc = schema.node("doc", null, [node]);
				expect(toHTML(schema, doc)).toBe("<blockquote>foo</blockquote>");
			});
		});
	});

	describe("type", () => {
		it("returns 'node'", () => {
			expect(node.type).toBe("node");
		});
	});
});
