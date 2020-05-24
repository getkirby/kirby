<template>
  <k-picker
    ref="picker"
    v-bind="$props"
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
    help: String,
    limit: {
      type: Number,
      default: 15
    },
    options: {
      type: Function,
      async default({page, limit, parent, search}) {
        return [];
      }
    }
  },
  data() {
    return {
      page: 1
    };
  },
  methods: {
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
