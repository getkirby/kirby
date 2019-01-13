<template>
  <k-dialog
    v-if="error"
    ref="dialog"
    :visible="true"
    class="k-error-dialog"
    @close="exit"
    @open="enter"
  >
    <k-text>{{ error.message }}</k-text>
    <dl v-if="error.details && Object.keys(error.details).length" class="k-error-details">
      <template v-for="(detail, index) in error.details">
        <dt :key="'detail-label-' + index">{{ detail.label }}</dt>
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

    <k-button-group slot="footer">
      <k-button icon="check" @click="close">
        {{ $t("confirm") }}
      </k-button>
    </k-button-group>

  </k-dialog>
</template>

<script>
import DialogMixin from "@/mixins/dialog.js";

export default {
  mixins: [DialogMixin],
  computed: {
    error() {
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
        this.$el.querySelector(".k-dialog-footer .k-button").focus();
      });
    },
    exit() {
      this.$store.dispatch("notification/close");
    }
  }
};
</script>

<style lang="scss">
.k-error-details {
  background: $color-white;
  display: block;
  overflow: auto;
  padding: 1rem;
  font-size: $font-size-small;
  line-height: 1.25em;
  margin-top: 0.75rem;
}
.k-error-details dt {
  color: $color-negative-on-dark;
  margin-bottom: 0.25rem;
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
  border-bottom: 1px solid $color-background;
  padding-bottom: 0.25rem;
  margin-bottom: 0.25rem;
}
</style>
