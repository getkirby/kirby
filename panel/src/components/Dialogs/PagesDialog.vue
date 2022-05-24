<template>
  <k-dialog
    ref="dialog"
    class="k-pages-dialog"
    size="medium"
    @cancel="$emit('cancel')"
    @submit="submit"
  >
    <template v-if="issue">
      <k-box :text="issue" theme="negative" />
    </template>
    <template v-else>
      <header v-if="model" class="k-pages-dialog-navbar">
        <k-button
          :disabled="!model.id"
          :tooltip="$t('back')"
          icon="angle-left"
          @click="back"
        />
        <k-headline>{{ model.title }}</k-headline>
      </header>

      <k-input
        v-if="options.search"
        v-model="search"
        :autofocus="true"
        :placeholder="$t('search') + ' …'"
        type="text"
        class="k-dialog-search"
        icon="search"
      />

      <k-collection v-bind="collection" @item="toggle" @paginate="paginate">
        <template #options="{ item: page }">
          <k-button v-bind="toggleBtn(page)" @click="toggle(page)" />
          <k-button
            v-if="page"
            :disabled="!page.hasChildren"
            :tooltip="$t('open')"
            icon="angle-right"
            @click.stop="go(page)"
          />
        </template>
      </k-collection>
    </template>
  </k-dialog>
</template>

<script>
import picker from "@/mixins/picker/dialog.js";

export default {
  mixins: [picker],
  data() {
    const mixin = picker.data();
    return {
      ...mixin,
      model: {
        title: null,
        parent: null
      },
      options: {
        ...mixin.options,
        parent: null
      }
    };
  },
  computed: {
    emptyProps() {
      return {
        icon: "page",
        text: this.$t("dialog.pages.empty")
      };
    },
    fetchData() {
      return {
        parent: this.options.parent
      };
    }
  },
  methods: {
    back() {
      this.options.parent = this.model.parent;
      this.pagination.page = 1;
      this.fetch();
    },
    go(page) {
      this.options.parent = page.id;
      this.pagination.page = 1;
      this.fetch();
    },
    onFetched(response) {
      this.model = response.model;
    }
  }
};
</script>

<style>
.k-pages-dialog-navbar {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 0.5rem;
  padding-inline-end: 38px;
}
.k-pages-dialog-navbar .k-button {
  width: 38px;
}
.k-pages-dialog-navbar .k-button[disabled] {
  opacity: 0;
}
.k-pages-dialog-navbar .k-headline {
  flex-grow: 1;
  text-align: center;
}
.k-pages-dialog .k-list-item {
  cursor: pointer;
}
.k-pages-dialog .k-list-item .k-button[data-theme="disabled"],
.k-pages-dialog .k-list-item .k-button[disabled] {
  opacity: 0.25;
}
.k-pages-dialog .k-list-item .k-button[data-theme="disabled"]:hover {
  opacity: 1;
}
</style>
