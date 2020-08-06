<template>
  <k-dialog
    ref="dialog"
    :submit-button="$t('change')"
    size="medium"
    theme="positive"
    @submit="$refs.form.submit()"
  >
    <k-form ref="form" @submit="submit">
      <k-text-field v-bind="field" :value="slug" @input="sluggify($event)">
        <k-button
          slot="options"
          icon="wand"
          data-options
          @click="sluggify(page.title)"
        >
          {{ $t("page.changeSlug.fromTitle") }}
        </k-button>
      </k-text-field>
    </k-form>
  </k-dialog>
</template>

<script>
import DialogMixin from "@/mixins/dialog.js";

export default {
  mixins: [DialogMixin],
  data() {
    return {
      slug: null,
      url: null,
      page: {
        id: null,
        parent: null,
        title: null
      }
    };
  },
  computed: {
    field() {
      return {
        name: "slug",
        label: this.$t("slug"),
        type: "text",
        required: true,
        icon: "url",
        help: "/" + this.url,
        counter: false,
        preselect: true
      };
    },
    slugs() {
      return this.$store.state.languages.current ? this.$store.state.languages.current.rules : this.system.slugs;
    },
    system() {
      return this.$store.state.system.info;
    }
  },
  methods: {
    sluggify(input) {
      this.slug = this.$helper.slug(input, [this.slugs, this.system.ascii]);

      if (this.page.parents) {
        this.url = this.page.parents.map(p => p.slug).
                                     concat([this.slug]).
                                     join("/");
      } else {
        this.url = this.slug;
      }
    },
    async open(id) {
      try {
        this.page = await this.$api.pages.get(id, { view: "panel" });
        this.sluggify(this.page.slug);
        this.$refs.dialog.open();

      } catch (error) {
        this.$store.dispatch('notification/error', error);
      }
    },
    async submit() {
      if (this.slug === this.page.slug) {
        this.$refs.dialog.close();
        this.$store.dispatch("notification/success", ":)");
        return;
      }

      if (this.slug.length === 0) {
        return this.$refs.dialog.error(this.$t("error.page.slug.invalid"));
      }

      try {
        const page = await this.$api.pages.changeSlug(this.page.id, this.slug);

        // move form changes
        this.$store.dispatch("content/move", [
          "pages/" + this.page.id,
          "pages/" + page.id
        ]);

        const payload = {
          message: ":)",
          event: "page.changeSlug"
        };

        // if in PageView and default language, redirect
        if (
          this.$route.params.path &&
          this.page.id === this.$route.params.path.replace(/\+/g, "/") &&
          (
            !this.$store.state.languages.current ||
            this.$store.state.languages.current.default === true
          )
        ) {
          payload.route = this.$api.pages.link(page.id);
          delete payload.event;
        }

        this.success(payload);

      } catch (error) {
        this.$refs.dialog.error(error.message);
      }
    }
  }
};
</script>
