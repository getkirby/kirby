import Calendar from "./Calendar.vue";

export default {
  title: "Interaction / Calendar",
  component: Calendar
};

export const regular = () => ({
  data() {
    return {
      date: "",
    };
  },
  template: `
    <div>
      <k-calendar v-model="date" />

      <br>

      Value: {{ date }}
    </div>
  `,
});


export const dropdown = () => ({
  data() {
    return {
      date: ""
    };
  },
  template: `
    <k-dropdown>
      <k-button
        icon="calendar"
        @click="$refs.calendar.open()"
      >
        Open calendar
      </k-button>
      <k-dropdown-content ref="calendar">
        <k-calendar
          v-model="date"
          @input="$refs.calendar.close()"
        />
      </k-dropdown-content>

      <br>
      <br>

      Value: {{ date }}
    </div>
  `
});

