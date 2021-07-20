<template>
  <k-inside>
    <k-view class="k-users-view">
      <k-header>
        {{ $t('view.users') }}

        <template #left>
          <k-button-group>
            <k-button
              :disabled="$permissions.users.create === false"
              icon="add"
              @click="$dialog('users/create')"
            >
              {{ $t('user.create') }}
            </k-button>
          </k-button-group>
        </template>

        <template #right>
          <k-button-group>
            <k-dropdown>
              <k-button
                :responsive="true"
                icon="funnel"
                @click="$refs.roles.toggle()"
              >
                {{ $t("role") }}: {{ role ? role.title : $t("role.all") }}
              </k-button>
              <k-dropdown-content ref="roles" align="right">
                <k-dropdown-item icon="bolt" link="/users">
                  {{ $t("role.all") }}
                </k-dropdown-item>
                <hr>
                <k-dropdown-item
                  v-for="roleFilter in roles"
                  :key="roleFilter.id"
                  :link="'/users/?role=' + roleFilter.id"
                  icon="bolt"
                >
                  {{ roleFilter.title }}
                </k-dropdown-item>
              </k-dropdown-content>
            </k-dropdown>
          </k-button-group>
        </template>
      </k-header>

      <template v-if="users.data.length > 0">
        <k-collection
          :items="items"
          :pagination="users.pagination"
          @paginate="paginate"
        />
      </template>
      <template v-else-if="users.pagination.total === 0">
        <k-empty icon="users">
          {{ $t("role.empty") }}
        </k-empty>
      </template>
    </k-view>
  </k-inside>
</template>

<script>
export default {
  props: {
    role: Object,
    roles: Array,
    search: String,
    title: String,
    users: Object
  },
  computed: {
    items() {
      return this.users.data.map(user => {
        user.options = this.$dropdown(this.$api.users.url(user.id));
        return user;
      })
    }
  },
  methods: {
    paginate(pagination) {
      this.$reload({
        query: {
          page: pagination.page
        }
      });
    }
  }
};
</script>
