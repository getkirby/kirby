<template>
  <k-dropdown>
    <k-toolbar-button
      command="headings"
      icon="title"
      @click="$refs.options.toggle()"
    />
    <k-dropdown-content ref="options">
      <k-dropdown-item icon="title" @click="$emit('command', 'heading', 1)">{{ $t('toolbar.button.heading.1') }}</k-dropdown-item>
      <k-dropdown-item icon="title" @click="$emit('command', 'heading', 2)">{{ $t('toolbar.button.heading.2') }}</k-dropdown-item>
      <k-dropdown-item icon="title" @click="$emit('command', 'heading', 3)">{{ $t('toolbar.button.heading.2') }}</k-dropdown-item>
    </k-dropdown-content>
  </k-dropdown>
</template>

