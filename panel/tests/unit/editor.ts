import { inputRules, type InputRule } from "prosemirror-inputrules";
import {
	DOMParser,
	DOMSerializer,
	Fragment,
	Schema,
	Slice,
	type MarkSpec,
	type MarkType,
	type Node
} from "prosemirror-model";
import { EditorState, type Plugin, type Transaction } from "prosemirror-state";
import { vi } from "vitest";
import type Editor from "@/components/Forms/Writer/Editor";

/**
 * Simulates typing `text` into a paragraph and returns the resulting HTML,
 * or null if the rule's regex did not match.
 *
 * The last character of `text` is treated as the trigger that fired the
 * rule. It is not present in the document when the handler runs, matching how
 * ProseMirror calls input rule handlers.
 */
export function applyInputRule(
	schema: Schema,
	rule: InputRule,
	text: string
): string | null {
	const trigger = text.slice(-1);
	const docText = text.slice(0, -1);

	const doc = schema.node("doc", null, [
		schema.node("paragraph", null, docText ? [schema.text(docText)] : [])
	]);

	const initial = EditorState.create({ doc });
	let result = initial;

	// Drive the rule through the `inputRules` plugin's
	// `handleTextInput` prop, exactly the path ProseMirror
	// takes when the trigger character is typed at the
	// end of the paragraph
	const view = {
		composing: false,
		state: initial,
		dispatch: (tr: Transaction) => {
			result = initial.apply(tr);
		}
	};

	const end = 1 + docText.length;
	const handled = (
		inputRules({ rules: [rule] }).props as {
			handleTextInput: (
				view: unknown,
				from: number,
				to: number,
				text: string
			) => boolean;
		}
	).handleTextInput(view, end, end, trigger);

	if (handled !== true) {
		return null;
	}

	return toHTML(schema, result.doc);
}

/**
 * Applies a paste rule plugin to a paragraph of pasted `text`
 * and returns the resulting HTML.
 */
export function applyPasteRule(
	schema: Schema,
	plugin: Plugin,
	text: string
): string {
	const slice = new Slice(
		Fragment.from(schema.node("paragraph", null, [schema.text(text)])),
		0,
		0
	);

	const result = (
		plugin.props as { transformPasted: (s: Slice) => Slice }
	).transformPasted(slice);

	return toHTML(schema, schema.node("doc", null, result.content));
}

// Marks from .js files don't satisfy MarkSpec until migrated to TypeScript
export function createSchemaWithMarks(marks: Record<string, object>): Schema {
	return new Schema({
		nodes: {
			doc: { content: "block+" },
			paragraph: {
				content: "inline*",
				group: "block",
				parseDOM: [{ tag: "p" }],
				toDOM: () => ["p", 0] as const
			},
			text: { group: "inline" }
		},
		marks: marks as Record<string, MarkSpec>
	});
}

function firstInlineNode(schema: Schema, html: string): Node {
	const node = parseHTML(schema, html).firstChild?.firstChild;

	if (!node) {
		throw new Error(`No inline node found in parsed HTML: "${html}"`);
	}

	return node;
}

export function getMarkAttrs(
	schema: Schema,
	html: string,
	mark: MarkType
): Record<string, unknown> | undefined {
	return firstInlineNode(schema, html).marks.find((m) => m.type === mark)
		?.attrs;
}

export function hasMark(schema: Schema, html: string, mark: MarkType): boolean {
	return firstInlineNode(schema, html).marks.some((m) => m.type === mark);
}

export function mockEditor(overrides = {}): Editor {
	return {
		removeMark: vi.fn(),
		toggleMark: vi.fn(),
		updateMark: vi.fn(),
		...overrides
	} as unknown as Editor;
}

export function parseHTML(schema: Schema, html: string): Node {
	const div = document.createElement("div");
	div.innerHTML = `<p>${html}</p>`;
	return DOMParser.fromSchema(schema).parse(div);
}

export function toHTML(schema: Schema, doc: Node): string {
	const div = document.createElement("div");
	div.appendChild(
		DOMSerializer.fromSchema(schema).serializeFragment(doc.content)
	);
	return div.innerHTML;
}
