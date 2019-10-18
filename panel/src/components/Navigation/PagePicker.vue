<template>
  <div class="k-page-picker">

    <template v-if="issue">
      <k-box :text="issue" theme="negative" />
    </template>

    <template v-else>

      <k-input
        v-if="search"
        :autofocus="true"
        :placeholder="$t('search') + ' …'"
        v-model="q"
        type="text"
        class="k-dialog-search"
        icon="search"
      />

      <template v-if="models.length">
        <k-list>
          <k-list-item
            v-for="page in models"
            :key="page.id"
            :text="page.text"
            :info="page.info"
            :image="page.image"
            :icon="page.icon"
            @click="onToggle(page)"
          >
            <template slot="options">
              <k-button
                v-if="isSelected(page)"
                :autofocus="true"
                :icon="checkedIcon"
                :tooltip="$t('remove')"
                theme="positive"
              />
              <k-button v-else
                :autofocus="true"
                :tooltip="$t('select')"
                icon="circle-outline"
              />
              <k-button
                :disabled="!page.hasChildren"
                :tooltip="$t('open')"
                icon="angle-right"
                @click.stop="go(page)"
              />
            </template>
          </k-list-item>
        </k-list>
        <k-pagination
          :details="true"
          :dropdown="false"
          v-bind="pagination"
          align="center"
          class="k-dialog-pagination"
          @paginate="onPaginate"
        />
      </template>
      <k-empty v-else icon="page">
        {{ $t("dialog.pages.empty") }}
      </k-empty>
    </template>

  </div>
</template>

<script>
import debounce from "@/helpers/debounce.js";

export default {
  inheritAttrs: false,
  props: {
    endpoint: String,
    limit: Number,
    multiple: Boolean,
    parent: String,
    selected: Array,
    search: Boolean,
  },
  created() {
    this.fetch();
  },
  data() {
    return {
      models: [],
      issue: null,
      pagination: {
        limit: this.limit,
        page: 1,
        total: 0
      },
      q: null,
    };
  },
  computed: {
    isMultiple() {
      return this.multiple === true && this.max !== 1;
    },
    checkedIcon() {
      return this.isMultiple === true ? "check" : "circle-filled";
    }
  },
  watch: {
    limit(limit) {
      this.pagination.limit = limit;
      this.fetch();
    },
    q: debounce(function () {
      this.pagination.page = 0;
      this.fetch();
    }, 200),
  },
  methods: {
    fetch() {
      const params = {
        page: this.pagination.page,
        limit: this.pagination.limit,
        search: this.q
      };

      return this.$api
        .get(this.endpoint, params)
        .then(response => {
          this.models     = response.data;
          this.pagination = response.pagination;
        })
        .catch(e => {
          this.models = [];
          this.issue  = e.message;
        });
    },
    isSelected() {

    },
    onToggle() {

    },
    onPaginate(pagination) {
      this.pagination.page  = pagination.page;
      this.pagination.limit = pagination.limit;
      this.fetch();
    }
  }
};
</script>

<style lang="scss">
</style>
