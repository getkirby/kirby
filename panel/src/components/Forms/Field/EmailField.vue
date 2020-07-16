<template>
  <k-field :input="_uid" v-bind="$props" class="k-email-field">
    <k-input
      ref="input"
      :id="_uid"
      v-bind="$props"
      theme="field"
      type="email"
      v-on="$listeners"
    >
      <k-button
        v-if="link"
        slot="icon"
        :icon="icon"
        :link="mailto"
        :tooltip="$t('open')"
        class="k-input-icon-button"
        tabindex="-1"
        target="_blank"
      />
    </k-input>
  </k-field>
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
  computed: {
    mailto() {
      return this.value && this.value.length > 0 ? 'mailto:' + this.value : null;
    }
  },
  methods: {
    focus() {
      this.$refs.input.focus();
    },
  }
}
</script>
