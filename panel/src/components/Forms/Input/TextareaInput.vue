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
        :buttons="buttons"
        :disabled="disabled"
        :uploads="uploads"
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
        :dir="direction"
        class="k-textarea-input-native"
        @click="onClick"
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

    <k-toolbar-email-dialog ref="emailDialog" @cancel="cancel" @submit="insert($event)" />
    <k-toolbar-link-dialog ref="linkDialog" @cancel="cancel" @submit="insert($event)" />
    <k-files-dialog ref="fileDialog" @cancel="cancel" @submit="insertFile($event)" />
    <k-upload v-if="uploads" ref="fileUpload" @success="insertUpload" />
  </div>
</template>

<script>
import config from "@/config/config.js";
import { required, minLength, maxLength } from "vuelidate/lib/validators";
import direction from "@/helpers/direction.js";

/**
 * @example <k-input v-model="text" name="text" type="textarea" />
 */
export default {
  inheritAttrs: false,
  props: {
    autofocus: Boolean,
    /**
     * Enables or disables all or specific buttons in the toolbar.
     */
    buttons: {
      type: [Boolean, Array],
      default: true
    },
    disabled: Boolean,
    endpoints: Object,
    font: String,
    id: [Number, String],
    name: [Number, String],
    maxlength: Number,
    minlength: Number,
    placeholder: String,
    preselect: Boolean,
    required: Boolean,
    /**
     * Pre-selects the size before auto-sizing kicks in. 
     * This can be useful to fill gaps in field layouts.
     * @values small, medium, large, huge
     */
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
  computed: {
    direction() {
      return direction(this);
    }
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
      this.$library.autosize(this.$refs.input);
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
    insertFile(files) {
      if (files && files.length > 0) {
        this.insert(files.map(file => file.dragText).join("\n\n"));
      }
    },
    insertUpload(files, response) {
      this.insert(response.map(file => file.dragText).join("\n\n"));
      this.$events.$emit("model.update");
    },
    onClick() {
      if (this.$refs.toolbar) {
        this.$refs.toolbar.close();
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
    },
    onDrop($event) {
      // dropping files
      if (this.uploads && this.$helper.isUploadEvent($event)) {
        return this.$refs.fileUpload.drop($event.dataTransfer.files, {
          url: config.api + "/" + this.endpoints.field + "/upload",
          multiple: false
        });
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
    onInvalid() {
      this.$emit("invalid", this.$v.$invalid, this.$v);
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
        this.$refs.toolbar.shortcut($event.key, $event);
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
      this.$refs.select();
    },
    selectFile() {
      this.$refs.fileDialog.open({
        endpoint: this.endpoints.field + "/files",
        multiple: false
      });
    },
    selection() {
      const area = this.$refs.input;
      const start = area.selectionStart;
      const end = area.selectionEnd;

      return area.value.substring(start, end);
    },
    uploadFile() {
      this.$refs.fileUpload.open({
        url: config.api + "/" + this.endpoints.field + "/upload",
        multiple: false,
      });
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

<style>
.k-textarea-input-wrapper {
  position: relative;
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
  color: var(--color-gray-500);
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
  font-family: var(--font-mono);
}

.k-toolbar {
  margin-bottom: .25rem;
  color: #aaa;
}
.k-textarea-input:focus-within .k-toolbar {
  position: sticky;
  top: 0;
  right: 0;
  left: 0;
  z-index: 1;
  box-shadow: rgba(0, 0, 0, .05) 0 2px 5px;
  border-bottom: 1px solid rgba(0 ,0 ,0 , .1);
  color: #000;
}
</style>
