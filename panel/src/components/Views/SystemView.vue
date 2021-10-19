<template>
  <k-inside>
    <k-view class="k-system-view">
      <k-header>
        {{ $t('view.system') }}
      </k-header>
      <section class="k-system-view-section">
        <header class="k-system-view-section-header">
          <k-headline>Kirby</k-headline>
        </header>

        <ul class="k-system-info-box">
          <li>
            <dl>
              <dt>{{ $t('license') }}</dt>
              <dd>
                <template v-if="$license">
                  {{ license }}
                </template>
                <button v-else class="k-system-unregistered" @click="$dialog('registration')">
                  {{ $t('license.unregistered') }}
                </button>
              </dd>
            </dl>
          </li>
          <li>
            <dl>
              <dt>{{ $t('version') }}</dt>
              <dd dir="ltr">
                {{ version }}
              </dd>
            </dl>
          </li>
        </ul>
      </section>

      <section class="k-system-view-section">
        <header class="k-system-view-section-header">
          <k-headline>Check</k-headline>
        </header>

        <ul class="k-system-info-box">
          <li>
            <dl>
              <dt>Debug mode</dt>
              <dd>
                {{ debug ? 'on' : 'off' }}
              </dd>
            </dl>
          </li>
          <li>
            <dl>
              <dt>SSL</dt>
              <dd>
                {{ ssl ? 'on' : 'off' }}
              </dd>
            </dl>
          </li>
          <li>
            <dl>
              <dt>PHP version</dt>
              <dd>
                {{ php }}
              </dd>
            </dl>
          </li>
        </ul>
      </section>

      <section class="k-system-view-section">
        <header class="k-system-view-section-header">
          <k-headline>Plugins</k-headline>
        </header>
        <table class="k-system-plugins">
          <tr>
            <th>Name</th>
            <th>Author</th>
            <th>License</th>
            <th>Version</th>
          </tr>
          <tr v-for="plugin in plugins" :key="plugin.name">
            <td>{{ plugin.name }}</td>
            <td>{{ plugin.author || "-" }}</td>
            <td>{{ plugin.license || "-" }}</td>
            <td>{{ plugin.version || "-" }}</td>
          </tr>
        </table>
      </section>
    </k-view>
  </k-inside>
</template>

<script>
export default {
  props: {
    debug: Boolean,
    license: String,
    php: String,
    plugins: Array,
    ssl: Boolean,
    version: String
  }
};
</script>

<style>
.k-system-view .k-header {
  margin-bottom: 1.5rem;
}
.k-system-view-section-header {
  margin-bottom: .5rem;
}
.k-system-view-section {
  margin-bottom: 3rem;
}

.k-system-info-box {
  display: grid;
  grid-gap: 1px;
  grid-template-columns: 1fr 1fr;
}
.k-system-info-box li {
  padding: .75rem;
  background: var(--color-white);
}

@media screen and (max-width: 40em){
  .k-system-info-box {
    flex-direction: column;
  }

  .k-system-info-box li:not(:last-child) {
    margin-bottom: .5rem;
  }
}

.k-system-info-box dt {
  font-size: var(--text-sm);
  color: var(--color-gray-600);
  margin-bottom: .25rem;
}
.k-system-unregistered {
  color: var(--color-negative);
  font-weight: var(--font-bold);
}

.k-system-plugins {
  width: 100%;
  border-spacing: 1px;
}
.k-system-plugins th,
.k-system-plugins td {
  text-align: left;
  padding: .75rem;
  font-weight: var(--font-normal);
  background: var(--color-white);
}
.k-system-plugins th {
  font-size: var(--text-sm);
  color: var(--color-gray-600);
}
</style>
