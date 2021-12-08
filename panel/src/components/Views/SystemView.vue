<template>
  <k-inside>
    <k-view class="k-system-view">
      <k-header>
        {{ $t("view.system") }}
      </k-header>
      <section class="k-system-view-section">
        <header class="k-system-view-section-header">
          <k-headline>Kirby</k-headline>
        </header>

        <ul class="k-system-info-box" style="--columns: 2">
          <li>
            <dl>
              <dt>{{ $t("license") }}</dt>
              <dd>
                <template v-if="$license">
                  {{ license }}
                </template>
                <k-button
                  v-else
                  class="k-system-warning"
                  @click="$dialog('registration')"
                >
                  {{ $t("license.unregistered") }}
                </k-button>
              </dd>
            </dl>
          </li>
          <li>
            <dl>
              <dt>{{ $t("version") }}</dt>
              <dd dir="ltr">
                <k-link
                  :to="
                    'https://github.com/getkirby/kirby/releases/tag/' + version
                  "
                >
                  {{ version }}
                </k-link>
              </dd>
            </dl>
          </li>
        </ul>
      </section>

      <section class="k-system-view-section">
        <header class="k-system-view-section-header">
          <k-headline>{{ $t("environment") }}</k-headline>
        </header>

        <ul class="k-system-info-box" style="--columns: 4">
          <li>
            <dl>
              <dt>{{ $t("debugging") }}</dt>
              <dd :class="{ 'k-system-warning': debug }">
                {{ debug ? $t("on") : $t("off") }}
              </dd>
            </dl>
          </li>
          <li>
            <dl>
              <dt>HTTPS</dt>
              <dd :class="{ 'k-system-warning': !https }">
                {{ https ? $t("on") : $t("off") }}
              </dd>
            </dl>
          </li>
          <li>
            <dl>
              <dt>PHP</dt>
              <dd>
                {{ php }}
              </dd>
            </dl>
          </li>
          <li>
            <dl>
              <dt>{{ $t("server") }}</dt>
              <dd>
                {{ server || "?" }}
              </dd>
            </dl>
          </li>
        </ul>
      </section>

      <section v-if="plugins.length" class="k-system-view-section">
        <header class="k-system-view-section-header">
          <k-headline link="https://getkirby.com/plugins">
            {{ $t("plugins") }}
          </k-headline>
        </header>
        <table class="k-system-plugins">
          <tr>
            <th>
              {{ $t("name") }}
            </th>
            <th class="desk">
              {{ $t("author") }}
            </th>
            <th class="desk">
              {{ $t("license") }}
            </th>
            <th style="width: 8rem">
              {{ $t("version") }}
            </th>
          </tr>
          <tr v-for="plugin in plugins" :key="plugin.name">
            <td>
              <k-link v-if="plugin.link" :to="plugin.link">
                {{ plugin.name }}
              </k-link>
              <template v-else>
                {{ plugin.name }}
              </template>
            </td>
            <td class="desk">
              {{ plugin.author || "-" }}
            </td>
            <td class="desk">
              {{ plugin.license || "-" }}
            </td>
            <td style="width: 8rem">
              {{ plugin.version || "-" }}
            </td>
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
    server: String,
    https: Boolean,
    version: String
  }
};
</script>

<style>
.k-system-view .k-header {
  margin-bottom: 1.5rem;
}
.k-system-view-section-header {
  margin-bottom: 0.5rem;
}
.k-system-view-section {
  margin-bottom: 3rem;
}

.k-system-info-box {
  display: grid;
  grid-gap: 1px;
  font-size: var(--text-sm);
}

@media screen and (min-width: 45rem) {
  .k-system-info-box {
    grid-template-columns: repeat(var(--columns), 1fr);
  }
}

.k-system-info-box li {
  padding: 0.75rem;
  background: var(--color-white);
}
.k-system-info-box dt {
  font-size: var(--text-sm);
  color: var(--color-gray-600);
  margin-bottom: 0.25rem;
}
.k-system-warning {
  color: var(--color-negative);
  font-weight: var(--font-bold);
  display: inline-flex;
}
.k-system-warning .k-button-text {
  opacity: 1;
}

.k-system-plugins {
  width: 100%;
  font-variant-numeric: tabular-nums;
  table-layout: fixed;
  border-spacing: 1px;
}
.k-system-plugins th,
.k-system-plugins td {
  text-align: left;
  padding: 0.75rem;
  font-weight: var(--font-normal);
  font-size: var(--text-sm);
  background: var(--color-white);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.k-system-plugins .desk {
  display: none;
}

@media screen and (min-width: 45rem) {
  .k-system-plugins .desk {
    display: table-cell;
  }
}

.k-system-plugins th {
  color: var(--color-gray-600);
}
</style>
