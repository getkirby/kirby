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
      <k-button
        v-if="link"
        slot="icon"
        :icon="icon"
        :link="value"
        :tooltip="$t('open')"
        class="k-input-icon-button"
        tabindex="-1"
        target="_blank"
      />
    </k-input>
  </k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as UrlInput } from "../Input/UrlInput.vue";

export default {
  mixins: [
    Field,
    Input,
    UrlInput
  ],
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
}
</script>
