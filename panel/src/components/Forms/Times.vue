<template>
  <div class="k-times">
    <div class="k-times-slot">
      <k-icon type="sun" />
      <ul>
        <li v-for="time in day" :key="time.select">
          <hr v-if="time === '-'" />
          <k-button v-else @click="select(time.select)">{{
            time.display
          }}</k-button>
        </li>
      </ul>
    </div>
    <div class="k-times-slot">
      <k-icon type="moon" />
      <ul>
        <li v-for="time in night" :key="time.select">
          <hr v-if="time === '-'" />
          <k-button v-else @click="select(time.select)">{{
            time.display
          }}</k-button>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
/**
 * The Times component displayes available times to choose from
 * @public
 *
 * @example <k-times value="12:12" @input="onInput" />
 */
export default {
  props: {
    display: {
      type: String,
      default: "HH:mm"
    },
    value: String
  },
  computed: {
    day() {
      return this.formatTimes([
        6,
        7,
        8,
        9,
        10,
        11,
        "-",
        12,
        13,
        14,
        15,
        16,
        17
      ]);
    },
    night() {
      return this.formatTimes([18, 19, 20, 21, 22, 23, "-", 0, 1, 2, 3, 4, 5]);
    }
  },
  methods: {
    formatTimes(times) {
      return times.map((time) => {
        if (time === "-") {
          return time;
        }

        const dt = this.$library.dayjs(time + ":00", "H:mm");
        return {
          display: dt.format(this.display),
          select: dt.toISO("time")
        };
      });
    },
    select(time) {
      this.$emit("input", time);
    }
  }
};
</script>

<style>
.k-times {
  padding: var(--spacing-4) var(--spacing-6);
  display: grid;
  line-height: 1;
  grid-template-columns: 1fr 1fr;
  grid-gap: var(--spacing-6);
}
.k-times .k-icon {
  width: 1rem;
  margin-bottom: var(--spacing-2);
}
.k-times-slot .k-button {
  padding: var(--spacing-1) var(--spacing-3) var(--spacing-1) 0;
  font-variant-numeric: tabular-nums;
  white-space: nowrap;
}
.k-times .k-times-slot hr {
  position: relative;
  opacity: 1;
  margin: var(--spacing-2) 0;
  border: 0;
  height: 1px;
  top: 1px;
  background: var(--color-dark);
}
</style>
