<template>
  <k-picker
    ref="picker"
    v-bind="$props"
    :options="load"
    v-on="$listeners"
    :pagination="{ page: page, limit: limit }"
    @paginate="onPaginate"
  />
</template>

<script>
import Picker from "@/ui/components/Picker.vue";
import items from "@/ui/mixins/items.js";

export default {
  mixins: [items],
  props: {
    ...Picker.props,
    endpoint: {
      type: String
    },
    help: String,
    limit: {
      type: Number,
      default: 15
    },
    options: {
      type: Function
    },
  },
  data() {
    return {
      page: 1
    };
  },
  methods: {
    async load(params) {
      let response;

      // provided an async function
      if (this.options) {
        response = await this.options(params);

      // provided an API endpoint
      } else {
        response = await this.$api.get(
          this.endpoint,
          params,
          null,
          true
        );
      }

      // map items if mapper method existis
      if (this.map) {
        response = this.map(response);
      }

      return response;
    },
    onPaginate(pagination) {
      this.page = pagination.page;
      this.$emit("paginate", pagination);
    },
    reset() {
      this.page = 1;
      this.$refs.picker.reset();
    }
  }
}
</script>
