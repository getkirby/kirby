/**
 * @vitest-environment jsdom
 */

import { describe, expect, it } from "vitest";
import {
	allowedExtensions,
	availableMarks,
	availableMarksFromPlugins,
	availableNodes,
	availableNodesFromPlugins,
	createExtensionsFromPlugins,
	createMarks,
	createNodes,
	extensionOptions,
	filterExtensions,
	keepInlineNodes
} from "./writer.js";
import Mark from "@/components/Forms/Writer/Mark";
import Node from "@/components/Forms/Writer/Node";

const allMarkNames = [
	"bold",
	"clear",
	"code",
	"email",
	"italic",
	"link",
	"strike",
	"sup",
	"sub",
	"underline"
];

const allNodeNames = [
	"bulletList",
	"doc",
	"hardBreak",
	"heading",
	"horizontalRule",
	"listItem",
	"orderedList",
	"paragraph",
	"quote",
	"text"
];

describe("allowedExtensions", () => {
	const available = { a: 1, b: 2, c: 3 };

	it("should return empty array for false", () => {
		expect(allowedExtensions(available, false)).toEqual([]);
	});

	it("should return all keys for true", () => {
		expect(allowedExtensions(available, true)).toEqual(["a", "b", "c"]);
	});

	it("should return the array as-is", () => {
		expect(allowedExtensions(available, ["a", "c"])).toEqual(["a", "c"]);
	});

	it("should return empty array for empty array", () => {
		expect(allowedExtensions(available, [])).toEqual([]);
	});

	it("should return enabled keys from object", () => {
		expect(
			allowedExtensions(available, { a: true, b: false, c: true })
		).toEqual(["a", "c"]);
	});

	it("should treat object values as enabled", () => {
		expect(
			allowedExtensions(available, {
				a: { option: "value" },
				b: true,
				c: false
			})
		).toEqual(["a", "b"]);
	});

	it("should return all keys for null", () => {
		expect(allowedExtensions(available, null)).toEqual(["a", "b", "c"]);
	});

	it("should return all keys for undefined", () => {
		expect(allowedExtensions(available, undefined)).toEqual(["a", "b", "c"]);
	});
});

describe("extensionOptions", () => {
	it("should return empty object for true", () => {
		expect(extensionOptions(true)).toEqual({});
	});

	it("should return empty object for false", () => {
		expect(extensionOptions(false)).toEqual({});
	});

	it("should return empty object for null", () => {
		expect(extensionOptions(null)).toEqual({});
	});

	it("should return empty object for array", () => {
		expect(extensionOptions(["a", "b"])).toEqual({});
	});

	it("should extract object values", () => {
		expect(
			extensionOptions({
				a: { level: 1 },
				b: true,
				c: { inline: true },
				d: false
			})
		).toEqual({
			a: { level: 1 },
			c: { inline: true }
		});
	});

	it("should return empty object when no options exist", () => {
		expect(extensionOptions({ a: true, b: false })).toEqual({});
	});
});

describe("availableMarks", () => {
	it("should return all built-in marks", () => {
		const marks = availableMarks();
		expect(Object.keys(marks).sort()).toEqual([...allMarkNames].sort());
	});

	it("should return instances with correct names", () => {
		const marks = availableMarks();
		for (const name of allMarkNames) {
			expect(marks[name].name).toBe(name);
		}
	});

	it("should pass options to constructors", () => {
		const marks = availableMarks({ bold: { custom: "value" } });
		expect(marks.bold.options.custom).toBe("value");
	});

	it("should use defaults without options", () => {
		const marks = availableMarks();
		expect(marks.bold.options).toEqual({});
	});
});

describe("availableMarksFromPlugins", () => {
	it("should return empty object without plugins", () => {
		expect(availableMarksFromPlugins()).toEqual({});
	});
});

describe("availableNodes", () => {
	it("should return all built-in nodes", () => {
		const nodes = availableNodes();
		expect(Object.keys(nodes).sort()).toEqual([...allNodeNames].sort());
	});

	it("should return instances with correct names", () => {
		const nodes = availableNodes();
		for (const name of allNodeNames) {
			expect(nodes[name].name).toBe(name);
		}
	});

	it("should pass options to constructors", () => {
		const nodes = availableNodes({ heading: { levels: [1, 2] } });
		expect(nodes.heading.options.levels).toEqual([1, 2]);
	});

	it("should use defaults without options", () => {
		const nodes = availableNodes();
		expect(nodes.heading.options.levels).toEqual([1, 2, 3, 4, 5, 6]);
		expect(nodes.hardBreak.options.enter).toBe(false);
		expect(nodes.hardBreak.options.text).toBe(false);
		expect(nodes.doc.options.inline).toBe(false);
	});

	it("should pass doc inline option", () => {
		const nodes = availableNodes({ doc: { inline: true } });
		expect(nodes.doc.options.inline).toBe(true);
	});

	it("should pass hardBreak options", () => {
		const nodes = availableNodes({
			hardBreak: { enter: true, text: true }
		});
		expect(nodes.hardBreak.options.enter).toBe(true);
		expect(nodes.hardBreak.options.text).toBe(true);
	});
});

describe("availableNodesFromPlugins", () => {
	it("should return empty object without plugins", () => {
		expect(availableNodesFromPlugins()).toEqual({});
	});
});

describe("createExtensionsFromPlugins", () => {
	it("should return empty object for empty plugins", () => {
		expect(createExtensionsFromPlugins({}, Mark.prototype)).toEqual({});
	});

	it("should create mark instances from plugin definitions", () => {
		const plugins = {
			highlight: {
				get schema() {
					return {
						parseDOM: [{ tag: "mark" }],
						toDOM: () => ["mark", 0]
					};
				}
			}
		};

		const result = createExtensionsFromPlugins(plugins, Mark.prototype);
		expect(result.highlight).toBeDefined();
		expect(result.highlight.name).toBe("highlight");
		expect(result.highlight.schema.parseDOM).toEqual([{ tag: "mark" }]);
	});

	it("should create node instances from plugin definitions", () => {
		const plugins = {
			customBlock: {
				get schema() {
					return {
						content: "inline*",
						group: "block",
						parseDOM: [{ tag: "div.custom" }],
						toDOM: () => ["div", { class: "custom" }, 0]
					};
				}
			}
		};

		const result = createExtensionsFromPlugins(plugins, Node.prototype);
		expect(result.customBlock).toBeDefined();
		expect(result.customBlock.name).toBe("customBlock");
		expect(result.customBlock.schema.group).toBe("block");
	});

	it("should handle multiple plugins", () => {
		const plugins = {
			foo: { get schema() { return {}; } },
			bar: { get schema() { return {}; } }
		};

		const result = createExtensionsFromPlugins(plugins, Mark.prototype);
		expect(Object.keys(result)).toEqual(["foo", "bar"]);
	});
});

describe("filterExtensions", () => {
	const available = {
		a: { name: "a" },
		b: { name: "b" },
		c: { name: "c" }
	};

	it("should return all extensions for true", () => {
		const result = filterExtensions(available, true);
		expect(Object.keys(result)).toEqual(["a", "b", "c"]);
	});

	it("should return no extensions for false", () => {
		const result = filterExtensions(available, false);
		expect(Object.keys(result)).toEqual([]);
	});

	it("should filter by array", () => {
		const result = filterExtensions(available, ["a", "c"]);
		expect(Object.keys(result)).toEqual(["a", "c"]);
	});

	it("should filter by object", () => {
		const result = filterExtensions(available, {
			a: true,
			b: false,
			c: { option: 1 }
		});
		expect(Object.keys(result)).toEqual(["a", "c"]);
	});

	it("should ignore unknown extensions in array", () => {
		const result = filterExtensions(available, ["a", "unknown"]);
		expect(Object.keys(result)).toEqual(["a"]);
	});

	it("should ignore unknown extensions in object", () => {
		const result = filterExtensions(available, {
			a: true,
			unknown: true
		});
		expect(Object.keys(result)).toEqual(["a"]);
	});

	it("should preserve extension references", () => {
		const result = filterExtensions(available, ["a"]);
		expect(result.a).toBe(available.a);
	});
});

describe("createMarks", () => {
	it("should create all marks with true", () => {
		const marks = createMarks(true);
		expect(Object.keys(marks).sort()).toEqual([...allMarkNames].sort());
	});

	it("should create no marks with false", () => {
		const marks = createMarks(false);
		expect(Object.keys(marks)).toEqual([]);
	});

	it("should filter marks by array", () => {
		const marks = createMarks(["bold", "italic"]);
		expect(Object.keys(marks).sort()).toEqual(["bold", "italic"]);
	});

	it("should filter marks by object", () => {
		const marks = createMarks({
			bold: true,
			italic: true,
			strike: false
		});
		expect(Object.keys(marks).sort()).toEqual(["bold", "italic"]);
	});

	it("should pass options from object values", () => {
		const marks = createMarks({
			bold: { custom: "test" },
			italic: true
		});
		expect(marks.bold.options.custom).toBe("test");
		expect(Object.keys(marks).sort()).toEqual(["bold", "italic"]);
	});

	it("should re-install required marks", () => {
		const marks = createMarks(["bold"], ["italic"]);
		expect(marks.bold).toBeDefined();
		expect(marks.italic).toBeDefined();
	});

	it("should re-install required marks even with false", () => {
		const marks = createMarks(false, ["bold"]);
		expect(Object.keys(marks)).toEqual(["bold"]);
	});

	it("should re-install required marks even when disabled in object", () => {
		const marks = createMarks({ bold: false }, ["bold"]);
		expect(marks.bold).toBeDefined();
	});

	it("should return instances with correct names", () => {
		const marks = createMarks(["bold", "italic"]);
		expect(marks.bold.name).toBe("bold");
		expect(marks.italic.name).toBe("italic");
	});
});

describe("createNodes", () => {
	it("should create all nodes with true", () => {
		const nodes = createNodes(true);
		expect(Object.keys(nodes).sort()).toEqual([...allNodeNames].sort());
	});

	it("should create no nodes with false", () => {
		const nodes = createNodes(false);
		expect(Object.keys(nodes)).toEqual([]);
	});

	it("should filter nodes by array", () => {
		const nodes = createNodes(["doc", "text", "paragraph"]);
		expect(Object.keys(nodes).sort()).toEqual(["doc", "paragraph", "text"]);
	});

	it("should filter nodes by object", () => {
		const nodes = createNodes({
			doc: true,
			text: true,
			paragraph: true,
			heading: false
		});
		expect(Object.keys(nodes).sort()).toEqual(["doc", "paragraph", "text"]);
	});

	it("should pass options from object values", () => {
		const nodes = createNodes({
			doc: { inline: true },
			text: true
		});
		expect(nodes.doc.options.inline).toBe(true);
	});

	it("should pass heading levels via object", () => {
		const nodes = createNodes({
			heading: { levels: [1, 2] },
			doc: true,
			text: true,
			paragraph: true
		});
		expect(nodes.heading.options.levels).toEqual([1, 2]);
	});

	it("should pass hardBreak options via object", () => {
		const nodes = createNodes({
			hardBreak: { enter: true, text: true },
			doc: true,
			text: true,
			paragraph: true
		});
		expect(nodes.hardBreak.options.enter).toBe(true);
		expect(nodes.hardBreak.options.text).toBe(true);
	});

	it("should re-install required nodes", () => {
		const nodes = createNodes(["doc"], ["text", "paragraph"]);
		expect(nodes.doc).toBeDefined();
		expect(nodes.text).toBeDefined();
		expect(nodes.paragraph).toBeDefined();
	});

	it("should re-install required nodes even with false", () => {
		const nodes = createNodes(false, ["doc", "text"]);
		expect(Object.keys(nodes).sort()).toEqual(["doc", "text"]);
	});

	it("should auto-install listItem for bulletList", () => {
		const nodes = createNodes(["bulletList", "doc", "text", "paragraph"]);
		expect(nodes.listItem).toBeDefined();
	});

	it("should auto-install listItem for orderedList", () => {
		const nodes = createNodes(["orderedList", "doc", "text", "paragraph"]);
		expect(nodes.listItem).toBeDefined();
	});

	it("should auto-install listItem for bulletList in object format", () => {
		const nodes = createNodes({
			bulletList: true,
			doc: true,
			text: true,
			paragraph: true
		});
		expect(nodes.listItem).toBeDefined();
	});

	it("should not install listItem without lists", () => {
		const nodes = createNodes(["doc", "text", "paragraph"]);
		expect(nodes.listItem).toBeUndefined();
	});

	it("should return instances with correct names", () => {
		const nodes = createNodes(["doc", "text", "paragraph"]);
		expect(nodes.doc.name).toBe("doc");
		expect(nodes.text.name).toBe("text");
		expect(nodes.paragraph.name).toBe("paragraph");
	});
});

describe("keepInlineNodes", () => {
	it("should keep only inline nodes", () => {
		const nodes = [
			{ schema: { inline: true } },
			{ schema: { inline: false } },
			{ schema: { inline: true } },
			{ schema: {} }
		];

		const result = keepInlineNodes(nodes);
		expect(result).toHaveLength(2);
		expect(result[0]).toBe(nodes[0]);
		expect(result[1]).toBe(nodes[2]);
	});

	it("should return empty array when no inline nodes exist", () => {
		const nodes = [{ schema: { group: "block" } }, { schema: {} }];
		expect(keepInlineNodes(nodes)).toEqual([]);
	});

	it("should return empty array for empty input", () => {
		expect(keepInlineNodes([])).toEqual([]);
	});
});
