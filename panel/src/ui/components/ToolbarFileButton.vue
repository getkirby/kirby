<template>
  <k-dropdown>
    <k-toolbar-button
      command="file"
      icon="attachment"
      @click="$refs.options.toggle()"
    />
    <k-dropdown-content ref="options">
      <k-dropdown-item icon="check" @click="$emit('command', 'file', 'select')">{{ $t('toolbar.button.file.select') }}</k-dropdown-item>
      <k-dropdown-item icon="upload" @click="$emit('command', 'file', 'upload')">{{ $t('toolbar.button.file.upload') }}</k-dropdown-item>
    </k-dropdown-content>
  </k-dropdown>
</template>
