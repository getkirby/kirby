<template>
  <k-field :input="_uid" v-bind="$props">
    <k-input
      :id="_uid"
      ref="input"
      v-bind="$props"
      theme="field"
      type="email"
      v-on="$listeners"
    >
      <template v-slot:icon>
        <k-button
          v-if="link"
          :icon="icon"
          :link="mailto"
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
import EmailInput from "./EmailInput.vue";

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
