<script>
import DateInput from "./DateInput.vue";

export const props = {
  props: {
    /**
     * Format to parse and display the time
     * @values HH, H, hh, h, mm, m, ss, s, a
     * @example `hh:mm a`
     */
    display: {
      type: String,
      default: "HH:mm"
    },
    /**
     * The last allowed time
     * as ISO time string
     * @example `22:30:00`
     */
    max: String,
    /**
     * The first allowed time
     * as ISO time string
     * @example `01:30:00`
     */
    min: String,
    /**
     * Rounding to the nearest step.
     * Requires an object with a `unit`
     * and a `size` key
     * @example { unit: 'second', size: 15 }
     */
    step: {
      type: Object,
      default() {
        return {
          size: 5,
          unit: "minute"
        };
      }
    },
    type: {
      type: String,
      default: "time"
    },
    /**
     * Value must be provided as ISO time string
     * @example `22:33:00`
     */
    value: String
  }
};

/**
 * Form input to handle a time value.
 *
 * Extends `k-date-input` and makes sure that values
 * get parsed and emitted as time-only ISO string `HH:mm:ss`
 *
 * @example <k-input v-model="time" name="time" type="time" />
 * @public
 */
export default {
  mixins: [DateInput, props],
  computed: {
    /**
     * Use the time part for handling input values
     * @returns {string}
     */
    inputType() {
      return "time";
    }
  }
};
</script>
