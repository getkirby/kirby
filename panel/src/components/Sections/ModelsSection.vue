<template>
  <section
    v-if="isLoading === false"
    :data-processing="isProcessing"
    :class="`k-models-section k-${type}-section`"
  >
    <header class="k-section-header">
      <k-headline :link="options.link">
        {{ options.headline || " " }}
        <abbr v-if="options.min" :title="$t('section.required')">*</abbr>
      </k-headline>

      <k-button-group
        v-if="canAdd"
        :buttons="[{ text: $t('add'), icon: 'add', click: onAdd }]"
      />
    </header>

    <k-box v-if="error" theme="negative">
      <k-text size="small">
        <strong> {{ $t("error.section.notLoaded", { name: name }) }}: </strong>
        {{ error }}
      </k-text>
    </k-box>

    <template v-else>
      <k-dropzone :disabled="!canDrop" @drop="onDrop">
        <k-collection
          v-bind="collection"
          :data-invalid="isInvalid"
          @action="onAction"
          @change="onChange"
          @sort="onSort"
          @empty="add ? onAdd : null"
          @paginate="onPaginate"
        />
      </k-dropzone>

      <k-upload ref="upload" @success="onUpload" @error="reload" />
    </template>
  </section>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    blueprint: String,
    column: String,
    parent: String,
    name: String,
    timestamp: Number
  },
  data() {
    return {
      data: [],
      error: null,
      isLoading: false,
      isProcessing: false,
      options: {
        empty: null,
        headline: null,
        help: null,
        layout: "list",
        link: null,
        max: null,
        min: null,
        size: null,
        sortable: null
      },
      pagination: {
        page: null
      }
    };
  },
  computed: {
    canAdd() {
      return true;
    },
    canDrop() {
      return false;
    },
    collection() {
      return {
        empty: this.emptyProps,
        layout: this.options.layout,
        help: this.options.help,
        items: this.items,
        pagination: this.pagination,
        sortable: !this.isProcessing && this.options.sortable,
        size: this.options.size
      };
    },
    emptyProps() {
      return {
        icon: "page",
        text: this.options.empty || this.$t("pages.empty")
      };
    },
    items() {
      return this.data;
    },
    isInvalid() {
      if (this.options.min && this.data.length < this.options.min) {
        return true;
      }

      if (this.options.max && this.data.length > this.options.max) {
        return true;
      }

      return false;
    },
    paginationId() {
      return "kirby$pagination$" + this.parent + "/" + this.name;
    },
    type() {
      return "models";
    }
  },
  watch: {
    // Reload the section when
    // the view has changed in the backend
    timestamp() {
      this.reload();
    }
  },
  created() {
    this.load();
  },
  methods: {
    add() {},

    async load(reload) {
      if (!reload) {
        this.isLoading = true;
      }

      this.isProcessing = true;

      if (this.pagination.page === null) {
        this.pagination.page = localStorage.getItem(this.paginationId) || 1;
      }

      try {
        const response = await this.$api.get(
          this.parent + "/sections/" + this.name,
          { page: this.pagination.page }
        );

        this.options = response.options;
        this.pagination = response.pagination;
        this.data = response.data;
      } catch (error) {
        this.error = error.message;
      } finally {
        this.isProcessing = false;
        this.isLoading = false;
      }
    },

    onAction() {},
    onAdd() {},
    onChange() {},
    onDrop() {},
    onSort() {},
    onPaginate(pagination) {
      localStorage.setItem(this.paginationId, pagination.page);
      this.pagination = pagination;
      this.reload();
    },
    onUpload() {},

    async reload() {
      await this.load(true);
    },
    update() {
      this.reload();
      this.$events.$emit("model.update");
    }
  }
};
</script>

<style>
.k-models-section[data-processing="true"] {
  pointer-events: none;
}
</style>
