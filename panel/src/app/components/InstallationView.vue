<template>
  <k-outside
    :loading="loading || processing"
    class="k-installation-view"
  >
    <k-view align="center">
      <template v-if="loading">
        <k-loader />
      </template>
      <template v-else>
        <k-form
          :autofocus="!processing"
          :fields="fields"
          v-model="values"
          @submit="$emit('install', $event)"
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
                :loading="processing"
                :tooltip="$t('install')"
                color="green"
                icon="check"
                type="submit"
                class="k-installation-button p-3"
              >
                <template v-if="!processing">
                  {{ $t("install") }}
                </template>
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
  props: {
    processing: {
      type: Boolean,
      default: false
    },
    loading: {
      type: Boolean,
      default: false
    },
    translation: {
      type: String,
      default: "en"
    },
    translations: {
      type: Array,
      default() {
        return [
          {
            text: "English",
            value: "en"
          }
        ];
      }
    },
  },
  data() {
    return {
      values: {
        name: "",
        email: "",
        language: this.translation,
        password: "",
        role: "admin"
      }
    }
  },
  computed: {
    fields() {
      return {
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
          options: this.translations,
          icon: "globe",
          empty: false,
          required: true
        }
      };
    }
  },
  watch: {
    translation() {
      this.values.language = this.translation;
    },
    "values.language"(translation) {
      this.$emit("translate", translation);
    }
  }
};
</script>

<style>
.k-installation-button {
  margin: 0 -.75rem;
}
</style>
