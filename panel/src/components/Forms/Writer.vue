<template>
  <div class="k-writer">
    <editor-menu-bubble
      :editor="editor"
      :keep-in-bounds="keepInBounds"
      v-slot="{ commands, getMarkAttrs, isActive, menu }"
    >
      <div
        :class="{'is-active': menuIsActive(menu, isActive)}"
        :style="`left: ${menu.left}px; bottom: ${menu.bottom}px;`"
        class="k-writer-menu"
      >
        <k-button
          :class="{'is-active': isActive.bold()}"
          class="k-writer-menu-button"
          icon="bold"
          @click="commands.bold"
        />
        <k-button
          :class="{'is-active': isActive.italic()}"
          class="k-writer-menu-button"
          icon="italic"
          @click="commands.italic"
        />
        <k-button
          :class="{'is-active': isActive.strike()}"
          class="k-writer-menu-button"
          icon="strikethrough"
          @click="commands.strike"
        />
        <k-button
          :class="{'is-active': isActive.underline()}"
          class="k-writer-menu-button"
          icon="underline"
          @click="commands.underline"
        />
        <k-button
          :class="{'is-active': isActive.code()}"
          class="k-writer-menu-button"
          icon="code"
          @click="commands.code"
        />
        <k-button
          :class="{'is-active': isActive.link()}"
          class="k-writer-menu-button"
          icon="url"
          @click="$refs.link.open(getMarkAttrs('link'))"
        />
        <k-writer-link-dialog
          ref="link"
          @submit="commands.link($event)"
          @close="editor.focus()"
        />
      </div>
    </editor-menu-bubble>
    <editor-content :editor="editor" class="k-writer-content" />

  </div>
</template>

<script>
// tiptap basics
import {
  Editor,
  EditorContent,
  EditorMenuBubble
} from 'tiptap';

// tiptap extensions
import {
  Blockquote,
  BulletList,
  CodeBlock,
  HardBreak,
  Heading,
  ListItem,
  OrderedList,
  Placeholder,
  Bold,
  Code,
  Italic,
  Strike,
  Underline,
  History,
} from 'tiptap-extensions';

// custom nodes
import Link from "./Writer/Nodes/Link.js";
import HorizontalRule from "./Writer/Nodes/HorizontalRule.js";

// dialogs
import LinkDialog from "./Writer/Dialogs/LinkDialog.vue";

export default {
  components: {
    "editor-content": EditorContent,
    "editor-menu-bubble": EditorMenuBubble,
    "k-writer-link-dialog": LinkDialog
  },
  props: {
    placeholder: String,
    value: String,
  },
  data() {
    return {
      keepInBounds: true,
      html: this.value,
      editor: new Editor({
        extensions: [
          new Blockquote(),
          new BulletList(),
          new CodeBlock(),
          new HardBreak(),
          new Heading({ levels: [1, 2, 3] }),
          new HorizontalRule,
          new ListItem(),
          new OrderedList(),
          new Placeholder({
            emptyEditorClass: 'is-editor-empty',
            emptyNodeClass: 'is-empty',
            emptyNodeText: this.placeholder,
            showOnlyWhenEditable: true,
            showOnlyCurrent: true,
          }),
          new Link({ openOnClick: false }),
          new Bold(),
          new Code(),
          new Italic(),
          new Strike(),
          new Underline(),
          new History(),
        ],
        content: this.value,
        onUpdate: ({ getHTML }) => {
          this.html = getHTML();
          this.$emit('input', this.html);
        },
      }),
    }
  },
  watch: {
    value(value) {
      if (value !== this.html) {
        this.editor.setContent(value);
      }
    }
  },
  beforeDestroy() {
    this.editor.destroy()
  },
  methods: {
    menuIsActive(menu, isActive) {
      return menu.isActive && !isActive.horizontal_rule();
    }
  }
}
</script>

<style lang="scss">
.k-writer-content {
  line-height: 1.5em;
}
.k-writer * {
  caret-color: currentColor;
}
.k-writer .ProseMirror {
  overflow-wrap: break-word;
  word-wrap: break-word;
  word-break: break-word;
  white-space: pre-wrap;
  -webkit-font-variant-ligatures: none;
  font-variant-ligatures: none;
  line-height: 1.5em;
}
.k-writer .ProseMirror > p {
  margin-bottom: .75rem;
}
.k-writer .ProseMirror:focus {
  outline: 0;
}
.k-writer .ProseMirror a {
  color: $color-blue-600  ;
  text-decoration: underline;
}
.k-writer .ProseMirror strong {
  font-weight: $font-bold;
}
.k-writer .ProseMirror pre {
  padding: 0.75rem 1rem;
  border-radius: $rounded;
  background: $color-black;
  color: $color-white;
  font-family: $font-mono;
  overflow-x: auto;
  margin-bottom: .75rem;
}
.k-writer .ProseMirror pre code {
  display: block;
  font-size: $text-lg;
}
.k-writer .ProseMirror p code {
  position: relative;
  font-size: .925em;
  display: inline-block;
  line-height: 1.325;
  padding: .05em .325em;
  background: $color-aqua-200;
  border-radius: $rounded;
  font-family: $font-mono;
}
.k-writer .ProseMirror ul,
.k-writer .ProseMirror ol {
  margin-left: 1.5rem;
  margin-bottom: .75rem;
}
.k-writer .ProseMirror ol ol,
.k-writer .ProseMirror ol ul,
.k-writer .ProseMirror ul ul,
.k-writer .ProseMirror ul ol {
  margin-bottom: 0;
}

.k-writer .ProseMirror li {
  list-style: disc;
}
.k-writer .ProseMirror ol > li {
  list-style: decimal;
}
.k-writer .ProseMirror h1 {
  font-size: $text-3xl;
  font-weight: 600;
  line-height: 1.25em;
  margin-bottom: .75rem;
}
.k-writer .ProseMirror h2 {
  font-size: $text-xl;
  font-weight: 600;
  line-height: 1.35em;
  margin-bottom: .75rem;
}
.k-writer .ProseMirror h3 {
  font-size: $text-base;
  font-weight: 600;
  line-height: 1.5em;
  margin-bottom: .75rem;
}
.k-writer .ProseMirror blockquote {
  font-size: 1.25rem;
  line-height: 1.5em;
  padding: 0 0 0 1rem;
  border-left: 3px solid #000;
  margin-bottom: .75rem;
}
.k-writer .ProseMirror hr {
  position: relative;
  height: 1.5rem;
  margin-bottom: .75rem;
  border: 0;
  color: $color-gray-300;
  cursor: pointer;
}
.k-writer .ProseMirror hr::after {
  content: "";
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  height: 1px;
  background: currentColor;
}
.k-writer .ProseMirror hr.ProseMirror-selectednode {
  color: $color-blue-200;
}
.k-writer .ProseMirror hr.ProseMirror-selectednode::after {
  outline: 1px solid $color-blue-200;
}
.k-writer .ProseMirror :last-child {
  margin-bottom: 0;
}
.k-writer p.is-editor-empty:first-child::before {
  content: attr(data-empty-text);
  float: left;
  color: $color-gray-500;
  pointer-events: none;
  height: 0;
}


/** Toolbar **/
.k-writer-menu {
  position: absolute;
  display: flex;
  opacity: 0;
  background: $color-black;
  height: 36px;
  visibility: hidden;
  transform: translateX(-50%) translateY(-.75rem);
  z-index: 1;
  box-shadow: $shadow;
  color: $color-white;
  border-radius: $rounded;
}
.k-writer-menu.is-active {
  visibility: visible;
  opacity: 1;
}
.k-button.k-writer-menu-button {
  display: flex;
  align-items: center;
  height: 36px;
  padding: 0 .5rem;
  font-size: $text-sm;
  color: currentColor;
}
.k-button.k-writer-menu-button.is-active {
  color: $color-blue-300 !important;
}
</style>
