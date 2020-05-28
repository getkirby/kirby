
export default {
  title: "Ui | Interaction / Notifications "
};

export const regular = () => ({
  data() {
    return {
      notifications: []
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
      const notification = {
        ...this.pool[Math.floor(Math.random() * this.pool.length)],
        id: Date.now()
      };
      this.notifications.push(notification);

      if (
        notification.permanent !== true &&
        notification.type !== "error"
      ) {
        setTimeout(() => {
          this.onClose(notification.id);
        }, 2500);
      }
    },
    onClose(id) {
      this.notifications = this.notifications.filter(notification => notification.id !== id);
    }
  },
  template: `
    <k-inside :registered="true">
      <k-view class="py-6">
        <k-notifications
          :notifications="notifications"
          @close="onClose"
        />
        <k-button
          icon="wand"
          text="Trigger new notification"
          @click="onAdd"
        />
        <k-code-block :code="notifications" class="mt-8" />
      </k-view>
    </k-inside>
  `
});
