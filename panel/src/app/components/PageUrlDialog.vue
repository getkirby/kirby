<template>
  <k-form-dialog
    ref="dialog"
    :loading="isLoading"
    :cancel-button="cancelButton"
    :submit-button="submitButton"
    @submit="onSubmit"
  >
    <k-text-field
      ref="input"
      :counter="false"
      :help="url"
      :label="$t('slug')"
      :preselect="true"
      :required="true"
      :slug="true"
      icon="url"
      name="slug"
      v-model="slug"
    >
      <k-button
        slot="options"
        icon="wand"
        data-options
        @click="useTitle"
      >
        {{ $t("page.changeSlug.fromTitle") }}
      </k-button>
    </k-text-field>
  </k-form-dialog>
</template>
<script>
import AsyncFormDialog from "@/ui/components/AsyncFormDialog.vue";

export default {
  extends: AsyncFormDialog,
  data() {
    return {
      id: null,
      parents: [],
      slug: null,
      title: null,
    };
  },
  computed: {
    url() {
      if (this.parents) {
        return "/" + this.parents
          .map(p => p.slug)
          .concat([this.slug])
          .join("/");
      }

      return "/" + this.slug;
    }
  },
  methods: {
    async load(id) {
      const page = await this.$api.pages.get(id, {
        select: [
          "parents",
          "slug",
          "title"
        ]
      });

      this.id = id;
      this.parents = page.parents;
      this.slug = page.slug;
      this.title = page.title;
      this.submitButton = this.$t("change");
    },
    async submit() {
      return await this.$api.pages.changeSlug(this.id, this.slug);

      // TODO: put this into view
      // if in PageView and default language, redirect
      if (
        this.$route.params.path &&
        this.page.id === this.$route.params.path.replace(/\+/g, "/") &&
        (
          !this.$store.state.languages.current ||
          this.$store.state.languages.current.default === true
        )
      ) {
        payload.route = this.$model.pages.link(page.id);
        delete payload.event;
      }
    },
    async validate() {
      if (this.slug.length === 0) {
        throw this.$t("error.page.slug.invalid");
      }
    },
    useTitle() {
      this.slug = this.$helper.slug(this.title);
    }
  }
}
</script>
