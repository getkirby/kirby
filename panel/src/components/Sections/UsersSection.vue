<template>
  <section v-if="isLoading === false" class="k-users-section">

    <header class="k-section-header">
      <k-headline :link="options.link">
        {{ headline }} <abbr v-if="options.min" :title="$t('section.required')">*</abbr>
      </k-headline>

      <k-button-group v-if="add">
        <k-button icon="add" @click="create">{{ $t("add") }}</k-button>
      </k-button-group>
    </header>

    <template v-if="error">
      <k-box theme="negative">
        <k-text size="small">
          <strong>
            {{ $t("error.section.notLoaded", { name: name }) }}:
          </strong>
          {{ error }}
        </k-text>
      </k-box>
    </template>

    <template v-else>
      <k-collection
        v-if="data.length"
        :layout="options.layout"
        :help="help"
        :items="data"
        :pagination="pagination"
        :size="options.size"
        :data-invalid="isInvalid"
        @paginate="paginate"
        @action="action"
      />

      <template v-else>
        <k-empty
          :layout="options.layout"
          :data-invalid="isInvalid"
          icon="user"
          @click="create"
        >
          {{ options.empty || $t('users.empty') }}
        </k-empty>
        <footer class="k-collection-footer">
          <k-text
            v-if="help"
            theme="help"
            class="k-collection-help"
            v-html="help"
          />
        </footer>
      </template>

      <k-user-create-dialog ref="create" @success="update" />
      <k-user-email-dialog ref="email" @success="update" />
      <k-user-language-dialog ref="language" @success="update" />
      <k-user-password-dialog ref="password" />
      <k-user-remove-dialog ref="remove" @success="update" />
      <k-user-rename-dialog ref="rename" @success="update" />
      <k-user-role-dialog ref="role" @success="update" />
    </template>

  </section>

</template>

<script>
import CollectionSectionMixin from "@/mixins/section/collection.js";

export default {
  mixins: [CollectionSectionMixin],
  computed: {
    add() {
      return this.options.add && this.$permissions.users.create;
    }
  },
  created() {
    this.load();
  },
  methods: {
    action(user, action) {
      switch (action) {
        case "edit":
          this.$router.push("/users/" + user.id);
          break;
        case "email":
          this.$refs.email.open(user.id);
          break;
        case "role":
          this.$refs.role.open(user.id);
          break;
        case "rename":
          this.$refs.rename.open(user.id);
          break;
        case "password":
          this.$refs.password.open(user.id);
          break;
        case "language":
          this.$refs.language.open(user.id);
          break;
        case "remove":
          this.$refs.remove.open(user.id);
          break;
      }
    },
    create() {
      if (this.add) {
        this.$refs.create.open();
      }
    },
    items(data) {
      return data.map(user => {
        user.options = ready => {
          this.$api.users
            .options(user.id, "list")
            .then(options => ready(options))
            .catch(error => {
              this.$store.dispatch("notification/error", error);
            });
        };

        return user;
      });
    },
    update() {
      this.reload();
      this.$events.$emit("model.update");
    }
  }
};
</script>
