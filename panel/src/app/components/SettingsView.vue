<template>
  <k-inside
    class="k-settings-view"
    view="settings"
  >
    <k-view>
      <k-header>
        {{ $t('view.settings') }}
      </k-header>

      <section class="k-system-info mb-12">
        <header class="mb-3">
          <k-headline>Kirby</k-headline>
        </header>

        <k-auto-grid element="ul" style="--gap: 1.5rem" class="k-system-info-box bg-white p-3 shadow rounded-sm">
          <li>
            <dl>
              <dt class="text-sm text-gray mb-2">{{ $t('license') }}</dt>
              <dd class="truncate leading-tight">
                <k-link
                  v-if="license"
                  target="_blank"
                  to="https://licenses.getkirby.com"
                >
                  {{ license }}
                </k-link>
                <button
                  v-else
                  class="k-system-unregistered text-red font-bold"
                  type="button"
                  @click="$refs.registrationDialog.open()"
                >
                  {{ $t('license.unregistered') }}
              </button>
              </dd>
            </dl>
          </li>
          <li>
            <dl>
              <dt class="text-sm text-gray mb-2">{{ $t('version') }}</dt>
              <dd class="leading-tight">
                <k-link
                  target="_blank"
                  to="https://github.com/getkirby/kirby/releases"
                >
                  {{ version }}
                </k-link>
              </dd>
            </dl>
          </li>
        </k-auto-grid>
      </section>

      <!-- languages -->
      <k-languages v-if="multilang" />

      <!-- dialogs -->
      <k-registration-dialog ref="registrationDialog" />

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
.k-system-unregistered:focus {
  outline: none;
}
</style>
