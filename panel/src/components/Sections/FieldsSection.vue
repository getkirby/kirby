<template>
  <section class="kirby-fields-section">
    <kirby-box v-if="issue" :text="issue.message" />
    <kirby-form
      v-else
      :fields="fields"
      :validate="true"
      v-model="values"
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
      errors: [],
      fields: [],
      isLoading: true,
      stored: {},
      values: {},
      issue: null
    };
  },
  computed: {
    id() {
      if (this.$route.name === "Account") {
        return '/users/' + this.$store.state.user.current.id;
      }

      return this.$route.path;
    }
  },
  created: function() {
    this.fetch();
    this.$events.$on("form.saved", this.fetch);
    this.$events.$on("form.reset", this.fetch);
  },
  destroyed: function() {
    this.$events.$off("form.saved", this.fetch);
    this.$events.$off("form.reset", this.fetch);
  },
  methods: {
    input(values) {
      this.$cache.set(this.id, values);
      this.$events.$emit("form.changed");
    },
    fetch() {
      this.$api
        .section(this.parent, this.name)
        .then(response => {
          this.errors = response.errors;
          this.fields = response.fields;
          this.stored = response.values;
          this.values = Object.assign(
            {},
            response.values,
            this.$cache.get(this.id) || {}
          );
          this.$events.$emit("form.changed");
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
.kirby-fields-section input[type="submit"] {
  display: none;
}
</style>
