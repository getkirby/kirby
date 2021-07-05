<template>
  <div
    :data-language="$language.code"
    :dir="$translation.direction"
    class="k-panel k-panel-outside"
  >
    <slot />
  </div>
</template>