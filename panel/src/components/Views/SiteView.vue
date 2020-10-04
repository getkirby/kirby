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
      :editable="permissions.changeTitle && !isLocked"
      :tab="tab"
      :tabs="tabs"
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

    <k-sections
      v-if="site.url"
      :blueprint="site.blueprint.name"
      :empty="$t('site.blueprint')"
      parent="site"
      :tab="tab"
      :tabs="tabs"
      @submit="$emit('submit', $event)"
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
      tab: "main",
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
    fetch() {
      this.$api.site
        .get({ view: "panel" })
        .then(site => {
          this.site = site;
          this.tab = this.$route.hash.slice(1) || "main";
          this.tabs = site.blueprint.tabs;
          this.permissions = site.options;
          this.options = ready => {
            this.$api.site.options().then(options => {
              ready(options);
            });
          };
          this.$store.dispatch("breadcrumb", []);
          this.$store.dispatch("title", null);
          this.$store.dispatch("content/create", {
            id: "site",
            api: "site",
            content: site.content
          });

        })
        .catch(error => {
          this.issue = error;
        });
    },
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
    }
  }
};
</script>
