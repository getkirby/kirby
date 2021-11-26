<template>
  <input
    ref="input"
    v-bind="{
      autofocus,
      disabled,
      id,
      minlength,
      name,
      pattern,
      placeholder,
      required
    }"
    v-direction
    :value="slug"
    autocomplete="off"
    spellcheck="false"
    type="text"
    class="k-text-input"
    v-on="listeners"
  />
</template>

<script>
import TextInput from "./TextInput.vue";
import { props as TextInputProps } from "./TextInput.vue";

export const props = {
  mixins: [TextInputProps],
  props: {
    allow: {
      type: String,
      default: ""
    },
    formData: {
      type: Object,
      default() {
        return {};
      }
    },
    sync: {
      type: String
    }
  }
};

/**
 * @example <k-input v-model="slug" name="slug" type="slug" />
 */
export default {
  extends: TextInput,
  mixins: [props],
  data() {
    return {
      slug: this.sluggify(this.value),
      slugs: this.$language ? this.$language.rules : this.$system.slugs,
      syncValue: null
    };
  },
  watch: {
    formData: {
      handler(newValue) {
        if (this.disabled) {
          return false;
        }

        if (!this.sync || newValue[this.sync] === undefined) {
          return false;
        }

        if (newValue[this.sync] == this.syncValue) {
          return false;
        }

        this.syncValue = newValue[this.sync];
        this.onInput(this.sluggify(this.syncValue));
      },
      deep: true,
      immediate: true
    },
    value(newValue) {
      newValue = this.sluggify(newValue);

      if (newValue !== this.slug) {
        this.slug = newValue;
        this.$emit("input", this.slug);
      }
    }
  },
  methods: {
    sluggify(value) {
      return this.$helper.slug(
        value,
        [this.slugs, this.$system.ascii],
        this.allow
      );
    },
    onInput(value) {
      this.slug = this.sluggify(value);
      this.$emit("input", this.slug);
    }
  }
};
</script>
