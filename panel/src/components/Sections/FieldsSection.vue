<template>
  <section v-if="!isLoading" class="k-fields-section">
    <template v-if="issue">
      <k-headline class="k-fields-issue-headline">
        Error
      </k-headline>
      <k-box :text="issue.message" theme="negative" />
    </template>
    <k-form
      :fields="fields"
      :validate="true"
      :value="values"
      :disabled="$store.state.content.status.lock !== null"
      @input="input"
      @submit="onSubmit"
    />
  </section>
</template>

<script>
import SectionMixin from "@/mixins/section/section.js";
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
    language() {
      return this.$store.state.languages.current;
    },
    values() {
      return this.$store.getters["content/values"]();
    }
  },
  watch: {
    language() {
      this.fetch();
    }
  },
  created() {
    this.input = debounce(this.input, 50);
    this.fetch();
  },
  methods: {
    input(values, field, fieldName) {
      this.$store.dispatch("content/update", [
        fieldName,
        values[fieldName]
      ]);
    },
    async fetch() {
      try {
        const response = await this.load();
        this.fields = response.fields;

        Object.keys(this.fields).forEach(name => {
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
  margin-bottom: .5rem;
}
.k-fields-section input[type="submit"] {
  display: none;
}

[data-locked] .k-fields-section {
  opacity: .2;
  pointer-events: none;
}
</style>
