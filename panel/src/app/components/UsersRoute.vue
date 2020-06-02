<template>
  <k-users-view
    :role="role"
    :roles="roles"
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
  async created() {
    this.roles = await this.$api.roles.list().then(response => {
      return response.data
    });
  }
}
</script>
