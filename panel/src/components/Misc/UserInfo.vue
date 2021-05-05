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
  line-height: 1;
  font-size: var(--text-sm);
}
.k-user-info .k-image {
  width: 1.5rem;
  margin-right: .75rem;
}
.k-user-info .k-icon {
  width: 1.5rem;
  height: 1.5rem;
  margin-right: .75rem;
  background: var(--color-black);
  color: var(--color-white);
}
</style>
