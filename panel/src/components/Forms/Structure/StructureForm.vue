<template>
  <div class="k-structure-form">
    <!-- Backdrop -->
    <div class="k-structure-backdrop" @click="onDiscard" />

    <section>
      <!-- Form -->
      <k-form
        ref="form"
        :value="value"
        :fields="fields"
        class="k-structure-form-fields"
        @input="onInput"
        @submit="onSubmit"
      />

      <!-- Footer -->
      <footer class="k-structure-form-buttons">
        <k-button
          :text="$t('cancel')"
          icon="cancel"
          class="k-structure-form-cancel-button"
          @click="$emit('close')"
        />
        <k-pagination
          v-if="index !== 'new'"
          :dropdown="false"
          :total="total"
          :limit="1"
          :page="index + 1"
          :details="true"
          @paginate="$emit('paginate', $event)"
        />
        <k-button
          :text="$t(index !== 'new' ? 'confirm' : 'add')"
          icon="check"
          class="k-structure-form-submit-button"
          @click="onSubmit"
        />
      </footer>
    </section>
  </div>
</template>

<script>
export default {
  props: {
    /**
     * Form fields
     */
    fields: Object,
    /**
     * Index of current model/row
     */
    index: [Number, String],
    /**
     * Total number of rows in field
     */
    total: Number,
    value: Object
  },
  mounted() {
    this.$store.dispatch("content/disable");
    this.$events.$on("keydown.cmd.s", this.onSubmit);
    this.$events.$on("keydown.esc", this.onDiscard);
  },
  destroyed() {
    this.$events.$off("keydown.cmd.s", this.onSubmit);
    this.$events.$off("keydown.esc", this.onDiscard);
    this.$store.dispatch("content/enable");
  },
  methods: {
    focus(field) {
      this.$refs.form.focus(field);
    },
    onDiscard() {
      this.$emit("discard");
    },
    onInput(input) {
      this.$emit("input", input);
    },
    onSubmit() {
      this.$emit("submit");
    }
  }
};
</script>

<style>
.k-structure-backdrop {
  position: absolute;
  inset: 0;
  z-index: 2;
  height: 100vh;
}

.k-structure-form section {
  position: relative;
  z-index: 3;
  border-radius: var(--rounded-xs);
  margin-bottom: 1px;
  box-shadow: rgba(17, 17, 17, 0.05) 0 0 0 3px;
  border: 1px solid var(--color-border);
  background: var(--color-background);
}

.k-structure-form-fields {
  padding: 1.5rem 1.5rem 2rem;
}

.k-structure-form-buttons {
  border-top: 1px solid var(--color-border);
  display: flex;
  justify-content: space-between;
}

.k-structure-form-buttons .k-pagination {
  display: none;
}
@media screen and (min-width: 65em) {
  .k-structure-form-buttons .k-pagination {
    display: flex;
  }
}

.k-structure-form-buttons .k-pagination > .k-button,
.k-structure-form-buttons .k-pagination > span {
  padding: 0.875rem 1rem !important;
}

.k-structure-form-cancel-button,
.k-structure-form-submit-button {
  padding: 0.875rem 1.5rem;
  line-height: 1rem;
  display: flex;
}
</style>
