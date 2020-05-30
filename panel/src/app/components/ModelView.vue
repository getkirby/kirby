<template>
  <k-view
    :data-loading="saving"
    class="pb-24"
    @keydown.meta.s.native.prevent="$emit('save', value)"
  >

    <!-- header -->
    <k-header
      :editable="rename && lock === false"
      :tab="tab"
      :tabs="tabs"
      @edit="$emit('rename')"
    >
      {{ title || $t("untitled") + " …" }}
      <template v-slot:left>
        <k-button-group>
          <slot name="options" />
          <k-dropdown v-if="options.length">
            <k-button
              :disabled="lock !== false"
              :responsive="true"
              :text="$t('settings')"
              icon="cog"
              @click="$refs.settings.toggle()"
            />
            <k-dropdown-content
              ref="settings"
              :options="options"
              @option="onOption"
            />
          </k-dropdown>
          <k-languages-dropdown />
        </k-button-group>
      </template>

      <template v-slot:right>
        <k-prev-next :prev="prev" :next="next" />
      </template>
    </k-header>

    <!-- columns -->
    <k-sections
      :columns="columns"
      :lock="lock"
      :value="value"
      v-on="$listeners"
      @submit="$emit('save', value)"
    />

    <!-- footer -->
    <slot name="footer" />

    <!-- form buttons -->
    <portal>
      <k-form-buttons
        v-if="unlocked"
        :dir="$direction"
        class="k-model-form-buttons"
        mode="unlock"
        v-on="$listeners"
      />
      <k-form-buttons
        v-else-if="lock"
        :dir="$direction"
        :lock="lock"
        class="k-model-form-buttons"
        mode="lock"
        v-on="$listeners"
      />
      <k-form-buttons
        v-else-if="changes"
        :dir="$direction"
        :saving="saving"
        class="k-model-form-buttons"
        mode="changes"
        v-on="$listeners"
      />
    </portal>

  </k-view>
</template>

<script>
export default {
  props: {
    breadcrumb: {
      type: Array,
      default() {
        return [];
      }
    },
    changes: {
      type: Boolean,
      default: false
    },
    columns: {
      type: Array,
      default() {
        return [];
      }
    },
    lock: {
      type: [Boolean, Object],
      default: false
    },
    next: {
      type: Object,
    },
    options: {
      type: Array,
      default() {
        return [];
      }
    },
    prev: {
      type: Object,
    },
    rename: {
      type: Boolean,
      default: false
    },
    saving: {
      type: Boolean,
      default: false,
    },
    tab: {
      type: String,
      default: ""
    },
    tabs: {
      type: Array,
      default() {
        return [];
      }
    },
    title: {
      type: String,
      default: ""
    },
    unlocked: {
      type: Boolean,
      default: false
    },
    value: {
      type: Object,
      default() {
        return {};
      }
    }
  },
  methods: {
    onOption(option, item, itemIndex) {
      this.$emit("option", option, item, itemIndex);
    }
  }
};
</script>

<style>
.k-model-form-buttons {
  position: fixed;
  right: 0;
  bottom: 0;
  left: 0;
}
</style>
