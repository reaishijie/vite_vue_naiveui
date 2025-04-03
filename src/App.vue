<script setup lang="ts" name="App">
import { NDialogProvider, NMessageProvider } from 'naive-ui'
</script>

<template>
  <div>
    <n-dialog-provider>
      <n-message-provider>
        <router-view />
      </n-message-provider>
    </n-dialog-provider>
  </div>
</template>

<style scoped></style>