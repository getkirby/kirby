<template>
  <div :data-align="align" class="k-view">
    <!-- @slot where your content goes -->
    <slot />
  </div>
</template>

<script>
/**
* The View component is a very basic wrapper, that makes sure to keep the right padding around your interface and also limits the max width. You should use it for full screen layouts only.
* @example <k-view>
  <!-- your interface goes here -->
</k-view>
*/
export default {
  props: {
    /**
     * To center the content
     * @values centre
     */
    align: String
  }
};
</script>

<style>
.k-view {
  padding-inline: 1.5rem;
  margin: 0 auto;
  max-width: 100rem;
}
@media screen and (min-width: 30em) {
  .k-view {
    padding-inline: 3rem;
  }
}

@media screen and (min-width: 90em) {
  .k-view {
    padding-inline: 6rem;
  }
}

.k-view[data-align="center"] {
  height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 3rem;
  overflow: auto;
}
.k-view[data-align="center"] > * {
  flex-basis: 22.5rem;
}
</style>
