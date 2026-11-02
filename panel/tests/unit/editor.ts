import { inputRules, type InputRule } from "prosemirror-inputrules";
import {
	DOMParser,
	DOMSerializer,
	Fragment,
	Schema,
	Slice,
	type Mark,
	type MarkSpec,
	type MarkType,
	type Node,
	type NodeSpec,
	type NodeType
} from "prosemirror-model";
import {
	EditorState,
	TextSelection,
	type Command,
	type Plugin,
	type Transaction
} from "prosemirror-state";
import { vi } from "vitest";
import type Editor from "@/components/Forms/Writer/Editor";
import type Extension from "@/components/Forms/Writer/Extension";

const BASE_NODES = {
	doc: { content: "block+" },
	paragraph: {
		content: "inline*",
		group: "block",
		parseDOM: [{ tag: "p" }],
		toDOM: () => ["p", 0] as const
	},
	text: { group: "inline" }
} satisfies Record<string, NodeSpec>;

/**
 * Runs the command and returns the state it dispatched,
 * or null if it did not dispatch
 */
export function applyCommand(
	state: EditorState,
	command: Command
): EditorState | null {
	let result: EditorState | null = null;

	command(state, (tr) => {
		result = state.apply(tr);
	});

	return result;
}

/**
 * Simulates typing `text` into a paragraph and returns the content of the
 * resulting block as HTML, or null if the rule's regex did not match.
 *
 * The last character of `text` is treated as the trigger that fired the
 * rule. It is not present in the document when the handler runs, matching how
 * ProseMirror calls input rule handlers.
 *
 * `suffix` is text that already follows the cursor when the rule fires, e.g.
 * the heading content in `applyInputRule(schema, rule, "# ", "Hello")`.
 */
export function applyInputRule(
	schema: Schema,
	rule: InputRule,
	text: string,
	suffix: string = ""
): string | null {
	const trigger = text.slice(-1);
	const docText = text.slice(0, -1);

	// The text before the trigger, plus any `suffix` that should already
	// follow the cursor when the rule fires (e.g. the heading text after
	// typing "# ").
	const combined = docText + suffix;
	const doc = schema.node("doc", null, [
		schema.node("paragraph", null, combined ? [schema.text(combined)] : [])
	]);

	const initial = EditorState.create({ doc });
	let result = initial;

	// Drive the rule through the `inputRules` plugin's
	// `handleTextInput` prop, exactly the path ProseMirror
	// takes when the trigger character is typed at the end
	// of the text before the cursor
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

/**
 * Clicks an `<a>` element via the `handleClick` prop
 * of the extension's first plugin and returns the event
 */
export function click(
	extension: Extension,
	event: Partial<MouseEvent> = {}
): MouseEvent {
	const [plugin] = extension.plugins();
	const handleClick = plugin.props?.handleClick as unknown as (
		view: unknown,
		pos: number,
		event: MouseEvent
	) => void;

	const clicked = {
		altKey: false,
		stopPropagation: vi.fn(),
		target: document.createElement("a"),
		...event
	} as unknown as MouseEvent;

	handleClick(null, 0, clicked);

	return clicked;
}

export function createSchemaWithMarks(marks: Record<string, MarkSpec>): Schema {
	return new Schema({ nodes: BASE_NODES, marks });
}

export function createSchemaWithNodes(nodes: Record<string, NodeSpec>): Schema {
	return new Schema({ nodes: { ...BASE_NODES, ...nodes } });
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

export function getNodeAttrs(
	schema: Schema,
	html: string,
	type: NodeType
): Record<string, unknown> | undefined {
	const div = document.createElement("div");
	div.innerHTML = html;
	const doc = DOMParser.fromSchema(schema).parse(div);
	const node = doc.firstChild;
	return node?.type === type ? node.attrs : undefined;
}

export function hasNode(schema: Schema, html: string, type: NodeType): boolean {
	const div = document.createElement("div");
	div.innerHTML = html;
	const doc = DOMParser.fromSchema(schema).parse(div);
	return doc.firstChild?.type === type;
}

export function hasMark(schema: Schema, html: string, mark: MarkType): boolean {
	return firstInlineNode(schema, html).marks.some((m) => m.type === mark);
}

export function mockEditor(overrides = {}): Editor {
	return {
		activeMarks: [],
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

/**
 * Renders a paragraph with `text` carrying the mark
 */
export function render(schema: Schema, mark: Mark, text: string): string {
	const doc = schema.node("doc", null, [
		schema.node("paragraph", null, [schema.text(text, [mark])])
	]);

	return toHTML(schema, doc);
}

/**
 * Creates a state with a text selection from `from` to `to`,
 * or a cursor at `from` if `to` is omitted
 */
export function select(
	doc: Node,
	from: number,
	to: number = from
): EditorState {
	return EditorState.create({
		doc,
		selection: TextSelection.create(doc, from, to)
	});
}

export function toHTML(schema: Schema, doc: Node): string {
	const div = document.createElement("div");
	div.appendChild(
		DOMSerializer.fromSchema(schema).serializeFragment(doc.content)
	);
	return div.innerHTML;
}
