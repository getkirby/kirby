<template>
  <div class="k-registration">
    <p>{{ $t('license.unregistered') }}</p>
    <k-button
      :responsive="true"
      :tooltip="$t('license.unregistered')"
      class="k-topbar-signals-button"
      icon="key"
      @click="$dialog('registration')"
    >
      {{ $t('license.register') }}
    </k-button>
    <k-button
      :responsive="true"
      class="k-topbar-signals-button"
      link="https://getkirby.com/buy"
      target="_blank"
      icon="cart"
    >
      {{ $t('license.buy') }}
    </k-button>
  </div>
</template>

<style>
.k-registration {
  display: flex;
  margin-inline-end: 1rem;
  align-items: center;
}
.k-registration p {
  color: var(--color-negative-light);
  font-size: var(--text-sm);
  margin-inline-end: 1rem;
  font-weight: 600;
  display: none;
}
@media screen and (min-width: 90em) {
  .k-registration p {
    display: block;
  }
}
.k-registration .k-button {
  color: var(--color-white);
}
</style>