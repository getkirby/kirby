<template>
  <k-user-view
    :user="user"
    @remove="onRemoved"
    @update="load"
  />
</template>
<script>
export default {
  props: {
    id: {
      type: String
    }
  },
  data() {
    return {
      user: {}
    };
  },
  created() {
    this.load();
  },
  watch: {
    "$route": "load",
  },
  methods: {
    async load() {
      this.user = await this.$model.user.get(this.id);
    },
    onRemoved() {
      this.$router.push("/users");
    },
  }
}
</script>
