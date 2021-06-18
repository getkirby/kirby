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
      required,
    }"
    :value="slug"
    :dir="direction"
    autocomplete="off"
    spellcheck="false"
    type="text"
    class="k-text-input"
    v-on="listeners"
  >
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
    }
  }
}

/**
 * @example <k-input v-model="slug" name="slug" type="slug" />
 */
export default {
  extends: TextInput,
  mixins: [props],
  data() {
    return {
      slug: this.sluggify(this.value),
      slugs: this.$languages.current ? this.$languages.current.rules : this.$system.slugs
    };
  },
  watch: {
    value() {
      this.slug = this.sluggify(this.value);
    }
  },
  methods: {
    sluggify(value) {
      return this.$helper.slug(value.trim(), [this.slugs, this.$system.ascii], this.allow);
    },
    onInput(value) {
      this.$emit("input", this.sluggify(value));
    }
  }
}
</script>
