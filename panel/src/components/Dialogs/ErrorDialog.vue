<template>
  <k-dialog
    ref="dialog"
    :cancel-button="false"
    :size="size"
    :visible="true"
    class="k-error-dialog"
    @cancel="$emit('cancel')"
    @close="$emit('close')"
    @submit="$refs.dialog.close()"
  >
    <k-text>{{ message }}</k-text>
    <dl v-if="detailsList.length" class="k-error-details">
      <template v-for="(detail, index) in detailsList">
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
  props: {
    details: [Object, Array],
    message: String,
    size: {
      type: String,
      default: "medium"
    }
  },
  computed: {
    detailsList() {
      return Array.isArray(this.details)
        ? this.details
        : Object.values(this.details || {});
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
  margin-top: 0.75rem;
}
.k-error-details dt {
  color: var(--color-negative-light);
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
  border-bottom: 1px solid var(--color-background);
  padding-bottom: 0.25rem;
  margin-bottom: 0.25rem;
}
</style>
