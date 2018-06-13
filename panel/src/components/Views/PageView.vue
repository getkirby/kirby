<template>
  <kirby-error-view v-if="issue">
    {{ issue.message }}
  </kirby-error-view>
  <kirby-view v-else class="kirby-page-view">

    <kirby-header
      :tabs="tabs"
      :tab="tab"
      @edit="action('rename')"
    >
      {{ page.title }}
      <kirby-button-group slot="left">
        <kirby-button icon="open" @click="action('preview')">
          {{ $t('open') }}
        </kirby-button>
        <kirby-button
          v-if="status"
          :disabled="permissions.changeStatus === false"
          :icon="status.icon"
          @click="action('status')"
        >
          {{ status.label }}
        </kirby-button>
        <kirby-dropdown>
          <kirby-button icon="cog" @click="$refs.settings.toggle()">
            {{ $t('settings') }}
          </kirby-button>
          <kirby-dropdown-content ref="settings" :options="options" @action="action" />
        </kirby-dropdown>
      </kirby-button-group>

      <kirby-button-group v-if="page.id" slot="right">
        <kirby-button :disabled="!prev" v-bind="prev" icon="angle-left" />
        <kirby-button :disabled="!next" v-bind="next" icon="angle-right" />
      </kirby-button-group>
    </kirby-header>

    <kirby-tabs
      v-if="page.id"
      ref="tabs"
      :key="'page-' + page.id + '-tabs'"
      :parent="$api.page.url(page.id)"
      :blueprint="blueprint"
      :tabs="tabs"
      @tab="tab = $event"
    />

    <kirby-page-rename-dialog ref="rename" @success="fetch" />
    <kirby-page-url-dialog ref="url" />
    <kirby-page-status-dialog ref="status" @success="fetch" />
    <kirby-page-template-dialog ref="template" @success="fetch" />
    <kirby-page-remove-dialog ref="remove" />

  </kirby-view>

</template>

<script>
import PrevNext from "@/mixins/prevnext.js";

export default {
  mixins: [PrevNext],
  props: {
    path: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      page: {
        title: "",
        id: null,
        prev: null,
        next: null,
        status: null
      },
      blueprint: null,
      permissions: {
        changeTitle: false,
        changeStatus: false
      },
      icon: "page",
      issue: null,
      tab: null,
      tabs: [],
      options: null
    };
  },
  computed: {
    prev() {
      if (this.page.prev) {
        return {
          link: this.$api.page.link(this.page.prev.id),
          tooltip: this.page.prev.title
        };
      }
    },
    next() {
      if (this.page.next) {
        return {
          link: this.$api.page.link(this.page.next.id),
          tooltip: this.page.next.title
        };
      }
    },
    status() {
      return this.page.status !== null
        ? this.$api.page.states()[this.page.status]
        : null;
    }
  },
  methods: {
    fetch() {
      this.$api.page
        .get(this.path, { view: "panel" })
        .then(page => {
          this.page = page;
          this.blueprint = page.blueprint.name;
          this.permissions = page.blueprint.options;
          this.tabs = page.blueprint.tabs;
          this.options = ready => {
            this.$api.page.options(this.page.id).then(options => {
              ready(options);
            });
          };

          this.$store.dispatch("breadcrumb", this.$api.page.breadcrumb(page));
          this.$store.dispatch("title", this.page.title);
        })
        .catch(error => {
          this.issue = error;
        });
    },
    action(action) {
      switch (action) {
        case "preview":
          window.open(this.page.url);
          break;
        case "rename":
          this.$refs.rename.open(this.page.id);
          break;
        case "url":
          this.$refs.url.open(this.page.id);
          break;
        case "status":
          this.$refs.status.open(this.page.id);
          break;
        case "template":
          this.$refs.template.open(this.page.id);
          break;
        case "remove":
          this.$refs.remove.open(this.page.id);
          break;
        default:
          this.$store.dispatch(
            "notification/error",
            this.$t("notification.notImplemented")
          );
          break;
      }
    }
  }
};
</script>

<style lang="scss">
.kirby-page-view .kirby-header .kirby-headline {
  max-width: 35rem;
}
</style>
