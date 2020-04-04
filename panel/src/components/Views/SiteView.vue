<template>
  <k-error-view v-if="issue">
    {{ issue.message }}
  </k-error-view>
  <k-view
    v-else
    key="site-view"
    :data-locked="isLocked"
    class="k-site-view"
  >
    <k-header
      :tabs="tabs"
      :tab="tab"
      :editable="permissions.changeTitle && !isLocked"
      @edit="action('rename')"
    >
      {{ site.title }}
      <k-button-group slot="left">
        <k-button
          :responsive="true"
          :link="site.previewUrl"
          target="_blank"
          icon="open"
        >
          {{ $t('open') }}
        </k-button>
        <k-languages-dropdown />
      </k-button-group>
    </k-header>

    <k-tabs
      v-if="site.url"
      ref="tabs"
      :tabs="tabs"
      :blueprint="site.blueprint.name"
      parent="site"
      @tab="tab = $event"
    />

    <k-site-rename-dialog ref="rename" @success="fetch" />
  </k-view>
</template>

<script>
export default {
  data() {
    return {
      site: {
        title: null,
        url: null
      },
      issue: null,
      tab: null,
      tabs: [],
      options: null,
      permissions: {
        changeTitle: true
      }
    };
  },
  computed: {
    isLocked() {
      return this.$store.state.content.status.lock !== null;
    },
    language() {
      return this.$store.state.languages.current;
    }
  },
  watch: {
    language() {
      this.fetch();
    }
  },
  created() {
    this.fetch();
  },
  methods: {
    action(action) {
      switch (action) {
        case "languages":
          this.$refs.languages.open();
          break;
        case "rename":
          this.$refs.rename.open();
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
        this.site = await this.$api.site.get({ view: "panel" });
        this.tabs = this.site.blueprint.tabs;
        this.permissions = this.site.options;

        this.options = async ready => {
          let options = await this.$model.site.options();
          ready(options);
        };

        this.$store.dispatch("breadcrumb", []);
        this.$store.dispatch("title", null);
        this.$store.dispatch("content/create", {
          id: "site",
          api: "site",
          content: this.site.content
        });

      } catch (error) {
        this.issue = error;
      }
    }
  }
};
</script>
