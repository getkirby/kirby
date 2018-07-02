<template>
  <div :data-theme="theme" class="kirby-textarea-input">
    <textarea
      ref="input"
      v-bind="{
        autofocus,
        disabled,
        id,
        maxlength,
        minlength,
        name,
        placeholder,
        required,
        spellcheck,
        value
      }"
      :data-size="size"
      class="kirby-textarea-input-native"
      @focus="onFocus"
      @input="onInput"
      @keydown.meta.enter="onSubmit"
      @keydown.meta="onShortcut"
    />
    <kirby-toolbar
      v-if="!disabled && buttons !== false "
      ref="toolbar"
      :buttons="buttons"
      @cancel="focus"
      @command="onCommand"
    />
  </div>
</template>

<script>
import Toolbar from "../Toolbar.vue";
import autosize from "autosize";
import {
  required,
  minLength,
  maxLength
} from "vuelidate/lib/validators";

export default {
  inheritAttrs: false,
  components: {
    "kirby-toolbar": Toolbar
  },
  props: {
    autofocus: Boolean,
    buttons: {
      type: [Boolean, Array],
      default: true,
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
    value: String,
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
    focus() {
      this.$refs.input.focus();
    },
    info() {
      return true;
    },
    insert(text) {

      const input    = this.$refs.input;
      const prevalue = input.value;

      input.focus();

      document.execCommand("insertText", false, text);

      // document.execCommand did not work
      if (input.value === prevalue) {
        const value = input.value.slice(0, input.selectionStart) + text + input.value.slice(input.selectionEnd);
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
    onCommand(command, callback) {

      if (typeof this[command] !== "function") {
        console.warn(command + " is not a valid command");
        return;
      }

      if (typeof callback === "function") {
        this[command](callback(this.$refs.input, this.selection()));
      } else {
        this[command](callback);
      }
    },
    onFocus($event) {
      this.$emit("focus", $event);
    },
    onInput($event) {
      this.$emit("input", $event.target.value);
    },
    onInvalid() {
      this.$emit("invalid", this.$v.$invalid, this.$v);
    },
    onShortcut($event) {
      if (this.buttons !== false && $event.key !== "Meta" && this.$refs.toolbar) {
        this.$refs.toolbar.shortcut($event.key, $event);
      }
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
    },
  },
  validations() {
    return {
      value: {
        required: this.required ? required : true,
        minLength: this.minlength ? minLength(this.minlength) : true,
        maxLength: this.maxlength ? maxLength(this.maxlength) : true,
      }
    };
  }
}
</script>

<style lang="scss">
.kirby-textarea-input-native {
  resize: none;
  border: 0;
  width: 100%;
  background: none;
  font: inherit;
  line-height: 1.5em;
  color: inherit;
}
.kirby-textarea-input-native:focus {
  outline: 0;
}
.kirby-textarea-input-native:invalid {
  box-shadow: none;
  outline: 0;
}
.kirby-textarea-input-native[data-size="small"] {
  min-height: 7.5rem;
}
.kirby-textarea-input-native[data-size="medium"] {
  min-height: 15rem;
}
.kirby-textarea-input-native[data-size="large"] {
  min-height: 30rem;
}
.kirby-textarea-input-native[data-size="huge"] {
  min-height: 45rem;
}

.kirby-textarea-input .kirby-toolbar-button {
  color: $color-border;
}
.kirby-textarea-input:focus-within .kirby-toolbar-button  {
  color: $color-dark;
}

</style>
