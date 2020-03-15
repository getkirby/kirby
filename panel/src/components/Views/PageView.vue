<template>
  <k-error-view v-if="issue">
    {{ issue.message }}
  </k-error-view>
  <k-view v-else :data-locked="isLocked" class="k-page-view">

    <k-header
      :tabs="tabs"
      :tab="tab"
      :editable="permissions.changeTitle && !isLocked"
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
          :disabled="!permissions.changeStatus || isLocked"
          :icon="!permissions.changeStatus || isLocked ? 'protected' : 'circle'"
          :responsive="true"
          :tooltip="status.label"
          @click="action('status')"
        >
          {{ status.label }}
        </k-button>
        <k-dropdown>
          <k-button
            :responsive="true"
            :disabled="isLocked === true"
            icon="cog"
            @click="$refs.settings.toggle()"
          >
            {{ $t('settings') }}
          </k-button>
          <k-dropdown-content ref="settings" :options="options" @action="action" />
        </k-dropdown>

        <k-languages-dropdown ref="languages" />

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
      @tab="onTab"
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
import PrevNext from "@/mixins/view/prevnext.js";

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
    prev() {
      if (this.page.prev) {
        return {
          link: this.$api.pages.link(this.page.prev.id),
          tooltip: this.page.prev.title
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
    this.$events.$on("keydown.cmd.alt.c", this.actionDuplicate);
    this.$events.$on("keydown.cmd.alt.l", this.actionLanguage);
    this.$events.$on("keydown.cmd.alt.n", this.actionNext);
    this.$events.$on("keydown.cmd.alt.o", this.actionOpen);
    this.$events.$on("keydown.cmd.alt.p", this.actionPrev);
    this.$events.$on("keydown.cmd.alt.d", this.actionRemove);
    this.$events.$on("keydown.cmd.alt.r", this.actionRename);
    this.$events.$on("keydown.cmd.alt.x", this.actionStatus);
    this.$events.$on("keydown.cmd.alt.t", this.actionTemplate);
    this.$events.$on("keydown.cmd.alt.u", this.actionUrl);
  },
  destroyed() {
    this.$events.$off("page.changeSlug", this.update);
    this.$events.$off("keydown.cmd.alt.c", this.actionDuplicate);
    this.$events.$off("keydown.cmd.alt.l", this.actionLanguage);
    this.$events.$off("keydown.cmd.alt.n", this.actionNext);
    this.$events.$off("keydown.cmd.alt.o", this.actionOpen);
    this.$events.$off("keydown.cmd.alt.p", this.actionPrev);
    this.$events.$off("keydown.cmd.alt.d", this.actionRemove);
    this.$events.$off("keydown.cmd.alt.r", this.actionRename);
    this.$events.$off("keydown.cmd.alt.x", this.actionStatus);
    this.$events.$off("keydown.cmd.alt.t", this.actionTemplate);
    this.$events.$off("keydown.cmd.alt.u", this.actionUrl);
  },
  methods: {
    action(action) {
      switch (action) {
        case "duplicate":
          this.$refs.duplicate.open(this.page.id);
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
    actionDuplicate() {
      if (this.permissions.create && !this.isLocked) {
        return this.$refs.duplicate.open(this.page.id);
      }
    },
    actionLanguage() {
      if (this.$store.state.languages) {
        let languages = this.$store.state.languages.all;

        if (languages.length > 1) {
          let currentLanguage = this.$store.state.languages.current;
          let nextIndex = languages.findIndex(language => language.code === currentLanguage.code) + 1;

          if (languages[nextIndex]) {
            this.$refs.languages.change(languages[nextIndex]);
          } else {
            this.$refs.languages.change(languages[0]);
          }
        }
      }
    },
    actionNext() {
      if (this.page.id && this.next) {
        this.$router.push(this.next.link);
      }
    },
    actionOpen() {
      if (this.permissions.preview && this.page.previewUrl) {
        window.open(this.page.previewUrl, "_blank");
      }
    },
    actionPrev() {
      if (this.page.id && this.prev) {
        this.$router.push(this.prev.link);
      }
    },
    actionRemove() {
      if (this.permissions.delete && !this.isLocked) {
        this.$refs.remove.open(this.page.id);
      }
    },
    actionRename() {
      if (this.permissions.changeTitle && !this.isLocked) {
        this.$refs.rename.open(this.page.id);
      }
    },
    actionStatus() {
      if (this.status && this.permissions.changeStatus && !this.isLocked) {
        this.$refs.status.open(this.page.id);
      }
    },
    actionTemplate() {
      if (this.permissions.changeTemplate && !this.isLocked) {
        this.$refs.template.open(this.page.id);
      }
    },
    actionUrl() {
      if (this.permissions.changeSlug && !this.isLocked) {
        this.$refs.url.open(this.page.id);
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

          this.$store.dispatch("content/create", {
            id: "pages/" + this.page.id,
            api: this.$api.pages.link(this.page.id),
            content: this.page.content
          });

        })
        .catch(error => {
          this.issue = error;
        });
    },
    onTab(tab) {
      this.tab = tab;
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
