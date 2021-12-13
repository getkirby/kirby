<template>
  <k-field :input="_uid" v-bind="$props" class="k-date-field">
    <k-input
      :id="_uid"
      ref="input"
      :value="date"
      v-bind="$props"
      theme="field"
      type="date"
      v-on="listeners"
    >
      <template v-if="calendar" #icon>
        <k-dropdown>
          <k-button
            :icon="icon"
            :tooltip="$t('date.select')"
            class="k-input-icon-button"
            tabindex="-1"
            @click="$refs.calendar.toggle()"
          />
          <k-dropdown-content ref="calendar" align="right">
            <k-calendar
              :value="datetime"
              :min="min"
              :max="max"
              @input="onUpdate"
            />
          </k-dropdown-content>
        </k-dropdown>
      </template>
    </k-input>
  </k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as DateInput } from "../Input/DateInput.vue";

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-datetime-input>` for additional information.
 * @example <k-date-field v-model="date" name="date" label="Date" />
 */
export default {
  mixins: [Field, Input, DateInput],
  inheritAttrs: false,
  props: {
    /**
     * Deactivate the dropdown calendar or not
     */
    calendar: {
      type: Boolean,
      default: true
    },
    icon: {
      type: String,
      default: "calendar"
    }
  },
  data() {
    return {
      datetime: this.value
    };
  },
  computed: {
    listeners() {
      return {
        ...this.$listeners,
        enter: this.onSelect,
        focus: this.onFocus,
        input: this.onInput,
        update: this.onUpdate
      };
    }
  },
  watch: {
    value(value) {
      this.datetime = value;
    }
  },
  methods: {
    focus() {
      this.$refs.input.focus();
    },
    onUpdate(value) {
      this.$emit("input", value);
    },
    onFocus() {
      if (this.$refs.calendar) {
        this.$refs.calendar.open();
      }
    },
    onInput(value) {
      this.datetime = value;
    },
    onSelect(value) {
      this.onUpdate(value);

      if (this.$refs.calendar) {
        this.$refs.calendar.close();
      }
    }
  }
};
</script>
