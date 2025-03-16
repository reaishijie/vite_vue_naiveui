<script setup lang="ts" name="Log">
import {  } from 'vue'
</script>

<template>
    <div id="log">
    <n-card title="卡片">
      <n-timeline>
        <n-timeline-item content="更新记录" />
        <n-time time-zone="Asia/Shanghai" />
        <n-timeline-item time="" content="更新记录" />

        <n-timeline-item type="info" title="安装" content="icon库" time="2025-03-11 14:30" line-type="dashed"
          color="red" />

        <n-timeline-item time="2025-03-10 10:46" type="info" title="首次安装"
          content="vite+vue3+typescript+pinia+router+naive-ui" color="blue" />
      </n-timeline>
    </n-card>
  </div>
</template>

<style scoped>
  
</style>