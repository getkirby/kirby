<template>
  <k-field :input="_uid" v-bind="$props" class="k-url-field">
    <k-input
      :id="_uid"
      ref="input"
      v-bind="$props"
      theme="field"
      type="url"
      v-on="$listeners"
    >
      <template #icon>
        <k-button
          v-if="link"
          :icon="icon"
          :link="value"
          :tooltip="$t('open')"
          class="k-input-icon-button"
          tabindex="-1"
          target="_blank"
        />
      </template>
    </k-input>
  </k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as UrlInput } from "../Input/UrlInput.vue";

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-url-input>`
 * for additional information.
 * @example <k-url-field v-model="url" name="url" label="Url" />
 */
export default {
  mixins: [Field, Input, UrlInput],
  inheritAttrs: false,
  props: {
    link: {
      type: Boolean,
      default: true
    },
    icon: {
      type: String,
      default: "url"
    }
  },
  methods: {
    focus() {
      this.$refs.input.focus();
    }
  }
};
</script>
