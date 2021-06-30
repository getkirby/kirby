<template>
  <k-field
    :input="_uid"
    v-bind="{
      ...$props,
      help: preview
    }"
    class="k-slug-field"
  >
    <template v-if="wizzard && wizzard.text" #options>
      <k-button icon="wand" @click="onWizzard">
        {{ wizzard.text }}
      </k-button>
    </template>

    <k-input
      :id="_uid"
      ref="input"
      v-bind="{
        ...$props,
        value: slug
      }"
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
 * Have a look at `<k-field>`, `<k-input>` and `<k-slug-input>`
 * for additional information.
 * @example <k-slug-field v-model="slug" name="slug" label="Slug" />
 */
export default {
  mixins: [
    Field,
    Input,
    SlugInput
  ],
  inheritAttrs: false,
  props: {
    icon: {
      type: String,
      default: "url"
    },
    path: {
      type: String
    },
    wizzard: {
      type: [Boolean, Object],
      default: false,
    }
  },
  data() {
    return {
      slug: this.value,
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
    onWizzard() {
      if (this.wizzard && this.wizzard.field && this.formData[this.wizzard.field]) {
        this.slug = this.formData[this.wizzard.field];
      }
    }
  }
}
</script>
