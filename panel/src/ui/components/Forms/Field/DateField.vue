<template>
  <kirby-field :input="_uid" v-bind="$props" class="kirby-date-field">
    <kirby-input
      ref="input"
      :id="_uid"
      :type="inputType"
      :value="date"
      v-bind="$props"
      theme="field"
      v-on="listeners"
    >
      <template slot="icon">
        <kirby-dropdown>
          <kirby-button
            :icon="icon"
            class="kirby-input-icon-button"
            tabindex="-1"
            @click="$refs.dropdown.toggle()"
          />
          <kirby-dropdown-content ref="dropdown" align="right">
            <kirby-calendar :value="date" @input="onInput($event); $refs.dropdown.close()" />
          </kirby-dropdown-content>
        </kirby-dropdown>
      </template>

    </kirby-input>
  </kirby-field>
</template>

<script>
import Field from "../Field.vue";
import Input from "../Input.vue";
import DateTimeInput from "../Input/DateTimeInput.vue";

export default {
  inheritAttrs: false,
  props: {
    ...Field.props,
    ...Input.props,
    ...DateTimeInput.props,
    icon: {
      type: String,
      default: "calendar"
    }
  },
  data() {
    return {
      date: this.value,
      listeners: {
        ...this.$listeners,
        input: this.onInput
      }
    };
  },
  watch: {
    value(value) {
      this.date = value;
    }
  },
  computed: {
    inputType() {
      return this.time === false ? "date" : "datetime";
    }
  },
  methods: {
    focus() {
      this.$refs.input.focus();
    },
    onInput(date) {
      this.date = date;
      this.$emit("input", date);
    }
  }
}
</script>
