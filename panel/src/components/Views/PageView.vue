<template>
  <k-error-view v-if="issue">
    {{ issue.message }}
  </k-error-view>
  <k-view v-else :data-locked="isLocked" class="k-page-view">
    <k-header
      :editable="permissions.changeTitle && !isLocked"
      :tab="tab"
      :tabs="tabs"
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
        <k-status-icon
          v-if="status"
          :status="page.status"
          :disabled="!permissions.changeStatus || isLocked"
          :responsive="true"
          :text="status.label"
          @click="action('status')"
        />
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

        <k-languages-dropdown />
      </k-button-group>

      <k-prev-next
        v-if="page.id"
        slot="right"
        :prev="prev"
        :next="next"
      />
    </k-header>

    <k-sections
      v-if="page.id"
      :blueprint="blueprint"
      :empty="$t('page.blueprint', { template: $esc(blueprint) })"
      :parent="$api.pages.url(page.id)"
      :tab="tab"
      :tabs="tabs"
    />

    <k-page-rename-dialog ref="rename" @success="update" />
    <k-page-duplicate-dialog ref="duplicate" />
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
    },
    tab: {
      type: String
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

      return null;
    },
    prev() {
      if (this.page.prev) {
        return {
          link: this.$api.pages.link(this.page.prev.id),
          tooltip: this.page.prev.title
        };
      }

      return null;
    },
    status() {
      return this.page.status !== null
        ? this.page.blueprint.status[this.page.status]
        : null;
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
        case "rename":
          this.$refs.rename.open(this.page.id, this.permissions, "title");
          break;
        case "url":
          this.$refs.rename.open(this.page.id, this.permissions, "slug");
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
    async fetch() {
      try {
        this.page = await this.$api.pages.get(this.path, { view: "panel" });
        this.blueprint = this.page.blueprint.name;
        this.permissions = this.page.options;
        this.tabs = this.page.blueprint.tabs;
        this.options = async ready => {
          const options = await this.$api.pages.options(this.page.id);
          ready(options);
        };

        this.$store.dispatch("breadcrumb", this.$api.pages.breadcrumb(this.page));
        this.$store.dispatch("title", this.page.title);

        this.$store.dispatch("content/create", {
          id: "pages/" + this.page.id,
          api: this.$api.pages.link(this.page.id),
          content: this.page.content
        });

      } catch (error) {
        this.issue = error;
      }
    },
    update() {
      this.fetch();
      this.$emit("model.update");
    }
  }
};
</script>
