<template>
  <k-field :input="_uid" v-bind="$props" class="k-date-field">
    <div
      :data-time-length="timeLength"
      class="k-date-field-body"
      data-theme="field"
    >
      <k-input
        ref="dateInput"
        :autofocus="autofocus"
        :id="_uid"
        :disabled="disabled"
        :display="display"
        :max="max"
        :min="min"
        :required="required"
        :value="value"
        theme="field"
        type="date"
        v-bind="$props"
        @input="onDateInput"
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
import { props as DateTimeInput } from "../Input/DateTimeInput.vue";

/**
 * Form field to handle a date/datetime value.
 *
 * Bundles `k-date-input`/`k-datetime-input` with `k-calendar`.
 * This is why we need to store a temporary datetimo ISO string
 * which represents a current but unstored state of the input
 * that we pass on to the calendar. That way the calendar shows
 * the same state of the input, even when the value isn't yet passed
 * up to the content store.
 *
 * Have a look at `<k-field>`, `<k-input>`
 * and `<k-datetime-input>` for additional information.
 *
 * @example <k-date-field v-model="date" name="date" label="Date" />
 * @public
 */
export default {
  mixins: [Field, Input, DateTimeInput],
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
    },
    /**
     * Deactivate th dropdown timer or not
     */
    times: {
      type: Boolean,
      default: true
    }
  },
  data() {
    return {
      iso: this.toIso(this.value)
    };
  },
  computed: {
    timeLength() {
      const length = String(this.time.display).length;

      if (length <= 5) {
        return "sm";
      }

      if (length <= 8) {
        return "md";
      }

      return "lg";
    }
  },
  watch: {
    value() {
      this.iso = this.toIso(this.value);
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
    onInput() {
      if (this.iso.date === null && this.iso.time === null) {
        this.$emit("input", "");
      }

      const dt = this.$library.dayjs.iso(this.iso.date + " " + this.iso.time);

      this.$emit("input", dt?.toISO() || "");
    },
    onCalendarInput(value) {
      this.$refs.calendar?.close();
      this.onDateInput(value);
    },
    onDateInput(value) {
      if (value && !this.iso.time) {
        this.iso.time = this.$library.dayjs().toISO("time");
      }

      this.iso.date = value;
      this.onInput();
    },
    onTimeInput(value) {
      if (value && !this.iso.date) {
        this.iso.date = this.$library.dayjs().toISO("date");
      }

      this.iso.time = value;
      this.onInput();
    },
    onTimesInput(value) {
      this.$refs.times?.close();
      this.onTimeInput(value + ":00");
    },
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
  padding: var(--spacing-1);
  background: var(--field-input-background);
  gap: var(--spacing-1);
}
.k-date-field-body:focus-within {
  border: var(--field-input-focus-border);
  box-shadow: var(--color-focus-outline) 0 0 0 2px;
}
.k-date-field-body > .k-input[data-theme="field"] {
  border: 0;
  box-shadow: none;
  background: var(--color-light);
  border-radius: var(--rounded-sm);
}
.k-date-field-body > .k-input[data-invalid="true"],
.k-date-field-body > .k-input[data-invalid="true"]:focus-within {
  border: 0 !important;
  box-shadow: none !important;
}
.k-date-field-body > .k-input[data-theme="field"] .k-text-input {
  padding: var(--spacing-1) var(--spacing-2);
  font-variant-numeric: tabular-nums;
}
.k-date-field-body .k-input .k-input-icon-button,
.k-date-field-body .k-input .k-input-icon {
  width: 1.75rem;
}
.k-date-field-body[data-time-length="sm"] {
  --time-width: 6rem;
}
.k-date-field-body[data-time-length="md"] {
  --time-width: 7.5rem;
}
.k-date-field-body[data-time-length="lg"] {
  --time-width: 9rem;
}
.k-date-field-body .k-input[data-type="date"] {
  flex-grow: 1;
  flex-basis: calc(100% - var(--time-width) - 1rem);
}
.k-date-field-body .k-input[data-type="time"] {
  flex-grow: 1;
  flex-basis: var(--time-width);
}
</style>
