<template>
  <div class="k-login-alert" @click="$emit('click')">
    <span><slot /></span>
    <k-icon type="alert" />
  </div>
</template>

<style>
.k-login-alert {
  padding: 0.5rem 0.75rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  min-height: 38px;
  margin-bottom: 2rem;
  background: var(--color-negative);
  color: var(--color-white);
  font-size: var(--text-sm);
  border-radius: var(--rounded-xs);
  box-shadow: var(--shadow-lg);
  cursor: pointer;
}
</style>
