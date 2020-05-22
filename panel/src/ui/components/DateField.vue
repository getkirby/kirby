<template>
  <k-field :input="_uid" v-bind="$props">
    <k-input
      :id="_uid"
      ref="input"
      :value="date"
      v-bind="$props"
      :type="inputType"
      theme="field"
      v-on="listeners"
    >
      <template v-slot:icon>
        <k-dropdown>
          <k-button
            :icon="icon"
            :tooltip="$t('date.select')"
            class="k-input-icon-button"
            tabindex="-1"
            @click="$refs.dropdown.toggle()"
          />
          <k-dropdown-content
            ref="dropdown"
            align="right"
          >
            <k-calendar
              :value="date"
              @input="onInput($event); $refs.dropdown.close()"
            />
          </k-dropdown-content>
        </k-dropdown>
      </template>
    </k-input>
  </k-field>
</template>

<script>
import Field from "./Field.vue";
import Input from "./Input.vue";
import DatetimeInput from "./DatetimeInput.vue";

export default {
  inheritAttrs: false,
  props: {
    ...Field.props,
    ...Input.props,
    ...DatetimeInput.props,
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
  computed: {
    inputType() {
      return this.time === false ? "date" : "datetime";
    }
  },
  watch: {
    value(value) {
      this.date = value;
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
