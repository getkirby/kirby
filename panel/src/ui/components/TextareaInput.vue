<template>
  <div
    :data-theme="theme"
    :data-over="over"
    class="k-textarea-input"
  >
    <div class="k-textarea-input-wrapper">
      <k-toolbar
        v-if="buttons && !disabled"
        ref="toolbar"
        :layout="buttons"
        @mousedown.native.prevent
        @command="onCommand"
      />
      <textarea
        ref="input"
        v-bind="{
          autofocus,
          disabled,
          id,
          minlength,
          name,
          placeholder,
          required,
          spellcheck,
          value
        }"
        :data-font="font"
        :data-size="size"
        class="k-textarea-input-native"
        @focus="onFocus"
        @input="onInput"
        @keydown.meta.enter="onSubmit"
        @keydown.ctrl.enter="onSubmit"
        @keydown.meta="onShortcut"
        @keydown.ctrl="onShortcut"
        @dragover="onOver"
        @dragleave="onOut"
        @drop="onDrop"
      />
    </div>

    <k-toolbar-email-dialog
      ref="emailDialog"
      @cancel="cancel"
      @submit="insertEmail"
    />

    <k-toolbar-link-dialog
      ref="linkDialog"
      @cancel="cancel"
      @submit="insertLink"
    />


  </div>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    autofocus: Boolean,
    buttons: {
      type: [Boolean, Array],
      default: true
    },
    disabled: Boolean,
    font: String,
    id: [Number, String],
    name: [Number, String],
    markup: {
      type: String,
      default: "kirbytext",
    },
    maxlength: Number,
    minlength: Number,
    placeholder: String,
    preselect: Boolean,
    required: Boolean,
    size: String,
    spellcheck: {
      type: [Boolean, String],
      default: "off"
    },
    theme: String,
    uploads: [Boolean, Object, Array],
    value: String
  },
  data() {
    return {
      over: false
    };
  },
  watch: {
    value() {
      this.$nextTick(() => {
        this.resize();
      });
    }
  },
  mounted() {
    this.$nextTick(() => {
      this.$library.autosize(this.$refs.input);
    });

    if (this.$props.autofocus) {
      this.focus();
    }

    if (this.$props.preselect) {
      this.select();
    }
  },
  methods: {
    append(append) {
      this.insert(this.selection() + " " + append);
    },
    bold() {
      this.wrap("**");
    },
    cancel() {
      this.$refs.input.focus();
    },
    code() {
      this.wrap("`");
    },
    email(email, text) {
      this.$refs.emailDialog.open(email, text || this.selection());
    },
    focus() {
      this.$refs.input.focus();
    },
    heading(level) {
      this.prepend("#".repeat(level));
    },
    insert(text) {
      const input = this.$refs.input;
      const prevalue = input.value;

      setTimeout(() => {
        input.focus();

        document.execCommand("insertText", false, text);

        // document.execCommand did not work
        if (input.value === prevalue) {
          const value =
            input.value.slice(0, input.selectionStart) +
            text +
            input.value.slice(input.selectionEnd);
          input.value = value;
          this.$emit("input", value);
        }
      });

      this.resize();
    },
    insertEmail(email, text) {
      this.insert(this.$helper[this.markup].email(email, text));
    },
    insertLink(url, text) {
      this.insert(this.$helper[this.markup].link(url, text));
    },
    italic() {
      this.wrap("*");
    },
    link(url, text) {
      this.$refs.linkDialog.open(url, text || this.selection());
    },
    list(type) {

      let html = [];
      const selection = this.selection();

      selection.split("\n").forEach((line, index) => {
        let prepend = type === "ol" ? index + 1 + "." : "-";
        html.push(prepend + " " + line);
      });

      this.insert(html.join("\n"));

    },
    onCommand(command, ...args) {
      if (typeof this[command] === "function") {
        this[command](...args);
      }
    },
    onDrop($event) {
      // dropping files
      if (this.uploads && this.$helper.isUploadEvent($event)) {
        return this.$refs.fileUpload.drop($event.dataTransfer.files, {
          url: config.api + "/" + this.endpoints.field + "/upload",
          multiple: false
        });
      }

      if (!this.$store || !this.$store.state) {
        return;
      }

      // dropping text
      const drag = this.$store.state.drag;

      if (drag && drag.type === "text") {
        this.focus();
        this.insert(drag.data);
      }
    },
    onFocus($event) {
      this.$emit("focus", $event);
    },
    onInput($event) {
      this.$emit("input", $event.target.value);
    },
    onOut() {
      this.$refs.input.blur();
      this.over = false;
    },
    onOver($event) {
      // drag & drop for files
      if (this.uploads && this.$helper.isUploadEvent($event)) {
        $event.dataTransfer.dropEffect = "copy";
        this.focus();
        this.over = true;
        return;
      }

      if (!this.$store || !this.$store.state) {
        return;
      }

      // drag & drop for text
      const drag = this.$store.state.drag;

      if (drag && drag.type === "text") {
        $event.dataTransfer.dropEffect = "copy";
        this.focus();
        this.over = true;
      }
    },
    onShortcut($event) {
      if (
        this.buttons !== false &&
        $event.key !== "Meta" &&
        $event.key !== "Control" &&
        this.$refs.toolbar
      ) {
        this.$refs.toolbar.shortcut($event.key);
      }
    },
    onSubmit($event) {
      return this.$emit("submit", $event);
    },
    prepend(prepend) {
      this.insert(prepend + " " + this.selection());
    },
    resize() {
      this.$library.autosize.update(this.$refs.input);
    },
    select() {
      this.$refs.input.select();
    },
    selection() {
      const area = this.$refs.input;
      const start = area.selectionStart;
      const end = area.selectionEnd;

      return area.value.substring(start, end);
    },
    wrap(text) {
      this.insert(text + this.selection() + text);
    }
  }
};
</script>

<style lang="scss">
.k-textarea-input-wrapper {
  position: relative;
}

/** Native textarea **/
.k-textarea-input-native {
  resize: none;
  border: 0;
  width: 100%;
  background: none;
  font: inherit;
  line-height: 1.5em;
  color: inherit;
}
.k-textarea-input-native::placeholder {
  color: $color-light-grey;
}
.k-textarea-input-native:focus {
  outline: 0;
}
.k-textarea-input-native:invalid {
  box-shadow: none;
  outline: 0;
}
.k-textarea-input-native[data-size="small"] {
  min-height: 7.5rem;
}
.k-textarea-input-native[data-size="medium"] {
  min-height: 15rem;
}
.k-textarea-input-native[data-size="large"] {
  min-height: 30rem;
}
.k-textarea-input-native[data-size="huge"] {
  min-height: 45rem;
}
.k-textarea-input-native[data-font="monospace"] {
  font-family: $font-mono;
}

/** Toolbar **/
.k-textarea-input .k-toolbar {
  margin-bottom: 0.25rem;
  border-bottom: 1px solid $color-background;
  color: #aaa;
}
.k-textarea-input:focus-within .k-toolbar {
  position: sticky;
  top: 0;
  right: 0;
  left: 0;
  z-index: 1;
  box-shadow: $shadow-sticky;
  border-bottom: 1px solid rgba(#000, 0.1);
  color: #000;
}

/** Theming **/
.k-textarea-input[data-theme="field"] {
  background: #fff;
}
.k-textarea-input[data-theme="field"] .k-textarea-input-native {
  padding: .25rem $field-input-padding;
  line-height: 1.5rem;
}
</style>
