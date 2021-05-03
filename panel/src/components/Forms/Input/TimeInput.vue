<script>
import DateInput from "./DateInput.vue";

/**
 * @example <k-input v-model="time" name="time" type="time" />
 */
export default {
  extends: DateInput,
  props: {
    display: {
      type: String,
      default: "HH:mm"
    },
    max: String,
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
  },
  computed: {
    is12HourFormat() {
      return this.display.toLowerCase().includes("a")
    },
    /**
     * Map for matching time units with dayjs tokens
     */
    map() {
      return {
        second: ["s", "ss"],
        minute: ["m", "mm"],
        hour:   this.is12HourFormat ? ["h", "hh"] : ["H", "HH"]
      };
    },
    /**
     *  All variations of parsing patterns
     *  for dayjs tokens included in `display`
     */
    patterns() {
      // get computed patterns prop from original DateInput component
      let patterns = DateInput.computed.patterns.apply(this);

      // add patterns for am/pm token
      if (this.is12HourFormat) {
        patterns = patterns.map(pattern => pattern  + "a").concat(patterns);
      }

      return patterns;
    }
  },
  methods: {
    emit(event) {
      const value = this.toFormat(this.parsed, "HH:mm:ss") || "";
      this.$emit(event, value);
    },
    toDatetime(string) {
      return this.$library.dayjs.utc(string, "HH:mm:ss");
    },
  }
};
</script>
