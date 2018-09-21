<template>
  <section class="k-fields-section">
    <template v-if="issue">
      <k-headline class="k-fields-issue-headline">Error</k-headline>
      <k-box :text="issue.message" theme="negative" />
    </template>
    <k-form
      v-else
      :fields="fields"
      :validate="true"
      :value="values"
      @input="input"
      @submit="onSubmit"
    />
  </section>
</template>

<script>
import SectionMixin from "@/mixins/section.js";

export default {
  mixins: [SectionMixin],
  data() {
    return {
      fields: {},
      isLoading: true,
      issue: null
    };
  },
  computed: {
    id() {
      return this.$store.getters["form/id"](this.$route);
    },
    language() {
      return this.$store.state.languages.current;
    },
    values() {
      return this.$store.getters["form/values"](this.id);
    }
  },
  watch: {
    language() {
      this.fetch();
    }
  },
  created: function() {
    this.$store.dispatch("form/create", this.id);
    this.fetch();
    this.$events.$on("form.reset", this.fetch);
    this.$events.$on("model.update", this.fetch);
  },
  destroyed: function() {
    this.$events.$off("form.reset", this.fetch);
    this.$events.$off("model.update", this.fetch);
  },
  methods: {
    input(values) {
      this.$store.dispatch("form/update", [this.id, values]);
    },
    fetch() {
      this.$api
        .get(this.parent + "/sections/" + this.name)
        .then(response => {
          this.$store.dispatch("form/content", [this.id, response.data]);
          this.$store.dispatch("form/errors", [this.id, response.errors]);
          this.fields    = response.fields;
          this.isLoading = false;
        })
        .catch(error => {
          this.issue = error;
          this.isLoading = false;
        });
    },
    onSubmit($event) {
      this.$events.$emit("keydown.cmd.s", $event);
    }
  }
};
</script>

<style>
.k-fields-issue-headline {
  margin-bottom: .5rem;
}
.k-fields-section input[type="submit"] {
  display: none;
}
</style>
