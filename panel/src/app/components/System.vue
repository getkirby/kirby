<template>
  <section class="k-system mb-12">
    <header class="mb-3">
      <k-headline>Kirby</k-headline>
    </header>

    <ul class="k-system-box">
      <li>
        <dl>
          <dt>{{ $t('license') }}</dt>
          <dd>
            <template v-if="license">
              {{ license }}
            </template>
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
          <dt>{{ $t('version') }}</dt>
          <dd>{{ version || "–" }}</dd>
        </dl>
      </li>
    </ul>

    <!-- update -->
    <ul v-if="!isDisabled" class="k-system-box">
      <li>
        <dl>
          <dt>{{ $t('update.latest') }}</dt>
          <dd>
            <k-button v-bind="latest" />
          </dd>
        </dl>
      </li>
      <li>
        <dl>
          <dt>{{ $t('update.status') }}</dt>
          <dd>
            <k-button v-bind="status" />
          </dd>
        </dl>
      </li>
      <li>
        <dl>
          <dt>
            {{ $t('update.checked', { last: updated }) }}
          </dt>
          <dd>
            <k-button icon="refresh" @click="$emit('update')">
              {{ $t('update.check') }}
            </k-button>
          </dd>
        </dl>
      </li>
    </ul>

    <!-- incidents -->
    <ul
      v-if="isChecked && update.incidents.length"
      class="k-system-box k-system-incidents"
    >
      <li>
        <dl>
          <dt>{{ $t('update.incidents') }}</dt>
          <dd>
            <k-button
              v-for="incident in update.incidents"
              :key="incident.id"
              v-bind="getIncident(incident)"
            />
          </dd>
        </dl>
      </li>
    </ul>

    <!-- dialogs -->
    <k-registration-dialog ref="registrationDialog" />
  </section>
</template>

<script>
export default {
  props: {
    license: String,
    links: Object,
    update: [Object, Boolean],
    version: String
  },
  computed: {
    isChecked() {
      return this.update && this.update.hasOwnProperty("status");
    },
    isDisabled() {
      return this.update === false;
    },
    latest() {
      if (this.isChecked === false) {
        return { disabled: true, text: "–" };
      }

      return {
        link: this.update.latestUrl,
        text: this.update.latest
      };
    },
    status() {
      if (this.isChecked === false) {
        return { disabled: true, text: "–" };
      }

      return {
        class: "text-" + this.statusColor(this.update.status, this.update.severity),
        link: this.update.latestUrl,
        text: this.$t("update.status." + this.update.status)
      };
    },
    updated() {
      if (!this.isChecked) {
        return "–";
      }

      return this.$library.dayjs.unix(this.update.updated).format("YYYY-MM-DD");
    }
  },
  methods: {
    getIncident(incident) {
      let icon = null;

      if (incident.severity === "minor") {
        icon = "bug";
      } else if (incident.severity === "notable") {
        icon = "alert";
      } else if (incident.severity === "major") {
        icon = "bolt";
      }

      return {
        color: this.statusColor("at-risk", incident.severity),
        link: "https://getkirby.com/security",
        icon: icon,
        text: `
         ${incident.description} [${incident.severity} - fixed in ${incident.fixed}]
        `
      };
    },
    statusColor(status, severity) {
      if (status === "ok") {
        return "positive";
      }

      if (status === "outdated") {
       return "blue";
      }

      if (status === "at-risk") {
        if (severity === "minor") {
          return "yellow";
        }

        if (severity === "notable") {
          return "orange";
        }

        if (severity === "major") {
          return "negative";
        }
      }
    }
  }
}
</script>

<style lang="scss">
.k-system-box {
  display: flex;
  margin-top: .75rem;
  padding: .75rem;
  align-items: center;
  background: $color-white;
  box-shadow: $shadow;
  border-radius: $rounded-sm;
}
.k-system-box li {
  flex-shrink: 0;
  flex-grow: 1;
  flex-basis: 0;
}
.k-system-box dt {
  font-size: $text-sm;
  color: $color-gray-600;
  margin-bottom: .5rem;
}
.k-system-box .k-button {
  font-size: 1rem;
}
.k-system-incidents {
  margin-top: .25rem;
}
.k-system-incidents .k-button {
  display: block;

  + .k-button {
    margin-top: .75rem;
  }
}
</style>
