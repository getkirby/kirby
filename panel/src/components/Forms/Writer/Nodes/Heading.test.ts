import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import {
	applyInputRule,
	createSchemaWithNodes,
	getNodeAttrs,
	hasNode,
	toHTML
} from "@test/unit/editor";
import utils from "../Utils";
import Heading from "./Heading";

const node = new Heading();
const schema = createSchemaWithNodes({ heading: node.schema });
const context = { type: schema.nodes.heading, schema, utils };

const customNode = new Heading({ levels: [2, 3] });
const customSchema = createSchemaWithNodes({ heading: customNode.schema });
const customContext = {
	type: customSchema.nodes.heading,
	schema: customSchema,
	utils
};

describe("Heading node", () => {
	beforeEach(() => {
		vi.stubGlobal("panel", { $t: (key: string) => key });
	});

	afterEach(() => {
		vi.unstubAllGlobals();
	});

	describe("button", () => {
		it("returns one button per level", () => {
			const buttons = node.button;
			expect(buttons).toHaveLength(6);
		});

		it("returns button configs", () => {
			const buttons = node.button;
			expect(buttons[0].id).toBe("h1");
			expect(buttons[0].icon).toBe("h1");
			expect(buttons[0].label).toBeDefined();

			expect(buttons[1].id).toBe("h2");
			expect(buttons[1].icon).toBe("h2");
			expect(buttons[1].label).toBeDefined();

			expect(buttons[5].id).toBe("h6");
			expect(buttons[5].icon).toBe("h6");
			expect(buttons[5].label).toBeDefined();
		});

		it("sets separator on the last button", () => {
			const buttons = node.button;
			expect(buttons[buttons.length - 1].separator).toBe(true);
		});

		it("does not set separator on other buttons", () => {
			const buttons = node.button;
			buttons.slice(0, -1).forEach((b: Record<string, unknown>) => expect(b.separator).toBeUndefined());
		});

		describe("with custom levels [2, 3]", () => {
			it("returns one button per configured level", () => {
				expect(customNode.button).toHaveLength(2);
			});

			it("returns buttons for h2 and h3 only", () => {
				const buttons = customNode.button;
				expect(buttons[0].id).toBe("h2");
				expect(buttons[1].id).toBe("h3");
			});

			it("sets separator on the last button", () => {
				const buttons = customNode.button;
				expect(buttons[1].separator).toBe(true);
			});
		});
	});

	describe("commands", () => {
		it("returns toggleHeading command that calls toggleBlockType", () => {
			const toggleBlockType = vi.fn();
			const commands = node.commands({
				...context,
				utils: { ...utils, toggleBlockType }
			});

			commands.toggleHeading({ level: 2 });
			expect(toggleBlockType).toHaveBeenCalledWith(
				schema.nodes.heading,
				schema.nodes.paragraph,
				{ level: 2 }
			);
		});

		it("returns h1..h6 commands that call toggleBlockType with level", () => {
			const toggleBlockType = vi.fn();
			const commands = node.commands({
				...context,
				utils: { ...utils, toggleBlockType }
			}) as Record<string, (...args: unknown[]) => unknown>;

			for (const level of [1, 2, 3, 4, 5, 6]) {
				commands[`h${level}`]();
				expect(toggleBlockType).toHaveBeenCalledWith(
					schema.nodes.heading,
					schema.nodes.paragraph,
					{ level }
				);
			}
		});

		describe("with custom levels [2, 3]", () => {
			it("returns h2 and h3 commands but not h1", () => {
				const toggleBlockType = vi.fn();
				const commands = customNode.commands({
					...customContext,
					utils: { ...utils, toggleBlockType }
				}) as Record<string, ((...args: unknown[]) => unknown) | undefined>;

				expect(commands.h2).toBeDefined();
				expect(commands.h3).toBeDefined();
				expect(commands.h1).toBeUndefined();
			});

			it("calls toggleBlockType with the correct level", () => {
				const toggleBlockType = vi.fn();
				const commands = customNode.commands({
					...customContext,
					utils: { ...utils, toggleBlockType }
				}) as Record<string, (...args: unknown[]) => unknown>;

				commands.h2();
				expect(toggleBlockType).toHaveBeenCalledWith(
					customSchema.nodes.heading,
					customSchema.nodes.paragraph,
					{ level: 2 }
				);
			});
		});
	});

	describe("defaults", () => {
		it("returns levels 1..6", () => {
			expect(node.defaults.levels).toEqual([1, 2, 3, 4, 5, 6]);
		});
	});

	describe("inputRules", () => {
		it("returns one rule per level", () => {
			expect(node.inputRules(context)).toHaveLength(6);
		});

		it("converts # to h1", () => {
			const [rule] = node.inputRules(context);
			const result = applyInputRule(schema, rule, "# ", "Hello");
			expect(result).toBe("<h1>Hello</h1>");
		});

		it("converts ## to h2", () => {
			const [, rule] = node.inputRules(context);
			const result = applyInputRule(schema, rule, "## ", "Hello");
			expect(result).toBe("<h2>Hello</h2>");
		});

		it("does not trigger for text that doesn't start with #", () => {
			const [rule] = node.inputRules(context);
			const result = applyInputRule(schema, rule, "not a heading ");
			expect(result).toBeNull();
		});

		describe("with custom levels [2, 3]", () => {
			it("returns two rules", () => {
				expect(customNode.inputRules(customContext)).toHaveLength(2);
			});

			it("converts ## to h2", () => {
				const [rule] = customNode.inputRules(customContext);
				const result = applyInputRule(customSchema, rule, "## ", "Hello");
				expect(result).toBe("<h2>Hello</h2>");
			});

			it("does not trigger for # (h1 not configured)", () => {
				const [rule] = customNode.inputRules(customContext);
				const result = applyInputRule(customSchema, rule, "# ");
				expect(result).toBeNull();
			});

			it("does not trigger for #### (h3+ out of range)", () => {
				const [rule] = customNode.inputRules(customContext);
				const result = applyInputRule(customSchema, rule, "#### ");
				expect(result).toBeNull();
			});
		});
	});

	describe("keys", () => {
		it("maps Shift-Ctrl-1 to set block type as h1", () => {
			const setBlockType = vi.fn(() => vi.fn());
			const keys = node.keys({
				...context,
				utils: { ...utils, setBlockType }
			});
			expect(keys["Shift-Ctrl-1"]).toBeDefined();
			expect(setBlockType).toHaveBeenCalledWith(schema.nodes.heading, {
				level: 1
			});
		});

		it("maps Shift-Ctrl-1 through Shift-Ctrl-6", () => {
			const setBlockType = vi.fn(() => vi.fn());
			const keys = node.keys({
				...context,
				utils: { ...utils, setBlockType }
			});
			for (const level of [1, 2, 3, 4, 5, 6]) {
				expect(keys[`Shift-Ctrl-${level}`]).toBeDefined();
			}
		});

		describe("with custom levels [2, 3]", () => {
			it("maps only Shift-Ctrl-2 and Shift-Ctrl-3", () => {
				const setBlockType = vi.fn(() => vi.fn());
				const keys = customNode.keys({
					...customContext,
					utils: { ...utils, setBlockType }
				});
				expect(keys["Shift-Ctrl-2"]).toBeDefined();
				expect(keys["Shift-Ctrl-3"]).toBeDefined();
				expect(keys["Shift-Ctrl-1"]).toBeUndefined();
			});
		});
	});

	describe("name", () => {
		it("returns 'heading'", () => {
			expect(node.name).toBe("heading");
		});
	});

	describe("schema", () => {
		describe("parseDOM", () => {
			for (const level of [1, 2, 3, 4, 5, 6]) {
				it(`parses <h${level}> as heading node with level ${level}`, () => {
					const html = `<h${level}>foo</h${level}>`;
					expect(hasNode(schema, html, schema.nodes.heading)).toBe(true);
					const attrs = getNodeAttrs(schema, html, schema.nodes.heading);
					expect(attrs?.level).toBe(level);
				});
			}
		});

		describe("toDOM", () => {
			it("renders h1 as <h1>", () => {
				const node = schema.nodes.heading.create({ level: 1 }, [
					schema.text("foo")
				]);
				const doc = schema.node("doc", null, [node]);
				expect(toHTML(schema, doc)).toBe("<h1>foo</h1>");
			});

			it("renders h3 as <h3>", () => {
				const node = schema.nodes.heading.create({ level: 3 }, [
					schema.text("foo")
				]);
				const doc = schema.node("doc", null, [node]);
				expect(toHTML(schema, doc)).toBe("<h3>foo</h3>");
			});
		});

		describe("with custom levels [2, 3]", () => {
			it("parses h2 as heading node with level 2", () => {
				const attrs = getNodeAttrs(
					customSchema,
					"<h2>foo</h2>",
					customSchema.nodes.heading
				);
				expect(attrs?.level).toBe(2);
			});

			it("parses h3 as heading node with level 3", () => {
				const attrs = getNodeAttrs(
					customSchema,
					"<h3>foo</h3>",
					customSchema.nodes.heading
				);
				expect(attrs?.level).toBe(3);
			});

			it("does not parse h1 as heading node", () => {
				expect(
					hasNode(customSchema, "<h1>foo</h1>", customSchema.nodes.heading)
				).toBe(false);
			});
		});
	});

	describe("type", () => {
		it("returns 'node'", () => {
			expect(node.type).toBe("node");
		});
	});
});
