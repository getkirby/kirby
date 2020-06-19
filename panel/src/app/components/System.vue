<template>
  <section class="k-system-info mb-12">
    <header class="mb-3">
      <k-headline>Kirby</k-headline>
    </header>

    <k-auto-grid
      element="ul"
      style="--gap: 1.5rem"
      class="k-system-info-box bg-white p-3 shadow rounded-sm mb-2"
    >
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
          <dd class="truncate leading-tight">{{ version || "–" }}</dd>
        </dl>
      </li>
    </k-auto-grid>

    <!-- update -->
    <k-auto-grid
      v-if="!isDisabled"
      element="ul"
      style="--gap: 1.5rem"
      class="k-system-info-box bg-white p-3 shadow rounded-sm mb-2"
    >
      <li>
        <dl>
          <dt class="text-sm text-gray mb-2">{{ $t('update.latest') }}</dt>
          <dd class="truncate leading-tight"><k-button v-bind="latest" /></dd>
        </dl>
      </li>
      <li>
        <dl>
          <dt class="text-sm text-gray mb-2">{{ $t('update.status') }}</dt>
          <dd class="truncate leading-tight"><k-button v-bind="status" /></dd>
        </dl>
      </li>
      <li>
        <dl>
          <dt class="text-sm text-gray mb-2">{{ $t('update.checked', { date: updated }) }}</dt>
          <dd class="truncate leading-tight">
            <k-button icon="refresh" @click="$emit('update')">
              {{ $t('update.check') }}
            </k-button>
          </dd>
        </dl>
      </li>
    </k-auto-grid>

    <!-- incidents -->
    <k-auto-grid
      v-if="isChecked && update.incidents.length"
      element="ul"
      style="--gap: 1.5rem"
      class="k-system-info-box bg-white p-3 shadow rounded-sm"
    >
      <li>
        <dl>
          <dt class="text-sm text-gray mb-2">{{ $t('update.incidents') }}</dt>
          <dd class="truncate leading-tight">
            <k-button
              v-for="incident in update.incidents"
              :key="incident.id"
              v-bind="getIncident(incident)"
              class="mb-2"
            />
          </dd>
        </dl>
      </li>
    </k-auto-grid>

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
