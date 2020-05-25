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
      // Provided an async function
      if (this.options) {
        return this.options(params);
      }

      // Provided an API endpoint
      return this.$api.get(this.endpoint, params);
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
