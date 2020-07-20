<template>
  <k-dropdown class="k-page-dropdown">
    <k-button
      :text="text"
      class="k-page-dropdown-toggle"
      @click="$refs.dropdown.toggle()"
    />
    <k-dropdown-content
      ref="dropdown"
      align="center"
      class="k-page-dropdown-content flex items-center justify-center"
      @open="onOpen"
    >
      <label
        for="k-pagination-page"
        class="flex items-center text-sm px-3 py-3"
      >
        <span class="mr-4">{{ pageLabel }}:</span>
        <select
          id="k-pagination-page"
          ref="select"
          v-model="selectedPage"
        >
          <option
            v-for="p in pages"
            :key="p"
            :value="p"
          >
            {{ p }}
          </option>
        </select>
      </label>
      <k-button
        icon="check"
        class="px-3 py-3"
        @click="onChange"
      />
    </k-dropdown-content>
  </k-dropdown>
</template>

<script>
export default {
  props: {
    page: {
      type: Number,
      default: 1
    },
    pages: {
      type: Number,
      default: 1
    },
    /**
     * Sets the label for the page selector
     */
    pageLabel: {
      type: String,
      default() {
        return this.$t("pagination.page");
      }
    },
    text: String
  },
  data() {
    return {
      selectedPage: this.page,
    };
  },
  watch: {
    page() {
      this.selectedPage = this.page;
    }
  },
  methods: {
    onChange() {
      this.$emit("change", this.selectedPage);
      this.$refs.dropdown.close();
    },
    onOpen() {
      this.$nextTick(() => {
        this.$refs.select.focus();
      });
    }
  }
}
</script>

<style lang="scss">
.k-page-dropdown-content label {
  border-right: 1px solid $color-gray-800;
}
.k-page-dropdown-content label span {
  margin-right: .5rem;
}
</style>
