import { describe, expect, it } from "vitest";
import { createSchemaWithNodes, select } from "@test/unit/editor";
import getNodeAttrs from "./getNodeAttrs";

const schema = createSchemaWithNodes({
	section: {
		group: "block",
		content: "block+",
		attrs: { id: { default: "" } }
	},
	heading: {
		group: "block",
		content: "inline*",
		attrs: { level: { default: 1 } }
	}
});

const headingType = schema.nodes.heading;
const sectionType = schema.nodes.section;

// doc(heading({level:1}, "a") + paragraph("b") + heading({level:2}, "c"))
// Positions: 0=before h1, 1=inside h1, 2=after h1, 3=before para, 4=inside para, 5=after para, 6=before h2, 7=inside h2, 8=after h2
const doc = schema.node("doc", null, [
	schema.node("heading", { level: 1 }, [schema.text("a")]),
	schema.node("paragraph", null, [schema.text("b")]),
	schema.node("heading", { level: 2 }, [schema.text("c")])
]);

// doc(section({id:"outer"}, section({id:"inner"}, paragraph("hello"))))
const docNested = schema.node("doc", null, [
	schema.node("section", { id: "outer" }, [
		schema.node("section", { id: "inner" }, [
			schema.node("paragraph", null, [schema.text("hello")])
		])
	])
]);

describe("getNodeAttrs", () => {
	it("returns an empty object when no node of that type is in the selection", () => {
		const state = select(doc, 4, 4); // cursor in paragraph
		expect(getNodeAttrs(state, headingType)).toEqual({});
	});

	it("returns the attrs of the matching node", () => {
		const state = select(doc, 1, 1); // cursor in h1
		expect(getNodeAttrs(state, headingType)).toEqual({ level: 1 });
	});

	it("returns the attrs of the last matched node when the selection spans multiple", () => {
		const state = select(doc, 1, 7); // spans h1 through h2
		expect(getNodeAttrs(state, headingType)).toEqual({ level: 2 });
	});

	it("returns the attrs of the deepest matching node when types are nested", () => {
		const state = select(docNested, 4, 4); // cursor inside "hello"
		expect(getNodeAttrs(state, sectionType)).toEqual({ id: "inner" });
	});
});
