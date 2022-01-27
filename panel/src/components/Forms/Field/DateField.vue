<template>
  <k-field :input="_uid" v-bind="$props" class="k-date-field">
    <div
      ref="body"
      :data-invalid="!novalidate && isInvalid"
      class="k-date-field-body"
      data-theme="field"
    >
      <!-- Date input -->
      <k-input
        :id="_uid"
        ref="dateInput"
        :autofocus="autofocus"
        :disabled="disabled"
        :display="display"
        :max="max"
        :min="min"
        :required="required"
        :value="value"
        theme="field"
        type="date"
        v-bind="$props"
        @invalid="onDateInvalid"
        @input="onDateInput"
        @submit="$emit('submit')"
      >
        <template v-if="calendar" #icon>
          <k-dropdown>
            <k-button
              :icon="icon"
              :tooltip="$t('date.select')"
              class="k-input-icon-button"
              @click="$refs.calendar.toggle()"
            />
            <k-dropdown-content ref="calendar" align="right">
              <k-calendar
                :value="value"
                :min="min"
                :max="max"
                @input="onCalendarInput"
              />
            </k-dropdown-content>
          </k-dropdown>
        </template>
      </k-input>

      <!-- Time input (optional) -->
      <k-input
        v-if="time"
        ref="timeInput"
        :disabled="disabled"
        :display="time.display"
        :required="required"
        :step="time.step"
        :value="iso.time"
        :icon="time.icon"
        theme="field"
        type="time"
        @input="onTimeInput"
        @submit="$emit('submit')"
      >
        <template v-if="times" #icon>
          <k-dropdown>
            <k-button
              :icon="time.icon || 'clock'"
              :tooltip="$t('time.select')"
              class="k-input-icon-button"
              @click="$refs.times.toggle()"
            />
            <k-dropdown-content ref="times" align="right">
              <k-times
                :display="time.display"
                :value="value"
                @input="onTimesInput"
              />
            </k-dropdown-content>
          </k-dropdown>
        </template>
      </k-input>
    </div>
  </k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as DateInput } from "../Input/DateInput.vue";

/**
 * Form field to handle a date/datetime value.
 *
 * Bundles `k-date-input` with `k-calendar` and, optionally,
 * `k-time-input` with `k-times`.
 *
 * Have a look at `<k-field>`, `<k-input>`
 * and `<k-datetime-input>` for additional information.
 *
 * @example <k-date-field v-model="date" name="date" label="Date" />
 * @public
 */
export default {
  mixins: [Field, Input, DateInput],
  inheritAttrs: false,
  props: {
    /**
     * Deactivate the calendar dropdown or not
     */
    calendar: {
      type: Boolean,
      default: true
    },
    /**
     * Icon used for the date input (and calendar dropdown)
     */
    icon: {
      type: String,
      default: "calendar"
    },
    /**
     * Time options (e.g. `display`, `icon`, `step`).
     * Please check docs for `k-time-input` props.
     * @example { display: 'HH:mm', step: { unit: "minute", size: 30 } }
     */
    time: {
      type: [Boolean, Object],
      default() {
        return {};
      }
    },
    /**
     * Deactivate the times dropdown or not
     */
    times: {
      type: Boolean,
      default: true
    }
  },
  data() {
    return {
      isInvalid: false,
      // keep an object of separate ISO values
      // for date and time parts
      iso: this.toIso(this.value)
    };
  },
  computed: {
    /**
     * Whether the field is empty
     * @returns {bool}
     */
    isEmpty() {
      if (this.time) {
        return this.iso.date === null && this.iso.time;
      }

      return this.iso.date === null;
    }
  },
  watch: {
    value(newValue, oldValue) {
      if (newValue !== oldValue) {
        this.iso = this.toIso(newValue);
      }
    }
  },
  methods: {
    /**
     * Focuses the input element
     * @public
     */
    focus() {
      this.$refs.dateInput.focus();
    },
    /**
     * Returns an object of ISO date and time parts
     * for the current date/time
     * @returns {Object}
     */
    now() {
      const now = this.$library.dayjs();
      return {
        date: now.toISO("date"),
        time: this.time ? now.toISO("time") : "00:00:00"
      };
    },
    /**
     * Handle any input action
     */
    onInput() {
      if (this.isEmpty) {
        return this.$emit("input", "");
      }

      const dt = this.$library.dayjs.iso(this.iso.date + " " + this.iso.time);

      if (!dt) {
        if (this.iso.date === null || this.iso.time === null) {
          return;
        }
      }

      this.$emit("input", dt?.toISO() || "");
    },
    /**
     * Handle input event from calendar dropdown
     * @param {string} value
     */
    onCalendarInput(value) {
      this.$refs.calendar?.close();
      this.onDateInput(value);
    },
    /**
     * Handle input event from date input
     * @param {string} value
     */
    onDateInput(value) {
      // fill in the current time if the time input is empty
      if (value && !this.iso.time) {
        this.iso.time = this.now().time;
      }

      this.iso.date = value;
      this.onInput();
    },
    /**
     * Handle invalid event from date input
     * @param {bool} state
     */
    onDateInvalid(state) {
      this.isInvalid = state;
    },
    /**
     * Handle input event from time input
     * @param {string} value
     */
    onTimeInput(value) {
      // fill in the current date if the date input is empty
      if (value && !this.iso.date) {
        this.iso.date = this.now().date;
      }

      this.iso.time = value;
      this.onInput();
    },
    /**
     * Handle input event from times dropdown
     * @param {string} value
     */
    onTimesInput(value) {
      this.$refs.times?.close();
      this.onTimeInput(value + ":00");
    },
    /**
     * Convert an ISO string into an object
     * of date/time part ISO strings
     * @param {string} value
     */
    toIso(value) {
      const dt = this.$library.dayjs.iso(value);
      return {
        date: dt?.toISO("date") || null,
        time: dt?.toISO("time") || null
      };
    }
  }
};
</script>

<style>
.k-date-field-body {
  display: flex;
  flex-wrap: wrap;
  line-height: 1;
  border: var(--field-input-border);
  background: var(--color-gray-300);
  gap: 1px;
}
.k-date-field-body:focus-within {
  border: var(--field-input-focus-border);
  box-shadow: var(--color-focus-outline) 0 0 0 2px;
}
.k-date-field[data-disabled] .k-date-field-body {
  background: none;
}
.k-date-field-body > .k-input[data-theme="field"] {
  border: 0;
  box-shadow: none;
  border-radius: var(--rounded-sm);
}
.k-date-field-body > .k-input[data-invalid="true"],
.k-date-field-body > .k-input[data-invalid="true"]:focus-within {
  border: 0 !important;
  box-shadow: none !important;
}

/* https://heydonworks.com/article/the-flexbox-holy-albatross/ */
.k-date-field-body {
  --multiplier: calc(25rem - 100%);
}
.k-date-field-body > * {
  flex-grow: 1;
  flex-basis: calc(var(--multiplier) * 999);
  max-width: 100%;
}
.k-date-field-body .k-input[data-type="date"] {
  min-width: 60%;
}
.k-date-field-body .k-input[data-type="time"] {
  min-width: 30%;
}
</style>
