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
      @submit="onClose()"
      class="k-notifications-dialog"
    >
      <k-text>
        {{ dialog.message }}
      </k-text>
      <dl
        :data-theme="dialog.type"
        class="k-notifications-details bg-white block overflow-auto p-4 text-sm mt-3"
      >
        <template v-for="(detail, index) in dialog.details">
          <dd
            v-if="typeof detail === 'string'"
            :key="'detail-message-' + index"
            class="truncate"
          >
            {{ detail }}
          </dd>

          <template v-else>
            <dt :key="'detail-label-' + index" class="mb-1">
              {{ detail.label }}
            </dt>
            <dd :key="'detail-message-' + index" class="truncate">
              <template v-if="typeof detail.message === 'object'">
                <ul>
                  <li
                    v-for="(msg, msgIndex) in detail.message"
                    :key="msgIndex"
                  >
                    {{ msg }}
                  </li>
                </ul>
              </template>
              <template v-else>
                {{ dialog.message }}
              </template>
            </dd>
          </template>
        </template>
      </dl>
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
    }
  }
}
</script>


<style lang="scss">
.k-notifications-details {
  line-height: 1.25em;
}
.k-notifications-details dd:not(:last-of-type) {
  margin-bottom: 1.5em;
}
.k-notifications-details li:not(:last-child) {
  border-bottom: 1px solid $color-background;
  padding-bottom: 0.25rem;
  margin-bottom: 0.25rem;
}
.k-notifications-details[data-theme="success"] dt {
  color: $color-green-600;
}
.k-notifications-details[data-theme="info"] dt {
  color: $color-blue-600;
}
.k-notifications-details[data-theme="error"] dt {
  color: $color-red-600;
}
</style>
