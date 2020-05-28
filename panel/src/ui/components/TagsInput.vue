<template>
  <div class="k-tags-input">
    <k-tags
      ref="tags"
      v-model="tags"
      :layout="layout"
      :max="max"
      :removable="!disabled"
      @input="onInput"
      @edit="onEdit"
      @blur="$refs.input.focus()"
      @navigate-next="$refs.input.focus()"
    >
      <template v-slot:footer>
        <span class="k-tags-input-element">
          <k-autocomplete
            ref="autocomplete"
            v-bind="autocomplete"
            @select="onSelect"
            @leave="$refs.input.focus()"
          >
            <input
              :id="id"
              ref="input"
              v-model.trim="typed"
              :autofocus="autofocus"
              :disabled="disabled || (max && tags.length >= max)"
              :name="name"
              autocomplete="off"
              type="text"
              class="text-sm p-1 bg-none"
              @input="onType($event.target.value)"
              @blur="onBlur"
              @keydown.meta.s="onBlur"
              @keydown.enter.exact="onConfirm"
              @keydown.tab.exact="onConfirm"
              @keydown.left.exact="onLeave"
              @keydown.backspace.exact="onLeave"
            >
          </k-autocomplete>
        </span>
      </template>
    </k-tags>
  </div>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    autofocus: Boolean,
    accept: {
      type: String,
      default: "all"
    },
    disabled: Boolean,
    icon: {
      type: [String, Boolean],
      default: "tag"
    },
    id: [Number, String],
    layout: String,
    max: Number,
    min: Number,
    name: [Number, String],
    options: {
      type: Array,
      default() {
        return [];
      }
    },
    required: Boolean,
    separator: {
      type: String,
      default: ","
    },
    value: {
      type: Array,
      default() {
        return [];
      }
    }
  },
  data() {
    return {
      tags: this.sanitize(this.value),
      typed: null
    };
  },
  computed: {
    autocomplete() {
      return {
        options: this.sanitize(this.options),
        skip: this.tags.map(tag => tag.value)
      };
    }
  },
  watch: {
    value(value) {
      this.tags = this.sanitize(value);
    }
  },
  mounted() {
    if (this.$props.autofocus) {
      this.focus();
    }
  },
  methods: {
    focus() {
      this.$refs.input.focus();
    },
    onAdd(tag) {
      if (this.accept === "options") {
        const option = this.options.filter(
          option => option.value === tag.value
        )[0];

        if (!option) {
          return;
        }
      }

      this.$refs.tags.add(tag);
      this.typed = null;
    },
    onBlur(event) {
      let related = event.relatedTarget || event.explicitOriginalTarget;

      if (
        this.$refs.autocomplete.$el &&
        this.$refs.autocomplete.$el.contains(related)
      ) {
        return;
      }

      if (this.$refs.input.value.length) {
        this.onAdd(this.$refs.input.value);
        this.$refs.autocomplete.close();
      }
    },
    onConfirm(event) {
      if (this.typed && this.typed.length > 0) {
        event.preventDefault();

        this.typed.split(this.separator).forEach(tag => {
          this.onAdd({ text: tag, value: tag });
        });
      }
    },
    onEdit(tag) {
      this.typed = tag.text;
      this.$refs.input.select();
    },
    onInput() {
      const tags = this.unsanitize(this.tags);
      this.$emit("input", tags);
    },
    onLeave(e) {
      if (
        e.target.selectionStart === 0 &&
        e.target.selectionStart === e.target.selectionEnd &&
        this.tags.length !== 0
      ) {
        this.$refs.autocomplete.close();
        this.$refs.tags.focus("last");
        e.preventDefault();
      }
    },
    onSelect(tag) {
      this.addTagToIndex(tag);
      this.$refs.autocomplete.close();
      this.$refs.input.focus();
    },
    onType(value) {
      this.typed = value;
      this.$refs.autocomplete.search(value);
    },
    sanitize(value) {
      if (Array.isArray(value) === false) {
        return [];
      }

      let tags = this.$helper.input.options(value);

      return this.$helper.clone(tags).map(tag => {
        if (this.icon && this.icon.length > 0) {
          tag.icon = this.icon;
        }

        return tag;
      });
    },
    select() {
      this.focus();
    },
    unsanitize(value) {
      return this.$helper.clone(value).map(tag => {
        delete tag.icon;
        return tag;
      });
    }
  }
};
</script>

<style lang="scss">
.k-tags-input-element {
  flex-grow: 1;
  flex-basis: 0;
  min-width: 20%;

  .k-tags-input:focus-within & {
    flex-basis: 4rem;
  }
}
.k-tags-input-element input {
  font: inherit;
  line-height: 1;
  border: 0;
  width: 100%;

  &:focus {
    outline: 0;
  }
}
.k-tags-input .k-dropdown-content {
  top: calc(100% + .5rem + 2px);
}

/** Theming **/
.k-input[data-theme="field"][data-type="tags"] {
  .k-tag,
  .k-tags-input input {
    height: 1.75rem;
  }
}
</style>
