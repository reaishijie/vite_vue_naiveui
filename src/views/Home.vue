<script setup lang="ts" name="Home页面">
import { onMounted } from 'vue'
import { useUserStore } from '../store/user'
import { useRouter } from 'vue-router'
import { useMessage } from 'naive-ui'
const message = useMessage()
const router = useRouter()
const userStore = useUserStore() 

onMounted(() => {
  if (!userStore.logState) {
    message.error('请先登录账号')
    router.push('/login')
  }
})
</script>

<template>
  <h3>Home页面</h3>
</template>

<style scoped>
  
</style>