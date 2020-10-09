<template>
  <k-view align="center">
    <k-button icon="open" @click="$refs.dialog.open()">Open Dialog</k-button>
    <k-dialog ref="dialog">This is a nice dialog</k-dialog>
  </k-view>
</template>
