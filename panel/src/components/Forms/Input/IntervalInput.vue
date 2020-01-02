<template>
  <div class="k-calendar-input k-interval-input">
    <nav>
      <k-button icon="angle-left" @click="prev" />
      <span class="k-calendar-selects">
        <k-select-input
          :options="months"
          :disabled="disabled"
          :required="true"
          v-model.number="month"
        />
        <k-select-input
          :options="years"
          :disabled="disabled"
          :required="true"
          v-model.number="year"
        />
      </span>
      <k-button icon="angle-right" @click="next" />
    </nav>
    <table class="k-calendar-table">
      <thead>
        <tr>
          <th v-for="day in weekdays" :key="'weekday_' + day">{{ day }}</th>
        </tr>
      </thead>
      <tbody @mouseleave="hover = null">
        <tr v-for="week in numberOfWeeks" :key="'week_' + week">
          <td
            v-for="(day, dayIndex) in days(week)"
            :key="'day_' + dayIndex"
            :aria-current="isToday(day)"
            :aria-selected="isCurrent(day)"
            :data-intersected="isIntersected(day)"
            :data-isStart="isStart(day)"
            :data-isEnd="isEnd(day)"
            @mouseover="setHover(day)"
            class="k-interval-day"
          >
            <k-button v-if="day" @click="select(day)">
              {{ day }}
            </k-button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import Calendar from "../Calendar.vue";

export default {
  props: {
    from: Object,
    to: Object,
    disabled: Boolean
  },
  data() {
    const current = this. from ? this.from : this.to ? this.to : this.$library.dayjs().startOf("day");

    return {
      month: current.month(),
      year: current.year(),
      start: this.from,
      end: this.to,
      today: this.$library.dayjs().startOf("day"),
      hover: null,
    };
  },
  watch: {
    from(value) {
      this.from = value;
      this.current = this.getCurrent();
    },
    to(value) {
      this.to = value;
      this.current = this.getCurrent();
    }
  },
  computed: {
    ...Calendar.computed,
    date() {
      return this.getDate(1);
    }
  },
  methods: {
    ...Calendar.methods,
    getDate(day, month = this.month, year = this.year) {
      return this.$library.dayjs(new Date(
        year,
        month,
        day,
        0,
        0,
        0
      ));
    },
    setHover(day) {
      this.hover = this.getDate(day);
    },
    isStart(day) {
      const date = this.getDate(day);

      if (date.isSame(this.start)) {
        return true;
      }

     if (this.start && this.end) {
        return false;
      }

      return !this.start && date.isSame(this.hover) && date.isBefore(this.end);
    },
    isEnd(day) {
      const date = this.getDate(day);

      if (date.isSame(this.end)) {
        return true;
      }

      if (this.start && this.end) {
        return false;
      }

      return date.isSame(this.hover) && date.isAfter(this.start);
    },
    isToday(day) {
      return this.getDate(day).isSame(this.today);
    },
    isInRange(date, from, to) {
      if (from && to) {
        return (
          date.isSame(from) ||
          date.isSame(to) ||
          (date.isAfter(from) &&
          date.isBefore(to))
        );
      }

      if (from) {
        return date.isSame(from);
      }

      if (to) {
        return date.isSame(to);
      }

      return false;
    },
    isCurrent(day) {
      if (day === "") {
        return false;
      }

      return this.isInRange(
        this.getDate(day),
        this.start,
        this.end
      );
    },
    isIntersected(day) {
      if (day === "") {
        return false;
      }

      if (this.start && this.end) {
        return false;
      }

      if (this.start && this.hover) {
        if (this.hover.isAfter(this.start)) {
          return this.isInRange(
            this.getDate(day),
            this.start,
            this.hover
          );
        }
      }

      if (this.end && this.hover) {
        if (this.hover.isBefore(this.end)) {
          return this.isInRange(
            this.getDate(day),
            this.hover,
            this.end
          );
        }
      }

      return false;
    },
    select(day) {
      const date = this.getDate(day);

      if (this.start && this.end) {
        this.start = date;
        this.end   = null;
      } else if (this.start) {
        if (date.isBefore(this.start)) {
          this.start = date;
        } else {
          this.end = date;
        }
      } else {
        if (date.isAfter(this.end)) {
          this.end = date;
        } else {
          this.start = date;
        }
      }
      this.onSelect();
    },
    onSelect() {
      if (this.start && this.end) {
        this.$emit("input", {
            from: this.start,
            to: this.end
        });
      }
    }
  }
};
</script>

<style lang="scss">
.k-interval-day .k-button {
  width: 2rem;
  height: 2rem;
  margin: 0 auto;
  color: #fff;
  line-height: 1.75rem;
  display: flex;
  justify-content: center;
  border-radius: 50%;
  border: 2px solid transparent;
}
.k-interval-day .k-button .k-button-text {
  opacity: 1;
}
.k-interval-day:hover .k-button {
  border-color: rgba(#fff, 0.25);
}
.k-interval-day[data-intersected] {
  background-color: rgba(#fff, 0.25);
}
.k-interval-day[aria-current] .k-button {
  color: var(--color-focus);
  border-color: var(--color-focus);
  font-weight: 500;
}
.k-interval-day[aria-selected] {
  background-color: var(--color-focus-light);
  color: #fff;
}
.k-interval-day[data-isstart] {
  border-top-left-radius: 100%;
  border-bottom-left-radius: 100%;
}
.k-interval-day[data-isend] {
  border-top-right-radius: 100%;
  border-bottom-right-radius: 100%;
}
</style>
