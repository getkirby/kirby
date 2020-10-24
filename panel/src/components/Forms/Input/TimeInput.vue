<script>
import DateInput from "./DateInput.vue";

export default {
  extends: DateInput,
  props: {
    display: {
      type: String,
      default: "HH:mm"
    },
    max: String,
    min: String,
    notation: {
      type: Number,
      default: 24
    },
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
    /**
     * Match format chunks to dayjs tokens
     */
    tokens() {
      return{
        H: ["HH"],
        h: ["hh"],
        m: ["mm"],
        s: ["ss"]
      };
    },
    /**
     *  Generate all possible dayjs parsing patterns
     *  for all chunks of the provided format
     */
    patterns() {
      // get computed patterns prop from original DateInput component
      let patterns = DateInput.computed.patterns.apply(this);

      // add patterns for am/pm token
      if (this.notation === 12) {
        patterns = patterns.map(pattern => pattern  + "a").concat(patterns);
      }

      return patterns;
    },
    /**
     * Separator for date format
     */
    separator() {
      return ":";
    }
  },
  validations() {
    return {
      value: {
        min: this.min ? value => this.$helper.validate.datetime(
          this,
          value,
          this.min,
          "isAfter",
          this.step.unit
        ) : true,
        max: this.max ? value => this.$helper.validate.datetime(
          this,
          value,
          this.max,
          "isBefore",
          this.step.unit
        ) : true,
      }
    }
  }
};
</script>
