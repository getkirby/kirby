<template>
  <div class="k-user-info">
    <k-image
      v-if="user.avatar"
      :cover="true"
      :src="user.avatar.url"
      ratio="1/1"
    />
    <k-icon v-else type="user" />

    {{ user.name || user.email || user }}
  </div>
</template>

<script>
export default {
  props: {
    user: [Object, String]
  }
};
</script>

<style>
.k-user-info {
  display: flex;
  align-items: center;
  padding: 1.5rem 0 2rem;
}

.k-user-info .k-image {
  width: 1.5rem;
  margin-right: 0.7rem;
}

.k-user-info .k-icon {
  margin-right: 0.5rem;
}
</style>
