<template>

  <k-dropdown v-if="entries.length > 0" class="k-topbar-menu k-form-indicator">
    <k-button
      icon="notes"
      :tooltip="$t('form.changes.unsaved')"
      @click="$refs.changes.toggle()"
    >
      {{ entries.length }}
    </k-button>

    <k-dropdown-content ref="changes" align="right" class="k-topbar-menu">

      <ul>
        <li v-for="entry in entries" :key="entry.link">
          <k-dropdown-item
            :link="entry.link"
            :icon="entry.icon"
            :theme="entry.theme"
          >
            {{ entry.text }}
          </k-dropdown-item>
        </li>
        <li><hr></li>
        <li class="k-form-indicator-btns">
          <k-button icon="undo" @click="$refs.revert.open()">
            {{ $t("revert") }}
          </k-button>
          <k-button icon="check" @click="$refs.save.open()">
            {{ $t("save") }}
          </k-button>
        </li>
      </ul>
    </k-dropdown-content>

    <k-dialog
      ref="save"
      button="Save all"
      theme="positive"
      icon="check"
      @submit="save"
    >
      <k-text>
        Do you really want to save all changes?
        <ul>
          <li v-for="entry in entries" :key="entry.link">
            <strong>{{ entry.text }}</strong>
          </li>
        </ul>
      </k-text>
    </k-dialog>

    <k-dialog
      ref="revert"
      button="Revert all"
      theme="negative"
      icon="undo"
      @submit="revert"
    >
      <k-text>
        Do you really want to revert all changes?
        <ul>
          <li v-for="entry in entries" :key="entry.link">
            <strong>{{ entry.text }}</strong>
          </li>
        </ul>
      </k-text>
    </k-dialog>


  </k-dropdown>

</template>

<script>
export default {
  data() {
    return {
      entries: []
    }
  },
  computed: {
    changes() {
      return this.$store.getters["form/changes"]();
    },
    errors() {
      return this.$store.getters["form/errors"]();
    }
  },
  watch: {
    changes() {
      this.refresh();
    },
    errors() {
      this.refresh();
    }
  },
  mounted() {
    this.refresh();
  },
  methods: {
    type(entry) {
      if (entry.startsWith('/users')) {
        return 'user';
      }

      if (entry.startsWith('/pages') && entry.includes('/files/')) {
        return 'file';
      }

      return 'page';
    },
    icon(entry) {
      let type = this.type(entry);
      return type === 'file' ? 'image' : type;
    },
    refresh() {
      let entries = Object.keys(this.changes).map(change => {
        return this.$api.get(change).then(model => {
          return {
            type: this.type(change),
            text: model.title || model.filename || model.email,
            link: change,
            icon: this.icon(change),
            count: this.changes[change] ? Object.keys(this.changes[change]).length : 0,
            theme: this.$store.getters["form/hasErrors"](change) ? 'negative' : null,
            model: model
          };
        });
      });

      Promise.all(entries).then(results => {
        this.entries = results.sort((a, b) => a.type > b.type);
      });
    },
    revert() {
      this.$refs.revert.close();
      this.$refs.changes.close();
      this.$store.dispatch("form/reset");
    },
    save() {
      this.$refs.save.close();
      this.$refs.changes.close();

      let errors  = {};
      let updates = this.entries.map(change => {
        return this.$store.dispatch("form/save", change.link).then(() => {
          this.$store.dispatch("form/errors", [change.link, {}]);
        }).catch(response => {
          errors[change.link] = response;
          this.$store.dispatch("form/errors", [change.link, errors[change.link]]);
        });
      });

      Promise.all(updates).then(() => {
        this.$events.$emit("model.update");

        if (Object.keys(errors).length === 0) {
          this.$store.dispatch("notification/success", this.$t("saved"));
        } else {
          this.$refs.changes.open();
          this.$store.dispatch("notification/error", {
            message: "Not all changes could be saved",
            details: Object.keys(errors).map(error => {
              return {
                label: this.entries.find(x => x.link === error).text,
                message: errors[error].message
              }
            })
          });
        }
      });
    }
  }
};
</script>

<style lang="scss">
.k-form-indicator {
  display: inline-block;
}
.k-form-indicator-title {
  padding: 0.75rem;
  font-size: $font-size-small;
  white-space: nowrap;
  font-weight: $font-weight-bold;
}
.k-form-indicator .k-dialog {
  color: $color-dark;
}
.k-form-indicator-btns {
  display: flex;
  justify-content: space-between;

}
</style>
