<template>
  <k-dialog
    ref="dialog"
    :autofocus="false"
    :submit-button="$t('change')"
    size="medium"
    theme="positive"
    class="k-page-rename-dialog"
    @ready="onReady"
    @submit="$refs.form.submit()"
  >
    <k-form ref="form" @submit="submit">
      <k-text-field
        ref="title"
        v-model="title"
        v-bind="fields.title"
      />
      <k-text-field
        ref="slug"
        v-bind="fields.slug"
        :value="slug"
        @input="sluggify($event)"
      >
        <k-button
          slot="options"
          :disabled="fields.slug.disabled"
          icon="wand"
          @click="sluggify(title)"
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
      page: {
        id: null,
        parent: null,
        title: null
      },
      permissions: {},
      select: "title",
      slug: null,
      title: null,
      url: null,
    };
  },
  computed: {
    fields() {
      return {
        title: {
          label: this.$t("title"),
          type: "text",
          required: true,
          icon: "title",
          disabled: this.permissions.changeTitle === false
        },
        slug: {
          name: "slug",
          label: this.$t("slug"),
          type: "text",
          required: true,
          icon: "url",
          help: "/" + this.url,
          counter: false,
          disabled: this.permissions.changeSlug === false
        }
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
    onReady() {
      if (this.$refs[this.select]) {
        this.$refs[this.select].select();
      }
    },
    async open(id, permissions, select = "title") {
      try {
        this.page   = await this.$api.pages.get(id, { view: "panel" });
        this.select = select;
        this.title  = this.page.title;
        this.sluggify(this.page.slug);
        this.permissions = permissions;
        this.$refs.dialog.open();
      } catch (error) {
        this.$store.dispatch('notification/error', error);
      }
    },
    sluggify(input) {
      input = input.trim();
      this.slug = this.$helper.slug(input, [this.slugs, this.system.ascii]);

      if (this.page.parents) {
        this.url = this.page.parents.map(p => p.slug).
          concat([this.slug]).
          join("/");
      } else {
        this.url = this.slug;
      }
    },
    async submit() {
      // prevent empty title with just spaces
      this.title = this.title.trim();

      if (
        this.title === this.page.title &&
        this.slug === this.page.slug
      ) {
        this.$refs.dialog.close();
        this.$store.dispatch("notification/success", ":)");
        return;
      }

      if (this.title.length === 0) {
        return this.$refs.dialog.error(this.$t("error.page.changeTitle.empty"));
      }

      if (this.slug.length === 0) {
        return this.$refs.dialog.error(this.$t("error.page.slug.invalid"));
      }

      try {
        let payload = {
          message: ":)",
          event: []
        };

        // title changed
        if (this.title !== this.page.title) {
          await this.$api.pages.changeTitle(this.page.id, this.title);
          payload.event.push("page.changeTitle");
        }

        // slug changed
        if (this.slug !== this.page.slug) {
          const page = await this.$api.pages.changeSlug(this.page.id, this.slug);

          // move form changes
          this.$store.dispatch("content/move", [
            "pages/" + this.page.id,
            "pages/" + page.id
          ]);

          payload.event.push("page.changeSlug");

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
            payload.emit = false;
            delete payload.event;
          }
        }

        this.success(payload);

      } catch (error) {
        this.$refs.dialog.error(error.message);
      }
    }
  }
};
</script>

<style>
.k-page-rename-dialog .k-form .k-field + .k-field {
  margin-top: 2.25rem;
}
</style>
