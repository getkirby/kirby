import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import {
	applyInputRule,
	createSchemaWithNodes,
	hasNode,
	toHTML
} from "@test/unit/editor";
import utils from "../Utils";
import HorizontalRule from "./HorizontalRule";

const node = new HorizontalRule();
const schema = createSchemaWithNodes({ horizontalRule: node.schema });
const context = { type: schema.nodes.horizontalRule, schema, utils };

describe("HorizontalRule node", () => {
	beforeEach(() => {
		vi.stubGlobal("panel", { $t: (key: string) => key });
	});

	afterEach(() => {
		vi.unstubAllGlobals();
	});

	describe("commands", () => {
		it("returns a command that inserts the node", () => {
			const insertNode = vi.fn();
			const command = node.commands({
				...context,
				utils: { ...utils, insertNode }
			});
			command();
			expect(insertNode).toHaveBeenCalledWith(schema.nodes.horizontalRule);
		});
	});

	describe("inputRules", () => {
		const [rule] = node.inputRules(context);

		it("insert rule when '---' is typed", () => {
			const result = applyInputRule(schema, rule, "---");
			expect(result).toBe("<hr>");
		});

		it("insert rule when '___ '  is typed", () => {
			const result = applyInputRule(schema, rule, "___ ");
			expect(result).toBe("<hr>");
		});

		it("insert rule when '*** '  is typed", () => {
			const result = applyInputRule(schema, rule, "*** ");
			expect(result).toBe("<hr>");
		});

		it("does not insert rule for partial '--' matches", () => {
			const result = applyInputRule(schema, rule, "--");
			expect(result).toBeNull();
		});

		it("does not insert rule for partial '__' matches", () => {
			const result = applyInputRule(schema, rule, "__");
			expect(result).toBeNull();
		});

		it("does not insert rule for partial '**' matches", () => {
			const result = applyInputRule(schema, rule, "**");
			expect(result).toBeNull();
		});
	});

	describe("name", () => {
		it("returns 'horizontalRule'", () => {
			expect(node.name).toBe("horizontalRule");
		});
	});

	describe("schema", () => {
		describe("parseDOM", () => {
			it("parses <hr> as horizontalRule node", () => {
				const html = "<hr>";
				expect(hasNode(schema, html, schema.nodes.horizontalRule)).toBe(true);
			});
		});

		describe("toDOM", () => {
			it("renders the horizontalRule node as <hr>", () => {
				const node = schema.nodes.horizontalRule.create();
				const doc = schema.node("doc", null, [node]);
				expect(toHTML(schema, doc)).toBe("<hr>");
			});
		});
	});

	describe("type", () => {
		it("returns 'node'", () => {
			expect(node.type).toBe("node");
		});
	});
});
