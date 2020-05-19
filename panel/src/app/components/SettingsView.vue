<template>
  <k-inside class="k-settings-view">
    <k-view>
      <k-header>
        {{ $t('view.settings') }}
      </k-header>

      <section class="k-system-info mb-12">
        <header class="mb-3">
          <k-headline>Kirby</k-headline>
        </header>

        <ul class="k-system-info-box bg-white p-3 flex items-center shadow rounded-sm">
          <li>
            <dl>
              <dt class="text-sm text-gray mb-2">{{ $t('license') }}</dt>
              <dd>
                <template v-if="license">
                  {{ license }}
                </template>
                <button
                  v-else
                  class="k-system-unregistered text-red font-bold"
                  type="button"
                  @click="$emit('register')"
                >
                  {{ $t('license.unregistered') }}
              </button>
              </dd>
            </dl>
          </li>
          <li>
            <dl>
              <dt class="text-sm text-gray mb-2">{{ $t('version') }}</dt>
              <dd>{{ version }}</dd>
            </dl>
          </li>
        </ul>
      </section>

      <k-languages v-if="multilang" />

    </k-view>
  </k-inside>
</template>

<script>
export default {
  props: {
    license: String,
    multilang: Boolean,
    version: String
  },
};
</script>

<style>
.k-system-info-box li {
  flex-shrink: 0;
  flex-grow: 1;
  flex-basis: 0;
}
</style>
