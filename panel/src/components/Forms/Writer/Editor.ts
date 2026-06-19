import { baseKeymap } from "prosemirror-commands";
import {
	type InputRule,
	inputRules,
	undoInputRule
} from "prosemirror-inputrules";
import { keymap } from "prosemirror-keymap";
import { DOMParser, DOMSerializer, Schema } from "prosemirror-model";
import type {
	Fragment,
	MarkSpec,
	Node as ProsemirrorNode,
	NodeSpec,
	ParseOptions,
	Slice
} from "prosemirror-model";
import {
	EditorState,
	Plugin,
	Selection,
	TextSelection
} from "prosemirror-state";
import type { Transaction } from "prosemirror-state";
import {
	EditorView,
	type MarkViewConstructor,
	type NodeViewConstructor
} from "prosemirror-view";
import { reactive, toRaw } from "vue";

import Emitter from "./Emitter";
import type { Button, ExtensionCommand } from "./Extension";
import type Extension from "./Extension";
import Extensions from "./Extensions";
import { Doc, Paragraph, Text } from "./Nodes";
import utils from "./Utils";

export type EditorOptions = {
	autofocus: boolean | "start" | "end";
	content: string | Record<string, unknown>;
	disableInputRules: boolean | string[];
	disablePasteRules: boolean | string[];
	editable: boolean;
	element: HTMLElement | null;
	extensions: Extension[];
	emptyDocument: { type: string; content: unknown[] };
	events: Record<string, (...args: unknown[]) => unknown>;
	inline: boolean;
	parseOptions: ParseOptions;
	topNode: string;
	useBuiltInExtensions: boolean;
};

type EditorActiveState = {
	marks: string[];
	nodes: string[];
	markAttrs: Record<string, Record<string, unknown>>;
	nodeAttrs: Record<string, Record<string, unknown>>;
};

type EditorEvents = {
	blur: { event: FocusEvent; state: EditorState; view: EditorView };
	deselect: EditorSelectPayload;
	drop: { event: DragEvent; moved: boolean; slice: Slice; view: EditorView };
	focus: { event: FocusEvent; state: EditorState; view: EditorView };
	init: { state: EditorState | undefined; view: EditorView | null };
	select: EditorSelectPayload;
	transaction: EditorTransactionPayload;
	update: EditorTransactionPayload;
};

type EditorTransactionPayload = {
	editor: Editor;
	getHTML: (fragment?: Fragment) => string;
	getJSON: () => ReturnType<ProsemirrorNode["toJSON"]>;
	state: EditorState;
	transaction: Transaction;
};

type EditorSelectPayload = EditorTransactionPayload & {
	from: number;
	hasChanged: boolean;
	to: number;
};

/**
 * ProseMirror-based rich text editor used by the Writer field.
 * Manages the schema, extensions, state, and view lifecycle,
 * and emits typed events for selection, focus, content changes, and more.
 */
export default class Editor extends Emitter<EditorEvents> {
	active!: EditorActiveState;
	commands!: Record<string, ExtensionCommand>;
	element!: HTMLElement | null;
	events!: Record<string, (...args: unknown[]) => unknown>;
	extensions!: Extensions;
	focused!: boolean;
	inputRules!: InputRule[];
	keymaps!: Plugin[];
	marks!: Record<string, MarkSpec>;
	nodes!: Record<string, NodeSpec>;
	options!: EditorOptions;
	pasteRules!: Plugin[];
	plugins!: Plugin[];
	schema!: Schema;
	view: EditorView | null = null;

	private readonly defaults: EditorOptions = {
		autofocus: false,
		content: "",
		disableInputRules: false,
		disablePasteRules: false,
		editable: true,
		element: null,
		extensions: [],
		emptyDocument: {
			type: "doc",
			content: []
		},
		events: {},
		inline: false,
		parseOptions: {},
		topNode: "doc",
		useBuiltInExtensions: true
	};

	constructor(options: Partial<EditorOptions> = {}) {
		super();

		this.options = {
			...this.defaults,
			...options
		};

		this.element = this.options.element;
		this.focused = false;

		// Initialize reactive state for active marks and nodes
		this.active = reactive<EditorActiveState>({
			marks: [],
			nodes: [],
			markAttrs: {},
			nodeAttrs: {}
		});

		this.events = this.createEvents();
		this.extensions = this.createExtensions();
		this.nodes = this.createNodes();
		this.marks = this.createMarks();
		this.schema = this.createSchema();
		this.keymaps = this.createKeymaps();
		this.inputRules = this.createInputRules();
		this.pasteRules = this.createPasteRules();
		this.plugins = this.createPlugins();
		this.view = this.createView();
		this.commands = this.createCommands();

		this.setActiveNodesAndMarks();

		if (this.options.autofocus !== false) {
			this.focus(this.options.autofocus);
		}

		this.emit("init", {
			view: this.view,
			state: this.state
		});

		this.setContent(this.options.content);
	}

	/**
	 * Names of all marks active at the current cursor position
	 */
	get activeMarks(): string[] {
		return this.active.marks;
	}

	/**
	 * Names of all nodes active at the current cursor position
	 */
	get activeNodes(): string[] {
		return this.active.nodes;
	}

	/**
	 * Attribute map for all marks active at the current cursor position,
	 * keyed by mark name
	 */
	get activeMarkAttrs(): Record<string, Record<string, unknown>> {
		return this.active.markAttrs;
	}

	/**
	 * Attribute map for all nodes active at the current cursor position,
	 * keyed by node name
	 */
	get activeNodeAttrs(): Record<string, Record<string, unknown>> {
		return this.active.nodeAttrs;
	}

	/**
	 * Removes browser focus from the editor
	 */
	blur(): void {
		this.view!.dom.blur();
	}

	get builtInExtensions(): Extension[] {
		if (this.options.useBuiltInExtensions !== true) {
			return [];
		}

		return [
			new Doc({ inline: this.options.inline }),
			new Text(),
			new Paragraph()
		];
	}

	/**
	 * Returns all button configs for either marks or nodes
	 */
	buttons(type: "mark" | "node"): Record<string, Button> {
		return this.extensions.buttons(type);
	}

	/**
	 * Remove all editor content
	 */
	clearContent(emitUpdate = false): void {
		this.setContent(this.options.emptyDocument, emitUpdate);
	}

	command(command: string, ...args: unknown[]): void {
		this.commands[command]?.(...args);
	}

	createCommands(): Record<string, ExtensionCommand> {
		return this.extensions.commands({
			schema: this.schema,
			view: this.view!
		});
	}

	createDocument(
		content: string | Record<string, unknown> | null,
		parseOptions?: ParseOptions
	): ProsemirrorNode;
	createDocument(
		content: unknown,
		parseOptions?: ParseOptions
	): ProsemirrorNode | false;
	createDocument(
		content: unknown,
		parseOptions: ParseOptions = this.options.parseOptions
	): ProsemirrorNode | false {
		if (content === null) {
			return this.schema.nodeFromJSON(this.options.emptyDocument);
		}

		if (typeof content === "object") {
			try {
				return this.schema.nodeFromJSON(content as Record<string, unknown>);
			} catch (error) {
				window.console.warn(
					"Invalid content.",
					"Passed value:",
					content,
					"Error:",
					error
				);
				return this.schema.nodeFromJSON(this.options.emptyDocument);
			}
		}

		if (typeof content === "string") {
			const html = `<div>${content}</div>`;
			const parser = new window.DOMParser();
			const element = parser.parseFromString(html, "text/html").body
				.firstElementChild!;

			return DOMParser.fromSchema(this.schema).parse(element, parseOptions);
		}

		return false;
	}

	createEvents(): Record<string, (...args: unknown[]) => unknown> {
		const events = this.options.events;

		for (const [name, callback] of Object.entries(events)) {
			this.on(name, callback);
		}

		return events;
	}

	createExtensions(): Extensions {
		return new Extensions(
			[...this.builtInExtensions, ...this.options.extensions],
			this
		);
	}

	createFocusEvents(): Plugin {
		const toggleFocus = (
			view: EditorView,
			event: FocusEvent,
			focus = true
		): void => {
			this.focused = focus;
			this.emit(focus ? "focus" : "blur", {
				event,
				state: view.state,
				view
			});

			const transaction = this.state!.tr.setMeta("focused", focus);
			this.view!.dispatch(transaction);
		};

		return new Plugin({
			props: {
				attributes: {
					tabindex: "0"
				},
				handleDOMEvents: {
					focus: (view, event) => toggleFocus(view, event, true),
					blur: (view, event) => toggleFocus(view, event, false)
				}
			}
		});
	}

	createInputRules(): InputRule[] {
		return this.extensions.inputRules({
			schema: this.schema,
			excludedExtensions: this.options.disableInputRules
		});
	}

	createKeymaps(): Plugin[] {
		return this.extensions.keymaps({ schema: this.schema });
	}

	createMarks(): Record<string, MarkSpec> {
		return this.extensions.marks;
	}

	createMarkViews(): Record<string, MarkViewConstructor> {
		return this.extensions.markViews;
	}

	createNodes(): Record<string, NodeSpec> {
		return this.extensions.nodes;
	}

	createNodeViews(): Record<string, NodeViewConstructor> {
		return this.extensions.nodeViews;
	}

	createPasteRules(): Plugin[] {
		return this.extensions.pasteRules({
			schema: this.schema,
			excludedExtensions: this.options.disablePasteRules
		});
	}

	createPlugins(): Plugin[] {
		return this.extensions.plugins({ schema: this.schema });
	}

	createSchema(): Schema {
		return new Schema({
			topNode: this.options.topNode,
			nodes: this.nodes,
			marks: this.marks
		});
	}

	createState(): EditorState {
		return EditorState.create({
			schema: this.schema,
			doc: this.createDocument(this.options.content),
			plugins: [
				...this.plugins,
				inputRules({ rules: this.inputRules }),
				...this.pasteRules,
				...this.keymaps,
				keymap({ Backspace: undoInputRule }),
				keymap(baseKeymap),
				this.createFocusEvents()
			]
		});
	}

	createView(): EditorView {
		return new EditorView(this.element, {
			dispatchTransaction: this.dispatchTransaction.bind(this),
			attributes: {
				class: "k-text"
			},
			editable: () => this.options.editable,
			handlePaste: (_view, event) => {
				if (typeof this.events["paste"] === "function") {
					const html = event.clipboardData!.getData("text/html");
					const text = event.clipboardData!.getData("text/plain");
					return this.events["paste"](event, html, text) === true;
				}

				return false;
			},
			handleDrop: (
				view: EditorView,
				event: DragEvent,
				slice: Slice,
				moved: boolean
			) => {
				this.emit("drop", view, event, slice, moved);
			},
			markViews: this.createMarkViews(),
			nodeViews: this.createNodeViews(),
			state: this.createState()
		});
	}

	destroy(): void {
		if (this.view) {
			this.view.destroy();
			this.view = null;
		}
	}

	dispatchTransaction(transaction: Transaction): void {
		// keep the old state for comparison
		const lastState = this.state!;

		// create a new state with the transaction
		const newState = this.state!.apply(transaction);

		// apply the new state to the view
		this.view!.updateState(newState);

		// store active nodes and marks for the toolbar
		this.setActiveNodesAndMarks();

		// prepare event information for all following events
		const payload: EditorTransactionPayload = {
			editor: this,
			getHTML: this.getHTML.bind(this),
			getJSON: this.getJSON.bind(this),
			state: this.state!,
			transaction
		};

		// emit details about the transaction
		this.emit("transaction", payload);

		// Only emit an update if the doc has changed and
		// an update has not been actively prevented
		if (transaction.docChanged && !transaction.getMeta("preventUpdate")) {
			this.emit("update", payload);
		}

		// Only emit a select event if the selection changed
		const { from, to } = this.state!.selection;
		const hasChanged = !lastState.selection.eq(newState.selection);

		const selectPayload: EditorSelectPayload = {
			...payload,
			from,
			hasChanged,
			to
		};

		this.emit(newState.selection.empty ? "deselect" : "select", selectPayload);
	}

	focus(position: "start" | "end" | number | boolean | null = null): void {
		if ((this.view!.hasFocus() && position === null) || position === false) {
			return;
		}

		const { from, to } = this.selectionAtPosition(position);

		this.setSelection(from, to);

		// DOM focus must be deferred so it fires after ProseMirror's own
		// selection handling, otherwise the cursor position is lost.
		setTimeout(() => this.view?.focus(), 10);
	}

	getHTML(fragment: Fragment = this.state!.doc.content): string {
		const div = document.createElement("div");
		const serializer = DOMSerializer.fromSchema(this.schema);
		const html = serializer.serializeFragment(fragment);

		div.appendChild(html);

		if (this.options.inline && div.querySelector("p")) {
			return div.querySelector("p")!.innerHTML;
		}

		return div.innerHTML;
	}

	getHTMLStartToSelection(): string {
		const fragment = this.state!.doc.slice(0, this.selection.head).content;
		return this.getHTML(fragment);
	}

	getHTMLSelectionToEnd(): string {
		const fragment = this.state!.doc.slice(this.selection.head).content;
		return this.getHTML(fragment);
	}

	getHTMLStartToSelectionToEnd(): [string, string] {
		return [this.getHTMLStartToSelection(), this.getHTMLSelectionToEnd()];
	}

	getJSON() {
		return this.state!.doc.toJSON();
	}

	getMarkAttrs<T extends object = Record<string, unknown>>(
		type: string | null = null
	): T | undefined {
		return type !== null ? (this.activeMarkAttrs[type] as T) : undefined;
	}

	insertText(text: string, selected = false): void {
		const { tr } = this.state!;

		const transaction = tr.insertText(text);
		this.view!.dispatch(transaction);

		if (selected) {
			const to = tr.selection.from;
			const from = to - text.length;
			this.setSelection(from, to);
		}
	}

	isEditable(): boolean {
		return this.options.editable;
	}

	isEmpty(): boolean {
		return (this.state?.doc.textContent.length ?? 0) === 0;
	}

	removeMark(mark: string): boolean | undefined {
		if (this.schema.marks[mark]) {
			return utils.removeMark(this.schema.marks[mark])(
				this.state!,
				this.view!.dispatch
			);
		}
	}

	get selection(): Selection {
		return this.state!.selection;
	}

	get selectionAtEnd(): Selection {
		return TextSelection.atEnd(this.state!.doc);
	}

	get selectionIsAtEnd(): boolean {
		return this.selection.head === this.selectionAtEnd.head;
	}

	get selectionAtStart(): Selection {
		return TextSelection.atStart(this.state!.doc);
	}

	get selectionIsAtStart(): boolean {
		return this.selection.head === this.selectionAtStart.head;
	}

	selectionAtPosition(
		position: "start" | "end" | number | true | null = null
	): Selection | { from: number; to: number } {
		if (position === null) {
			return this.selection;
		}

		if (position === "start" || position === true) {
			return this.selectionAtStart;
		}

		if (position === "end") {
			return this.selectionAtEnd;
		}

		return {
			from: position,
			to: position
		};
	}

	setActiveNodesAndMarks(): void {
		this.active.marks = Object.values(this.schema.marks)
			.filter((mark) => utils.markIsActive(this.state!, mark))
			.map((mark) => mark.name);

		this.active.markAttrs = Object.entries(this.schema.marks).reduce(
			(marks, [name, mark]) => ({
				...marks,
				[name]: utils.getMarkAttrs(this.state!, mark)
			}),
			{}
		);

		this.active.nodes = Object.values(this.schema.nodes)
			.filter((node) => utils.nodeIsActive(this.state!, node))
			.map((node) => node.name);

		this.active.nodeAttrs = Object.entries(this.schema.nodes).reduce(
			(nodes, [name, node]) => ({
				...nodes,
				[name]: utils.getNodeAttrs(this.state!, node)
			}),
			{}
		);
	}

	setContent(
		content: string | Record<string, unknown> | null = {},
		emitUpdate = false,
		parseOptions?: ParseOptions
	): void {
		const { doc, tr } = toRaw(this.state!);
		const node = this.createDocument(content, parseOptions);
		const transaction = tr
			.replaceWith(0, doc.content.size, node)
			.setMeta("preventUpdate", !emitUpdate);

		this.view!.dispatch(transaction);
	}

	setSelection(from = 0, to = 0): void {
		const { doc, tr } = toRaw(this.state!);
		const resolvedFrom = utils.minMax(from, 0, doc.content.size);
		const resolvedEnd = utils.minMax(to, 0, doc.content.size);
		const selection = TextSelection.create(doc, resolvedFrom, resolvedEnd);
		const transaction = tr.setSelection(selection);

		this.view!.dispatch(transaction);
	}

	get state(): EditorState | undefined {
		return this.view?.state;
	}

	toggleMark(mark: string): boolean | undefined {
		if (this.schema.marks[mark]) {
			return utils.toggleMark(this.schema.marks[mark])(
				this.state!,
				this.view!.dispatch
			);
		}
	}

	updateMark(
		mark: string,
		attrs: Record<string, unknown>
	): boolean | undefined {
		if (this.schema.marks[mark]) {
			return utils.updateMark(this.schema.marks[mark], attrs)(
				this.state!,
				this.view!.dispatch
			);
		}
	}
}
