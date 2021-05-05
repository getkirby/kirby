<template>
  <k-dialog
    v-if="notification"
    ref="dialog"
    :cancel-button="false"
    :visible="true"
    class="k-error-dialog"
    @close="exit"
    @submit="$refs.dialog.close()"
  >
    <k-text>{{ notification.message }}</k-text>
    <dl v-if="notification.details && Object.keys(notification.details).length" class="k-error-details">
      <template v-for="(detail, index) in notification.details">
        <dt :key="'detail-label-' + index">
          {{ detail.label }}
        </dt>
        <dd :key="'detail-message-' + index">
          <template v-if="typeof detail.message === 'object'">
            <ul>
              <li v-for="(msg, msgIndex) in detail.message" :key="msgIndex">
                {{ msg }}
              </li>
            </ul>
          </template>
          <template v-else>
            {{ detail.message }}
          </template>
        </dd>
      </template>
    </dl>
  </k-dialog>
</template>

<script>
import DialogMixin from "@/mixins/dialog.js";

export default {
  mixins: [DialogMixin],
  computed: {
    notification() {
      let notification = this.$store.state.notification;

      if (notification.type === "error") {
        return notification;
      }

      return null;
    }
  },
  methods: {
    enter() {
      this.$nextTick(() => {
        if (this.$el && this.$el.querySelector) {
          this.$el.querySelector(".k-dialog-footer .k-button").focus();
        }
      });
    },
    exit() {
      this.$store.dispatch("notification/close");
    }
  }
};
</script>

<style>
.k-error-details {
  background: var(--color-white);
  display: block;
  overflow: auto;
  padding: 1rem;
  font-size: var(--text-sm);
  line-height: 1.25em;
  margin-top: .75rem;
}
.k-error-details dt {
  color: var(--color-negative-light);
  margin-bottom: .25rem;
}
.k-error-details dd {
  overflow: hidden;
  overflow-wrap: break-word;
  text-overflow: ellipsis;
}
.k-error-details dd:not(:last-of-type) {
  margin-bottom: 1.5em;
}
.k-error-details li:not(:last-child) {
  border-bottom: 1px solid var(--color-background);
  padding-bottom: .25rem;
  margin-bottom: .25rem;
}
</style>
