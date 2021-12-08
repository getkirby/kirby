<template>
  <k-field :input="_uid" v-bind="$props" :help="preview" class="k-slug-field">
    <k-input
      :id="_uid"
      ref="input"
      v-bind="$props"
      :value="slug"
      theme="field"
      type="slug"
      v-on="$listeners"
    />
  </k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as SlugInput } from "../Input/SlugInput.vue";

/**
 * @example <k-slug-field v-model="slug" name="slug" label="Slug" />
 */
export default {
  mixins: [Field, Input, SlugInput],
  inheritAttrs: false,
  props: {
    icon: {
      type: String,
      default: "url"
    },
    path: {
      type: String
    },
  },
  data() {
    return {
      slug: this.value
    };
  },
  computed: {
    preview() {
      if (this.help !== undefined) {
        return this.help;
      }

      if (this.path !== undefined) {
        return this.path + this.value;
      }

      return null;
    }
  },
  watch: {
    value() {
      this.slug = this.value;
    }
  },
  methods: {
    focus() {
      this.$refs.input.focus();
    },
  }
};
</script>
