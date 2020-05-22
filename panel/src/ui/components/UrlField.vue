<template>
  <k-field :input="_uid" v-bind="$props">
    <k-input
      :id="_uid"
      ref="input"
      v-bind="$props"
      theme="field"
      type="url"
      v-on="$listeners"
    >
      <template v-slot:icon>
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
import Field from "./Field.vue";
import Input from "./Input.vue";
import UrlInput from "./UrlInput.vue";

export default {
  inheritAttrs: false,
  props: {
    ...Field.props,
    ...Input.props,
    ...UrlInput.props,
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
