<template>
  <ul v-if="value" class="k-users-field-preview">
    <li v-for="user in value" :key="user.email">
      <figure>
        <k-link :title="user.email" :to="user.link" @click.native.stop>
          <k-image
            v-if="user.avatar"
            :src="user.avatar.url"
            class="k-users-field-preview-avatar"
            back="pattern"
          />
          <k-icon
            v-else
            type="user"
            back="pattern"
            class="k-users-field-preview-avatar"
          />
          <figcaption>
            {{ user.username }}
          </figcaption>
        </k-link>
      </figure>
    </li>
  </ul>
</template>

<script>
export default {
  props: {
    value: Array
  }
};
</script>

<style>
.k-users-field-preview {
  padding: 0 0.25rem 0 0.75rem;
  display: flex;
}
.k-users-field-preview li {
  line-height: 0;
  margin-inline-end: 0.5rem;
}
.k-users-field-preview .k-link {
  display: flex;
  align-items: stretch;
  background: var(--color-background);
  box-shadow: var(--shadow);
}
.k-users-field-preview-avatar {
  width: 1.525rem;
  height: 1.525rem;
  color: var(--color-gray-500) !important;
}
.k-users-field-preview-avatar.k-image {
  display: block;
}
.k-users-field-preview figcaption {
  flex-grow: 1;
  line-height: 1.5em;
  padding-inline: 0.5rem;
  border: 1px solid var(--color-border);
  border-inline-start: 0;
  border-radius: var(--rounded-xs);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
