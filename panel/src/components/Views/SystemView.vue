<template>
  <k-inside>
    <k-view class="k-system-view">
      <k-header>
        {{ $t("view.system") }}
      </k-header>
      <section class="k-system-view-section">
        <header class="k-system-view-section-header">
          <k-headline>{{ $t("environment") }}</k-headline>
        </header>

        <dl class="k-system-info-box" style="--columns: 2">
          <div>
            <dt>{{ $t("license") }}</dt>
            <dd :class="{ 'k-system-warning': !$license }">
              <template v-if="$license">
                {{ license }}
              </template>
              <k-button v-else @click="$dialog('registration')">
                {{ $t("license.unregistered") }}
              </k-button>
            </dd>
          </div>
          <div>
            <dt>Kirby</dt>
            <dd dir="ltr">
              <k-link
                :to="
                  'https://github.com/getkirby/kirby/releases/tag/' + version
                "
              >
                {{ version }}
              </k-link>
            </dd>
          </div>
          <div>
            <dt>PHP</dt>
            <dd>
              {{ php }}
            </dd>
          </div>
          <div>
            <dt>{{ $t("server") }}</dt>
            <dd>
              {{ server || "?" }}
            </dd>
          </div>
        </dl>
      </section>

      <section v-if="hasSecurityIssues" class="k-system-view-section">
        <header class="k-system-view-section-header">
          <k-headline>{{ $t("security") }}</k-headline>
          <k-button :tooltip="$t('retry')" icon="refresh" @click="retry" />
        </header>

        <ul class="k-system-security">
          <li v-if="debug">
            <k-link to="https://getkirby.com/security/debug">
              <k-icon type="alert" />
              <span>{{ $t("system.issues.debugging") }}</span>
            </k-link>
          </li>
          <li v-if="!https">
            <k-link to="https://getkirby.com/security/https">
              <k-icon type="alert" />
              <span>{{ $t("system.issues.https") }}</span>
            </k-link>
          </li>
          <li v-if="git">
            <k-link to="https://getkirby.com/security/git">
              <k-icon type="alert" />
              <span>{{ $t("system.issues.git") }}</span>
            </k-link>
          </li>
          <li v-if="content">
            <k-link to="https://getkirby.com/security/content">
              <k-icon type="alert" />
              <span>{{ $t("system.issues.content") }}</span>
            </k-link>
          </li>
          <li v-if="kirby">
            <k-link to="https://getkirby.com/security/kirby">
              <k-icon type="alert" />
              <span>{{ $t("system.issues.kirby") }}</span>
            </k-link>
          </li>
          <li v-if="site">
            <k-link to="https://getkirby.com/security/site">
              <k-icon type="alert" />
              <span>{{ $t("system.issues.site") }}</span>
            </k-link>
          </li>
        </ul>
      </section>

      <section v-if="plugins.length" class="k-system-view-section">
        <header class="k-system-view-section-header">
          <k-headline link="https://getkirby.com/plugins">
            {{ $t("plugins") }}
          </k-headline>
        </header>
        <k-table
          :index="false"
          :columns="{
            name: {
              label: $t('name'),
              type: 'url'
            },
            author: {
              label: $t('author')
            },
            license: {
              label: $t('license')
            },
            version: {
              label: $t('version'),
              width: '8rem'
            }
          }"
          :rows="plugins"
        />
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
    urls: Object,
    version: String
  },
  data() {
    return {
      content: null,
      git: null,
      kirby: null,
      site: null
    };
  },
  computed: {
    hasSecurityIssues() {
      return (
        this.content ||
        this.git ||
        this.kirby ||
        this.site ||
        !this.https ||
        this.debug
      );
    }
  },
  created() {
    console.info(
      "Running system health checks for the Panel system view; failed requests in the following console output are expected behavior."
    );
    this.check("content");
    this.check("git");
    this.check("kirby");
    this.check("site");
  },
  methods: {
    async check(key) {
      const url = this.urls[key];
      this[key] = !url ? false : await this.isAccessible(url);
    },
    async isAccessible(url) {
      const response = await fetch(url, {
        cache: "no-store"
      });

      return response.status < 400;
    },
    retry() {
      this.$go(window.location.href);
    }
  }
};
</script>

<style>
.k-system-view .k-header {
  margin-bottom: 1.5rem;
}
.k-system-view-section-header {
  margin-bottom: 0.5rem;
  display: flex;
  justify-content: space-between;
}
.k-system-view-section {
  margin-bottom: 3rem;
}

.k-system-info-box {
  display: grid;
  grid-gap: 1px;
  font-size: var(--text-sm);
  box-shadow: var(--shadow);
}

@media screen and (min-width: 45rem) {
  .k-system-info-box {
    grid-template-columns: repeat(var(--columns), 1fr);
  }
}

.k-system-info-box div {
  padding: 0.75rem;
  background: var(--color-white);
}
.k-system-info-box dt {
  font-family: var(--font-mono);
  font-size: var(--text-xs);
  color: var(--color-gray-600);
  margin-bottom: 0.25rem;
}
.k-system-info-box dd button {
  font: inherit;
  display: inline-flex;
}
.k-system-warning {
  color: var(--color-negative);
  font-weight: var(--font-bold);
  display: inline-flex;
}
.k-system-warning .k-button-text {
  font: inherit;
  opacity: 1;
}

.k-system-security a {
  font-size: var(--text-sm);
  display: grid;
  align-items: center;
  grid-template-columns: 2.5rem auto;
  background: var(--color-white);
  color: var(--color-black);
  margin-bottom: 1px;
  line-height: var(--leading-tight);
}
.k-system-security a:focus {
  position: relative;
  z-index: 1;
}
.k-system-security .k-icon {
  background: var(--color-red-200);
  color: var(--color-negative);
  display: grid;
  place-items: center;
  width: 2.5rem;
  height: 100%;
}
.k-system-security span:last-of-type {
  padding: var(--spacing-3);
}
.k-system-security span:last-of-type::after {
  content: "→";
  margin-inline-start: var(--spacing-2);
}
</style>
