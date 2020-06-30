<template>
  <ul v-if="value" class="k-users-field-preview">
    <li v-for="user in value" :key="user.email">
      <figure>
        <k-link :title="user.email" :to="$api.users.link(user.id)" @click.native.stop>
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
    value: Array,
  }
}
</script>

<style lang="scss">

.k-users-field-preview {
  padding: 0 .25rem 0 .75rem;
  display: flex;
}
.k-users-field-preview li {
  line-height: 0;
  margin-right: .5rem;
}
.k-users-field-preview .k-link {
  display: flex;
  align-items: stretch;
  background: $color-background;
  box-shadow: $box-shadow-card;
}
.k-users-field-preview-avatar {
  width: 1.525rem;
  height: 1.525rem;
  color: $color-light-grey !important;
}
.k-users-field-preview-avatar.k-image {
  display: block;
}
.k-users-field-preview figcaption {
  flex-grow: 1;
  line-height: 1.5em;
  padding: 0 .5rem;
  border: 1px solid $color-border;
  border-left: 0;
  border-radius: $border-radius;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

</style>
