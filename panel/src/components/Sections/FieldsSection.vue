<template>
  <section v-if="!isLoading" class="k-fields-section">
    <template v-if="issue">
      <k-headline class="k-fields-issue-headline">Error</k-headline>
      <k-box :text="issue.message" theme="negative" />
    </template>
    <k-form
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
    $route() {
      this.fields    = {};
      this.isLoading = true;
      this.issue     = null;
    },
    language() {
      this.fetch();
    }
  },
  created: function() {
    this.fetch();
  },
  methods: {
    input(values, field, fieldName) {
      this.$store.dispatch("form/update", [this.id, fieldName, values[fieldName]]);
    },
    fetch() {
      this.$api
        .get(this.parent + "/sections/" + this.name)
        .then(response => {
          this.fields    = response.fields;
          this.isLoading = false;
        })
        .catch(error => {
          this.issue     = error;
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
