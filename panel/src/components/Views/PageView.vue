<template>
  <k-error-view v-if="issue">
    {{ issue.message }}
  </k-error-view>
  <k-view v-else class="k-page-view">

    <k-header
      :tabs="tabs"
      :tab="tab"
      :editable="permissions.changeTitle"
      @edit="action('rename')"
    >
      {{ page.title }}
      <k-button-group slot="left">
        <k-button
          v-if="permissions.preview && page.previewUrl"
          :responsive="true"
          :link="page.previewUrl"
          target="_blank"
          icon="open"
        >
          {{ $t('open') }}
        </k-button>
        <k-button
          v-if="status"
          :class="['k-status-flag', 'k-status-flag-' + page.status]"
          :disabled="permissions.changeStatus === false"
          :icon="permissions.changeStatus === false ? 'protected' : 'circle'"
          :responsive="true"
          @click="action('status')"
        >
          {{ status.label }}
        </k-button>
        <k-dropdown>
          <k-button :responsive="true" icon="cog" @click="$refs.settings.toggle()">
            {{ $t('settings') }}
          </k-button>
          <k-dropdown-content ref="settings" :options="options" @action="action" />
        </k-dropdown>

        <k-languages-dropdown />

      </k-button-group>

      <k-prev-next
        v-if="page.id"
        slot="right"
        :prev="prev"
        :next="next"
      />
    </k-header>

    <k-tabs
      v-if="page.id"
      ref="tabs"
      :key="tabsKey"
      :parent="$api.pages.url(page.id)"
      :blueprint="blueprint"
      :tabs="tabs"
      @tab="tab = $event"
    />

    <k-page-rename-dialog ref="rename" @success="update" />
    <k-page-duplicate-dialog ref="duplicate" />
    <k-page-url-dialog ref="url" />
    <k-page-status-dialog ref="status" @success="update" />
    <k-page-template-dialog ref="template" @success="update" />
    <k-page-remove-dialog ref="remove" />

  </k-view>

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
      preview: true,
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
          link: this.$api.pages.link(this.page.prev.id),
          tooltip: this.page.prev.title
        };
      }
    },
    language() {
      return this.$store.state.languages.current;
    },
    next() {
      if (this.page.next) {
        return {
          link: this.$api.pages.link(this.page.next.id),
          tooltip: this.page.next.title
        };
      }
    },
    status() {
      return this.page.status !== null
        ? this.page.blueprint.status[this.page.status]
        : null;
    },
    tabsKey() {
      return "page-" + this.page.id + "-tabs";
    }
  },
  watch: {
    language() {
      this.fetch();
    },
    path() {
      this.fetch();
    }
  },
  created() {
    this.$events.$on("page.changeSlug", this.update);
  },
  destroyed() {
    this.$events.$off("page.changeSlug", this.update);
  },
  methods: {
    action(action) {
      switch (action) {
        case "duplicate":
          this.$refs.duplicate.open(this.page.id);
          break;
        case "preview":
          this.$api.pages
            .preview(this.page.id)
            .then(url => {
              window.open(url);
            })
            .catch(error => {
              this.$store.dispatch("notification/error", error);
            });
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
    },
    fetch() {
      this.$api.pages
        .get(this.path, { view: "panel" })
        .then(page => {
          this.page = page;
          this.blueprint = page.blueprint.name;
          this.permissions = page.options;
          this.tabs = page.blueprint.tabs;
          this.options = ready => {
            this.$api.pages.options(this.page.id).then(options => {
              ready(options);
            });
          };

          this.$store.dispatch("breadcrumb", this.$api.pages.breadcrumb(page));
          this.$store.dispatch("title", this.page.title);
          this.$store.dispatch("form/create", {
            id: "pages/" + page.id,
            api: this.$api.pages.link(page.id),
            content: page.content
          });
        })
        .catch(error => {
          this.issue = error;
        });
    },
    update() {
      this.fetch();
      this.$emit("model.update");
    }
  }
};
</script>

<style lang="scss">
.k-status-flag svg {
  width: 14px;
  height: 14px;
}
.k-status-flag-listed .k-icon {
  color: $color-positive-on-dark;
}
.k-status-flag-unlisted .k-icon {
  color: $color-focus-on-dark;
}
.k-status-flag-draft .k-icon {
  color: $color-negative-on-dark;
}
.k-status-flag[disabled] {
  opacity: 1;
}
</style>
