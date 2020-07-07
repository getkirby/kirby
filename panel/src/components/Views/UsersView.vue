<template>
  <k-error-view v-if="issue">
    {{ issue.message }}
  </k-error-view>
  <k-view v-else class="k-users-view">
    <k-header>
      {{ $t('view.users') }}
      <k-button-group slot="left">
        <k-button :disabled="$permissions.users.create === false" icon="add" @click="$refs.create.open()">{{ $t('user.create') }}</k-button>
      </k-button-group>
      <k-button-group slot="right">
        <k-dropdown>
          <k-button :responsive="true" icon="funnel" @click="$refs.roles.toggle()">
            {{ $t("role") }}: {{ role ? role.text : $t("role.all") }}
          </k-button>
          <k-dropdown-content ref="roles" align="right">
            <k-dropdown-item icon="bolt" @click="filter(false)">
              {{ $t("role.all") }}
            </k-dropdown-item>
            <hr>
            <k-dropdown-item
              v-for="role in roles"
              :key="role.value"
              icon="bolt"
              @click="filter(role)"
            >
              {{ role.text }}
            </k-dropdown-item>
          </k-dropdown-content>
        </k-dropdown>
      </k-button-group>
    </k-header>

    <template v-if="users.length > 0">
      <k-collection
        :items="users"
        :pagination="pagination"
        @paginate="paginate"
        @action="action"
      />
    </template>
    <template v-else-if="total === 0">
      <k-empty icon="users">{{ $t("role.empty") }}</k-empty>
    </template>

    <k-user-create-dialog ref="create" @success="fetch" />
    <k-user-email-dialog ref="email" @success="fetch" />
    <k-user-language-dialog ref="language" @success="fetch" />
    <k-user-password-dialog ref="password" />
    <k-user-remove-dialog ref="remove" @success="fetch" />
    <k-user-rename-dialog ref="rename" @success="fetch" />
    <k-user-role-dialog ref="role" @success="fetch" />

  </k-view>

</template>

<script>
export default {
  data() {
    return {
      page: 1,
      limit: 20,
      total: null,
      users: [],
      roles: [],
      issue: null
    };
  },
  computed: {
    pagination() {
      return {
        page: this.page,
        limit: this.limit,
        total: this.total
      };
    },
    role() {
      let currentRole = null;

      if (this.$route.params.role) {
        this.roles.forEach(role => {
          if (role.value === this.$route.params.role) {
            currentRole = role;
          }
        });
      }

      return currentRole;
    }
  },
  watch: {
    $route() {
      this.fetch();
    }
  },
  created() {
    this.$store.dispatch("content/current", null);
    this.$api.roles.options().then(roles => {
      this.roles = roles;
      this.fetch();
    });
  },
  methods: {
    fetch() {
      this.$store.dispatch("title", this.$t("view.users"));

      let query = {
        paginate: {
          page: this.page,
          limit: this.limit
        },
        sortBy: "username asc"
      };

      if (this.role) {
        query.filterBy = [
          {
            field: "role",
            operator: "==",
            value: this.role.value
          }
        ];
      }

      this.$api.users
        .list(query)
        .then(response => {
          this.users = response.data.map(user => {
            let item = {
              id: user.id,
              icon: { type: "user", back: "black" },
              text: user.name || user.email,
              info: user.role.title,
              link: "/users/" + user.id,
              options: ready => {
                this.$api.users
                  .options(user.id, "list")
                  .then(options => ready(options))
                  .catch(error => {
                    this.$store.dispatch("notification/error", error);
                  });
              },
              image: true
            };

            if (user.avatar) {
              item.image = {
                url: user.avatar.url,
                cover: true
              };
            }

            return item;
          });

          if (this.role) {
            this.$store.dispatch("breadcrumb", [
              {
                link: "/users/role/" + this.role.value,
                label: this.$t("role") + ": " + this.role.text
              }
            ]);
          } else {
            this.$store.dispatch("breadcrumb", []);
          }

          // keep the pagination updated
          this.total = response.pagination.total;

        })
        .catch(error => {
          this.issue = error;
        });
    },
    paginate(pagination) {
      this.page = pagination.page;
      this.limit = pagination.limit;
      this.fetch();
    },
    action(user, action) {
      switch (action) {
        case "edit":
          this.$go("/users/" + user.id);
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
    filter(role) {
      if (role === false) {
        this.$go("/users");
      } else {
        this.$go("/users/role/" + role.value);
      }

      this.$refs.roles.close();
    }
  }
};
</script>
