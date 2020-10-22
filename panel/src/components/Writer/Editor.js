import { EditorState, Plugin, TextSelection } from "prosemirror-state";
import { EditorView } from "prosemirror-view";
import { Schema, DOMParser, DOMSerializer } from "prosemirror-model";
import { keymap } from "prosemirror-keymap";
import { baseKeymap } from "prosemirror-commands";
import { inputRules, undoInputRule } from "prosemirror-inputrules";

// Prosemirror utils
import utils from "./Utils";

import Extensions from "./Extensions";

// Built-in extensions
import { Doc, Paragraph, Text } from "./Nodes";

export default class Editor {

  constructor(options = {}) {
    this.defaults = {
      autofocus: false,
      content: "",
      disableInputRules: false,
      disablePasteRules: false,
      editable: true,
      element: null,
      extensions: [],
      emptyDocument: {
        type: 'doc',
        content: [{
          type: 'paragraph'
        }]
      },
      events: {
        blur: () => {},
        focus: () => {},
        init: () => {},
        paste: () => {},
        transaction: () => {},
        update: () => {}
      },
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
    if (!this.options.useBuiltInExtensions) {
      return []
    }

    return [
      new Doc(),
      new Text(),
      new Paragraph(),
    ];
  }

  clearContent(emitUpdate = false) {
    this.setContent(this.options.emptyDocument, emitUpdate);
  }

  command(command, ...args) {
    if (this.commands[command]) {
      this.commands[command](...args);
    }
  }

  createCommands() {
    return this.extensions.commands({
      schema: this.schema,
      view: this.view,
    });
  }

  createButtons() {
    return this.extensions.buttons;
  }

  createDocument(content, parseOptions = this.options.parseOptions) {
    if (content === null) {
      return this.schema.nodeFromJSON(this.options.emptyDocument);
    }

    if (typeof content === "object") {
      try {
        return this.schema.nodeFromJSON(content);
      } catch (error) {
        window.console.warn("Invalid content.", "Passed value:", content, "Error:", error);
        return this.schema.nodeFromJSON(this.options.emptyDocument);
      }
    }

    if (typeof content === "string") {
      const htmlString = `<div>${content}</div>`;
      const parser     = new window.DOMParser()
      const element    = parser.parseFromString(htmlString, "text/html").body.firstElementChild;

      return DOMParser.fromSchema(this.schema).parse(element, parseOptions);
    }

    return false;
  }

  createExtensions() {
    return new Extensions([
      ...this.builtInExtensions,
      ...this.options.extensions,
    ], this);
  }

  createFocusEvents() {

    const toggleFocus = (view, event, focus = true) => {
      this.focused = focus;
      this.emit((focus ? "focus" : "blur"), {
        event,
        state: view.state,
        view,
      });

      const transaction = this.state.tr.setMeta("focused", focus);
      this.view.dispatch(transaction);
    };

    return new Plugin({
      props: {
        attributes: {
          tabindex: 0,
        },
        handleDOMEvents: {
          focus: (view, event) => {
            toggleFocus(view, event, true);
          },
          blur: (view, event) => {
            toggleFocus(view, event, false);
          }
        }
      }
    });
  }

  createInputRules() {
    return this.extensions.inputRules({
      schema: this.schema,
      excludedExtensions: this.options.disableInputRules,
    });
  }

  createKeymaps() {
    return this.extensions.keymaps({
      schema: this.schema,
    });
  }

  createMarks() {
    return this.extensions.marks;
  }

  createNodes() {
    return this.extensions.nodes;
  }

  createPasteRules() {
    return this.extensions.pasteRules({
      schema: this.schema,
      excludedExtensions: this.options.disablePasteRules,
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
      marks: this.marks,
    });
  }

  createState() {
    return EditorState.create({
      schema: this.schema,
      doc: this.createDocument(this.options.content),
      plugins: [
        ...this.plugins,
        inputRules({
          rules: this.inputRules,
        }),
        ...this.pasteRules,
        ...this.keymaps,
        keymap({
          Backspace: undoInputRule,
        }),
        keymap(baseKeymap),
        this.createFocusEvents(),
      ]
    });
  }

  createView() {
    return new EditorView(this.element, {
      dispatchTransaction: this.dispatchTransaction.bind(this),
      editable: () => this.options.editable,
      handlePaste: (...args) => {
        this.emit("paste", ...args)
      },
      handleDrop: (...args) => {
        this.emit("drop", ...args)
      },
      state: this.createState(),
    });
  }

  destroy() {
    if (!this.view) {
      return
    }

    this.view.destroy();
  }

  dispatchTransaction(transaction) {
    // create a new state with the transaction
    const newState = this.state.apply(transaction);

    // apply the new state to the view
    this.view.updateState(newState);

    // store the updated selection
    this.selection = {
      from: this.state.selection.from,
      to: this.state.selection.to,
    }

    // store active nodes and marks for the toolbar
    this.setActiveNodesAndMarks();

    this.emit("transaction", {
      getHTML: this.getHTML.bind(this),
      getJSON: this.getJSON.bind(this),
      state: this.state,
      transaction,
    });

    // don't emit an update if the doc has not changed or
    // an update has been actively prevented
    if (!transaction.docChanged || transaction.getMeta("preventUpdate")) {
      return;
    }

    this.emitUpdate(transaction);
  }

  emit(event, ...args) {
    if (typeof this.events[event] === "function") {
      this.events[event].apply(this, args);
    }
  }

  emitUpdate(transaction) {
    this.emit("update", {
      getHTML: this.getHTML.bind(this),
      getJSON: this.getJSON.bind(this),
      state: this.state,
      transaction,
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

  getHTML() {
    const div = document.createElement('div');
    const fragment = DOMSerializer
      .fromSchema(this.schema)
      .serializeFragment(this.state.doc.content);

    div.appendChild(fragment);

    return div.innerHTML;
  }

  getJSON() {
    return this.state.doc.toJSON();
  }

  getMarkAttrs(type = null) {
    return this.activeMarkAttrs[type];
  }

  getSchemaJSON() {
    return JSON.parse(JSON.stringify({
      nodes: this.nodes,
      marks: this.marks,
    }));
  }

  init(options = {}) {
    this.options = {
      ...this.defaults,
      ...options,
    };

    this.events = {
      ...this.defaults.events,
      ...(options.events || {})
    };

    this.element = this.options.element;
    this.focused = false;
    this.selection = { from: 0, to: 0};

    this.extensions = this.createExtensions();
    this.buttons    = this.createButtons();
    this.nodes      = this.createNodes();
    this.marks      = this.createMarks();
    this.schema     = this.createSchema();
    this.keymaps    = this.createKeymaps();
    this.inputRules = this.createInputRules();
    this.pasteRules = this.createPasteRules();
    this.plugins    = this.createPlugins();
    this.view       = this.createView();
    this.commands   = this.createCommands();

    this.setActiveNodesAndMarks();

    if (this.options.autofocus !== false) {
      this.focus(this.options.autofocus);
    }

    this.emit("init", {
      view: this.view,
      state: this.state,
    });

    // give extensions access to our view
    this.extensions.view = this.view;
  }

  get isActive() {
    return Object
      .entries({
        ...this.activeMarks,
        ...this.activeNodes,
      })
      .reduce((types, [name, value]) => ({
        ...types,
        [name]: (attrs = {}) => value(attrs),
      }), {})
  }

  removeMark(mark) {
    if (this.schema.marks[mark]) {
      return utils.removeMark(this.schema.marks[mark])(this.state, this.view.dispatch);
    }
  }

  selectionAtPosition(position = null) {
    if (this.selection && position === null) {
      return this.selection;
    }

    if (position === "start" || position === true) {
      return {
        from: 0,
        to: 0,
      }
    }

    if (position === "end") {
      const { doc } = this.state;
      return {
        from: doc.content.size,
        to: doc.content.size,
      };
    }

    return {
      from: position,
      to: position,
    };
  }

  setActiveNodesAndMarks() {
    this.activeMarks = Object
      .values(this.schema.marks)
      .filter(mark => utils.markIsActive(this.state, mark))
      .map(mark => mark.name);

    this.activeMarkAttrs = Object
      .entries(this.schema.marks)
      .reduce((marks, [name, mark]) => ({
        ...marks,
        [name]: utils.getMarkAttrs(this.state, mark),
      }), {});

    this.activeNodes = Object
      .entries(this.schema.nodes)
      .reduce((nodes, [name, node]) => ({
        ...nodes,
        [name]: (attrs = {}) => utils.nodeIsActive(this.state, node, attrs),
      }), {});
  }

  setContent(content = {}, emitUpdate = false, parseOptions) {
    const { doc, tr } = this.state;
    const document = this.createDocument(content, parseOptions);
    const selection = TextSelection.create(doc, 0, doc.content.size);
    const transaction = tr
      .setSelection(selection)
      .replaceSelectionWith(document, false)
      .setMeta("preventUpdate", !emitUpdate);

    this.view.dispatch(transaction);
  }

  setSelection(from = 0, to = 0) {
    const { doc, tr }  = this.state;
    const resolvedFrom = utils.minMax(from, 0, doc.content.size);
    const resolvedEnd  = utils.minMax(to, 0, doc.content.size);
    const selection    = TextSelection.create(doc, resolvedFrom, resolvedEnd);
    const transaction  = tr.setSelection(selection);

    this.view.dispatch(transaction);
  }

  get state() {
    return this.view ? this.view.state : null
  }

  toggleMark(mark) {
    if (this.schema.marks[mark]) {
      return utils.toggleMark(this.schema.marks[mark])(this.state, this.view.dispatch);
    }
  }

  updateMark(mark, attrs) {
    if (this.schema.marks[mark]) {
      return utils.updateMark(this.schema.marks[mark], attrs)(this.state, this.view.dispatch);
    }
  }

}
