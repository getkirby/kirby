<script>
import DateInput from "./DateInput.vue";

export const props = {
  props: {
    /**
     * dayjs format to parse and display the time
     * @example `hh:mm a`
     */
    display: {
      type: String,
      default: "HH:mm"
    },
    /**
     * The last allowed time
     * @example `22:30:00`
     */
    max: String,
    /**
     * The first allowed time
     * @example `01:30:00`
     */
    min: String,
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
    }
  }
};

/**
 * @example <k-input v-model="time" name="time" type="time" />
 */
export default {
  extends: DateInput,
  mixins: [props],
  methods: {
    /**
     * Converts ISO string to dayjs object
     * @param {string} string
     * @public
     */
    toDatetime(string) {
      return this.$library.dayjs.iso(string, "time");
    },
    /**
     * Converts dayjs object to ISO string
     * @param {Object} dt
     */
    toISO(dt) {
      return dt.toISO("time");
    }
  }
};
</script>
