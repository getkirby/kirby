import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "Ui | Interaction / Alerts",
  decorators: [Padding]
};

export const regular = () => ({
  data() {
    return {
      alerts: []
    }
  },
  computed: {
    pool() {
      return [
        {
          type: "success",
          message: "Saved"
        },
        {
          type: "success",
          message: "Page created"
        },
        {
          type: "success",
          message: "File uploaded"
        },
        {
          type: "info",
          message: 'The status of "album" cannot be changed'
        },
        {
          type: "error",
          message: 'Cannot connect to API'
        },
        {
          type: "error",
          message: 'Error: in line 89'
        },
        {
          type: "info",
          message: "Update available",
          click: () => this.$router.push("/system"),
          permanent: true
        },
        {
          type: "info",
          message: "Welcome Bastian!"
        }
      ]
    }
  },
  methods: {
    onAdd() {
      const alert = {
        ...this.pool[Math.floor(Math.random() * this.pool.length)],
        id: Date.now()
      };
      this.alerts.push(alert);

      if (
        alert.permanent !== true &&
        alert.type !== "error"
      ) {
        setTimeout(() => {
          this.onClose(alert.id);
        }, 2500);
      }
    },
    onClose(id) {
      this.alerts = this.alerts.filter(alert => alert.id !== id);
    }
  },
  template: `
    <div>
      <k-alerts
        :alerts="alerts"
        @close="onClose"
      />
      <k-button
        icon="wand"
        text="Trigger new alert"
        @click="onAdd"
      />
      <k-code-block :code="alerts" class="mt-8" />
    </div>
  `
});
