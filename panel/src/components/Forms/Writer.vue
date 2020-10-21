<template>
  <div
    ref="editor"
    :spellcheck="spellcheck"
    :class="{ 'k-writer': true, 'k-writer-text': !code, 'k-writer-code': code }"
    @focusin="onFocus"
    @focusout="onBlur"
  >
    <k-writer-toolbar
      v-if="editor"
      v-show="toolbar"
      ref="toolbar"
      :marks="toolbar.marks"
      :options="editor.state.schema.marks"
      :style="{bottom: toolbar.bottom + 'px', left: toolbar.left + 'px'}"
      @option="onOption"
    />
    <span v-if="placeholder && editor && isEmpty()" class="k-writer-placeholder">{{ placeholder }}</span>
    <k-writer-link-dialog
      ref="link"
      @close="focus()"
      @submit="insertLink"
    />
  </div>
</template>

<script>
/** ProseMirror */
import { TextSelection, AllSelection } from "prosemirror-state";
import { DOMSerializer, Slice, Fragment } from "prosemirror-model";
import { undo, redo } from "prosemirror-history";
import Doc from "./Writer/Editor/Document.js";

/** Editor wrapper */
import Editor from "./Writer/Editor/View.js";

/** Commands */
import { toggleMark } from "prosemirror-commands";

/** Utils */
import {
  getActiveMarks,
  getMarkAttrs,
  getHTML,
} from "./Writer/Utils.js";

/** Dialogs */
import LinkDialog from "./Writer/Dialogs/Link.vue";

/** Toolbar */
import Toolbar from "./Writer/Toolbar.vue";

export default {
  components: {
    "k-writer-link-dialog": LinkDialog,
    "k-writer-toolbar": Toolbar
  },
  props: {
    breaks: Boolean,
    code: Boolean,
    disabled: Boolean,
    marks: {
      type: Array,
      default() {
        return [
          'bold',
          'italic',
          'strikeThrough',
          'underline',
          'code',
          'link'
        ];
      }
    },
    paste: {
      type: Function,
      default() {
        return function () {};
      }
    },
    placeholder: String,
    spellcheck: Boolean,
    value: {
      type: String,
      default: ""
    },
  },
  data() {
    return {
      editor: null,
      toolbar: false
    };
  },
  mounted() {
    this.editor = Editor({
      breaks: this.breaks,
      code: this.code,
      content: this.value,
      disabled: this.disabled,
      element: this.$el,
      keymap: this.keymap(),
      marks: this.marks,
      onInput: this.onInput,
      onPaste: this.onPaste,
      onSelect: this.onSelect,
    });

    this.onInput();
  },
  destroyed() {
    this.editor.destroy();
  },
  computed: {
    info() {
      if (!this.editor) {
        return {};
      }

      return {
        top: this.coordsAtStart().top,
        bottom: this.coordsAtEnd().bottom
      };
    }
  },
  watch: {
    value() {
      this.doc().setContent(this.value);
    }
  },
  methods: {
    keymap() {
      return {
        "ArrowDown": this.onArrowDown,
        "ArrowUp": this.onArrowUp,
        "Backspace": this.onBackspace,
        "Cmd-b": () => this.toggleMark("bold"),
        "Cmd-i": () => this.toggleMark("italic"),
        "Cmd-k": () => this.link(),
        "Cmd-u": () => this.toggleMark("underline"),
        "Cmd-z": undo,
        "Cmd-Shift-s": () => this.toggleMark("strikeThrough"),
        "Cmd-Shift-z": redo,
        "Delete": this.onForwardDelete,
        "Enter": this.onEnter,
        "Shift-Enter": this.onShiftEnter,
        "Shift-Tab": this.onShiftTab,
        "Tab": this.onTab,
      };
    },
    addMark(type, attrs) {
      const { from, to } = this.selection();
      const mark = this.mark(type);

      if (mark) {
        return this.dispatch(this.tr().addMark(from, to, mark.create(attrs)))
      }
    },
    coordsAtPos(pos) {
      return this.editor.coordsAtPos(pos);
    },
    coordsAtEnd() {
      return this.editor.coordsAtPos(this.cursorPositionAtEnd());
    },
    coordsAtStart() {
      return this.editor.coordsAtPos(0);
    },
    coordsAtCursor() {
      return this.coordsAtPos(this.cursorPosition());
    },
    posAtCoords(coords) {
      return this.editor.posAtCoords(coords);
    },
    cursor() {
      let { $cursor } = this.selection();
      return $cursor;
    },
    cursorAtEnd() {
      let { $cursor } = this.selectionAtEnd();
      return $cursor;
    },
    cursorAtStart() {
      let { $cursor } = this.selectionAtStart();
      return $cursor;
    },
    cursorPosition() {
      const $cursor = this.cursor();
      return $cursor ? $cursor.pos : 0;
    },
    cursorPositionAtEnd() {
      const $cursor = this.cursorAtEnd();
      return $cursor ? $cursor.pos : 0;
    },
    cursorPositionAtStart() {
      return 0;
    },
    dispatch(tr) {
      this.editor.dispatch(tr);
    },
    doc() {
      return this.editor.state.doc;
    },
    focus(cursor) {

      if (cursor) {

        if (typeof cursor === "object") {

          const at     = cursor.at || "start";
          const coords = (at === "end") ? this.coordsAtEnd() : this.coordsAtStart();

          const pos = this.posAtCoords({
            top: coords.top + 1,
            left: cursor.left || 0
          });

          if (pos && pos.pos) {
            cursor = pos.pos;
          } else {
            cursor = at;
          }

        }

        let selection = null;

        switch (cursor) {
          case "start":
            selection = TextSelection.atStart(this.doc());
            break;
          case "end":
            selection = TextSelection.atEnd(this.doc());
            break;
          default:

            try {
              selection = TextSelection.near(this.doc().resolve(cursor));
            } catch (e) {
              selection = TextSelection.atStart(this.doc());
            }
            break;
        }

        this.dispatch(this.tr().setSelection(selection));

      }

      setTimeout(() => {
        this.editor.focus();
      }, 1);

    },
    getActiveMarks() {
      return getActiveMarks(this.editor.state.schema, this.editor.state, this.marks);
    },
    getMarkAttrs(type) {
      return getMarkAttrs(this.state(), type);
    },
    hasFocus() {
      return this.editor.hasFocus;
    },
    hasMark(type) {
      return this.editor.state.schema.marks[type] !== undefined;
    },
    htmlAtSelection(selection) {
      return this.nodeToHtml(this.nodeAtSelection(selection));
    },
    htmlBeforeCursor() {
      return this.htmlAtSelection(this.selectionBeforeCursor());
    },
    htmlAfterCursor() {
      return this.htmlAtSelection(this.selectionAfterCursor());
    },
    insertBreak() {
      if (this.breaks !== true) {
        return false;
      }

      if (this.code) {
        this.dispatch(
            this.tr()
              .insertText("\n")
              .scrollIntoView()
        );
      } else {
        this.dispatch(
          this.tr()
            .replaceSelectionWith(this.schema().nodes.hard_break.create())
            .scrollIntoView()
        );
      }

    },
    insertHtml(html) {
      const node = Doc(this.schema(), html);
      this.dispatch(this.tr().replaceSelectionWith(node).scrollIntoView());
    },
    insertLink(link) {
      if (!link.href) {
        this.removeMark("link");
      } else {
        this.addMark("link", link);
      }
      this.focus();
    },
    insertText(text) {
      this.dispatch(this.tr().insertText(text).scrollIntoView());
    },
    isEmpty() {
      return this.doc().content.size === 0;
    },
    isSelected() {
      const selection = this.selection();
      const end       = this.cursorPositionAtEnd();

      return selection.from === 0 && selection.to === end;
    },
    length() {
      return this.cursorAtEnd().pos;
    },
    link() {
      const attrs = this.getMarkAttrs("link");
      this.$refs.link.open(attrs);
    },
    mark(type) {
      return this.editor.state.schema.marks[type];
    },
    nodeAtSelection(selection) {
      return this.doc().cut(selection.from, selection.to);
    },
    nodeToHtml(node) {
      const result = DOMSerializer
        .fromSchema(this.editor.state.schema)
        .serializeFragment(node);

      let dom = document.createElement("div");
      dom.append(result);

      return dom.innerHTML;
    },
    onArrowDown(state, dispatch, view) {
      this.$emit("arrowDown");
      if (view.endOfTextblock("down", state)) {
        this.$emit("blurDown", this.coordsAtCursor());
        return true;
      } else {
        this.$emit("lineDown");
      }
    },
    onArrowUp(state, dispatch, view) {
      this.$emit("arrowUp");
      if (view.endOfTextblock("up", state)) {
        this.$emit("blurUp", this.coordsAtCursor());
        return true;
      } else {
        this.$emit("lineUp");
      }
    },
    onBackspace(state) {
      this.$emit("backspace");
      if (state.selection.$cursor && state.selection.$cursor.pos === 0) {
        if (this.isEmpty()) {
          this.$emit("backspaceWhenEmpty");
        } else {
          this.$emit("backspaceAtStart");
        }
      }
    },
    onBlur() {
      this.$emit("blur");
    },
    onConvert(type) {
      this.$emit("convert", type);
    },
    onEnter(state, dispatch, view) {
      this.$emit("enter", {
        before: this.htmlBeforeCursor(),
        after: this.htmlAfterCursor()
      });
    },
    onFocus() {
      this.$emit("focus");
    },
    onForwardDelete(state, dispatch, view) {
      this.$emit("forwardDelete");
      if (state.selection.$cursor && view.endOfTextblock("forward", state)) {
        if (this.isEmpty()) {
          this.$emit("forwardDeleteWhenEmpty");
        } else {
          this.$emit("forwardDeleteAtEnd");
        }
      }
    },
    onInput(html) {
      this.$emit("input", this.toHTML());
    },
    onOption(option) {
      if (!this[option.action]) {
        return false;
      }

      const args = option.args || [];

      this[option.action](...args);
    },
    onPaste(html, text) {
      this.$emit("paste", { html, text });
    },
    onSelect() {

      const selection = this.editor.state.selection;

      if (selection.empty) {
        this.toolbar = false;
        this.$emit("deselect");
      } else {

        const toolbar = this.$refs.toolbar;

        if (!toolbar) {
          return false;
        }

        const { from, to } = selection;

        const start = this.coordsAtPos(from);
        const end = this.coordsAtPos(to, true);

        // The box in which the tooltip is positioned, to use as base
        const box = this.$el.getBoundingClientRect();
        const el = toolbar.$el.getBoundingClientRect();

        // Find a center-ish x position from the selection endpoints (when
        // crossing lines, end may be more to the left)
        let left = ((start.left + end.left) / 2) - box.left
        let bottom = Math.round(box.bottom - start.top);

        this.toolbar = {
          left: left,
          bottom: bottom,
          marks: this.getActiveMarks()
        };

        this.$emit("select");
      }
    },
    onShiftEnter() {
      this.$emit("shiftEnter");
      this.insertBreak();
    },
    onShiftTab() {
      this.$emit("shiftTab");
      this.$emit("blurUp", this.coordsAtCursor());
    },
    onTab() {
      this.$emit("tab");
      this.$emit("blurDown", this.coordsAtCursor());
    },
    removeMark(type) {
      const { from, to } = this.selection();
      const mark = this.mark(type);

      if (mark) {
        return this.dispatch(this.tr().removeMark(from, to, mark));
      }
    },
    schema() {
      return this.editor.state.schema;
    },
    selection() {
      return this.editor.state.selection;
    },
    selectionAtEnd() {
      return TextSelection.atEnd(this.doc());
    },
    selectionAtStart() {
      return TextSelection.atStart(this.doc());
    },
    selectionBeforeCursor() {
      return new TextSelection(this.doc().resolve(0), this.selection().$from);
    },
    selectionAfterCursor() {
      return new TextSelection(this.selection().$to, this.selectionAtEnd().$to);
    },
    state() {
      return this.editor.state;
    },
    toggleMark(type, attrs) {
      const mark = this.mark(type);

      if (mark) {
        toggleMark(mark, attrs)(this.editor.state, this.editor.dispatch);
        this.$emit("toggleMark", mark, attrs);
      }

      this.editor.focus();
    },
    toHTML() {
      return getHTML(this.editor.state, this.code);
    },
    toJSON() {
      return this.doc().toJSON();
    },
    tr() {
      return this.editor.state.tr;
    },
    view() {
      return this.editor;
    }
  }
};
</script>

<style lang="scss">
.k-writer {
  position: relative;
  width: 100%;
}
.k-writer .ProseMirror {
  word-wrap: break-word;
  white-space: pre-wrap;
  -webkit-font-variant-ligatures: none;
  font-variant-ligatures: none;
  line-height: 1.5em;
}
.k-writer .ProseMirror:focus {
  outline: 0;
}
.k-writer .ProseMirror a {
  color: $color-focus;
  text-decoration: underline;
}
.k-writer-text .ProseMirror strong {
  font-weight: 600;
}
.k-writer-text .ProseMirror code {
  position: relative;
  font-size: .925em;
  display: inline-block;
  line-height: 1.325;
  padding: .05em .325em;
  background: $color-gray-300;
  border-radius: $rounded;
  font-family: $font-mono;
}
.k-writer-placeholder {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  color: $color-gray-500;
  pointer-events: none;
  font: inherit;
  line-height: 1.5em;
  -webkit-font-variant-ligatures: none;
  font-variant-ligatures: none;
}
.k-writer-code pre {
  tab-size: 2;
  font-size: $text-sm;
  line-height: 2em;
  overflow-x: auto;
  overflow-y: hidden;
  white-space: pre;
}
.k-writer-code code {
  font-family: $font-mono;
}
</style>
