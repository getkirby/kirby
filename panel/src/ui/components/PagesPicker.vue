<template>
  <div class="k-pages-picker">
    <!-- Search -->
    <k-input
      v-if="search"
      v-model="q"
      :autofocus="true"
      :placeholder="$t('search') + ' …'"
      type="text"
      icon="search"
      class="k-picker-search mb-4 py-2 px-4 rounded-sm"
    />

    <!-- Navigation -->
    <header
      v-if="parent"
      class="k-picker-navbar mb-4"
      slot="navigation"
    >
      <k-button
        :disabled="!parent.id"
        :tooltip="$t('back')"
        icon="angle-left"
        @click="onBack"
      >
        {{ parent.title }}
      </k-button>
    </header>

    <!-- Collection -->
    <k-async-collection
      ref="collection"
      :items="items"
      :image="image"
      :layout="layout"
      :pagination="pagination"
      :sortable="sortable"
      class="k-picker"
      @item="onItem"
      @flag="onFlag"
      @option="onEnter"
      @paginate="onPaginate"
      @sort="onSort"
    />
  </div>
</template>

<script>
import Picker from "./Picker.vue";

export default {
  extends: Picker,
  data() {
    return {
      selected: this.value,
      q: null,
      parents: [],
      page: 1,
      total: 0,
      limit: this.limit
    }
  },
  computed: {
    loader() {
      return async () => {
        return await this.options({
           ...this.pagination,
            parent: this.parent,
            search: this.q
          });
      }
    },
    parent() {
      if (this.parents.length === 0) {
        return null;
      }

      return this.parents[this.parents.length - 1];
    }
  },
  methods: {
    onBack() {
      this.parents.pop();
      this.reset();
      this.reload();
    },
    onEnter(option, item, itemIndex) {
      this.parents.push(item);
      this.reset();
      this.reload();
    }
  }
}
</script>
