<template>
  <section v-if="!isLoading" class="k-fields-section">
    <template v-if="issue">
      <k-headline class="k-fields-issue-headline"> Error </k-headline>
      <k-box :text="issue.message" :html="false" theme="negative" />
    </template>
    <k-form
      :fields="fields"
      :validate="true"
      :value="values"
      :disabled="lock && lock.state === 'lock'"
      @input="input"
      @submit="onSubmit"
    />
  </section>
</template>

<script>
import SectionMixin from "@/mixins/section.js";
import debounce from "@/helpers/debounce.js";

export default {
  mixins: [SectionMixin],
  inheritAttrs: false,
  data() {
    return {
      fields: {},
      isLoading: true,
      issue: null
    };
  },
  computed: {
    values() {
      return this.$store.getters["content/values"]();
    }
  },
  watch: {
    // Reload values and field definitions
    // when the view has changed in the backend
    timestamp() {
      this.fetch();
    }
  },
  created() {
    this.input = debounce(this.input, 50);
    this.fetch();
  },
  methods: {
    input(values, field, fieldName) {
      this.$store.dispatch("content/update", [fieldName, values[fieldName]]);
    },
    async fetch() {
      try {
        const response = await this.load();
        this.fields = response.fields;

        Object.keys(this.fields).forEach((name) => {
          this.fields[name].section = this.name;
          this.fields[name].endpoints = {
            field: this.parent + "/fields/" + name,
            section: this.parent + "/sections/" + this.name,
            model: this.parent
          };
        });
      } catch (error) {
        this.issue = error;
      } finally {
        this.isLoading = false;
      }
    },
    onSubmit($event) {
      this.$events.$emit("keydown.cmd.s", $event);
    }
  }
};
</script>

<style>
.k-fields-issue-headline {
  margin-bottom: 0.5rem;
}
.k-fields-section input[type="submit"] {
  display: none;
}

[data-locked="true"] .k-fields-section {
  opacity: 0.2;
  pointer-events: none;
}
</style>
