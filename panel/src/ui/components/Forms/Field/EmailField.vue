<template>
  <kirby-field :input="_uid" v-bind="$props" class="kirby-email-field">
    <kirby-input
      ref="input"
      :id="_uid"
      v-bind="$props"
      theme="field"
      v-on="$listeners"
    >
      <kirby-button
        v-if="link"
        slot="icon"
        :icon="icon"
        :link="'mailto:' + value"
        class="kirby-input-icon-button"
        tabindex="-1"
        target="_blank"
      />
    </kirby-input>
  </kirby-field>
</template>

<script>
import Field from "../Field.vue";
import Input from "../Input.vue";
import EmailInput from "../Input/EmailInput.vue";

export default {
  inheritAttrs: false,
  props: {
    ...Field.props,
    ...Input.props,
    ...EmailInput.props,
    link: {
      type: Boolean,
      default: true
    },
    icon: {
      type: String,
      default: "email"
    }
  },
  methods: {
    focus() {
      this.$refs.input.focus();
    }
  }
}
</script>
