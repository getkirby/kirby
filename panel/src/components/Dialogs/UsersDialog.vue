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
      <k-list v-if="models.length">
        <k-list-item
          v-for="(user, index) in models"
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
        {{ $t("dialog.users.empty") }}
      </k-empty>
    </template>
  </k-dialog>
</template>

<script>
import picker from "@/mixins/picker/dialog.js";

export default {
  mixins: [picker],
  computed: {
    id() {
      return "email";
    }
  },
  methods: {
    isFiltered(user) {
      return user.email.includes(this.search) ||
             user.username.includes(this.search);
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
.k-users-dialog-search {
  margin-bottom: 0.5rem;
}
</style>
