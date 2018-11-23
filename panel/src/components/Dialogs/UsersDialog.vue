<template>
  <k-dialog
    ref="dialog"
    class="k-users-dialog"
    size="medium"
    @cancel="$emit('cancel')"
    @submit="submit"
  >
    <template v-if="issue">
      <k-box :text="issue" theme="negative" />
    </template>
    <template v-else>
      <k-list v-if="users.length">
        <k-list-item
          v-for="(user, index) in users"
          :key="user.email"
          :text="user.username"
          :image="
            user.avatar ?
              {
                url: user.avatar.url,
                back: 'pattern',
                cover: true
              }
              :
              null
          "
          :icon="{
            type: 'user',
            back: 'black'
          }"
          @click="toggle(index)"
        >
          <k-button
            v-if="user.selected"
            slot="options"
            :autofocus="true"
            :icon="checkedIcon"
            :tooltip="$t('remove')"
            theme="positive"
          />
          <k-button
            v-else
            slot="options"
            :autofocus="true"
            :tooltip="$t('select')"
            icon="circle-outline"
          />
        </k-list-item>
      </k-list>
      <k-empty v-else icon="users">
        No users to select
      </k-empty>
    </template>
  </k-dialog>
</template>

<script>
export default {
  data() {
    return {
      users: [],
      issue: null,
      options: {
        max: null,
        multiple: true,
        selected: []
      }
    }
  },
  computed: {
    multiple() {
      return this.options.multiple === true && this.options.max !== 1;
    },
    checkedIcon() {
      return this.multiple === true ? "check" : "circle-filled";
    }
  },
  methods: {
    fetch() {

      this.users = [];

      return this.$api
        .get("users")
        .then(users => {

          const selected = this.options.selected || [];

          this.users = users.data.map(user => {
            user.selected = selected.indexOf(user.email) !== -1;
            return user;
          });

        })
        .catch(e => {
          this.users = [];
          this.issue = e.message;
        });
    },
    selected() {
      return this.users.filter(user => user.selected);
    },
    submit() {
      this.$emit("submit", this.selected());
      this.$refs.dialog.close();
    },
    toggle(index) {
      if (this.options.multiple === false) {
        this.users = this.users.map(user => {
          user.selected = false;
          return user;
        });
      }

      if (!this.users[index].selected) {
        if (this.options.max && this.options.max <= this.selected().length) {
          return;
        }
        this.users[index].selected = true;
      } else {
        this.users[index].selected = false;
      }
    },
    open(options) {
      this.options = options;
      this.fetch().then(() => {
        this.$refs.dialog.open();
      });
    }
  }
};
</script>

<style lang="scss">
.k-users-dialog .k-list-item {
  cursor: pointer;
}
.k-users-dialog .k-empty {
  border: 0;
}
</style>
