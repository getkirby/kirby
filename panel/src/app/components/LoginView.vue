<template>
  <k-outside
    :loading="loading || authenticating"
    class="k-loading-view"
  >
    <k-view align="center">
      <template v-if="loading">
        <k-loader />
      </template>
      <template v-else>
        <k-form
          ref="form"
          :autofocus="!authenticating"
          :fields="fields"
          v-model="values"
          @submit="$emit('login', $event)"
        >
          <template v-slot:footer>
            <footer class="pt-6 flex justify-between">
              <k-toggle-input
                class="text-sm"
                :text="$t('login.remember')"
                v-model="values.remember"
              />
              <k-button
                :loading="authenticating"
                :text="$t('login')"
                class="k-login-button p-3"
                icon="check"
                type="submit"
                theme="positive"
              />
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
    authenticating: {
      type: Boolean,
      default: false
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      values: {
        email: null,
        password: null,
        remember: false,
      }
    };
  },
  computed: {
    fields() {
      return {
        email: {
          label: this.$t("email"),
          type: "email",
          required: true,
          link: false,
        },
        password: {
          label: this.$t("password"),
          type: "password",
          minLength: 8,
          required: true,
          autocomplete: "current-password",
          counter: false
        }
      }
    }
  }
}
</script>

<style>
.k-login-button {
  margin-right: -.75rem;
}
</style>
