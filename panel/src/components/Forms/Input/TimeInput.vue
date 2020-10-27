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
     *  All variations of parsing patterns
     *  for dayjs tokens included in `display`
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
     * Separator for time format
     */
    separator() {
      return ":";
    }
  }
};
</script>
