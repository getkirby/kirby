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
          <li>
            <dl>
              <dt>Kirby folder</dt>
              <dd :class="{ 'k-system-warning': kirby }">
                {{ kirby ? "exposed" : "hidden" }}
              </dd>
            </dl>
          </li>
          <li>
            <dl>
              <dt>Git Repo</dt>
              <dd :class="{ 'k-system-warning': git }">
                {{ git ? "exposed" : "hidden" }}
              </dd>
            </dl>
          </li>
          <li>
            <dl>
              <dt>Content folder</dt>
              <dd :class="{ 'k-system-warning': content }">
                {{ content ? "exposed" : "hidden" }}
              </dd>
            </dl>
          </li>
          <li>
            <dl>
              <dt>Site folder</dt>
              <dd :class="{ 'k-system-warning': site }">
                {{ site ? "exposed" : "hidden" }}
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
  created() {
    this.checkContent();
    this.checkGit();
    this.checkKirby();
    this.checkSite();
  },
  methods: {
    async checkContent() {
      this.content = await this.isAccessible(
        window.location.origin + "/content/site.txt"
      );
    },
    async checkGit() {
      this.git = await this.isAccessible(
        window.location.origin + "/.git/config"
      );
    },
    async checkKirby() {
      this.kirby = await this.isAccessible(
        window.location.origin + "/kirby/composer.json"
      );
    },
    async checkSite() {
      this.site = await this.isAccessible(
        window.location.origin + "/site/blueprints/site.yml"
      );
    },
    async isAccessible(url) {
      const response = await fetch(url, {
        cache: "no-store"
      });

      console.log(response);

      return response.status < 400;
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

.k-system-info-box li {
  padding: 0.75rem;
  background: var(--color-white);
}
.k-system-info-box dt {
  font-family: var(--font-mono);
  font-size: var(--text-xs);
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
</style>
