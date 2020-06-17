<template>
  <k-users-view
    :loading="loading"
    :role="role"
    :roles="roles"
    :users="users"
    @role="onChangeRole"
    @reload="load"
  />
</template>

<script>
export default {
  props: {
    role: {
      type: String
    }
  },
  data() {
    return {
      limit: 20,
      loading: true,
      page: 1,
      roles: [],
      users: [],
    };
  },
  async created() {
    this.$model.system.title(this.$t("view.users"));
    const response = await this.$api.roles.list();
    this.roles = response.data;
    await this.load();
    this.loading = false;
  },
  watch: {
    role() {
      this.load();
    }
  },
  methods: {
    async load() {
      const response = await this.$api.users.list({
        page: this.page,
        limit: this.limit,
        role: this.role
      });

      this.users = response.data.map(user => {
        return {
          id: user.id,
          info: user.role.title,
          link: this.$model.users.link(user.id),
          options: async (ready) => {
            return ready(await this.$model.users.options(user.id))
          },
          preview: user.avatar ?
            {
              image: user.avatar.url,
              cover: true
            }
            :
            {
              icon: "user"
            },
          title: user.name || user.email,
        }
      });
    },
    onChangeRole(role) {
      const path = this.$model.users.link(null, role ? "role/" + role : null);
      this.$router.push(path);
    }
  }
}
</script>
