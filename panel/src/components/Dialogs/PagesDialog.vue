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

      <k-list v-if="pages.length">
        <k-list-item
          v-for="page in pages"
          :key="page.id"
          :text="page.text"
          :info="page.info"
          :image="page.image"
          :icon="page.icon"
          @click="toggle(page)"
        >
          <template slot="options">
            <k-button
              v-if="isSelected(page)"
              slot="options"
              :autofocus="true"
              :icon="checkedIcon"
              :tooltip="$t('remove')"
              theme="positive"
            />
            <k-button
              v-else
              slot="options"
              :autofocus="true"
              :tooltip="$t('select')"
              icon="circle-outline"
            />
            <k-button
              v-if="model"
              :disabled="!page.hasChildren"
              :tooltip="$t('open')"
              icon="angle-right"
              @click.stop="go(page)"
            />
          </template>
        </k-list-item>
      </k-list>
      <k-empty v-else icon="page">
        No pages to select
      </k-empty>
    </template>
  </k-dialog>
</template>

<script>
export default {
  data() {
    return {
      model: {
        title: null,
        parent: null
      },
      pages: [],
      issue: null,
      options: {
        endpoint: null,
        max: null,
        multiple: true,
        parent: null,
        selected: []
      }
    };
  },
  computed: {
    multiple() {
      return this.options.multiple === true && this.options.max !== 1;
    },
    checkedIcon() {
      return this.multiple === true ? "check" : "circle-filled";
    }
  },
  methods: {
    fetch() {
      return this.$api
        .get(this.options.endpoint, { parent: this.options.parent })
        .then(response => {
          this.model = response.model;
          this.pages = response.pages;
        })
        .catch(e => {
          this.pages = [];
          this.issue = e.message;
        });
    },
    back() {
      this.options.parent = this.model.parent ? this.model.parent.id : null;
      this.fetch();
    },
    submit() {
      this.$emit("submit", this.options.selected);
      this.$refs.dialog.close();
    },
    isSelected(page) {
      return this.options.selected.map(page => page.id).includes(page.id);
    },
    toggle(page) {
      if (this.options.multiple === false) {
        this.options.selected = [];
      }

      if (this.isSelected(page) === false) {
        if (
          this.options.max &&
          this.options.max <= this.options.selected.length
        ) {
          return;
        }

        this.options.selected.push(page);
      } else {
        this.options.selected = this.options.selected.filter(
          p => p.id !== page.id
        );
      }
    },
    open(options) {
      this.options = options;
      this.fetch().then(() => {
        this.$refs.dialog.open();
      });
    },
    go(page) {
      this.options.parent = page.id;
      this.fetch();
    }
  }
};
</script>

<style lang="scss">
.k-pages-dialog-navbar {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 0.5rem;
  padding-right: 38px;
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
.k-pages-dialog .k-empty {
  border: 0;
}
</style>
