<template>
  <div
    ref="editor"
    v-direction
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
        :active-node-attrs="toolbar.nodeAttrs"
        :is-paragraph-node-hidden="isParagraphNodeHidden"
        :style="{
          bottom: toolbar.position.bottom + 'px',
          'inset-inline-start': toolbar.position.left + 'px'
        }"
        @command="editor.command($event)"
      />
      <k-writer-link-dialog
        ref="linkDialog"
        @close="editor.focus()"
        @submit="editor.command('toggleLink', $event)"
      />
      <k-writer-email-dialog
        ref="emailDialog"
        @close="editor.focus()"
        @submit="editor.command('toggleEmail', $event)"
      />
    </template>
  </div>
</template>

<script>
import Editor from "./Editor";

// Dialogs
import LinkDialog from "./Dialogs/LinkDialog.vue";
import EmailDialog from "./Dialogs/EmailDialog.vue";

// Marks
import Code from "./Marks/Code";
import Bold from "./Marks/Bold";
import Italic from "./Marks/Italic";
import Link from "./Marks/Link";
import Email from "./Marks/Email";
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
import Insert from "./Extensions/Insert.js";
import Toolbar from "./Extensions/Toolbar.js";

// Toolbar
import ToolbarComponent from "./Toolbar.vue";

export const props = {
  props: {
    autofocus: Boolean,
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
      default: false
    },
    marks: {
      type: [Array, Boolean],
      default: true
    },
    nodes: {
      type: [Array, Boolean],
      default() {
        return ["heading", "bulletList", "orderedList"];
      }
    },
    paste: {
      type: Function,
      default() {
        return () => {
          return false;
        };
      }
    },
    placeholder: String,
    spellcheck: Boolean,
    extensions: Array,
    value: {
      type: String,
      default: ""
    }
  }
};

export default {
  components: {
    "k-writer-email-dialog": EmailDialog,
    "k-writer-link-dialog": LinkDialog,
    "k-writer-toolbar": ToolbarComponent
  },
  mixins: [props],
  data() {
    return {
      editor: null,
      json: {},
      html: this.value,
      isEmpty: true,
      toolbar: false
    };
  },
  computed: {
    isParagraphNodeHidden() {
      return (
        Array.isArray(this.nodes) === true &&
        this.nodes.length !== 3 &&
        this.nodes.includes("paragraph") === false
      );
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
  mounted() {
    this.editor = new Editor({
      autofocus: this.autofocus,
      content: this.value,
      editable: !this.disabled,
      element: this.$el,
      emptyDocument: this.emptyDocument,
      events: {
        link: (editor) => {
          this.$refs.linkDialog.open(editor.getMarkAttrs("link"));
        },
        email: () => {
          this.$refs.emailDialog.open(this.editor.getMarkAttrs("email"));
        },
        paste: this.paste,
        toolbar: (toolbar) => {
          this.toolbar = toolbar;

          if (this.toolbar.visible) {
            this.$nextTick(() => {
              this.onToolbarOpen();
            });
          }
        },
        update: (payload) => {
          // compare documents to avoid minor HTML differences
          // to cause unwanted updates
          const jsonNew = JSON.stringify(this.editor.getJSON());
          const jsonOld = JSON.stringify(this.json);

          if (jsonNew === jsonOld) {
            return;
          }

          this.json = jsonNew;
          this.isEmpty = payload.editor.isEmpty();

          // create the final HTML to send to the server
          this.html = payload.editor.getHTML();

          // when a new list item or heading is created, textContent length returns 0
          // checking active nodes to prevent this issue
          // empty input means no nodes or just the paragraph node and its length 0
          if (
            this.isEmpty &&
            (payload.editor.activeNodes.length === 0 ||
              payload.editor.activeNodes.includes("paragraph"))
          ) {
            this.html = "";
          }

          this.$emit("input", this.html);
        }
      },
      extensions: [
        ...this.createMarks(),
        ...this.createNodes(),

        // Extensions
        new History(),
        new Insert(),
        new Toolbar(),
        ...(this.extensions || [])
      ],
      inline: this.inline
    });

    this.isEmpty = this.editor.isEmpty();
    this.json = this.editor.getJSON();
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

      allowed.forEach((allowed) => {
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
      return this.filterExtensions(
        {
          bold: new Bold(),
          italic: new Italic(),
          strike: new Strike(),
          underline: new Underline(),
          code: new Code(),
          link: new Link(),
          email: new Email()
        },
        this.marks
      );
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

      return this.filterExtensions(
        {
          bulletList: new BulletList(),
          orderedList: new OrderedList(),
          heading: new Heading(),
          horizontalRule: new HorizontalRule(),
          listItem: new ListItem()
        },
        this.nodes,
        (allowed, installed) => {
          // install the list item when there's a list available
          if (
            allowed.includes("bulletList") ||
            allowed.includes("orderedList")
          ) {
            installed.push(new ListItem());
          }

          // always install the hard break
          installed.push(hardBreak);

          return installed;
        }
      );
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
        if (left - toolbarWidth / 2 < 0) {
          left = left + (toolbarWidth / 2 - left) - 20;
        }

        // adjust right overflow
        if (left + toolbarWidth / 2 > editorWidth) {
          left = left - (left + toolbarWidth / 2 - editorWidth) + 20;
        }

        if (left !== this.toolbar.position.left) {
          this.$refs.toolbar.$el.style.left = left + "px";
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
  grid-template-areas: "content";
  display: grid;
}
.k-writer .ProseMirror {
  overflow-wrap: break-word;
  word-wrap: break-word;
  word-break: break-word;
  white-space: pre-wrap;
  -webkit-font-variant-ligatures: none;
  font-variant-ligatures: none;
  line-height: inherit;
  grid-area: content;
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
  margin-bottom: 0.75rem;
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
  font-size: 0.925em;
  display: inline-block;
  line-height: 1.325;
  padding: 0.05em 0.325em;
  background: var(--color-gray-300);
  border-radius: var(--rounded);
  font-family: var(--font-mono);
}
.k-writer .ProseMirror ul,
.k-writer .ProseMirror ol {
  padding-inline-start: 1rem;
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
  -webkit-overflow-scrolling: touch;
  white-space: pre;
}
.k-writer-code code {
  font-family: var(--font-mono);
}

.k-writer[data-placeholder][data-empty="true"]::before {
  grid-area: content;
  content: attr(data-placeholder);
  line-height: inherit;
  color: var(--color-gray-500);
  pointer-events: none;
  white-space: pre-wrap;
  word-wrap: break-word;
}
</style>
