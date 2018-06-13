<template>
  <kirby-dialog
    v-if="error"
    ref="dialog"
    :visible="true"
    class="kirby-error-dialog"
    @close="exit"
    @open="enter"
  >
    <kirby-text>{{ error.message }}</kirby-text>
    <dl v-if="error.details" class="kirby-error-details">
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

    <footer slot="footer" class="kirby-dialog-footer">
      <kirby-button-group>
        <kirby-button icon="check" @click="close">
          {{ $t("confirm") }}
        </kirby-button>
      </kirby-button-group>
    </footer>

  </kirby-dialog>
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
        this.$el.querySelector(".kirby-dialog-footer .kirby-button").focus();
      });
    },
    exit() {
      this.$store.dispatch("notification/close");
    }
  }
};
</script>

<style lang="scss">
.kirby-error-details {
  background: $color-white;
  display: block;
  overflow: auto;
  padding: 1rem;
  font-size: $font-size-small;
  line-height: 1.25em;
  margin-top: 0.75rem;
}
.kirby-error-details dt {
  color: $color-negative-on-dark;
  margin-bottom: 0.25rem;
}
.kirby-error-details dd {
  overflow: auto;
}
.kirby-error-details dd:not(:last-of-type) {
  margin-bottom: 1.5em;
}
.kirby-error-details li:not(:last-child) {
  border-bottom: 1px solid $color-background;
  padding-bottom: 0.25rem;
  margin-bottom: 0.25rem;
}
</style>
