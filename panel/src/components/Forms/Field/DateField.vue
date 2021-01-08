<template>
  <k-field :input="_uid" v-bind="$props" class="k-date-field">
    <k-input
      :id="_uid"
      ref="input"
      :type="inputType"
      :value="value"
      v-bind="$props"
      theme="field"
      v-on="listeners"
    >
      <template v-if="calendar" #icon>
        <k-dropdown>
          <k-button
            :icon="icon"
            :tooltip="$t('date.select')"
            class="k-input-icon-button"
            tabindex="-1"
            @click="onFocus"
          />
          <k-dropdown-content ref="calendar" align="right">
            <k-calendar
              :value="datetime"
              :min="min"
              :max="max"
              @input="onSelect"
            />
          </k-dropdown-content>
        </k-dropdown>
      </template>
    </k-input>
  </k-field>
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
    inputType() {
      return this.time === false ? "date" : "datetime";
    },
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
  created() {
    // binds click event that clicking out of the calendar closes it
    this.outsideClick = (event) => {
      const calendarInput = this.$el.querySelector(".k-calendar-input");
      if (calendarInput && this.$el.contains(event.target) === false) {
        this.close();
      }
    };

    document.addEventListener("click", this.outsideClick, true);
  },
  destroyed() {
    document.removeEventListener("click", this.outsideClick);
  },
  methods: {
    close() {
      if (this.$refs.calendar) {
        this.$refs.calendar.close();
      }
    },
    focus() {
      this.$refs.input.focus();
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
      this.close();
    },
    onUpdate(value) {
      this.$emit("input", value);
    }
  }
}
</script>
