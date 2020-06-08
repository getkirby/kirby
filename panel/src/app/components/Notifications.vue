<template>
  <portal>
    <!--  Alerts -->
    <k-alerts
      v-if="alerts.length"
      :alerts="alerts"
      @close="onClose"
    />

    <!-- Dialog for details -->
    <k-dialog
      v-if="Boolean(dialog)"
      :visible="true"
      :cancel-button="false"
      :submit-button="{ text: $t('confirm') }"
      @close="onClose()"
      @submit="onSubmit()"
      class="k-notifications-dialog"
    >
      <div :data-theme="dialog.type">

        <!-- details is a string -->
        <template v-if="typeof dialog.details === 'string'">
          <div class="k-notifications-title">
            {{ dialog.message }}
          </div>

          <dl class="k-notifications-details">
            <dd v-html="dialog.details" />
          </dl>
        </template>

        <!-- details as array -->
        <template v-else>
          <k-text>
            {{ dialog.message }}
          </k-text>

          <dl class="k-notifications-details">
            <template v-for="(detail, index) in dialog.details">
                <dt
                  :key="'detail-label-' + index"
                  class="k-notifications-title mb-1"
                >
                  {{ detail.label }}
                </dt>
                <dd :key="'detail-message-' + index" class="truncate">
                  <template v-if="typeof detail.message === 'string'">
                    {{ dialog.message }}
                  </template>
                  <template v-else>
                    <ul>
                      <li
                        v-for="(msg, msgIndex) in detail.message"
                        :key="msgIndex"
                      >
                        {{ msg }}
                      </li>
                    </ul>
                  </template>

                </dd>
            </template>
          </dl>
        </template>

      </div>
    </k-dialog>
  </portal>
</template>

<script>
export default {
  computed: {
    alerts() {
      return this.$store.state.notification.alerts;
    },
    dialog() {
      return this.$store.state.notification.dialog;
    }
  },
  methods: {
    onClose(id) {
      this.$store.dispatch("notification/close", id);
    },
    onSubmit(id) {
      if (this.dialog.click) {
        this.dialog.click();
      }
      this.onClose(id);
    }
  }
}
</script>


<style lang="scss">
.k-notifications-details {
  display: block;
  padding: 1rem;
  margin-top: .75rem;
  font-size: $text-sm;
  line-height: 1.25em;
  background: $color-white;
  overflow: auto;
}
.k-notifications-details dd:not(:last-of-type) {
  margin-bottom: 1.5em;
}
.k-notifications-details li:not(:last-child) {
  border-bottom: 1px solid $color-background;
  padding-bottom: 0.25rem;
  margin-bottom: 0.25rem;
}
[data-theme="success"] .k-notifications-title {
  color: $color-green-600;
}
[data-theme="info"] .k-notifications-title {
  color: $color-blue-600;
}
[data-theme="error"] .k-notifications-title {
  color: $color-red-600;
}
</style>
