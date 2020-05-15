<template>
  <k-topbar
    :breadcrumb="breadcrumb"
    :menu="menu"
  />
</template>

<script>
export default {
  props: {
    breadcrumb: Array,
  },
  computed: {
    menu() {
      return [
        {
          link: 'site',
          icon: 'page',
          text: 'Site'
        },
        {
          link: 'users',
          icon: 'users',
          text: 'Users',
          current: 'page',
          color: 'blue'
        },
        {
          link: 'settings',
          icon: 'settings',
          text: 'Settings'
        },
        '-',
        {
          link: 'account',
          icon: 'account',
          text: 'Your account'
        },
        '-',
        {
          link: 'logout',
          icon: 'logout',
          text: 'Logout'
        },
      ];
    }
  }
};
</script>
