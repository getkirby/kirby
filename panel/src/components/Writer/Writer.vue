<template>
  <div
    ref="editor"
    :data-empty="isEmpty"
    :data-placeholder="placeholder"
    :spellcheck="spellcheck"
    class="k-writer"
  >
    <template v-if="editor">
      <k-writer-toolbar
        v-if="toolbar.visible"
        ref="toolbar"
        :editor="editor"
        :active-marks="toolbar.marks"
        :active-nodes="toolbar.nodes"
        :style="{
          bottom: toolbar.position.bottom + 'px',
          left: toolbar.position.left + 'px'
        }"
        @command="editor.command($event)"
      />
      <k-writer-link-dialog
        ref="linkDialog"
        @close="editor.focus()"
        @submit="editor.command('toggleLink', $event)"
      />
    </template>
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
import BulletList from "./Nodes/BulletList";
import HardBreak from "./Nodes/HardBreak";
import Heading from "./Nodes/Heading";
import HorizontalRule from "./Nodes/HorizontalRule";
import ListItem from "./Nodes/ListItem";
import OrderedList from "./Nodes/OrderedList";

// Extensions
import History from "./Extensions/History.js";
import Toolbar from "./Extensions/Toolbar.js";

// Toolbar
import ToolbarComponent from "./Toolbar.vue";

export default {
  components: {
    "k-writer-link-dialog": LinkDialog,
    "k-writer-toolbar": ToolbarComponent,
  },
  props: {
    breaks: Boolean,
    code: Boolean,
    disabled: Boolean,
    emptyDocument: {
      type: Object,
      default() {
        return {
          type: "doc",
          content: []
        };
      }
    },
    headings: [Array, Boolean],
    inline: {
      type: Boolean,
      default: false,
    },
    marks: {
      type: [Array, Boolean],
      default: true
    },
    nodes: {
      type: [Array, Boolean],
      default() {
        return [
          "heading",
          "bulletList",
          "orderedList"
        ];
      }
    },
    placeholder: String,
    spellcheck: Boolean,
    extensions: Array,
    value: {
      type: String,
      default: ""
    },
  },
  data() {
    return {
      editor: null,
      html: this.value,
      isEmpty: true,
      toolbar: false
    };
  },
  watch: {
    value(newValue, oldValue) {
      if (newValue !== oldValue && newValue !== this.html) {
        this.html = newValue;
        this.editor.setContent(this.html);
      }
    }
  },
  mounted() {
    this.editor = new Editor({
      content: this.value,
      editable: !this.disabled,
      element: this.$el,
      emptyDocument: this.emptyDocument,
      events: {
        link: () => {
          this.$refs.linkDialog.open(this.editor.getMarkAttrs("link"));
        },
        toolbar: (toolbar) => {
          this.toolbar = toolbar;

          if (this.toolbar.visible) {
            this.$nextTick(() => {
              this.onToolbarOpen();
            });
          }
        },
        update: () => {
          this.html    = this.editor.getHTML();
          this.isEmpty = this.editor.isEmpty();

          this.$emit("input", this.isEmpty === false ? this.html : "");
        }
      },
      extensions: [
        ...this.createMarks(),
        ...this.createNodes(),

        // Extensions
        new History,
        new Toolbar,
        ...this.extensions || [],
      ],
      inline: this.inline
    });

    this.isEmpty = this.editor.isEmpty();
  },
  beforeDestroy() {
    this.editor.destroy();
  },
  methods: {
    filterExtensions(available, allowed, postFilter) {
      if (allowed === false) {
        allowed = [];
      } else if (allowed === true || Array.isArray(allowed) === false) {
        allowed = Object.keys(available);
      }

      let installed = [];

      allowed.forEach(allowed => {
        if (available[allowed]) {
          installed.push(available[allowed]);
        }
      });

      if (typeof postFilter === "function") {
        installed = postFilter(allowed, installed);
      }

      return installed;
    },
    command(command, ...args) {
      this.editor.command(command, ...args);
    },
    createMarks() {
      return this.filterExtensions({
        bold: new Bold,
        italic: new Italic,
        strike: new Strike,
        underline: new Underline,
        code: new Code,
        link: new Link,
      }, this.marks);
    },
    createNodes() {

      const hardBreak = new HardBreak({
        text: true,
        enter: this.inline
      });

      // inline fields only get the hard break
      if (this.inline === true) {
        return [hardBreak];
      }

      return this.filterExtensions({
        bulletList: new BulletList,
        orderedList: new OrderedList,
        heading: new Heading,
        horizontalRule: new HorizontalRule,
        listItem: new ListItem
      }, this.nodes, (allowed, installed) => {

        // install the list item when there's a list available
        if (allowed.includes("bulletList") || allowed.includes("orderedList")) {
          installed.push(new ListItem);
        }

        // always install the hard break
        installed.push(hardBreak);

        return installed;
      });

    },
    getHTML() {
      return this.editor.getHTML();
    },
    focus() {
      this.editor.focus();
    },
    onToolbarOpen() {
      if (this.$refs.toolbar) {
        const editorWidth = this.$el.clientWidth;
        const toolbarWidth = this.$refs.toolbar.$el.clientWidth;

        let left = this.toolbar.position.left;

        // adjust left overflow
        if (left - (toolbarWidth / 2) < 0) {
          left = left + ((toolbarWidth / 2) - left) - 20;
        }

        // adjust right overflow
        if (left + (toolbarWidth / 2) > editorWidth) {
          left = left - (left + (toolbarWidth / 2) - editorWidth) + 20;
        }

        if (left !== this.toolbar.position.left) {
          this.$refs.toolbar.$el.style.left = left + 'px';
        }
      }
    }
  }
};
</script>

<style>
.k-writer {
  position: relative;
  width: 100%;
}
.k-writer .ProseMirror {
  overflow-wrap: break-word;
  word-wrap: break-word;
  word-break: break-word;
  white-space: pre-wrap;
  -webkit-font-variant-ligatures: none;
  font-variant-ligatures: none;
  line-height: inherit;
}
.k-writer .ProseMirror:focus {
  outline: 0;
}
.k-writer .ProseMirror * {
  caret-color: currentColor;
}
.k-writer .ProseMirror a {
  color: var(--color-focus);
  text-decoration: underline;
}
.k-writer .ProseMirror > *:last-child {
  margin-bottom: 0;
}
.k-writer .ProseMirror p,
.k-writer .ProseMirror ul,
.k-writer .ProseMirror ol,
.k-writer .ProseMirror h1,
.k-writer .ProseMirror h2,
.k-writer .ProseMirror h3 {
  margin-bottom: .75rem;
}

.k-writer .ProseMirror h1 {
  font-size: var(--text-3xl);
  line-height: 1.25em;
}
.k-writer .ProseMirror h2 {
  font-size: var(--text-2xl);
  line-height: 1.25em;
}
.k-writer .ProseMirror h3 {
  font-size: var(--text-xl);
  line-height: 1.25em;
}
.k-writer .ProseMirror h1 strong,
.k-writer .ProseMirror h2 strong,
.k-writer .ProseMirror h3 strong {
  font-weight: 700;
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
  background: var(--color-gray-300);
  border-radius: var(--rounded);
  font-family: var(--font-mono);
}
.k-writer .ProseMirror ul,
.k-writer .ProseMirror ol {
  padding-left: 1rem;
}
.k-writer .ProseMirror ul > li {
  list-style: disc;
}
.k-writer .ProseMirror ul ul > li {
  list-style: circle;
}
.k-writer .ProseMirror ul ul ul > li {
  list-style: square;
}
.k-writer .ProseMirror ol > li {
  list-style: decimal;
}
.k-writer .ProseMirror li > p,
.k-writer .ProseMirror li > ol,
.k-writer .ProseMirror li > ul {
  margin: 0;
}

.k-writer-code pre {
  tab-size: 2;
  font-size: var(--text-sm);
  line-height: 2em;
  overflow-x: auto;
  overflow-y: hidden;
  white-space: pre;
}
.k-writer-code code {
  font-family: var(--font-mono);
}

.k-writer[data-placeholder][data-empty]::before {
  content: attr(data-placeholder);
  position: absolute;
  line-height: inherit;
  color: var(--color-gray-500);
  pointer-events: none;
}
</style>
