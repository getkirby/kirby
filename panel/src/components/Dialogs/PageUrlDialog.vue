<template>
  <kirby-dialog
    ref="dialog"
    :button="$t('change')"
    size="medium"
    theme="positive"
    @submit="$refs.form.submit()"
  >
    <kirby-form ref="form" @submit="submit">
      <kirby-text-field v-bind="field" :value="slug" @input="sluggify($event)">
        <kirby-button
          slot="options"
          icon="wand"
          data-options
          @click="sluggify(page.title)"
        >
          {{ $t("page.rename.fromTitle") }}
        </kirby-button>
      </kirby-text-field>
    </kirby-form>
  </kirby-dialog>
</template>

<script>
import DialogMixin from "@/mixins/dialog.js";
import slug from "@/ui/helpers/slug.js";

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
        label: this.$t("page.slug"),
        type: "text",
        required: true,
        icon: "url",
        help: "/" + this.url,
        preselect: true
      };
    }
  },
  methods: {
    sluggify(input) {
      this.slug = slug(input);

      if (this.page.parent) {
        this.url = this.page.parent.id + "/" + this.slug;
      } else {
        this.url = this.slug;
      }
    },
    open(id) {
      this.$api.pages.get(id)
        .then(page => {
          this.page = page;
          this.sluggify(this.page.slug);
          this.$refs.dialog.open();
        })
        .catch(error => {
          this.$store.dispatch('notification/error', error);
        });
    },
    submit() {
      if (this.slug === this.page.slug) {
        this.$refs.dialog.close();
        this.$store.dispatch(
          "notification/success",
          this.$t("page.status.changed.same")
        );
        return;
      }

      if (this.slug.length === 0) {
        this.$refs.dialog.error(this.$t("error.page.slug.invalid"));
        return;
      }

      this.$api.pages
        .slug(this.page.id, this.slug)
        .then(page => {
          const payload = {
            message: this.$t("page.status.changed", { url: page.id }),
            event: "page.changeSlug"
          };

          // if in PageView, redirect
          if (
            this.$route.params.path &&
            this.page.id === this.$route.params.path.replace(/\+/g, "/")
          ) {
            payload.route = this.$api.pages.link(page.id);
          }

          this.success(payload);
        })
        .catch(error => {
          this.$refs.dialog.error(error.message);
        });
    }
  }
};
</script>
