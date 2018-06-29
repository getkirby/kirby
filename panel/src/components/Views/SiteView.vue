<template>
  <kirby-error-view v-if="issue">
    {{ issue.message }}
  </kirby-error-view>
  <kirby-view v-else key="site-view" class="kirby-site-view">
    <kirby-header :tabs="tabs" :tab="tab" :editable="permissions.changeTitle" @edit="action('rename')">
      {{ site.title }}
      <kirby-button-group slot="left">
        <kirby-button icon="open" @click="action('preview')">
          {{ $t("open" ) }}
        </kirby-button>
        <kirby-dropdown>
          <kirby-button icon="cog" @click="$refs.settings.toggle()">
            {{ $t('settings') }}
          </kirby-button>
          <kirby-dropdown-content ref="settings" :options="options" @action="action" />
        </kirby-dropdown>
      </kirby-button-group>
    </kirby-header>

    <kirby-tabs
      v-if="site.url"
      ref="tabs"
      :tabs="tabs"
      parent="site"
      @tab="tab = $event"
    />

    <kirby-site-rename-dialog ref="rename" @success="fetch" />

  </kirby-view>
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
  created() {
    this.fetch();
  },
  methods: {
    fetch() {
      this.$api.site
        .get({ view: "panel" })
        .then(site => {
          this.site = site;
          this.tabs = site.blueprint.tabs;
          this.permissions = site.blueprint.options;
          this.options = ready => {
            this.$api.site.options().then(options => {
              ready(options);
            });
          };
          this.$store.dispatch("breadcrumb", []);
          this.$store.dispatch("title", this.$t("view.site"));
        })
        .catch(error => {
          this.issue = error;
        });
    },
    action(action) {
      switch (action) {
        case "preview":
          window.open(this.site.url);
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
