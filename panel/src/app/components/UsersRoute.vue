<template>
  <k-users-view
    :role="role"
    :roles="roles"
    :users="users"
    @role="onChangeRole"
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
      roles: []
    };
  },
  computed: {
    users() {
      return async ({ page, limit }) => {
        const response = await this.$api.users.list({
          page: page,
          limit: limit,
          role: this.role
        });

        return response.data.map(user => {
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
      }
    }
  },
  async created() {
    const response = await this.$api.roles.list();
    this.roles = response.data;
  },
  methods: {
    onChangeRole(role) {
      const path = this.$model.users.link(null, role ? "role/" + role : null);
      this.$router.push(path);
    }
  }
}
</script>
