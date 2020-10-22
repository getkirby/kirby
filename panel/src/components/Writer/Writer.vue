<template>
  <div
    ref="editor"
    :spellcheck="spellcheck"
    class="k-writer"
  >
    <k-writer-toolbar
      v-if="editor"
      v-show="toolbar"
      ref="toolbar"
      :buttons="editor.buttons"
      :marks="toolbar.marks"
      :sorting="[
        'bold',
        'italic',
        'strike',
        'underline',
        'code',
        'link'
      ]"
      :style="{
        bottom: toolbar.bottom + 'px',
        left: toolbar.left + 'px'
      }"
      @command="onCommand"
    />
    <k-writer-link-dialog
      ref="linkDialog"
      @close="editor.focus()"
      @submit="toggleLink"
    />
  </div>
</template>

<script>
import Editor from "./Editor";

// Dialogs
import LinkDialog from "./Dialogs/LinkDialog.vue";

// Marks
import Code from "./Marks/Code";
import Bold from "./Marks/Bold";
import Italic from "./Marks/Italic";
import Link from "./Marks/Link";
import Strike from "./Marks/Strike";
import Underline from "./Marks/Underline";

// Nodes
import HardBreak from "./Nodes/HardBreak";

// Toolbar
import Toolbar from "./Toolbar.vue";

export default {
  components: {
    "k-writer-link-dialog": LinkDialog,
    "k-writer-toolbar": Toolbar,
  },
  props: {
    breaks: Boolean,
    code: Boolean,
    disabled: Boolean,
    placeholder: String,
    spellcheck: Boolean,
    value: {
      type: String,
      default: ""
    },
  },
  mounted() {
    this.editor = new Editor({
      editable: !this.disabled,
      content: this.value,
      element: this.$el,
      events: {
        link: () => {
          this.$refs.linkDialog.open(this.editor.getMarkAttrs("link"));
        },
        update: this.onUpdate,
        transaction: this.onTransaction
      },
      extensions: [
        new Code,
        new Bold,
        new HardBreak,
        new Italic,
        new Link,
        new Strike,
        new Underline,
      ]
    });
  },
  beforeDestroy() {
    this.editor.destroy();
  },
  data() {
    return {
      editor: null,
      html: this.value,
      toolbar: false
    };
  },
  computed: {
    toolbarButtons() {
      return {
        bold: {
          icon: "bold"
        },
        italic: {
          icon: "italic"
        }
      }
    }
  },
  watch: {
    value(newValue, oldValue) {
      if (newValue !== oldValue && newValue !== this.html) {
        this.html = newValue;
        this.editor.setContent(this.html);
      }
    }
  },
  methods: {
    closeToolbar() {
      this.toolbar = false;
      this.$emit("toolbar", false);
    },
    onCommand(command) {
      this.editor.command(command);
    },
    onTransaction({ state }) {
      if (state.selection.empty) {
        this.$emit("deselect");
        this.closeToolbar();
        return;
      }

      if (this.editor.focused === false) {
        this.closeToolbar();
        return;
      }

      const { from, to } = state.selection;

      const start = this.editor.view.coordsAtPos(from);
      const end   = this.editor.view.coordsAtPos(to, true);

      // The box in which the tooltip is positioned, to use as base
      const editorRect = this.$refs.editor.getBoundingClientRect();

      // Find a center-ish x position from the selection endpoints (when
      // crossing lines, end may be more to the left)
      let left = ((start.left + end.left) / 2) - editorRect.left
      let bottom = Math.round(editorRect.bottom - start.top);

      this.openToolbar({
        bottom,
        from,
        left,
        to
      });

      this.$emit("select", this.editor.selection);
    },
    onUpdate({ getHTML }) {
      this.html = getHTML();
      this.$emit("input", this.html);
    },
    openToolbar(attrs) {
      this.toolbar = {
        bottom: 0,
        from: 0,
        left: 0,
        marks: this.editor.activeMarks,
        to: 0,
        ...attrs,
      };

      this.$emit("toolbar", this.toolbar);
    },
    toggleLink(attrs) {
      if (attrs.href && attrs.href.length > 0) {
        this.editor.command("insertLink", attrs);
      } else {
        this.editor.command("removeLink");
      }
    },
  },
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
.k-writer .ProseMirror strong {
  font-weight: 600;
}
.k-writer .ProseMirror code {
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
