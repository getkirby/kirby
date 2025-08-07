import { EditorState, Plugin, TextSelection } from "prosemirror-state";
import { EditorView } from "prosemirror-view";
import { Schema, DOMParser, DOMSerializer } from "prosemirror-model";
import { keymap } from "prosemirror-keymap";
import { baseKeymap } from "prosemirror-commands";
import { inputRules, undoInputRule } from "prosemirror-inputrules";
import { toRaw } from "vue";

// Prosemirror utils
import utils from "./Utils";

import Emitter from "./Emitter";
import Extensions from "./Extensions";

// Built-in extensions
import { Doc, Paragraph, Text } from "./Nodes";

export default class Editor extends Emitter {
	constructor(options = {}) {
		super();

		this.defaults = {
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

		this.init(options);
	}

	blur() {
		this.view.dom.blur();
	}

	get builtInExtensions() {
		if (this.options.useBuiltInExtensions !== true) {
			return [];
		}

		return [
			new Doc({
				inline: this.options.inline
			}),
			new Text(),
			new Paragraph()
		];
	}

	buttons(type) {
		return this.extensions.buttons(type);
	}

	clearContent(emitUpdate = false) {
		this.setContent(this.options.emptyDocument, emitUpdate);
	}

	command(command, ...args) {
		this.commands[command]?.(...args);
	}

	createCommands() {
		return this.extensions.commands({
			schema: this.schema,
			view: this.view
		});
	}

	createDocument(content, parseOptions = this.options.parseOptions) {
		if (content === null) {
			return this.schema.nodeFromJSON(this.options.emptyDocument);
		}

		if (typeof content === "object") {
			try {
				return this.schema.nodeFromJSON(content);
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
			const htmlString = `<div>${content}</div>`;
			const parser = new window.DOMParser();
			const element = parser.parseFromString(htmlString, "text/html").body
				.firstElementChild;

			return DOMParser.fromSchema(this.schema).parse(element, parseOptions);
		}

		return false;
	}

	createEvents() {
		const events = this.options.events ?? {};

		for (const [name, callback] of Object.entries(events)) {
			this.on(name, callback);
		}

		return events;
	}

	createExtensions() {
		return new Extensions(
			[...this.builtInExtensions, ...this.options.extensions],
			this
		);
	}

	createFocusEvents() {
		const toggleFocus = (view, event, focus = true) => {
			this.focused = focus;
			this.emit(focus ? "focus" : "blur", {
				event,
				state: view.state,
				view
			});

			const transaction = this.state.tr.setMeta("focused", focus);
			this.view.dispatch(transaction);
		};

		return new Plugin({
			props: {
				attributes: {
					tabindex: 0
				},
				handleDOMEvents: {
					focus: (view, event) => toggleFocus(view, event, true),
					blur: (view, event) => toggleFocus(view, event, false)
				}
			}
		});
	}

	createInputRules() {
		return this.extensions.inputRules({
			schema: this.schema,
			excludedExtensions: this.options.disableInputRules
		});
	}

	createKeymaps() {
		return this.extensions.keymaps({
			schema: this.schema
		});
	}

	createMarks() {
		return this.extensions.marks;
	}

	createMarkViews() {
		return this.extensions.markViews;
	}

	createNodes() {
		return this.extensions.nodes;
	}

	createNodeViews() {
		return this.extensions.nodeViews;
	}

	createPasteRules() {
		return this.extensions.pasteRules({
			schema: this.schema,
			excludedExtensions: this.options.disablePasteRules
		});
	}

	createPlugins() {
		return this.extensions.plugins({
			schema: this.schema
		});
	}

	createSchema() {
		return new Schema({
			topNode: this.options.topNode,
			nodes: this.nodes,
			marks: this.marks
		});
	}

	createState() {
		return EditorState.create({
			schema: this.schema,
			doc: this.createDocument(this.options.content),
			plugins: [
				...this.plugins,
				inputRules({
					rules: this.inputRules
				}),
				...this.pasteRules,
				...this.keymaps,
				keymap({
					Backspace: undoInputRule
				}),
				keymap(baseKeymap),
				this.createFocusEvents()
			]
		});
	}

	createView() {
		return new EditorView(this.element, {
			dispatchTransaction: this.dispatchTransaction.bind(this),
			attributes: {
				class: "k-text"
			},
			editable: () => this.options.editable,
			handlePaste: (view, event) => {
				if (typeof this.events["paste"] === "function") {
					const html = event.clipboardData.getData("text/html");
					const text = event.clipboardData.getData("text/plain");

					const reply = this.events["paste"](event, html, text);

					if (reply === true) {
						return true;
					}
				}
			},
			handleDrop: (...args) => {
				this.emit("drop", ...args);
			},
			markViews: this.createMarkViews(),
			nodeViews: this.createNodeViews(),
			state: this.createState()
		});
	}

	destroy() {
		if (!this.view) {
			return;
		}

		this.view.destroy();
	}

	dispatchTransaction(transaction) {
		// keep the old state for comparison
		const lastState = this.state;

		// create a new state with the transaction
		const newState = this.state.apply(transaction);

		// apply the new state to the view
		this.view.updateState(newState);

		// store active nodes and marks for the toolbar
		this.setActiveNodesAndMarks();

		// prepare event information for all following events
		const payload = {
			editor: this,
			getHTML: this.getHTML.bind(this),
			getJSON: this.getJSON.bind(this),
			state: this.state,
			transaction
		};

		// emit details about the transaction
		this.emit("transaction", payload);

		// Only emit an update if the doc has changed and
		// an update has not been actively prevented
		if (
			transaction.docChanged &&
			!transaction.getMeta("preventUpdate") &&
			transaction.steps.length > 0
		) {
			this.emit("update", payload);
		}

		// Only emit a select event if the selection changed
		const { from, to } = this.state.selection;
		const hasChanged =
			!lastState || !lastState.selection.eq(newState.selection);

		this.emit(newState.selection.empty ? "deselect" : "select", {
			...payload,
			from,
			hasChanged,
			to
		});
	}

	focus(position = null) {
		if ((this.view.focused && position === null) || position === false) {
			return;
		}

		const { from, to } = this.selectionAtPosition(position);

		this.setSelection(from, to);

		setTimeout(() => this.view.focus(), 10);
	}

	getHTML(fragment = this.state.doc.content) {
		const div = document.createElement("div");
		const html = DOMSerializer.fromSchema(this.schema).serializeFragment(
			fragment
		);

		div.appendChild(html);

		if (this.options.inline && div.querySelector("p")) {
			return div.querySelector("p").innerHTML;
		}

		return div.innerHTML;
	}

	getHTMLStartToSelection() {
		const fragment = this.state.doc.slice(0, this.selection.head).content;
		return this.getHTML(fragment);
	}

	getHTMLSelectionToEnd() {
		const fragment = this.state.doc.slice(this.selection.head).content;
		return this.getHTML(fragment);
	}

	getHTMLStartToSelectionToEnd() {
		return [this.getHTMLStartToSelection(), this.getHTMLSelectionToEnd()];
	}

	getJSON() {
		return this.state.doc.toJSON();
	}

	getMarkAttrs(type = null) {
		return this.activeMarkAttrs[type];
	}

	getSchemaJSON() {
		return JSON.parse(
			JSON.stringify({
				nodes: this.nodes,
				marks: this.marks
			})
		);
	}

	init(options = {}) {
		this.options = {
			...this.defaults,
			...options
		};

		this.element = this.options.element;
		this.focused = false;
		// this.selection = { from: 0, to: 0 };

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

		// give extensions access to our view
		this.extensions.view = this.view;

		this.setContent(this.options.content);
	}

	insertText(text, selected = false) {
		const { tr } = this.state;

		const transaction = tr.insertText(text);
		this.view.dispatch(transaction);

		if (selected) {
			const to = tr.selection.from;
			const from = to - text.length;
			this.setSelection(from, to);
		}
	}

	get isActive() {
		return Object.entries({
			...this.activeMarks,
			...this.activeNodes
		}).reduce(
			(types, [name, value]) => ({
				...types,
				[name]: (attrs = {}) => value(attrs)
			}),
			{}
		);
	}

	isEditable() {
		return this.options.editable;
	}

	isEmpty() {
		if (this.state) {
			return this.state.doc.textContent.length === 0;
		}
	}

	removeMark(mark) {
		if (this.schema.marks[mark]) {
			return utils.removeMark(this.schema.marks[mark])(
				this.state,
				this.view.dispatch
			);
		}
	}

	get selection() {
		return this.state.selection;
	}
	get selectionAtEnd() {
		return TextSelection.atEnd(this.state.doc);
	}
	get selectionIsAtEnd() {
		return this.selection.head === this.selectionAtEnd.head;
	}
	get selectionAtStart() {
		return TextSelection.atStart(this.state.doc);
	}
	get selectionIsAtStart() {
		return this.selection.head === this.selectionAtStart.head;
	}

	selectionAtPosition(position = null) {
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

	setActiveNodesAndMarks() {
		this.activeMarks = Object.values(this.schema.marks)
			.filter((mark) => utils.markIsActive(this.state, mark))
			.map((mark) => mark.name);

		this.activeMarkAttrs = Object.entries(this.schema.marks).reduce(
			(marks, [name, mark]) => ({
				...marks,
				[name]: utils.getMarkAttrs(this.state, mark)
			}),
			{}
		);

		this.activeNodes = Object.values(this.schema.nodes)
			.filter((node) => utils.nodeIsActive(this.state, node))
			.map((node) => node.name);

		this.activeNodeAttrs = Object.entries(this.schema.nodes).reduce(
			(nodes, [name, node]) => ({
				...nodes,
				[name]: utils.getNodeAttrs(this.state, node)
			}),
			{}
		);
	}

	setContent(content = {}, emitUpdate = false, parseOptions) {
		const { doc, tr } = this.state;
		const document = this.createDocument(content, parseOptions);
		const transaction = tr
			.replaceWith(0, doc.content.size, document)
			.setMeta("preventUpdate", !emitUpdate);

		this.view.dispatch(transaction);
	}

	setSelection(from = 0, to = 0) {
		const { doc, tr } = toRaw(this.state);
		const resolvedFrom = utils.minMax(from, 0, doc.content.size);
		const resolvedEnd = utils.minMax(to, 0, doc.content.size);
		const selection = TextSelection.create(doc, resolvedFrom, resolvedEnd);
		const transaction = tr.setSelection(selection);

		this.view.dispatch(transaction);
	}

	get state() {
		return this.view?.state;
	}

	toggleMark(mark) {
		if (this.schema.marks[mark]) {
			return utils.toggleMark(this.schema.marks[mark])(
				this.state,
				this.view.dispatch
			);
		}
	}

	updateMark(mark, attrs) {
		if (this.schema.marks[mark]) {
			return utils.updateMark(this.schema.marks[mark], attrs)(
				this.state,
				this.view.dispatch
			);
		}
	}
}
