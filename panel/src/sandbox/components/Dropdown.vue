<template>
  <k-view align="center">
    <k-dropdown>
      <k-button icon="settings" @click="$refs.settings.toggle()">Settings</k-button>
      <k-dropdown-content ref="settings">
        <k-dropdown-item icon="edit">Edit</k-dropdown-item>
        <k-dropdown-item icon="trash">Delete</k-dropdown-item>
      </k-dropdown-content>
    </k-dropdown>
  </k-view>
</template>
