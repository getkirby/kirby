<template>
  <div :data-theme="theme" :data-over="over" class="k-textarea-input">
    <div class="k-textarea-input-wrapper">
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
        :data-size="size"
        class="k-textarea-input-native"
        @blur="onBlur"
        @click="onClick"
        @focus="onFocus"
        @input="onInput"
        @select="onSelect"
        @keydown.meta.enter="onSubmit"
        @keydown.meta="onShortcut"
        @dragover="onOver"
        @dragleave="onOut"
        @drop="onDrop"
      />
      <k-toolbar
        v-if="!disabled && buttons !== false && toolbar"
        ref="toolbar"
        :buttons="buttons"
        :style="{
          top: caret.top + 'px',
          left: caret.left + 'px'
        }"
        @blur="toolbar = false"
        @cancel="cancel"
        @command="onCommand"
      />
    </div>

    <k-email-dialog ref="emailDialog" @cancel="cancel" @submit="insert($event)" />
    <k-link-dialog ref="linkDialog" @cancel="cancel" @submit="insert($event)" />
    <k-upload ref="upload" />

  </div>
</template>

<script>
import Toolbar from "../Toolbar.vue";
import EmailDialog from "../Toolbar/EmailDialog.vue";
import LinkDialog from "../Toolbar/LinkDialog.vue";
import autosize from "autosize";
import caret from "textarea-caret";
import { required, minLength, maxLength } from "vuelidate/lib/validators";

export default {
  components: {
    "k-toolbar": Toolbar,
    "k-email-dialog": EmailDialog,
    "k-link-dialog": LinkDialog
  },
  inheritAttrs: false,
  props: {
    autofocus: Boolean,
    buttons: {
      type: [Boolean, Array],
      default: true
    },
    disabled: Boolean,
    id: [Number, String],
    name: [Number, String],
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
    value: String
  },
  data() {
    return {
      caret: {
        top: 0,
        left: 0
      },
      over: false,
      toolbar: false
    };
  },
  watch: {
    value() {
      this.onInvalid();
      this.$nextTick(() => {
        this.resize();
      });
    }
  },
  mounted() {
    this.$nextTick(() => {
      autosize(this.$refs.input);
    });

    this.onInvalid();

    if (this.$props.autofocus) {
      this.focus();
    }

    if (this.$props.preselect) {
      this.select();
    }
  },
  methods: {
    cancel() {
      this.$refs.input.focus();
      this.toolbar = false;
    },
    dialog(dialog) {
      if (this.$refs[dialog + "Dialog"]) {
        this.$refs[dialog + "Dialog"].open(this.$refs.input, this.selection());
      } else {
        throw "Invalid toolbar dialog";
      }
    },
    focus() {
      this.$refs.input.focus();
    },
    getCaret() {
      return caret(this.$refs.input, this.$refs.input.selectionEnd);
    },
    insert(text) {
      const input = this.$refs.input;
      const prevalue = input.value;

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

      this.resize();
    },
    prepend(prepend) {
      this.insert(prepend + " " + this.selection());
    },
    resize() {
      autosize.update(this.$refs.input);
    },
    onBlur($event) {
      if (
        $event.relatedTarget &&
        this.$el.contains($event.relatedTarget) === false
      ) {
        this.toolbar = false;
      }
    },
    onClick() {
      if (this.$refs.input.selectionStart === this.$refs.input.selectionEnd) {
        this.toolbar = false;
      }
    },
    onCommand(command, callback) {
      if (typeof this[command] !== "function") {
        window.console.warn(command + " is not a valid command");
        return;
      }

      if (typeof callback === "function") {
        this[command](callback(this.$refs.input, this.selection()));
      } else {
        this[command](callback);
      }

      this.toolbar = false;
    },
    onDrop() {
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
      this.toolbar = false;
      this.$emit("input", $event.target.value);
    },
    onInvalid() {
      this.$emit("invalid", this.$v.$invalid, this.$v);
    },
    onOut() {
      this.$refs.input.blur();
      this.over = false;
    },
    onOver($event) {
      const drag = this.$store.state.drag;

      if (drag && drag.type === "text") {
        $event.dataTransfer.dropEffect = "copy";
        this.focus();
        this.over = true;
      }
    },
    onSelect() {
      if (this.disabled) {
        return false;
      }

      this.toolbar = true;

      if (this.buttons === false) {
        return;
      }

      this.$nextTick(() => {
        if (
          !this.$refs.input ||
          !this.$refs.toolbar ||
          !this.$refs.toolbar.$el
        ) {
          return;
        }

        const startCaret = caret(
          this.$refs.input,
          this.$refs.input.selectionStart
        );
        const endCaret = caret(this.$refs.input, this.$refs.input.selectionEnd);

        const toolbarWidth = this.$refs.toolbar.$el.offsetWidth;
        const textareaWidth = this.$el.offsetWidth;
        const maxLeft = textareaWidth - toolbarWidth + 16;
        let left = endCaret.left - Math.round(toolbarWidth / 2);

        if (left < 0) {
          left = -16;
        }

        if (left > maxLeft) {
          left = maxLeft;
        }

        this.caret = {
          top: startCaret.top,
          left: left
        };
      });
    },
    onShortcut($event) {
      if (
        this.buttons !== false &&
        $event.key !== "Meta" &&
        this.$refs.toolbar
      ) {
        this.$refs.toolbar.shortcut($event.key, $event);
      }
    },
    onSubmit($event) {
      return this.$emit("submit", $event);
    },
    select() {
      this.$refs.select();
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
  },
  validations() {
    return {
      value: {
        required: this.required ? required : true,
        minLength: this.minlength ? minLength(this.minlength) : true,
        maxLength: this.maxlength ? maxLength(this.maxlength) : true
      }
    };
  }
};
</script>

<style lang="scss">
.k-textarea-input-wrapper {
  position: relative;
  transform: translate3d(0, 0, 0);
}
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

.k-toolbar {
  position: absolute;
  top: 0;
  left: 0;
  transform: translateY(-150%);
  z-index: z-index("toolbar");
}
</style>
