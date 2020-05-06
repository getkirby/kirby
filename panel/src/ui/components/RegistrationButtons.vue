<template>
  <div class="k-registration-buttons flex items-center text-sm">
    <p class="mr-3 text-red-light font-bold">{{ $t('license.unregistered') }}</p>
    <k-button
      :icon="{
        color: 'green-light',
        type: 'key'
      }"
      :responsive="true"
      :tooltip="$t('license.unregistered')"
      class="k-registration-button"
      @click="$emit('register')"
    >
      {{ $t('license.register') }}
    </k-button>
    <k-button
      :icon="{
        color: 'green-light',
        type: 'cart'
      }"
      :responsive="true"
      class="k-registration-button"
      link="https://getkirby.com/buy"
      target="_blank"
    >
      {{ $t('license.buy') }}
    </k-button>
  </div>
</template>

<style lang="scss">
.k-registration-buttons p {
  display: none;

  @media screen and (min-width: $breakpoint-lg) {
    display: block;
  }
}
.k-registration-button {
  padding: .75rem;
}
</style>
