<template>
  <kirby-error-view v-if="issue">
    {{ issue.message }}
  </kirby-error-view>
  <kirby-view v-else class="kirby-users-view">
    <kirby-header>
      {{ $t('view.users') }}
      <kirby-button-group slot="left">
        <kirby-button :disabled="$permissions.users.create === false" icon="add" @click="$refs.create.open()">{{ $t('user.create') }}</kirby-button>
      </kirby-button-group>
      <kirby-button-group slot="right">
        <kirby-dropdown>
          <kirby-button icon="funnel" @click="$refs.roles.toggle()">
            {{ $t("user.role") }}: {{ role ? role.text : $t("user.role.all") }}
          </kirby-button>
          <kirby-dropdown-content ref="roles" align="right">
            <kirby-dropdown-item icon="bolt" @click="filter(false)">
              {{ $t("user.role.all") }}
            </kirby-dropdown-item>
            <hr>
            <kirby-dropdown-item
              v-for="role in roles"
              :key="role.value"
              icon="bolt"
              @click="filter(role)"
            >
              {{ role.text }}
            </kirby-dropdown-item>
          </kirby-dropdown-content>
        </kirby-dropdown>
      </kirby-button-group>
    </kirby-header>

    <template v-if="users.length > 0">
      <kirby-collection
        :items="users"
        :pagination="pagination"
        @paginate="paginate"
        @action="action"
      />
    </template>
    <template v-else-if="total === 0">
      <kirby-box :text="$t('user.none')" />
    </template>

    <kirby-user-create-dialog ref="create" @success="fetch" />
    <kirby-user-email-dialog ref="email" @success="fetch" />
    <kirby-user-language-dialog ref="language" @success="fetch" />
    <kirby-user-password-dialog ref="password" />
    <kirby-user-remove-dialog ref="remove" @success="fetch" />
    <kirby-user-rename-dialog ref="rename" @success="fetch" />
    <kirby-user-role-dialog ref="role" @success="fetch" />

  </kirby-view>

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
        limit: this.limit
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
        }
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
          this.total = response.pagination.total;
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
                  .then(options => ready(options));
              },
              image: null
            };

            if (user.avatar.exists === true) {
              item.image = {
                url: user.avatar.url + "?v=" + user.avatar.modified,
                cover: true
              };
            }

            return item;
          });

          if (this.role) {
            this.$store.dispatch("breadcrumb", [
              {
                link: "/users/role/" + this.role.value,
                label: this.$t("user.role") + ": " + this.role.text
              }
            ]);
          } else {
            this.$store.dispatch("breadcrumb", []);
          }
        })
        .catch(error => {
          this.issue = error;
        });
    },
    paginate(pagination) {
      this.page = pagination.page;
      this.limit = pagination.limit;
    },
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
    filter(role) {
      if (role === false) {
        this.$router.push("/users");
      } else {
        this.$router.push("/users/role/" + role.value);
      }

      this.$refs.roles.close();
    }
  }
};
</script>
