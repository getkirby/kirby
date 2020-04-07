<template>
  <div class="k-card-items">
    <slot>
      <k-card-item
        v-for="(item, index) in items"
        :key="index"
        v-bind="item"
        v-on="$listeners"
      />
    </slot>
  </div>
</template>

<script>
export default {
  props: {
    items: Array
  }
};
</script>

<style lang="scss">
.k-card-items {
  display: grid;
  grid-gap: 1.5rem;
  grid-template-columns: repeat(auto-fit, minmax(12rem, 1fr));
  /**
    Making sure card doesn't break layout if
    in a parent narrower than 12rem

    TODO: refactor once min() is supported by all our browsers
    since LibSASS has its issues with min() we need to be tricky
    */
  grid-template-columns: repeat(auto-fill, minmax(#{"min(12rem, 100%)"}, 1fr));
}

@media screen and (min-width: $breakpoint-small) {
  .k-card-items[data-size="tiny"] {
    grid-template-columns: repeat(auto-fill, minmax(10rem, 1fr));
    /**
      Making sure card doesn't break layout if
      in a parent narrower than 12rem

      TODO: refactor once min() is supported by all our browsers
      since LibSASS has its issues with min() we need to be tricky
      */
    grid-template-columns: repeat(
      auto-fill,
      minmax(#{"min(10rem, 100%)"}, 1fr)
    );
  }
  .k-card-items[data-size="small"] {
    grid-template-columns: repeat(auto-fill, minmax(16rem, 1fr));
    /**
      Making sure card doesn't break layout if
      in a parent narrower than 16rem

      TODO: refactor once min() is supported by all our browsers
      since LibSASS has its issues with min() we need to be tricky
      */
    grid-template-columns: repeat(
      auto-fill,
      minmax(#{"min(16rem, 100%)"}, 1fr)
    );
  }
  .k-card-items[data-size="medium"] {
    grid-template-columns: repeat(auto-fill, minmax(24rem, 1fr));
    /**
      Making sure card doesn't break layout if
      in a parent narrower than 24rem

      TODO: refactor once min() is supported by all our browsers
      since LibSASS has its issues with min() we need to be tricky
      */
    grid-template-columns: repeat(
      auto-fill,
      minmax(#{"min(24rem, 100%)"}, 1fr)
    );
  }
  .k-card-items[data-size="large"],
  .k-card-items[data-size="huge"] {
    grid-template-columns: 1fr;
  }
}

@media screen and (min-width: $breakpoint-medium) {
  .k-card-items[data-size="large"] {
    grid-template-columns: repeat(auto-fill, minmax(32rem, 1fr));
    /**
      Making sure card doesn't break layout if
      in a parent narrower than 32rem

      TODO: refactor once min() is supported by all our browsers
      since LibSASS has its issues with min() we need to be tricky
     */
    grid-template-columns: repeat(
      auto-fill,
      minmax(#{"min(32rem, 100%)"}, 1fr)
    );
  }
}
</style>
