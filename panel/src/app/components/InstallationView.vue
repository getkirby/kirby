<template>
  <k-outside class="k-installation-view">
    <k-view align="center">
      <template v-if="isLoading">
        <k-loader />
      </template>
      <template v-else>
        <k-form
          :autofocus="true"
          :fields="fields"
          v-model="user"
          @submit="onSubmit"
        >
          <template v-slot:header>
            <header>
              <h1 class="sr-only">
                {{ $t("installation") }}
              </h1>
            </header>
          </template>

          <template v-slot:footer>
            <footer class="pt-6">
              <k-button
                class="k-installation-button p-3"
                icon="check"
                type="submit"
              >
                {{ $t("install") }} &rarr;
              </k-button>
            </footer>
          </template>
        </k-form>
      </template>
    </k-view>
  </k-outside>
</template>

<script>
export default {
  data() {
    return {
      fields: {},
      isLoading: true,
      user: {
        name: "",
        email: "",
        language: "en",
        password: "",
        role: "admin"
      }
    };
  },
  async created() {
    const languages = await this.$model.translations.options();

    this.isLoading = false;
    this.fields = {
      email: {
        label: this.$t("email"),
        type: "email",
        link: false,
        required: true
      },
      password: {
        label: this.$t("password"),
        type: "password",
        placeholder: this.$t("password") + " …",
        required: true
      },
      language: {
        label: this.$t("language"),
        type: "select",
        options: languages,
        icon: "globe",
        empty: false,
        required: true
      }
    };
  },
  methods: {
    async onSubmit() {
      await this.$api.system.install(this.user);
    }
  }
};
</script>

<style>
.k-installation-button {
  margin: 0 -.75rem;
}
</style>
